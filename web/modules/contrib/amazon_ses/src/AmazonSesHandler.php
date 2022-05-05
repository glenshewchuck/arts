<?php

namespace Drupal\amazon_ses;

use Aws\Exception\CredentialsException;
use Aws\Result;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Mime\Email;

/**
 * Amazon SES service.
 */
class AmazonSesHandler implements AmazonSesHandlerInterface {
  use StringTranslationTrait;

  /**
   * The AWS SesClient.
   *
   * @var \Aws\Ses\SesClient
   */
  protected $client;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs the service.
   *
   * @param \Aws\Ses\SesClient $client
   *   The AWS SesClient.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(SesClient $client, LoggerChannelInterface $logger, MessengerInterface $messenger, ConfigFactoryInterface $config_factory) {
    $this->client = $client;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->config = $config_factory->get('amazon_ses.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function send(Email $email) {
    try {
      $result = $this->client->sendRawEmail([
        'RawMessage' => [
          'Data' => $email->toString(),
        ],
      ]);

      $throttle = $this->config->get('throttle');
      if ($throttle) {
        $sleep_time = $this->getSleepTime();
        usleep($sleep_time);
      }

      return $result['MessageId'];
    }
    catch (CredentialsException $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
    catch (SesException $e) {
      $this->logger->error($e->getAwsErrorMessage());
      return FALSE;
    }
  }

  /**
   * Get the number of microseconds to pause for throttling.
   *
   * @return int
   *   The time to sleep in microseconds.
   */
  protected function getSleepTime() {
    $result = $this->client->getSendQuota();
    $results = $this->resultToArray($result);
    $per_second = ceil(1000000 / $results['MaxSendRate']);

    return intval($per_second);
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentities() {
    $identities = [];

    try {
      $results = $this->client->listIdentities();

      foreach ($results->toArray()['Identities'] as $identity) {
        $result = $this->client->getIdentityVerificationAttributes([
          'Identities' => [$identity],
        ]);
        $attributes = $result->toArray()['VerificationAttributes'];

        $domain = array_key_exists('VerificationToken', $attributes[$identity]);
        $item = [
          'identity' => $identity,
          'status' => $attributes[$identity]['VerificationStatus'],
          'type' => $domain ? 'Domain' : 'Email Address',
        ];

        if ($domain) {
          $item['token'] = $attributes[$identity]['VerificationToken'];
        }

        $identities[] = $item;
      }
    }
    catch (SesException $e) {
      $this->logger->error($e->getMessage());
      $this->messenger->addError($this->t('Unable to list identities.'));
    }

    return $identities;
  }

  /**
   * {@inheritdoc}
   */
  public function verifyIdentity($identity, $type) {
    switch ($type) {
      case 'domain':
        $this->client->verifyDomainIdentity([
          'Domain' => $identity,
        ]);
        break;

      case 'email':
        $this->client->verifyEmailIdentity([
          'EmailAddress' => $identity,
        ]);
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIdentity($identity) {
    $this->client->deleteIdentity([
      'Identity' => $identity,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSendQuota() {
    $result = $this->client->getSendQuota();
    $results = $this->resultToArray($result);

    return array_map('number_format', $results);
  }

  /**
   * {@inheritdoc}
   */
  public function getSendStatistics() {
    $statistics = [
      'DeliveryAttempts' => 0,
      'Bounces' => 0,
      'Complaints' => 0,
      'Rejects' => 0,
    ];

    $result = $this->client->getSendStatistics();
    $results = $this->resultToArray($result);

    foreach ($results['SendDataPoints'] as $data) {
      unset($data['Timestamp']);

      foreach ($data as $key => $value) {
        $statistics[$key] += (int) $value;
      }
    }

    return array_map('number_format', $statistics);
  }

  /**
   * Return the result data as an array.
   *
   * @param \Aws\Result $result
   *   The result from the API call.
   *
   * @return array
   *   The result data.
   */
  protected function resultToArray(Result $result) {
    $array = $result->toArray();
    unset($array['@metadata']);

    return $array;
  }

}

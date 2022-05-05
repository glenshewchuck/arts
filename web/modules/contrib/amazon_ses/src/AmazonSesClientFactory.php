<?php

namespace Drupal\amazon_ses;

use Aws\Exception\CredentialsException;
use Aws\Ses\SesClient;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Factory class for AWS SesClient instances.
 */
class AmazonSesClientFactory {
  use StringTranslationTrait;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger factory service.
   */
  public function __construct(LoggerChannelInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Creates an AWS SesClient instance.
   *
   * @param array $options
   *   The default client options.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory.
   *
   * @return \Aws\Ses\SesClient
   *   The client instance.
   */
  public function createInstance(array $options, ConfigFactory $configFactory) {
    $settings = $configFactory->get('aws_secrets_manager.settings');

    $options['region'] = $settings->get('aws_region');
    $options['credentials'] = [
      'key' => $settings->get('aws_key'),
      'secret' => $settings->get('aws_secret'),
    ];

    try {
      $client = new SesClient($options);

      return $client;
    }
    catch (CredentialsException $e) {
      $this->logger->error($e->getMessage());
    }

  }

}

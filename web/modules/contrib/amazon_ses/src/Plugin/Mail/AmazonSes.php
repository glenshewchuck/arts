<?php

namespace Drupal\amazon_ses\Plugin\Mail;

use Drupal\amazon_ses\Traits\HandlerTrait;
use Drupal\amazon_ses\Traits\MessageBuilderTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Amazon SES mail system plugin.
 *
 * @Mail(
 *   id = "amazon_ses_mail",
 *   label = @Translation("Amazon SES mailer"),
 *   description = @Translation("Sends an email using Amazon SES.")
 * )
 */
class AmazonSes extends PhpMail implements MailInterface, ContainerFactoryPluginInterface {
  use HandlerTrait;
  use MessageBuilderTrait;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $container,
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance
      ->setMessageBuilder($container->get('amazon_ses.message_builder'))
      ->setHandler($container->get('amazon_ses.handler'))
      ->setConfig($container->get('config.factory'))
      ->setQueue($container->get('queue'));

    return $instance;
  }

  /**
   * Set the config object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory object.
   *
   * @return $this
   */
  protected function setConfig(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('amazon_ses.settings');
    return $this;
  }

  /**
   * Set the queue object.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory service.
   *
   * @return $this
   */
  protected function setQueue(QueueFactory $queue_factory) {
    $this->queue = $queue_factory->get('amazon_ses_mail_queue');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    $email = $this->messageBuilder->buildMessage($message);

    if ($this->config->get('queue')) {
      $result = $this->queue->createItem($email);

      return (bool) $result;
    }
    else {
      $message_id = $this->handler->send($email);

      return (bool) $message_id;
    }
  }

}

<?php

namespace Drupal\Tests\amazon_ses\Traits;

use Drupal\amazon_ses\MessageBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Mock SES message builder trait.
 */
trait MockMessageBuilderTrait {

  /**
   * Mock the message builder.
   *
   * @return \Drupal\amazon_ses\MessageBuilder
   *   The message builder service.
   */
  protected function getMessageBuilder() {
    $logger = $this->prophesize(LoggerChannelInterface::class);

    $config = $this->prophesize(ImmutableConfig::class);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory
      ->get('amazon_ses.settings')
      ->willReturn($config->reveal());

    $file_system = $this->prophesize(FileSystemInterface::class);
    $mime_type_guesser = $this->prophesize(MimeTypeGuesserInterface::class);

    return new MessageBuilder(
      $logger->reveal(),
      $config_factory->reveal(),
      $file_system->reveal(),
      $mime_type_guesser->reveal()
    );
  }

}

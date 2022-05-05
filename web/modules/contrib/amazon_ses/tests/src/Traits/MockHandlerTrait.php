<?php

namespace Drupal\Tests\amazon_ses\Traits;

use Aws\Result;
use Drupal\amazon_ses\AmazonSesHandler;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Mock SES handler trait.
 */
trait MockHandlerTrait {

  /**
   * Mock the handler.
   *
   * @param \Aws\Ses\SesClient|\Prophecy\Prophecy\ObjectProphecy $client
   *   The mocked SES client.
   *
   * @return \Drupal\amazon_ses\AmazonSesHandler
   *   The handler service.
   */
  protected function getHandler($client) {
    $logger = $this->prophesize(LoggerChannelInterface::class);
    $messenger = $this->prophesize(MessengerInterface::class);

    $config = $this->prophesize(ImmutableConfig::class);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory
      ->get('amazon_ses.settings')
      ->willReturn($config->reveal());

    return new AmazonSesHandler(
      $client->reveal(),
      $logger->reveal(),
      $messenger->reveal(),
      $config_factory->reveal()
    );
  }

  /**
   * Generate an AWS Result with mock data.
   *
   * @param array $data
   *   The data to populate the result.
   *
   * @return \Aws\Result
   *   The result.
   */
  protected function mockResult(array $data) {
    $date = new \DateTime('UTC');

    $data = array_merge($data, [
      '@metadata' => [
        'statusCode' => 200,
        'effectiveUri' => 'https://email.us-east-1.amazonaws.com',
        'headers' => [
          'date' => $date->format('D, d M Y H:i:s \G\M\T'),
          'content-type' => 'text/xml',
          'connection' => 'keep-alive',
        ],
        'transferStats' => [
          'http' => [
            0 => [],
          ],
        ],
      ],
    ]);

    return new Result($data);
  }

}

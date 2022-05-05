<?php

namespace Drupal\Tests\amazon_ses\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Aws\Ses\SesClient;

/**
 * Tests the Amazon SES Client service.
 *
 * @group amazon_ses
 */
class AmazonSesClientServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'amazon_ses',
    'aws_secrets_manager',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['aws_secrets_manager']);
  }

  /**
   * Tests that the client service creates a SesClient object.
   */
  public function testCreateInstance() {
    $client = $this->container->get('amazon_ses.client');
    $this->assertInstanceOf(SesClient::class, $client);
  }

}

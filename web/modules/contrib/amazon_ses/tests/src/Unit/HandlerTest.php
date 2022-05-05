<?php

namespace Drupal\Tests\amazon_ses\Unit;

use Aws\Api\DateTimeResult;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Drupal\Tests\amazon_ses\Traits\MockHandlerTrait;
use Drupal\Tests\amazon_ses\Traits\MockMessageBuilderTrait;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Tests the Amazon SES handler service.
 *
 * @group amazon_ses
 */
class HandlerTest extends UnitTestCase {
  use MockHandlerTrait;
  use MockMessageBuilderTrait;

  /**
   * Tests that the handler successfully sends an email.
   *
   * @dataProvider messageData
   */
  public function testSend($message) {
    $message_id = $this->randomMachineName();

    $client = $this->prophesize(SesClient::class);
    $client
      ->sendRawEmail(Argument::type('array'))
      ->willReturn(['MessageId' => $message_id]);

    $message_builder = $this->getMessageBuilder();
    $handler = $this->getHandler($client);

    $email = $message_builder->buildMessage($message);
    $return = $handler->send($email);

    $this->assertEquals($return, $message_id);
  }

  /**
   * Tests that a failed send is handled.
   *
   * @dataProvider messageData
   */
  public function testFailedSend($message) {
    $client = $this->prophesize(SesClient::class);
    $client
      ->sendRawEmail(Argument::type('array'))
      ->willThrow(SesException::class);

    $message_builder = $this->getMessageBuilder();
    $handler = $this->getHandler($client);

    $email = $message_builder->buildMessage($message);
    $return = $handler->send($email);

    $this->assertFalse($return);
  }

  /**
   * Tests that the handler successfully sends an email.
   */
  public function testGetIdentities() {
    $client = $this->prophesize(SesClient::class);
    $client
      ->listIdentities()
      ->willReturn($this->mockResult([
        'Identities' => [
          'example.com',
          'email@example.com',
        ],
      ]));

    $client
      ->getIdentityVerificationAttributes(Argument::is([
        'Identities' => ['example.com'],
      ]))
      ->willReturn($this->mockResult([
        'VerificationAttributes' => [
          'example.com' => [
            'VerificationStatus' => 'Success',
            'VerificationToken' => 'verificationtoken',
          ],
        ],
      ]));

    $client
      ->getIdentityVerificationAttributes(Argument::is([
        'Identities' => ['email@example.com'],
      ]))
      ->willReturn($this->mockResult([
        'VerificationAttributes' => [
          'email@example.com' => [
            'VerificationStatus' => 'Success',
          ],
        ],
      ]));

    $handler = $this->getHandler($client);
    $return = $handler->getIdentities();

    $this->assertEquals($return[0]['identity'], 'example.com');
    $this->assertEquals($return[0]['status'], 'Success');
    $this->assertEquals($return[0]['type'], 'Domain');

    $this->assertEquals($return[1]['identity'], 'email@example.com');
    $this->assertEquals($return[1]['status'], 'Success');
    $this->assertEquals($return[1]['type'], 'Email Address');
  }

  /**
   * Tests getting sending quota data.
   */
  public function testSendQuota() {
    $client = $this->prophesize(SesClient::class);
    $client
      ->getSendQuota()
      ->willReturn($this->mockResult([
        'Max24HourSend' => 50000,
        'MaxSendRate' => 15,
        'SentLast24Hours' => 1000,
      ]));

    $handler = $this->getHandler($client);
    $return = $handler->getSendQuota();

    $this->assertEquals($return['Max24HourSend'], '50,000');
    $this->assertEquals($return['MaxSendRate'], '15');
    $this->assertEquals($return['SentLast24Hours'], '1,000');
  }

  /**
   * Tests getting sending statistics.
   */
  public function testSendStatistics() {
    $client = $this->prophesize(SesClient::class);
    $client
      ->getSendStatistics()
      ->willReturn($this->mockResult([
        'SendDataPoints' => [
          [
            'Timestamp' => new DateTimeResult(),
            'DeliveryAttempts' => '9',
            'Bounces' => '3',
            'Complaints' => '1',
            'Rejects' => '2',
          ],
          [
            'Timestamp' => new DateTimeResult(),
            'DeliveryAttempts' => '7',
            'Bounces' => '2',
            'Complaints' => '0',
            'Rejects' => '1',
          ],
        ],
      ]));

    $handler = $this->getHandler($client);
    $return = $handler->getSendStatistics();

    $this->assertEquals($return['DeliveryAttempts'], '16');
    $this->assertEquals($return['Bounces'], '5');
    $this->assertEquals($return['Complaints'], '1');
    $this->assertEquals($return['Rejects'], '3');
  }

  /**
   * Provides message data for a successful message.
   */
  public function messageData() {
    return [
      [
        [
          'to' => 'to@example.com',
          'from' => 'from@example.com',
          'subject' => 'Amazon SES test',
          'body' => 'test message body',
          'headers' => [
            'Content-Type' => 'text/plain',
          ],
        ],
      ],
    ];
  }

}

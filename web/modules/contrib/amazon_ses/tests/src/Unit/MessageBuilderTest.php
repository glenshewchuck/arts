<?php

namespace Drupal\Tests\amazon_ses\Unit;

use Drupal\Tests\amazon_ses\Traits\MockMessageBuilderTrait;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Mime\Email;

/**
 * Tests the Amazon SES handler service.
 *
 * @group amazon_ses
 */
class MessageBuilderTest extends UnitTestCase {
  use MockMessageBuilderTrait;

  /**
   * Tests that the message is built.
   *
   * @dataProvider messageData
   */
  public function testBuildMessage($message) {
    $message_builder = $this->getMessageBuilder();
    $email = $message_builder->buildMessage($message);

    $this->assertInstanceOf(Email::class, $email);

    $to = $email->getTo();
    $this->assertEquals($to[0]->getAddress(), $message['to']);

    $from = $email->getFrom();
    $this->assertEquals($from[0]->getAddress(), $message['from']);

    $subject = $email->getSubject();
    $this->assertEquals($subject, $message['subject']);

    $body = $email->getTextBody();
    $this->assertEquals($body, $message['body']);
  }

  /**
   * Tests that an HTML message is built.
   *
   * @dataProvider messageData
   */
  public function testHtmlMessage($message) {
    $message['body'] = '<p>Test HTML message body</p>';
    $message['headers']['Content-Type'] = 'text/html';

    $message_builder = $this->getMessageBuilder();
    $email = $message_builder->buildMessage($message);

    $body = $email->getHtmlBody();
    $this->assertEquals($body, $message['body']);
  }

  /**
   * Tests that a string with multiple recipients is split.
   *
   * @dataProvider messageData
   */
  public function testMultipleRecipients($message) {
    $email1 = $this->randomMachineName() . '@example.com';
    $email2 = $this->randomMachineName() . '@example.com';
    $email3 = $this->randomMachineName() . '@example.com';
    $message['to'] = "$email1, $email2; $email3";

    $message_builder = $this->getMessageBuilder();
    $email = $message_builder->buildMessage($message);

    $to = $email->getTo();
    $this->assertEquals($to[0]->getAddress(), $email1);
    $this->assertEquals($to[1]->getAddress(), $email2);
    $this->assertEquals($to[2]->getAddress(), $email3);
  }

  /**
   * Tests that CC addresses are added to the message.
   *
   * @dataProvider messageData
   */
  public function testCc($message) {
    $email1 = $this->randomMachineName() . '@example.com';
    $email2 = $this->randomMachineName() . '@example.com';
    $email3 = $this->randomMachineName() . '@example.com';
    $message['headers']['Cc'] = "$email1, $email2; $email3";

    $message_builder = $this->getMessageBuilder();
    $email = $message_builder->buildMessage($message);

    $cc = $email->getCc();
    $this->assertEquals($cc[0]->getAddress(), $email1);
    $this->assertEquals($cc[1]->getAddress(), $email2);
    $this->assertEquals($cc[2]->getAddress(), $email3);
  }

  /**
   * Tests that BCC addresses are added to the message.
   *
   * @dataProvider messageData
   */
  public function testBcc($message) {
    $email1 = $this->randomMachineName() . '@example.com';
    $email2 = $this->randomMachineName() . '@example.com';
    $email3 = $this->randomMachineName() . '@example.com';
    $message['headers']['Bcc'] = "$email1, $email2; $email3";

    $message_builder = $this->getMessageBuilder();
    $email = $message_builder->buildMessage($message);

    $bcc = $email->getBcc();
    $this->assertEquals($bcc[0]->getAddress(), $email1);
    $this->assertEquals($bcc[1]->getAddress(), $email2);
    $this->assertEquals($bcc[2]->getAddress(), $email3);
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

<?php

namespace Drupal\amazon_ses;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Amazon SES message builder service.
 */
class MessageBuilder implements MessageBuilderInterface {
  use StringTranslationTrait;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The MIME type guesser service.
   *
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * Constructs the message builder service.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Symfony\Component\Mime\MimeTypeGuesserInterface $mime_type_guesser
   *   The MIME type guesser service.
   */
  public function __construct(LoggerChannelInterface $logger, ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, MimeTypeGuesserInterface $mime_type_guesser) {
    $this->logger = $logger;
    $this->config = $config_factory->get('amazon_ses.settings');
    $this->fileSystem = $file_system;
    $this->mimeTypeGuesser = $mime_type_guesser;
  }

  /**
   * {@inheritdoc}
   */
  public function buildMessage(array $message) {
    if (isset($message['from'])) {
      $from = $message['from'];
    }
    else {
      $from = $this->config->get('from_address');
    }

    $to = preg_split('/[,;]/', $message['to']);

    $email = (new Email())
      ->from($from)
      ->to(...$to)
      ->subject($message['subject']);

    if (isset($message['headers'])) {
      $content_type = $this->getContentType($message['headers']);

      if (!empty($message['headers']['Cc'])) {
        if (is_array($message['headers']['Cc'])) {
          $cc_addresses = $message['headers']['Cc'];
        }
        else {
          $cc_addresses = preg_split('/[,;]/', $message['headers']['Cc']);
        }

        $email->cc(...$cc_addresses);
      }

      if (!empty($message['headers']['Bcc'])) {
        if (is_array($message['headers']['Bcc'])) {
          $bcc_addresses = $message['headers']['Bcc'];
        }
        else {
          $bcc_addresses = preg_split('/[,;]/', $message['headers']['Bcc']);
        }

        $email->bcc(...$bcc_addresses);
      }
    }
    else {
      $content_type = 'text/plain';
    }

    switch ($content_type) {
      case 'text/plain':
        $email->text($message['body']);
        break;

      case 'text/html':
        $email->html($message['body']);
        break;

      case 'multipart/mixed':
        $parts = $this->getParts($message);

        if ($parts) {
          $email->text($parts['plain']);
          $email->html($parts['html']);
        }

        break;

      default:
        $email->text($message['body']);

        $warning = $this->t('Unsupported content type: @type', [
          '@type' => $content_type,
        ]);
        $this->logger->warning($warning);
        break;
    }

    if (!empty($message['params']['attachments'])) {
      foreach ($message['params']['attachments'] as $attachment) {
        $file = $this->processAttachment($attachment);
        if ($file['content']) {
          $email->attach($file['content'], $file['name'], $file['mime']);
        }
      }
    }

    return $email;
  }

  /**
   * Determine the content type of a message.
   *
   * @param array $headers
   *   An array of message headers.
   *
   * @return string
   *   The content type.
   */
  protected function getContentType(array $headers) {
    if (isset($headers['Content-Type'])) {
      $content_type_parts = explode(';', $headers['Content-Type']);
      $content_type = trim($content_type_parts[0]);
    }
    else {
      $content_type = 'text/plain';
    }

    return $content_type;
  }

  /**
   * Get the plain text and HTML parts of a multipart MIME message.
   *
   * @param array $message
   *   The message being sent.
   *
   * @return array|false
   *   An array of the part contents, or FALSE if it could not be parsed.
   */
  protected function getParts(array $message) {
    $message_parts = [];
    $boundary = NULL;

    // Parse the Content-Type header to find the boundary string.
    $content_type_parts = explode(';', $message['headers']['Content-Type']);
    foreach ($content_type_parts as $part) {
      if (strpos($part, 'boundary') !== FALSE) {
        $boundary_parts = explode('=', $part);
        $boundary = trim($boundary_parts[1], '"');
      }
    }

    // Exit if no boundary string is found.
    if (!$boundary) {
      return FALSE;
    }

    $body_parts = explode($boundary, $message['body']);
    foreach ($body_parts as $part) {
      if (strpos($part, 'multipart/alternative') !== FALSE) {
        $boundary_start = strpos($part, 'boundary') + 10;
        if ($boundary_start !== FALSE) {
          $boundary_end = strpos($part, '"', $boundary_start);
          $boundary_length = $boundary_end - $boundary_start;
          $alternative_boundary = substr($part, $boundary_start, $boundary_length);

          // Exit if alternative boundary could not be determined.
          if (!$alternative_boundary) {
            return FALSE;
          }

          $alternative_parts = explode($alternative_boundary, $part);
          foreach ($alternative_parts as $part) {
            if (strpos($part, 'text/plain') !== FALSE) {
              $message_parts['plain'] = $this->getPartContent($part);
            }
            elseif (strpos($part, 'text/html') !== FALSE) {
              $message_parts['html'] = $this->getPartContent($part);
            }
          }
        }
      }
    }

    return $message_parts;
  }

  /**
   * Get the content from a MIME message part.
   *
   * @param string $part
   *   The message part.
   *
   * @return string|false
   *   The content, or FALSE if it could not be parsed.
   */
  protected function getPartContent($part) {
    $split = preg_split('#\r?\n\r?\n#', $part);

    if ($split && isset($split[1])) {
      return $split[1];
    }

    return FALSE;
  }

  /**
   * Process attachment parameters.
   *
   * @param array $attachment
   *   The attachment parameters.
   *
   * @return array
   *   An array of attachment data to add to the message.
   */
  protected function processAttachment(array $attachment) {
    $file = [
      'content' => NULL,
      'name' => NULL,
      'mime' => NULL,
    ];

    if (!empty($attachment['filepath'])) {
      if (is_file($attachment['filepath'])) {
        $file['content'] = file_get_contents($attachment['filepath']);
      }
      else {
        $path = $this->fileSystem->realpath($attachment['filepath']);
        if ($path) {
          $file['content'] = file_get_contents($path);
        }
      }

      if (!empty($attachment['filename'])) {
        $file['name'] = $attachment['filename'];
      }
      else {
        $file['name'] = basename($attachment['filepath']);
      }

      if (!empty($attachment['filemime'])) {
        $file['mime'] = $attachment['filemime'];
      }
      else {
        $file['mime'] = $this->mimeTypeGuesser->guessMimeType($file['name']);
      }
    }
    elseif (!empty($attachment['filecontent'])) {
      $file['content'] = $attachment['filecontent'];

      if (!empty($attachment['filename'])) {
        $file['name'] = $attachment['filename'];
      }
      else {
        $file['name'] = 'attachment.dat';
      }

      if (!empty($attachment['filemime'])) {
        $file['mime'] = $attachment['filemime'];
      }
      else {
        $file['mime'] = $this->mimeTypeGuesser->guessMimeType($file['name']);
      }
    }

    return $file;
  }

}

<?php

namespace Drupal\amazon_ses\Traits;

use Drupal\amazon_ses\MessageBuilderInterface;

/**
 * Amazon SES message builder trait.
 */
trait MessageBuilderTrait {

  /**
   * The message builder service.
   *
   * @var \Drupal\amazon_ses\MessageBuilderInterface
   */
  protected $messageBuilder;

  /**
   * Set the message builder.
   *
   * @param \Drupal\amazon_ses\MessageBuilderInterface $message_builder
   *   The message builder service.
   *
   * @return $this
   */
  protected function setMessageBuilder(MessageBuilderInterface $message_builder) {
    $this->messageBuilder = $message_builder;
    return $this;
  }

}

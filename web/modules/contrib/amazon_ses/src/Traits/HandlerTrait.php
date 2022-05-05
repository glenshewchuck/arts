<?php

namespace Drupal\amazon_ses\Traits;

use Drupal\amazon_ses\AmazonSesHandlerInterface;

/**
 * Amazon SES handler trait.
 */
trait HandlerTrait {

  /**
   * The Amazon SES handler service.
   *
   * @var \Drupal\amazon_ses\AmazonSesHandlerInterface
   */
  protected $handler;

  /**
   * Set the handler object.
   *
   * @param \Drupal\amazon_ses\AmazonSesHandlerInterface $handler
   *   The handler object.
   *
   * @return $this
   */
  protected function setHandler(AmazonSesHandlerInterface $handler) {
    $this->handler = $handler;

    return $this;
  }

}

<?php

namespace Drupal\amazon_ses;

/**
 * Interface for defining the message builder service.
 */
interface MessageBuilderInterface {

  /**
   * Build the message to prepare it for sending.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return \Symfony\Component\Mime\Email
   *   The prepared email object.
   */
  public function buildMessage(array $message);

}

<?php

namespace Drupal\amazon_ses\Form;

use Drupal\amazon_ses\Traits\MessageBuilderTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Amazon SES test form.
 */
class AmazonSesTestForm extends AmazonSesFormBase {
  use MessageBuilderTrait;

  /**
   * The Mail Manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance
      ->setMessageBuilder($container->get('amazon_ses.message_builder'))
      ->setHandler($container->get('amazon_ses.handler'))
      ->setMailManager($container->get('plugin.manager.mail'))
      ->setCurrentUser($container->get('current_user'))
      ->setConfig($container->get('config.factory'));

    return $instance;
  }

  /**
   * Set the Mail Manager.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The Mail Manger object.
   *
   * @return $this
   */
  protected function setMailManager(MailManagerInterface $mail_manager) {
    $this->mailManager = $mail_manager;
    return $this;
  }

  /**
   * Set the current user.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   *
   * @return $this
   */
  protected function setCurrentUser(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
    return $this;
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazon_ses_test_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['amazon_ses.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Enter an email address to send a test mail to.'),
      '#default_value' => $this->currentUser->getEmail(),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $to = $form_state->getValue('to');
    $from = $this->config->get('from_address');
    $body = $this->t('This is a test of the Amazon SES module. The module has
      been configured successfully!');

    $message = [
      'to' => $to,
      'from' => $from,
      'subject' => $this->t('Amazon SES test'),
      'body' => $body->__toString(),
    ];

    $email = $this->messageBuilder->buildMessage($message);
    $message_id = $this->handler->send($email);

    if ($message_id) {
      $this->messenger()->addMessage($this->t('A test message was sent to %to.', [
        '%to' => $to,
      ]));
    }
    else {
      $this->messenger()->addError($this->t('Error sending message to %to.', [
        '%to' => $to,
      ]));
    }
  }

}

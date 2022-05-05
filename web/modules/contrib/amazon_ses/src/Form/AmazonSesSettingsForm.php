<?php

namespace Drupal\amazon_ses\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Amazon SES settings form.
 */
class AmazonSesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazon_ses_settings_form';
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
    $config = $this->config('amazon_ses.settings');

    $form['from_address'] = [
      '#type' => 'email',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('The address emails will be sent from. This
        address must be verified by SES.'),
      '#default_value' => $config->get('from_address'),
      '#required' => TRUE,
    ];

    $form['throttle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Throttle'),
      '#description' => $this->t('Throttle the sending. Helpful to prevent
        exceeding the rate limit when send a high volume of emails.'),
      '#default_value' => $config->get('throttle'),
    ];

    $form['queue'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Queue emails'),
      '#description' => $this->t('Emails will be placed in a queue and sent when cron runs.'),
      '#default_value' => $config->get('queue'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config_keys = [
      'from_address',
      'throttle',
      'queue',
    ];

    $config = $this->config('amazon_ses.settings');
    foreach ($config_keys as $config_key) {
      if ($form_state->hasValue($config_key)) {
        $config->set($config_key, $form_state->getValue($config_key));
      }
    }
    $config->save();

    $this->messenger()->addMessage($this->t('The settings have been saved.'));
  }

}

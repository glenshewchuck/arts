<?php

namespace Drupal\amazon_ses\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Amazon SES verify identity form.
 */
class AmazonSesVerifyIdentityForm extends AmazonSesFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazonses_verify_identity_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = Url::fromUri('https://docs.aws.amazon.com/ses/latest/DeveloperGuide/verify-addresses-and-domains.html');
    $link = Link::fromTextAndUrl($this->t('Amazon SES documentation'), $url);

    $form['info'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Amazon SES requires verified identies to
        send mail. For more information about verifing identities, see the
        @link.', ['@link' => $link->toString()]) . '</p>',
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#description' => $this->t('The type of identity to verify.'),
      '#options' => [
        'domain' => $this->t('Domain'),
        'email' => $this->t('Email address'),
      ],
      '#required' => TRUE,
    ];

    $form['identity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identity'),
      '#description' => $this->t('The identity to verify.'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Verify'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $identity = $form_state->getValue('identity');
    $type = $form_state->getValue('type');

    $this->handler->verifyIdentity($identity, $type);

    $this->messenger()->addMessage($this->t('The request has been submitted.'));

    $form_state->setRedirect('amazon_ses.identities');
  }

}

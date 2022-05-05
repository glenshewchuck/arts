<?php

namespace Drupal\amazon_ses\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Amazon SES verified identities form.
 */
class AmazonSesIdentitiesForm extends AmazonSesFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazonses_identities_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => ['delete' => $this->t('Delete')],
    ];

    $header = [
      'identity' => $this->t('Identity'),
      'type' => $this->t('Type'),
      'record' => $this->t('Domain verification record'),
      'status' => $this->t('Status'),
    ];

    $identities = $this->handler->getIdentities();
    $options = [];

    foreach ($identities as $identity) {
      $options[$identity['identity']] = [
        'identity' => $identity['identity'],
        'type' => $identity['type'],
        'status' => $identity['status'],
      ];

      if ($identity['type'] == 'Domain') {
        $record = "<strong>Name:</strong> _amazonses.{$identity['identity']}<br/>
          <strong>Type:</strong> TXT<br/>
          <strong>Value:</strong> {$identity['token']}";

        $options[$identity['identity']]['record'] = [
          'data' => [
            '#markup' => $record,
          ],
        ];
      }
      else {
        $options[$identity['identity']]['record'] = '';
      }
    }

    $form['identities'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('There are no verified identities.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Apply to selected items'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getValue('action');

    if ($action == 'delete') {
      $identities = array_filter($form_state->getValue('identities'));

      foreach ($identities as $identity) {
        $this->handler->deleteIdentity($identity);
      }
    }

    $this->messenger()->addMessage($this->t('The identities have been deleted.'));
  }

}

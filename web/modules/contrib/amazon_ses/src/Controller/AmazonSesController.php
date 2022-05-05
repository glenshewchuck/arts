<?php

namespace Drupal\amazon_ses\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\amazon_ses\Traits\HandlerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Amazon SES routes.
 */
class AmazonSesController extends ControllerBase {
  use HandlerTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->setHandler($container->get('amazon_ses.handler'));

    return $instance;
  }

  /**
   * Outputs a page of statistics.
   *
   * @return array
   *   A render array to build the page.
   */
  public function statistics() {
    $quota = $this->handler->getSendQuota();
    $statistics = $this->handler->getSendStatistics();

    return [
      'quota' => [
        '#type' => 'details',
        '#title' => $this->t('Daily sending limits'),
        '#open' => TRUE,
        'sending_quota' => [
          '#markup' => $this->t('<strong>Quota:</strong> @max_send', [
            '@max_send' => $quota['Max24HourSend'],
          ]) . '<br />',
        ],
        'sent_mail' => [
          '#markup' => $this->t('<strong>Sent:</strong> @sent_last', [
            '@sent_last' => $quota['SentLast24Hours'],
          ]) . '<br />',
        ],
        'send_rate' => [
          '#markup' => $this->t('<strong>Maximum Send Rate:</strong> @send_rate
            emails/second', ['@send_rate' => $quota['MaxSendRate']]),
        ],
      ],
      'statistics' => [
        '#type' => 'details',
        '#title' => $this->t('Sending statistics'),
        '#open' => TRUE,
        'sent' => [
          '#markup' => $this->t('<strong>Sent:</strong> @sent', [
            '@sent' => $statistics['DeliveryAttempts'],
          ]) . '<br />',
        ],
        'bounces' => [
          '#markup' => $this->t('<strong>Bounces:</strong> @bounces', [
            '@bounces' => $statistics['Bounces'],
          ]) . '<br />',
        ],
        'complaints' => [
          '#markup' => $this->t('<strong>Complaints:</strong> @complaints', [
            '@complaints' => $statistics['Complaints'],
          ]) . '<br />',
        ],
        'rejected' => [
          '#markup' => $this->t('<strong>Rejected:</strong> @rejected', [
            '@rejected' => $statistics['Rejects'],
          ]),
        ],
        'description' => [
          '#markup' => '<p>' . $this->t('Sending statistics are compiled
            over the previous two weeks.') . '</p>',
        ],
      ],
    ];
  }

}

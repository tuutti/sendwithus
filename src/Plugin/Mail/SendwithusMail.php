<?php

namespace Drupal\sendwithus\Plugin\Mail;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\sendwithus\ApiManager;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Provides a 'SendwithusMail' mail plugin.
 *
 * @Mail(
 *  id = "sendwithus_mail",
 *  label = @Translation("Sendwithus mail")
 * )
 */
class SendwithusMail implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Queue\QueueFactory definition.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;
  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;
  /**
   * Drupal\sendwithus\ApiManager definition.
   *
   * @var \Drupal\sendwithus\ApiManager
   */
  protected $apiManager;
  /**
   * Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher definition.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   The queue.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   The logger factory.
   * @param \Drupal\sendwithus\ApiManager $apiManager
   *   The api key.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   The even dispatcher.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueueFactory $queue, LoggerChannelFactory $logger_factory, ApiManager $apiManager, ContainerAwareEventDispatcher $event_dispatcher) {

    $this->queue = $queue;
    $this->logger = $logger_factory->get('sendwithus');
    $this->apiManager = $apiManager;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('queue'),
      $container->get('logger.factory'),
      $container->get('sendwithus.api_manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    // Nothing to do.
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    if (empty($message['sendwithus']['template_id'])) {
      $this->logger->error(
        new FormattableMarkup('No template id found for given email (@type).', [
          '@type' => $message['id'],
        ])
      );

      return FALSE;
    }

    $api = $this->apiManager->getApi();

    $recipients = array_map(function ($element) {
      return ['address' => $element];
    }, explode(',', $message['to']));

    // Make the first recipient our 'primary' recipient.
    $to = array_shift($recipients);

    // Rest of the recipients should be set as bcc.
    if (!empty($recipients)) {
      $message['sendwithus']['args']['bcc'] = $recipients;
    }
    $status = $api->send($message['sendwithus']['template_id'], $to, $message['sendwithus']['args'] ?? NULL);

    if (!empty($status->success)) {
      return TRUE;
    }

    if (isset($status->exception)) {
      /** @var \sendwithus\API_Error $exception */
      $exception = $status->exception;
      $this->logger->error($exception->getBody());
    }
    return FALSE;
  }

}

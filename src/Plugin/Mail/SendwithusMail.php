<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Plugin\Mail;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sendwithus\Context;
use Drupal\sendwithus\Resolver\Template\TemplateResolver;
use Drupal\sendwithus\Template;
use Drupal\sendwithus\Variable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\sendwithus\ApiManager;
use Symfony\Component\HttpFoundation\ParameterBag;

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
   * The template resolver.
   *
   * @var \Drupal\sendwithus\Resolver\Template\TemplateResolver
   */
  protected $resolver;

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
   * @param \Drupal\sendwithus\Resolver\Template\TemplateResolver $resolver
   *   The template resolver.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueueFactory $queue, LoggerChannelFactory $logger_factory, ApiManager $apiManager, TemplateResolver $resolver) {

    $this->queue = $queue;
    $this->logger = $logger_factory->get('sendwithus');
    $this->apiManager = $apiManager;
    $this->resolver = $resolver;
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
      $container->get('sendwithus.template.resolver')
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
    $template = $this->resolver->resolve(
      new Context($message['module'], $message['id'], new ParameterBag($message))
    );

    if (!$template instanceof Template) {
      $this->logger->error(
        new FormattableMarkup('No template found for given email (@type).', [
          '@type' => $message['id'],
        ])
      );

      return FALSE;
    }
    $api = $this->apiManager->getApi();

    // Recipients must be formatted in ['address' => 'mail@example.tdl'] format.
    $recipients = array_map(function (string $element) {
      return ['address' => $element];
    }, explode(',', $message['to']));

    // Make the first recipient our 'primary' recipient.
    $to = array_shift($recipients);

    // Additional recipients should be set as bcc.
    if (!empty($recipients)) {
      $template->setVariable('bcc', $recipients);
    }

    // Collect all template data.
    $variables = $template->toArray();
    $status = $api->send($template->getTemplateId(), $to, $variables);

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

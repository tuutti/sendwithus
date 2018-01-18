<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Template;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\sendwithus\Context;
use Drupal\sendwithus\Resolver\Variable\VariableCollector;
use Drupal\sendwithus\Template;

/**
 * Defines the default template resolver.
 */
final class DefaultTemplateResolver extends BaseTemplateResolver {

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\sendwithus\Resolver\Variable\VariableCollector $collector
   *   The variable resolver.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration.
   */
  public function __construct(VariableCollector $collector, ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('sendwithus.settings');

    parent::__construct($collector);
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(Context $context) : ? Template {
    if (!$templates = $this->config->get('templates')) {
      return NULL;
    }

    $selected_template = NULL;
    foreach ($templates as $data) {
      list('module' => $module, 'template' => $template, 'key' => $key) = $data;

      // Module name must always match.
      if ($context->getModule() !== $module) {
        continue;
      }
      $selected_template = $template;

      if ($context->getId() === $key) {
        // Can't find better match than key+template match.
        break;
      }
    }

    if ($selected_template) {
      $template = new Template($selected_template);
      // Populate template variables.
      $this->variableCollector->collect($template, $context);

      return $template;
    }
    return NULL;
  }

}

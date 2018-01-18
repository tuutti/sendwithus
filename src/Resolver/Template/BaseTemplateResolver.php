<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Template;

use Drupal\sendwithus\Resolver\Variable\VariableCollector;

/**
 * Provides a base template resolver class.
 */
abstract class BaseTemplateResolver implements TemplateResolverInterface {

  protected $variableCollector;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\sendwithus\Resolver\Variable\VariableCollector $collector
   *   The variable resolver.
   */
  public function __construct(VariableCollector $collector) {
    $this->variableCollector = $collector;
  }

}

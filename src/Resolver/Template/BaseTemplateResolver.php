<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Template;

use Drupal\sendwithus\Context;
use Drupal\sendwithus\Template;

/**
 * Provides a base template resolver class.
 */
abstract class BaseTemplateResolver implements TemplateResolverInterface {

  protected $variableResolver;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\sendwithus\Resolver\Template\VariableResolver $resolver
   *   The variable resolver.
   */
  public function __construct(VariableResolver $resolver) {
    $this->variableResolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(Context $context) : ? Template {
    return new Template('', $this->variableResolver->resolve($context));
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Variable;

use Drupal\sendwithus\Context;

/**
 * Provides an interface for variable resolver.
 */
interface VariableResolverInterface {

  /**
   * Resolves the data for given context.
   *
   * @param \Drupal\sendwithus\Context $context
   *   The context.
   *
   * @return array
   *   The resolved variables.
   */
  public function resolve(Context $context) : array;

}

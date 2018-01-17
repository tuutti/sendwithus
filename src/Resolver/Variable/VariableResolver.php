<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Template;

use Drupal\sendwithus\Context;
use Drupal\sendwithus\Resolver\Variable\VariableResolverInterface;
use Webmozart\Assert\Assert;

/**
 * Provides a resolver to collect variables.
 */
class VariableResolver {

  protected $resolvers;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\sendwithus\Resolver\Variable\VariableResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    Assert::allIsInstanceOf($resolvers, VariableResolverInterface::class);
    $this->resolvers = $resolvers;
  }

  /**
   * Adds the resolver.
   *
   * @param \Drupal\sendwithus\Resolver\Variable\VariableResolverInterface $resolver
   *   The resolver to add.
   *
   * @return \Drupal\sendwithus\Resolver\Template\VariableResolver
   *   The self.
   */
  public function addResolver(VariableResolverInterface $resolver) : self {
    $this->resolvers[] = $resolver;
    return $this;
  }

  /**
   * Resolves the template for given parameters.
   *
   * @param \Drupal\sendwithus\Context $context
   *   The context.
   *
   * @return \Drupal\sendwithus\Template|null
   *   The template or null.
   */
  public function resolve(Context $context) : array {
    $variables = [];

    foreach ($this->resolvers as $resolver) {
      if (!$items = $resolver->resolve($context)) {
        continue;
      }
      $variables = array_merge($variables, $items);
    }
    return $variables;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\sendwithus;

use Webmozart\Assert\Assert;

/**
 * Provides a context to store required template data.
 */
final class Template implements \IteratorAggregate {

  protected $templateId;
  protected $variables;

  /**
   * Constructs a new instance.
   *
   * @param string $templateId
   *   The template id.
   * @param \Drupal\sendwithus\Variable[] $variables
   *   The variables.
   */
  public function __construct(string $templateId = NULL, array $variables = []) {
    Assert::allIsInstanceOf($variables, Variable::class);

    $this->templateId = $templateId;
    $this->variables = $variables;
  }

  /**
   * Gets the template id.
   *
   * @return string
   *   The template id.
   */
  public function getTemplateId() : string {
    return $this->templateId;
  }

  /**
   * Sets the template id.
   *
   * @param string $templateId
   *   The template id.
   *
   * @return \Drupal\sendwithus\Template
   *   The self.
   */
  public function setTemplateId(string $templateId) : self {
    $this->templateId = $templateId;
    return $this;
  }

  /**
   * Sets the variables.
   *
   * @param \Drupal\sendwithus\Variable[] $variables
   *   The variables.
   *
   * @return \Drupal\sendwithus\Template
   *   The template.
   */
  public function setVariables(array $variables) : self {
    Assert::allIsInstanceOf($variables, Variable::class);

    $this->variables = $variables;

    return $this;
  }

  /**
   * Sets the variable.
   *
   * @param \Drupal\sendwithus\Variable $variable
   *   The variable.
   *
   * @return \Drupal\sendwithus\Template
   *   The self.
   */
  public function setVariable(Variable $variable) : self {
    $this->variables[] = $variable;

    return $this;
  }

  /**
   * Gets the variables.
   *
   * @return \Drupal\sendwithus\Variable[]
   *   The variables.
   */
  public function getVariables() : array {
    return $this->variables;
  }

  /**
   * Converts variables to an array.
   *
   * @return array
   *   The array of variables.
   */
  public function toArray() : array {
    return iterator_to_array($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    foreach ($this->variables as $variable) {
      yield [$variable->getKey() => $variable->getValue()];
    }
  }

}

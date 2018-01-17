<?php

declare(strict_types = 1);

namespace Drupal\sendwithus;

/**
 * Provides a variable class to store resolved values.
 */
class Variable {

  protected $key;
  protected $value;

  /**
   * Constructs a new instance.
   *
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   */
  public function __construct(string $key, $value) {
    $this->key = $key;
    $this->value = $value;
  }

  /**
   * Gets the key.
   *
   * @return string
   *   The key.
   */
  public function getKey() : string {
    return $this->key;
  }

  /**
   * Gets the value.
   *
   * @return mixed
   *   The value.
   */
  public function getValue() {
    return $this->value;
  }

}

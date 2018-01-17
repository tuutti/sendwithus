<?php

declare(strict_types = 1);

namespace Drupal\sendwithus;

/**
 * Provides a context to store required email metadata.
 */
final class Context {

  protected $module;
  protected $id;
  protected $data;

  /**
   * Constructs a new instance.
   *
   * @param string $module
   *   The module sending email.
   * @param string $id
   *   The email id.
   * @param mixed $data
   *   The data.
   */
  public function __construct(string $module, string $id, $data) {
    $this->module = $module;
    $this->id = $id;
    $this->data = $data;
  }

  /**
   * Gets the module.
   *
   * @return string
   *   The module.
   */
  public function getModule() : string {
    return $this->module;
  }

  /**
   * Gets the email id.
   *
   * @return string
   *   The id.
   */
  public function getId() : string {
    return $this->id;
  }

  /**
   * Gets the data.
   *
   * @return mixed
   *   The data.
   */
  public function getData() {
    return $this->data;
  }

}

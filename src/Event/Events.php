<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Event;

/**
 * Defines the events.
 */
final class Events {

  /**
   * Allows the template to be altered before sending the email.
   *
   * @var string
   */
  public const EMAIL_TEMPLATE_ALTER = 'sendwithus.email_template_alter';

}

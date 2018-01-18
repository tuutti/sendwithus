<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Resolver\Variable;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\sendwithus\Context;
use Drupal\sendwithus\Template;

/**
 * Provides a variable collector for system module.
 */
class SystemVariableCollector implements VariableCollectorInterface {

  protected $config;
  protected $url;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator.
   */
  public function __construct(ConfigFactoryInterface $configFactory, UrlGeneratorInterface $urlGenerator) {
    $this->config = $configFactory->get('system.site');
    $this->url = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Template $template, Context $context) : void {
    $variables = [
      'name' => $this->config->get('name'),
      'slogan' => $this->config->get('slogan'),
      'mail' => $this->config->get('mail'),
      'url' => $this->url->generateFromRoute('<front>', [], ['absolute' => TRUE]),
      'login_url' => $this->url->generateFromRoute('user.page', [], ['absolute' => TRUE]),
    ];

    if ($langcode = $context->getData()->get('langcode')) {
      $variables['langcode'] = $langcode;
    }
    $template->setTemplateVariable('site', $variables);
  }

}

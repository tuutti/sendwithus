<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sendwithus\ApiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to manage sendwithus settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The api key.
   *
   * @var \Drupal\sendwithus\ApiManager
   */
  protected $apiManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\sendwithus\ApiManager $apiManager
   *   The api key service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ApiManager $apiManager) {
    parent::__construct($config_factory);

    $this->apiManager = $apiManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('sendwithus.api_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sendwithus_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_key'] = [
      '#type' => 'key_select',
      '#default_value' => $this->apiManager->getApiKey(),
      '#title' => $this->t('API key'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->apiManager->setApiKey($form_state->getValue('api_key'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sendwithus.settings'];
  }

}

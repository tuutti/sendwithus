<?php

declare(strict_types = 1);

namespace Drupal\sendwithus\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sendwithus\ApiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to manage sendwithus settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The api manager.
   *
   * @var \Drupal\sendwithus\ApiManager
   */
  protected $apiManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\sendwithus\ApiManager $apiManager
   *   The api key service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ApiManager $apiManager, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($config_factory);

    $this->apiManager = $apiManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('sendwithus.api_manager'),
      $container->get('module_handler')
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
    $config = $this->config('sendwithus.settings');

    $form['api_key'] = [
      '#type' => 'key_select',
      '#default_value' => $this->apiManager->getApiKey(),
      '#title' => $this->t('API key'),
    ];

    $form['templates'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Templates'),
      '#tree' => TRUE,
    ];

    $form['templates']['template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Template ID'),
      '#description' => $this->t('The sendwithus Template ID'),
      '#default_value' => '',
    ];

    $form['templates']['module'] = [
      '#type' => 'select',
      '#title' => $this->t('Module'),
      '#options' => $this->getModulesList(),
      '#empty_option' => $this->t('- Select -'),
    ];

    $form['templates']['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#description' => $this->t('The key is used to identify specific mails if the module sends more than one. Leave empty to use the configuration for all mails sent by the selected module.'),
      '#default_value' => '',
    ];

    $form['templates']['templates'] = [
      '#type' => 'table',
      '#header' => [
        'template' => $this->t('Template ID'),
        'key' => $this->t('Key'),
        'module' => $this->t('Module'),
        'remove' => $this->t('Remove'),
      ],
      '#empty' => $this->t('No templates set.'),
    ];

    foreach ($config->get('templates') ?? [] as $data) {
      list('module' => $module, 'template' => $template, 'key' => $key) = $data;

      if (!$this->moduleHandler->moduleExists($module)) {
        continue;
      }
      $row = [
        'template' => [
          '#type' => 'textfield',
          '#default_value' => $template,
        ],
        'key' => [
          '#type' => 'textfield',
          '#default_value' => $key,
        ],
        'module' => [
          '#type' => 'select',
          '#disabled' => TRUE,
          '#options' => $this->getModulesList(),
          '#default_value' => $module,
        ],
        'remove' => [
          '#type' => 'checkbox',
        ],
      ];

      $form['templates']['templates'][] = $row;
    }


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

    $templates = [];

    $template = $form_state->getValue('templates');

    // Attempt to add  new template.
    if (!empty($template['template'])) {
      $templates[] = [
        'template' => $template['template'],
        'module' => $template['module'],
        'key' => $template['key'],
      ];
    }
    foreach ($template['templates'] ?? [] as $value) {
      list(
        'module' => $module,
        'template' => $template,
        'key' => $key,
        'remove' => $remove
        ) = $value;

      if ($remove) {
        continue;
      }
      $templates[] = [
        'template' => $template,
        'key' => $key,
        'module' => $module,
      ];
    }
    $this->config('sendwithus.settings')
      ->set('templates', $templates);

    $this->apiManager->setApiKey($form_state->getValue('api_key'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sendwithus.settings'];
  }

  /**
   * Returns a list with all modules that send e-mails.
   *
   * Currently this is evaluated by the hook_mail implementation.
   *
   * @return string[]
   *   List of modules, keyed by the machine name.
   */
  protected function getModulesList() {
    $list = [];
    foreach ($this->moduleHandler->getImplementations('mail') as $module) {
      $list[$module] = $this->moduleHandler->getName($module);
    }
    asort($list);

    return $list;
  }

}

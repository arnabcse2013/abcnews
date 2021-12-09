<?php

namespace Drupal\pages_restriction\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\Role;

/**
 * Form for configure Pages.
 */
class PagesRestrictionSettingsForm extends ConfigFormBase {

  /**
   * User role object.
   *
   * @var array
   */
  protected $roles;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->roles = Role::loadMultiple();
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pages_restriction_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pages_restriction.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('pages_restriction.settings');

    $form['general_settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('General Settings'),
      '#open'  => TRUE,
    ];

    $form['general_settings']['pages_restriction'] = [
      '#title' => $this->t('Pages Restriction'),
      '#type' => 'textarea',
      '#description' => $this->t('Insert values with format: <br><br><b>your-url-restricted|your-target</b> Use one per line.<br><br>E.g.  <b>contact/thank-you-for-contacting-us|contact/send-your-message</b>'),
      '#default_value' => $config->get('pages_restriction'),
    ];

    $form['advanced_settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('Advanced Settings'),
      '#open'  => FALSE,
    ];

    $keep_parameters = $config->get('keep_parameters');

    $form['advanced_settings']['keep_parameters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Keep Parameters'),
      '#default_value' => $keep_parameters,
      '#description' => $this->t('Checking this option Gandalf will keep the parameters on URL.'),
    ];

    $option_roles = [];

    foreach ($this->roles as $key => $role) {
      if (!empty($key) && !empty($role->label())) {
        $option_roles[$key] = $role->label();
      }
    }

    if (!empty($option_roles)) {
      $form['advanced_settings']['bypass_role'] = [
        '#type' => 'checkboxes',
        '#options' => $option_roles,
        '#title' => $this->t('Ignore restrictions for the following roles (bypass)'),
        '#default_value' => $config->get('bypass_role'),
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('pages_restriction.settings');

    $pages_restriction = array_values(array_filter(explode(PHP_EOL, str_replace("\r", '', $form_state->getValue('pages_restriction')))));

    $pages_restriction = implode(PHP_EOL, $pages_restriction);

    $config->set('pages_restriction', $pages_restriction);

    $config->set('bypass_role', $form_state->getValue('bypass_role'));

    $config->set('keep_parameters', $form_state->getValue('keep_parameters'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\url_restrictions\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Url settings.
 */
class UrlRestrictionsSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'url_restrictions.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'url_restrictions_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $entity_type = [
      'node' => 'Node',
      'taxonomy' => 'Taxonomy',
      'user' => 'User',
    ];
    $default = $this->config(static::SETTINGS)->get();

    if (empty($default['entity_type'])) {
      $default['entity_type'] = [];
    }
    $form['entity_type'] = [
      '#type' => 'checkboxes',
      '#weight' => '1',
      '#multiple' => TRUE,
      '#options' => $entity_type,
      '#title' => $this->t('Entity Type'),
      '#default_value' => $default['entity_type'],
      '#required' => TRUE,
      '#description' => $this->t('Please choose the Entity Type to restrict.'),
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#weight' => '2',
      '#title' => $this->t('Path'),
      '#default_value' => $default['url'],
      '#maxlength' => 500,
      '#required' => TRUE,
      '#description' => $this->t('Please enter path to redirect. example : /contact-us , For home page: /.'),
    ];
    $form['pages'] = [
      '#title' => $this->t('Pages'),
      '#type' => 'textarea',
      '#weight' => '2',
      '#default_value' => $default['pages'],
      '#description' => $this->t('Please enter pages not to be restricted. example : /user/register, Add Multiple pages by next line.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    $flag = preg_match("/^\//", $url);
    if (!$flag) {
      $form_state->setErrorByName('url', $this->t("Url should start with '/'."));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $entity_type = $form_state->getValue('entity_type');
    $url = $form_state->getValue('url');
    $pages = $form_state->getValue('pages');
    $this->config(static::SETTINGS)->delete();
    $config = $this->config(static::SETTINGS);
    foreach ($entity_type as $key => $value) {
      $config->set('entity_type.' . $key, $value);
    }
    $config->set('url', $url);
    $config->set('pages', $pages);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}

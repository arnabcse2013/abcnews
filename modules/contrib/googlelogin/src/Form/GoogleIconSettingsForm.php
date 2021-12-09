<?php

namespace Drupal\googlelogin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Social API Icon Google.
 */
class GoogleIconSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_oauth_login_icon_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'googlelogin.icon.settings',
    ];
  }

  /**
   * Build Admin Settings Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('googlelogin.icon.settings');

    $path = drupal_get_path('module', 'googlelogin');
    global $base_url;

    $display1 = '<img src = "' . $base_url . '/' . $path . '/images/google-login.png" border="0" width="' . $config->get('width') . '">';
    $display2 = '<img src = "' . $base_url . '/' . $path . '/images/google-signin.png" border="0" width="' . $config->get('width') . '">';
    $display3 = '<img src = "' . $base_url . '/' . $path . '/images/google-sign-in.png" border="0" width="' . $config->get('width') . '">';

    $form['icon']['display'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display Settings'),
      '#default_value' => $config->get('display'),
      '#options' => [0 => $display1, 1 => $display2, 2 => $display3],
    ];

    $form['icon']['display_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Direct URL'),
      '#default_value' => $config->get('display_url'),
      '#description' => $this->t('Please use absolute URL'),
    ];

    $form['icon']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $config->get('width'),
      '#description' => $this->t('Width of the button or icon'),
    ];

    $form['icon']['show_on_login_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show button on login form'),
      '#default_value' => $config->get('show_on_login_form'),
      '#description' => $this->t('Whether to show sign in with google button on login.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit Common Admin Settings.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('googlelogin.icon.settings')
      ->set('display', $values['display'])
      ->set('display_url', $values['display_url'])
      ->set('width', $values['width'])
      ->set('show_on_login_form', $values['show_on_login_form'])
      ->save();

    $this->messenger()->addMessage($this->t('Icon Settings are updated'));
  }

}

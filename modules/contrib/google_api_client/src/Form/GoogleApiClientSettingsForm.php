<?php

namespace Drupal\google_api_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentEntityExampleSettingsForm.
 *
 * @package Drupal\google_api_client\Form
 *
 * @ingroup google_api_client
 */
class GoogleApiClientSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'google_api_client_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    _google_api_client_read_scope_info();
  }

  /**
   * Define the form used for ContentEntityExample settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['google_api_client_settings']['#markup'] = 'Settings form for GoogleApiClient. Manage field settings here.<br/><br/>';
    $form['google_api_client_intro']['#markup'] = "GoogleApiClient tries to detect all supported services and scopes from the library installed. <br/> If you don't find your desired class/service listed or you have updated library you need to flush cache or hit 'Scan library' button, to see them here.<br/>";
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Scan Library'),
    ];

    $names = $this->configFactory()->get('google_api_client.google_api_services')->get('google_api_client_google_api_services');
    $form['google_api_client_services'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#title' => $this->t('The supported services are:'),
      '#items' => $names,
    ];
    return $form;
  }

}

<?php

/**
 * @file
 * Hooks provided by the Google Api Client module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * A google response was received.
 *
 * Modules may use this hook to carry operations based on google response.
 * This is helpful when response other than authentication are received.
 * Google response have data in url so $_GET can be used in this function.
 */
function hook_google_api_client_google_response() {
  $request = \Drupal::request();
  if ($request->get('state')) {
    $state = json_decode($request->get('state'));
    if (isset($state['src']) && in_array('my_module', $state['src'])) {
      // Handle response only if the request was from my_module.
      return;
    }
    // Changes to be made for custom module.
  }
}

/**
 * Allows other modules to modify the scope before authentication.
 *
 * Developers may add or remove scopes,
 * like in this example I remove the gmail metadata scope.
 */
function hook_google_api_client_account_scopes_alter(&$scopes, $google_api_client_id) {
  if ($google_api_client_id == 1) {
    unset($scopes['gmail']['GMAIL_METADATA']);
  }
}

/**
 * Allows other modules to modify the state before authentication.
 *
 * Developers may state, redirect destination after authentication,
 * or set source or remove default source.
 */
function hook_google_api_client_account_state_alter(&$state, $google_api_client) {
  // We check the account id and the entity type (entity type is important as
  // Other modules like gauth_user extend the same interface
  // and may have same id.
  if ($google_api_client->getId() == 1 && $google_api_client->getEntityTypeId() == 'google_api_client') {
    // If we want that we don't save authentication with google api client
    // Example is if we use google api client for google sign in.
    $google_api_client_index = array_search('google_api_client', $state['src']);
    unset($state['src'][$google_api_client_index]);
    // If we want to redirect to /user page after authentication
    // Say it's again login with google.
    $state['destination'] = '/user';
    // If we are creating our own module which implements
    // hook_google_api_client_google_response()
    // In this case we can set the source and check this in response handler.
    $state['src'][] = 'my_module';
  }
}

/**
 * Allows other modules to check authentication url access.
 *
 * Developers set limit on which roles/accounts or some other criteria
 * who can authenticate a given account.
 *
 * @param int $google_api_client_id
 *   Google Api Client id (Can be a class id which extends
 *   \Drupal\google_api_client\GoogleApiClientInterface.
 * @param string $google_api_client_type
 *   Google Api Client type (It gives the entity_type normally it is
 *   google_api_client but if some other module extends
 *   GoogleApiClientInterface then it can be custom type).
 * @param \Drupal\Core\Session\AccountInterface $user_account
 *   Run access checks for this account. Logged in user session.
 *
 * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden|\Drupal\Core\Access\AccessResultNeutral
 *   Should Return AccessResult::forbidden() if want to restrict authentication
 *               AccessResult::allowed() if want to allow authentication
 *               AccessResult::neutral() if unsure.
 */
function hook_google_api_client_authenticate_account_access($google_api_client_id, $google_api_client_type, AccountInterface $user_account) {
  if ($google_api_client_id == 1 && $google_api_client_type == 'google_api_client') {
    // If we want that only users with specific user id are allowed.
    $allowed_users = [1, 5, 10];
    if (in_array($user_account->id(), $allowed_users)) {
      return \Drupal\Core\Access\AccessResult::allowed();
    }
    // If we want some role (say Google User role) to have access.
    if (in_array('google_user', $user_account->getRoles())) {
      return \Drupal\Core\Access\AccessResult::allowed();
    }
    // Nothing passed access check, restrict authentication.
    return \Drupal\Core\Access\AccessResult::forbidden();
  }
  else {
    // We don't want to check access of this account.
    return \Drupal\Core\Access\AccessResult::neutral();
  }
}

/**
 * @} End of "google_api_client hooks".
 */

CONTENTS OF THIS FILE
---------------------

  * Summary
  * Requirements
  * Installation
  * Google Configuration
  * Configuration
  * Credits


SUMMARY
-------

This module allows admins to configure Google Api Client account
and which allows to authenticate with google for website users.
Websites can then use this authentication to login or create new account.

This module is extension of Google Api Client module, it allows you to get the
OAuth2 access token from google and save it if configured and required.


REQUIREMENTS
------------

1. Google Api Client module - You need to download google api php client module
from https://drupal.org/project/google_api_client

2. Google Authentication for Users - This is required only if you want to save
OAuth2 access token.


INSTALLATION
------------

1. Follow steps at https://www.drupal.org/node/1897420


GOOGLE CONFIGURATION
--------------------
1. Visit https://console.developers.google.com/project
2. Create a new project with appropriate details,
   if you don't have a project created.
3. Under "Dashboard" on left sidebar click on "Use Google API"
   and enable the services which you want to use by clicking the links.
   i.e. Google Analytics, etc
   You can check enabled apis as a separate tab on the page.
4. Click on "Credentials" on the left sidebar.
5. If you have not created a oauth2.0 client id then create it
   with appropriate details i.e.
     Application Type: Web Application,
     Name: Name of the application
     Authorized Redirect uri's: You can copy the uri shown when you create a
     google oauth account in the admin settings.
6. Copy the client id, client secret, api key
   to configuration form of the Google Login module.

For more details refer
https://www.drupal.org/docs/contributed-modules/google-api-php-client/google-api-console-configurations


CONFIGURATION
-------------
1. Navigate to /admin/config/googlelogin and add client_id, client secret.

2. Optional configuration is to save the OAuth Access Token this needs
   Google Authentication for Users module, this option is for developers who
   want to perform additional actions with the access.

3. Save.

CREDITS
-------

The idea came up from no module providing google oauth2 authentication
in drupal 7 and now in drupal 8 the module is separated form gauth.

Current Maintainers: Suresh Senthatti https://www.drupal.org/u/sureshsenthatti
                     Sadashiv Dalvi https://www.drupal.org/u/sadashiv

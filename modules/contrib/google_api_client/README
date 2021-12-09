CONTENTS OF THIS FILE
---------------------

  * Summary
  * Requirements
  * Installation
  * Google Configuration
  * Configuration
  * Upgrade from GAuth (drupal 7)
  * Credits


SUMMARY
-------

This module allows you to authenticate with google,
and use this authentication to carry other api requests.

This module don't have functionality of its own,
but can help you to manage accounts, authenticate with google
(i.e. get access token) and use this authentication to carry api requests.

This module allows you to enter google account details like client id,
client secret key, developer key, select google services to be enabled
and gets the OAuth2 access token from google.

The account manage page also shows a authenticate link if the account is not
authenticated and Revoke link if the account is authenticated.


REQUIREMENTS
------------

1. Google client library - You need to download google api php client library
from http://code.google.com/p/google-api-php-client/downloads/list


INSTALLATION
------------

1. Copy this module directory to your modules directory.

2. Download the latest release of google php client library from
   https://github.com/google/google-api-php-client/releases and
   extract it in libraries folder of the site, mostly located at
   libraries, the path is 'libraries/google-api-php-client/vendor'

   You may use composer to install without downloading the file which is fine.
   a) If you use composer then goto libraries folder in core, create google-api-php-client and install
   using composer i.e. composer require google/apiclient:"^2.0"

   b) If you download then goto libraries folder in core and extract the tar

   The final path after installation should be 'libraries/google-api-php-client/vendor/autoload.php'

   Note that version 8.x-1.0 is tested and will only work with google-api-php-client 2.4 or later,

3. Enable the module and manage accounts at admin/config/services/google_api_client.


GOOGLE CONFIGURATION
--------------------
1. Visit https://console.developers.google.com/project
2. Create a new project with appropriate details,
   if you don't have a project created.
3. Under "Dashboard" on left sidebar click on "Use Google API" and enable the services which you want to use by clicking the links.
   i.e. Google Analytics, etc
   You can check enabled apis as a separate tab on the page.
4. Click on "Credentials" on the left sidebar.
5. If you have not created a oauth2.0 client id then create it
   with appropriate details i.e.
     Application Type: Web Application,
     Name: Name of the application
     Authorized Redirect uri's: You can copy the uri shown when you create a google oauth account in the admin settings.
6. Copy the client id, client secret, api key
   to configuration form of the module.


CONFIGURATION
-------------
1. Configure the api accounts at admin/config/services/google_api_client.

2. You can add new account or update existing accounts.
    Specify unique name by which you can identify the account.
    Add the client id, client secret and api key from you google project page
    i.e. https://code.google.com/apis/console/
    Select services for which this account will be used eg Google Calendar, etc.

3. On save of the form it will ask for access,
   click allow access so that the account gets authenticated.

4. Ready to use this account for api access.


Upgrade from GAuth (drupal 7)
-----------------------------
8.x-2.x Provides basic migration for gauth accounts.
Note that tracking of accounts in drupal 8 is going to be different.
a) Google Api Client entity will hold only accounts managed by admins.
b) Google Api Client User entity will hold all end user authenticated accounts.
c) Google Api Client Login entity will hold all login with google accounts.

As 8.x-2.x only supports part (a),
hence the migration won't migrate GAuth User or GAuth Login accounts.
Those will be provided in 8.x-3.x and later versions.
For executing migrations check
https://www.drupal.org/docs/8/api/migrate-api/executing-migrations.


CREDITS
-------

The idea came up from no module providing google oauth2 authentication in drupal 7 (gauth project)
and now in drupal 8 the module is more powerful than before.

Current Maintainers: Janak Sing (@dakku)
                     Sadashiv Dalvi (@sadashiv) <dalvisadashiv@gmail.com>
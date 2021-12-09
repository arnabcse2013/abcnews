CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------

Url Restrictions is a module to restrict the drupal default url such
as node/*,taxonomy/* and user/* for the entity type.
This module will support for restricting the url from page wise.

-> Node
-> Taxonomy
-> User

More info:

 * For a full description of the module, visit [the project page]
   (https://www.drupal.org/project/url_restrictions).

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the Url Restrictions module as you would normally install a 
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 
   for further information.

CONFIGURATION
-------------

 * Configure the Url Restrictions in 
  Administration » Configuration » Url Restrictions:

 1. After install this module, we can see the menu 'Url Restrictions' in the
    admin configuration menu.
 2. After submitted the form, Please clear the cache to reflect the updated
    configuration.
 3. By default, administrator role can access this url. If we want to access
    this url to different role, go to permission page and find the word
    'Edit Url Restrictions Settings' to set the permission for different role.


MAINTAINERS
-----------

Current maintainers:
  Elavarasan R - https://www.drupal.org/user/1902634

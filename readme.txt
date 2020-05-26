=== sbcoa ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: SBCOA, WP-CRM, Ultimate-Members
Requires at least: 5.4
Tested up to: 5.4.1
Gutenberg compatible: Yes
Requires PHP: 5.6
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

SBCOA thugin - WordPress theme/plugin hybrid for Sandown Bay Chalet Owners Association.

Implements some logic to allow users to update additional fields on their Ultimate-Member Account page and have the changes reflected in the WP-CRM data.

Note: Currently each field needs to be defined in both WP-CRM and Ultimate-Member.
The option definitions for checkbox and radio fields should match.


== Installation ==
1. Upload the contents of the sbcoa plugin to the `/wp-content/plugins/sbcoa' directory
1. Activate the sbcoa plugin through the 'Plugins' menu in WordPress
1. If you need to change the list of fields to display on the Account page see the FAQs


== Frequently Asked Questions ==

= How do I change the fields to display? = 

The list of fields to display can be extended either by editing the plugin - function query_extra_account_fields() - 
or by hooking into the `query_extra_account_fields` filter in your theme's functions.php

Note: If your theme does not implement this filter then the original logic to display and update the data is automatically disabled. 
See remove_themes_um_logic().


= How can I contribute? =
[github bobbingwide sbcoa]

== Screenshots ==
1. WP-CRM
2. Account page
 
== Upgrade Notice ==
= 0.0.1 =
Fixes issue #2. Needed to prevent errors when the bw_trace2() function does not exist.

= 0.0.0 = 
Install and activate to replace the original logic in the theme's functions.php file

== Changelog ==
= 0.0.1 = 
* Fixed: Reworked main plugin file ensure bw_trace2() exists in the global namespace.

= 0.0.0 = 
* Added: WP-CRM to UM logic copied and cobbled from the site's theme file functions.php
* Tested: With PHP 7.3



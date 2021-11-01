=== Plugin Name ===
Contributors: digitalapps
Donate link: https://digitalapps.com
Tags: global site tag, analytics, google analytics, google, gtm, google tag manager, global site tag, gtag
Requires at least: 3.0.1
Tested up to: 5.5
Stable tag: 5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Global Site Tag (gtag.js) is a new Google Analytics replacement – giving you better control while making implementation easier. Using gtag.js lets you benefit from the latest tracking features and integrations as they become available. This is the WordPress version.

== Description ==

<a href="https://digitalapps.com/wordpress-plugins/wp-global-site-tag/" title="WP Global Site Tag">Global Site Tag</a> (gtag.js) is a new Google Analytics replacement. WP Global Site Tag provides a framework for streamlined web page tagging – giving you better control while making implementation easier. Using gtag.js lets you benefit from the latest tracking features and integrations as they become available. This is the WordPress version.

To use gtag.js to track your site, install the plugin and activate.

<ul>
    <li>Simple to install and use even your clients can do it</li>
    <li>Configure multiple Google Analytics properties</li>
    <li>Minified version of the code is injected for faster loading times</li>
    <li>Built using WordPress best practices and standards</li>
    <li>Great for marketing agencies and individuals</li>
</ul>

== How to use Global Site Tag ==

To use gtag.js to track your site, install the plugin. Replace GA_TRACKING_ID with the tracking ID of the Google Analytics property you want to send data to.

== Why use Global Site Tag ==

Global Site Tag streamlines tracking across all Google products, including their measurement, conversion tracking, and remarketing products. Global Site Tag is the new replacement for old Google Analytics script.


== Difference between Universal Analytics & Global Site Tag ==

There are few differences you need to know before thinking of migrating to Global Site Tag.

Install:
Universal Analytics(analytics.js) is only used for installing Google Analytics, and global tag can be used to install multiple tools like, GA and GTM.

Tracking:
Universal Analytics uses trackers(ga(‘create’, ‘G-XXXXX-Y’, ‘auto’);) to send pageviews to Google Analytics while gtag send pageviews to GA property identified by the GA_Tracking_ID(gtag(‘config’,’GA_Tracking_ID’)).

Use:
Global Site Tag can be used for conversion tracking and remarketing while universal analytics can’t.



== Installation ==

Follow these steps to install the plugin on your site.

1. Upload `wp-global-site-tag` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Paste your Tracking ID and you are set to go
2. Advanced editor for those who need it

== Changelog ==

= 1.0 =
* First Release
= 1.0.2 =
* Testing on WordPress 5.0. Version number bump.
= 1.0.3 =
* Added settings hyperlink to the plugins page.
* Move settings page under Settings.
* Add update/install handlers.
* Add new screenshot.
= 1.0.5 =
* Updated label
= 1.0.6 =
* Added mime type
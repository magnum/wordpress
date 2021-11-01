=== WordPress OpenID Connect Client ===
Contributors: cyberlord92
Tags: openid, sso, openid connect, oidc, openidconnect
Requires at least: 3.0.1
Tested up to: 5.8
Stable tag: 2.1.5
License: MIT/Expat
License URI: https://docs.miniorange.com/mit-license

Wordpress OpenID Connect (OIDC / openidconnect) Client plugin allows Single Sign On (SSO) with any OpenID Connect provider that conforms to the OpenID Connect 1.0 standard.
You can SSO to your WordPress site with any OAuth 2.0 or OpenID Connect 1.0 provider using this plugin.

= Description =

OpenID Connect Client plugin works with any OAuth/OpenIDConnect provider that conforms to the OAuth 2.0 or OpenID Connect 1.0 standard. OpenID Connect 1.0 is a simple identity layer on top of the OAuth 2.0 protocol. Each time you need to log in to a website using OIDC, you are redirected to your OpenID provider where you login, and then taken back to the website. 
	
= Use-Cases =
* Login with Office365 (Office365 Login)
* Login with Cognito (Cognito Login)
* Login with Yahoo (Yahoo Login)
* Login with WordPress (WordPress Login)
* Login with Google (Google Login)
* Login with Facebook (Facebook Login)
and SSO with many more social login and mobile applications.


WP OIDC / OpenID Connect client plugin enables user registration and authentication against any standard OpenID Connect (OIDC) Provider. 

Login
OIDC Client plugin enables a WordPress site to send users to an external OpenID Provider for login.

Registration
If the user has an existing account in the OpenID Connect Provider, but not in WordPress, this plugin will enable dynamic registration of the user in WordPress.

= List of popular OpenID Connect Providers we support =
*   Office 365
*	AWS Cognito
*   Amazon
*	SalesForce
*	PayPal
*	Microsoft
*	Yahoo
*	Google
*   Onelogin
*   Okta
*   ADFS
*   Gigya
*   DID.app
*   PhantAuth
*   Openidentityplatform
*   Yelp

= List of popular OAuth Providers we support =
*	WSO2
*	Eve Online
*	Slack
*	Discord
*	HR Answerlink / Support center
*	Wechat
*	Weibo
*	AWS cognito
*	Azure AD
*	Gitlab
*	Shibboleth
*	Blizzard (Formerly Battle.net)
*	servicem8
*	Meetup
*   DigitalID
*   Aerobase
*   Yandex
*   ORY Hydra
*   Odex

= Other OpenID Connect/OAuth Providers we support =
*	Keycloak, Foursquare, Harvest, Mailchimp, Bitrix24, Spotify, Vkontakte, Huddle, Reddit, Strava, Ustream, Yammer, RunKeeper, Instagram, SoundCloud, Pocket, PayPal, Pinterest, Vimeo, Nest, Heroku, DropBox, Buffer, Box, Hubic, Deezer, DeviantArt, Delicious, Dailymotion, Bitly, Mondo, Netatmo, Amazon, WHMCS, FitBit, Clever, Sqaure Connect, Windows, Dash 10, Github, Invision Comminuty, Blizzar, authlete etc.


= List of grant types we support =
*   Authorization code grant
*   Implicit grant
*   Resource owner credentials grant (Password Grant)
*   Client credentials grant
*   Refresh token grant

= FREE VERSION FEATURES =

*	WordPress OpenID Connect Client (OIDC Client) supports single sign-on / SSO with any 3rd party OAuth / OpenIDConnect server or custom OAuth / OpenIDConnect server.
*   Auto Create Users : After SSO, new user automatically gets created in WordPress
*	Account Linking : After user SSO to WordPress, if user already exists in WordPress, then his profile gets updated or it will create a new WordPress User
*	Attribute Mapping : OpenIDConnect Client supports username Attribute Mapping feature to map WordPress user profile username attribute.
*	Login Widget : Use Widgets to easily integrate the login link with your WordPress site
*	OAuth / OpenID Connect Provider Support : OpenIDConnect / OAuth Login supports only one OAuth / OpenID Connect Provider. 
*	Redirect URL after Login : OpenIDConnect / OAuth Login Automatically Redirects user after successful login. 
*	Logging :  If you run into issues OAuth Login can be helpful to enable debug logging


= STANDARD VERSION FEATURES =

*	All the FREE Version Features included.
*	Optionally Auto Register Users : OpenIDConnect Login does automatic user registration after login if the user is not already registered with your site
*	Basic Role Mapping :  OpenIDConnect Client provides basic Attribute Mapping feature to map WordPress user profile attributes like username, firstname, lastname, email and profile picture. Manage username & email with data provided. 
                          Also, Assign default role to user registering through OIDC Login based on rules you define.
*	Support for Shortcode : Use shortcode to place OIDC / OAuth login button anywhere in your Theme or Plugin
*	Customize Login Buttons / Icons / Text : Wide range of OpenIDConnect / OAuth Login Buttons/Icons and it allows you to customize Text shadow
*	Custom Redirect URL after Login : WordPress OpenIDConnect SSO provides auto redirection and this is useful if you wanted to globally protect your whole site
*	Custom Redirect URL after logout : WordPress OIDC SSO allows you to auto redirect Users to custom URL after he logs out from your WordPress site


= PREMIUM VERSION FEATURES =

*	All the STANDARD Version Features
*	Advanced Role Mapping : Assign roles to users registering through OAuth Login based on rules you define.
*	Force Authentication / Protect Complete Site : Allows user to restrict login / authorization for particular site
*	Multiple Userinfo Endpoints Support : OIDC Login supports multiple Userinfo Endpoints.
*	App domain specific Registration Restrictions : OIDC Login restricts registration on your site based on the person's email address domain
*	Multi-site Support : OIDC Login have unique ability to support multiple sites under one account

= ENTERPRISE VERSION FEATURES =

*	All the PREMIUM Version Features
*	Multiple OAuth / OpenID Connect Provider Support
*	Single Login button for Multiple Apps : It provides single login button for multiple providers
*	Extended OIDC / OAuth API support : Extend OAuth API support to extend functionality to the existing OAuth client.
*	BuddyPress Attribute Mapping : OIDC Login allows BuddyPress Attribute Mapping.
*	Page Restriction according to roles : Limit Access to pages based on user status or roles. This WordPress OIDC Login plugin allows you to restrict access to the content of a page or post to which only certain group of users can access.
*	WP Hooks for Different Events : Provides support for different hooks for user defined functions
*	Login Reports : OIDC Login creates user login and registration reports based on application used. 

= REST API Authentication =
Secures the unauthorized access to your WordPress sites/pages using our <a href="https://wordpress.org/plugins/wp-rest-api-authentication/" target="_blank">WordPress REST API Authentication</a> plugin.

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `OpenID Connect Client`. Find and Install `OpenID Connect Client`
3. Activate the plugin from your plugins page

= From WordPress.org =
1. Download OpenID Connect Client.
2. Unzip and upload the `miniorange-openid-connect` directory to your `/wp-content/plugins/` directory.
3. Activate miniOrange OpenID Connect Client from your Plugins page.

= Once Activated =
1. Go to `Settings-> miniOrange OpenID Connect Client -> Configure OpenID Connect Client`, and follow the instructions
2. Go to `Appearance->Widgets` ,in available widgets you will find `miniOrange OpenID Connect Client` widget, drag it to chosen widget area where you want it to appear.
3. Now visit your site and you will see login with widget.


== Frequently Asked Questions ==
= I need to customize the plugin or I need support and help? =
Please email us at info@xecurify.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.


= How to configure the applications? =
On configure OpenID page, check if your app is already there in default app list, if not then select the Custom OpenID Connect Provider or custom OAuth 2.0 app based on the protocol supported by your OAuth/OpenID Connect Server. Then click on How to Configure link to see configuration instructions.


== Changelog ==

= 2.1.5 =
* Compatibility with WordPress 5.8
* Security Fixes

= 2.1.4 =
* Added Auth0 in default app list
* Added setup guide links for Nextcloud, EveOnline and Auth0
* Minor bug fixes

= 2.1.3 =
* Compatibility with WordPress 5.7
* Added Office 365 in default app list

= 2.1.2 =
* Compatibility with WordPress 5.6

= 2.1.1 =
* Updated setup guide links

= 2.1.0 =
* Readme changes

= 1.9.0 =
* Compatibility with WordPress 5.5

= 1.8.2 =
* Pricing update

= 1.8.1 =
* Dropdown with input box in Attribute mapping
* Update the Add on page

= 1.8.0 =
* Advertised PKCE Authentication
* Kept a single checkbox for password grant
* Added multisite licensing plans
* Added all inclusive plan and instance based price for same
* Update licensing plan names

= 1.7.7 =
* Added new OpenID providers

= 1.7.6 =
* Bug fixes
* Compatibility fixes

= 1.7.5 =
* Minor fixes
* Added new OpenID Connect Provider

= 1.7.4 =
* UI Updates

= 1.7.3 = 
* Fixed Registration issue
* Added send plugin configuration option
* Added Attribute Mapping dropdown
* UI Changes & bug fixes

= 1.7.2 =
* Minor fixes

= 1.7.1 =
* Updated plugin licensing
* Added compatibility for WordPress Version 5.3.2
* Added Compatibility for php7.4

= 1.7.0 =
* Updated UI & advertised new features
* Added icons for OAuth providers

= 1.6.0 =
* Updated UI
* Advertised New Features
* Updated registration form

= 1.5.1 =
* Added Delayed Registartion
* Added compatibility for WordPress 5.2.1
* Updated Licensing plan
* Migrated Domain
* Advertised New Features

= 1.5.0 =
* Compatibility with WordPress 5.1

= 1.4.0 =
* Added Feedback Form

= 1.3.0 =
* Updated Licensing Plan

= 1.2.0 =
* Updated the Default App List

= 1.1.0 =
* Updated the Redirect / Callback URL

= 1.0.2 =
* Updated version as per New Licensing Plan

= 1.0.1 =
* First version of plugin.

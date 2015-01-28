=== Authy Two Factor Authentication ===
Contributors: authy, ethitter
Tags: authy, authentication, two factor, security, login, 2fa, two step authentication, password, admin, mobile, mfa, otp, multi-factor, oauth, android, iphone, sso, strong authentication, two-step verification
Requires at least: 3.9
Tested up to: 4.5.3
Stable tag: 3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Authy is the easiest and fastest way to add strong 2 Factor Authentication to your WordPress blog to keep it safe of hacking attacks easy & quickly.

== Description ==

Authy helps you increase security for your user accounts in your WordPress site by using strong Two-Factor authentication. The plugin can be installed and configured in a matter of minutes.

Two-Factor Authentication protects you from password re-use, phishing and keylogger attacks. The Authy WordPress plugin was designed so that anyone can install it, configure it and use it. Security shouldn't be painful!

= How it Works =

Usually you use only a username and a password to login to your blog. If your password is stolen or guessed, someone else can now login to your blog. Therefore with two-factor authentication, you use an additional step to login and one that uses something you have in your possession that is harder to steal.

Authy uses your phone number as the extra piece of security and there are a few ways it is used.

1. Get a security token via SMS or a phone call. This code is then used to login with your username and password.
2. Generate the same token using Authy, our mobile application.
3. Get a push notification via Authy, out mobile application. This is a lot more secure and easier way to login.

[Watch out our video tutorial](https://player.vimeo.com/video/58410368)

https://player.vimeo.com/video/58410368

= Easy Installation =

Authy plugin takes five minutes to install and requires no security knowledge.

= Powerful security =

Two-Factor Authentication is used by the largest organizations in the world because it works. With Authy you get benefits without the hassle of managing it yourself.

= Full control =

You can allow your users to opt-in on WordPress two-factor authentication or Admins can force two-factor authentication on users.

= Role based =

You can control which users require two-factor authentication based on their WordPress role.

Plugin is open source and can be found at https://github.com/authy/authy-wordpress/

== Installation ==

1. Create an account, and get your Authy API Key at [www.authy.com/signup](www.authy.com/signup).
2. Install the plugin either via your site's dashboard or by downloading the plugin from WordPress.org and uploading the files to your server.
3. Activate the plugin through the WordPress Plugins menu.
4. Navigate to **Settings -> Authy** to enter your Authy API key.

== Frequently Asked Questions ==

= How can an user enable two-factor authentication? =
The user should go to his or her WordPress profile page and add his or her mobile number and country code.

= How can a user disable Authy after enabling it? =
The user should return to his or her WordPress profile screen and disable Authy at the bottom.

= Can an Admin can select specific user roles that should authenticate with Authy two-factor authentication? =
Yes, as an admin you can go to the settings page of the plugin, select the user roles in the list, and click "Save Changes" to save the configuration.

= How can the admin an admin force Authy two-factor authentication on a specific user? =
As an admin, you can go to the users page. Then, select the user in the list, and click edit. Go to the bottom, enter the user's mobile number and country code, and click "Update user."

== Screenshots ==
1. Authy Two-Factor Authentication.
2. Authentication with Authy OneTouch.
3. Authentication with Time-based One-time password (TOTP).

== Changelog ==

= 3.0 =
* Add support for Authy OneTouch.
* Resolved a WordPress 4.5 security issue where 2FA config was being ignored in some circumstances.
* Updated to support WordPress 4.5
* Resolved issue where incomplete 2FA configurations could be returned to and completed.

= 2.5.5 =
* Customize the user agent for the request to the Authy API
* Validate the format of the user id and tokens.

= 2.5.4 =
* Fixed the login styles for WordPress 3.9.
* Fix the login url action when the hidden backend option is enabled in a security plugin.

= 2.5.3 =
* Fixed the include of color-fresh.css file, the file was renamed to colors.css on WordPress 3.8
* Added translations for Spanish language.

= 2.5.2 =
* Encode the values on query before to sending to Authy API

= 2.5.1 =
* Improved settings for disable/enable XML-RPC requests.
* Fix error message: Missing credentials, only display when the user tries to verify an authy token without signature.

= 2.5 =
* Improved the remember me option in the user authentication.
* Use manage_option capability for display the plugin settings page.

= 2.4 =
* Use the remember me option when authenticate the user.

= 2.3 =
* Hide the authy settings page for other users except for super admin (multisite)

= 2.2 =
* Hide some digits of the cellphone.

= 2.1 =
* Added missing images.

= 2.0 =
* Refactor code
* The admin can now force a user to enable Authy on next login.

= 1.3 =
* Display API errors when try to register a user.

= 1.2 =
* Fix update user profile and verify SSL certificates.

= 1.1 =
* Fix reported issues and refactor code.

= 1.0 =
* Initial public release.

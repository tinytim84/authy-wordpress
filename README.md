# Authy WordPress Plugin

This plugin adds [Authy](http://www.authy.com) two-factor authentication to WordPress.

With Authy, you can secure your WordPress user accounts with two-factor authentication.

Tested from WordPress version 3.9 to 4.5.3

Usually you use only a username and a password to login to your blog. If your password is stolen or guessed, someone else can now login to your blog. Therefore with two-factor authentication, you use an additional step to login and one that uses something you have in your possession that is harder to steal.

Authy uses your phone number as the extra piece of security and there are a few ways it is used.

1. Get a security token via SMS or a phone call. This code is then used to login with your username and password.
2. Generate the same token using Authy, our mobile application.
3. Get a push notification via Authy, out mobile application. This is a lot more secure and easier way to login.

## NOTICE
If you are running an older version of the WordPress plugin (i.e. 2.5.5 and below) and you are running a version of Wordpress 4.5 and above, please upgrade your Authy plugin to 3.0 and above to remediate a problem where 2FA can be bypassed in certain circumstances.


## Installation

1. Create an account, and get your Authy API Key at [www.authy.com/signup](www.authy.com/signup).
2. Install the plugin either via your site's dashboard or by downloading the plugin from WordPress.org and uploading the files to your server.
3. Activate the plugin through the WordPress Plugins menu.
4. Navigate to **Settings -> Authy** to enter your Authy API key.


## Frequently Asked Questions

### How can a user enable Two-Factor Authentication?

The user should go to his or her WordPress profile page and add his or her mobile number and country code.

### How can a user disable Authy after enabling it?

The user should return to his or her WordPress profile screen and disable Authy at the bottom.

### Can an Admin select specific user roles that should authenticate with Authy Two-Factor Authentication?

Yes, as an admin you can go to the settings page of the plugin, select the user roles in the list, and click Save Changes to save configuration.

### How can an admin force Authy Two-Factor Authentication on a specific user?

As an admin, you can go to users page. Then, select the user in the list, and click edit. Go to the bottom, enter the user's mobile number and contry code, and click "Update user."

## Copyright
 
Copyright (c) 2011-2020 Authy Inc. See License.txt for further details.

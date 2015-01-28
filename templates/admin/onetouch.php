<?php $link_msg = $details['app']->onetouch_enabled ? __('Edit OneTouch settings in Authy Dashboard', 'authy') : __('Enable OneTouch', 'authy'); ?>

<h2><?php _e( 'Authy OneTouch', 'authy' ); ?></h2>
<p><?php echo __('Authy OneTouch is the easiest way to authenticate in WordPress. Approve or deny the authentication request with a simple yes/no question. Users will get a push notification alert on a previously authorized smartphone via Authy mobile app. No code to enter anywhere.','authy'); ?></p>
<?php if( !$details['app']->onetouch_enabled ): ?>
    <p><?php echo __('You can use OneTouch as your main two-factor authentication service, and keep it in combination with Authy TOTP. As soon as you enable OneTouch in an application, any users with the Authy app installed for your service will automatically be enabled for OneTouch.', 'authy'); ?></p>
    <p><?php echo __('Visit the OneTouch section in your Authy Dashboard to enable the service. Try it now!', 'authy'); ?></p>
<?php endif; ?>
<a href="<?php echo esc_attr(AUTHY_DASHBOARD_URL) ?>" target="_blank"><strong><?php echo $link_msg; ?></strong></a>

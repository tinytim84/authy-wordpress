<head>
  <?php
    global $wp_version;
    if ( version_compare( $wp_version, '3.3', '<=' ) ): 
  ?>
      <link rel="stylesheet" type="text/css" href="<?php echo admin_url( 'css/login.css' ); ?>" />
      <link rel="stylesheet" type="text/css" href="<?php echo admin_url( 'css/colors-fresh.css' ); ?>" />
  <?php
    elseif ( version_compare( $wp_version, '3.8', '<=' ) ):
      wp_admin_css("wp-admin", true);
      wp_admin_css("colors-fresh", true);
      wp_admin_css("ie", true);
    else:
      wp_admin_css("login", true);
    endif;
  ?>
  <link href="<?php echo esc_attr( AUTHY_CSS_URL ); ?>" media="screen" rel="stylesheet" type="text/css">
  <script src="<?php echo esc_attr( AUTHY_JS_URL ); ?>" type="text/javascript"></script>
  <script type="text/javascript">
  /* <![CDATA[ */
  var AuthyAjax = {"ajaxurl":"<?php echo admin_url( 'admin-ajax.php' ); ?>"};
  /* ]]> */
  </script>
  <?php if ( isset($installation_form) && $installation_form ): ?>
      <link href="<?php echo plugins_url( 'assets/authy.css', dirname(__FILE__) ); ?>" media="screen" rel="stylesheet" type="text/css">
      <?php wp_print_scripts( array( 'jquery', 'utils', 'authy' ) ); ?>
      <script src="<?php echo plugins_url( 'assets/authy-installation.js', dirname(__FILE__) ); ?>" type="text/javascript"></script>
  <?php elseif( isset($onetouch_request) && !empty($onetouch_request) ): ?>
      <?php wp_print_scripts( array( 'jquery' ) ); ?>
      <script src="<?php echo plugins_url( 'assets/authy-onetouch.js', dirname(__FILE__) ); ?>" type="text/javascript" defer="defer"></script>
  <?php endif; ?>
</head>

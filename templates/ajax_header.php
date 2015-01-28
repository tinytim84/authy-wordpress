<head>
    <?php
        wp_print_scripts( array( 'jquery' ) );
        wp_print_styles( array( 'colors', 'authy' ) );
    ?>
    <link href="<?php echo esc_attr( AUTHY_CSS_URL ); ?>" media="screen" rel="stylesheet" type="text/css" />
    <script src="<?php echo esc_attr( AUTHY_JS_URL ); ?>" type="text/javascript"></script>

    <style type="text/css">
        body {
            width: 450px;
            height: 380px;
            overflow: hidden;
            padding: 0 10px 10px 10px;
        }

        div.wrap {
            width: 450px;
            height: 380px;
            overflow: hidden;
        }

        table th label {
            font-size: 12px;
        }
    </style>
</head>

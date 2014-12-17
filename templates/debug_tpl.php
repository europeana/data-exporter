<?php

  function parse_array( array $array ) {
    $result = null;

    foreach ( $array as $key => $value ) {
      if ( is_array( $value ) ) {
        $result .= '[' . $key . '] => ' . PHP_EOL . addslashes( print_r( $value, true ) );
      } else {
        $result .= '[' . $key . '] => [' . addslashes( $value ) . ']<br/>';
      }
    }

    return $result;
  }


  $debug_html = '<div id="debug"><h2 class="page-header">debug</h2>' . PHP_EOL;

    if ( !empty( $error_msg_dev ) ) {
      $debug_html .=
        '<h4>Error Messages</h4>' . PHP_EOL .
          '<pre>' . $error_msg_dev . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_POST) && !empty($_POST)) {
      $debug_html .= '<h4>$_POST</h4>' . PHP_EOL .
        '<pre>' . parse_array( $_POST ) . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_GET) && !empty($_GET)) {
      $debug_html .=
        '<h4>$_GET</h4>' .PHP_EOL .
        '<pre>' . parse_array( $_GET ) . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_COOKIE) && !empty($_COOKIE)) {
      $debug_html .=
        '<h4>$_COOKIE</h4>' . PHP_EOL .
        '<pre>' . parse_array( $_COOKIE ) . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_SESSION) && !empty($_SESSION)) {
      $debug_html .=
        '<h4>$_SESSION</h4>' . PHP_EOL .
        '<pre>' . parse_array( $_SESSION ) . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_FILES) && !empty($_FILES)) {
      $debug_html .=
        '<h4>$_FILES</h4>' . PHP_EOL .
        '<pre>' . print_r( $_FILES, true ) . '</pre><br/>' . PHP_EOL;
    }

    if ( isset($_SERVER) && !empty($_SERVER)) {

      $debug_html .=
        '<h4>$_SERVER</h4>' . PHP_EOL .
        '<pre>' . print_r( $_SERVER, true ) . '</pre><br/>' . PHP_EOL;

    }

    if ( !empty( $debug_classes_loaded ) ) {
      $debug_html .=
        '<h4>Classes Loaded</h4>' . PHP_EOL .
        '<pre>' . $debug_classes_loaded . '</pre><br/>' . PHP_EOL;
    }

    if ( !empty( $debug_additional_info ) ) {
      $debug_html .=
        '<h4>Additional Info</h4>' . PHP_EOL .
        '<pre>' . $debug_additional_info . '</pre><br/>' . PHP_EOL;
    }

  $debug_html .= '</div>' . PHP_EOL;

  if ( !empty( $debug_html ) ) {
    echo $debug_html;
  }

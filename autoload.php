<?php

\spl_autoload_register(function ($class) {
  static $map = array (
  'Sukellos\\WP_Sukellos_Login_Wrapper_Loader' => 'sukellos-login-wrapper.php',
  'Sukellos\\WP_Sukellos_Login_Wrapper' => 'class-wp-sukellos-login-wrapper.php',
  'Sukellos\\Admin\\WP_Sukellos_Login_Wrapper_Admin' => 'admin/class-wp-sukellos-login-wrapper-admin.php',
  'Sukellos\\Login_Wrapper_Manager' => 'includes/managers/class-login-wrapper-manager.php',
);

  if (isset($map[$class])) {
    require_once __DIR__ . '/' . $map[$class];
  }
}, true, false);
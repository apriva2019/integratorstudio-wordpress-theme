<?php

  require_once 'pages.php';
  require_once 'posts.php';
  require_once 'user.php';

  add_action('rest_api_init', 'registerPageRoutes');
  add_action('rest_api_init', 'registerPostRoutes');
  add_action('wp_rest_user_create_user', 'user_created', 10, 2);

<?php

  require_once 'pages.php';
  require_once 'posts.php';
  /* require_once 'category.php'; */
  /* require_once 'items_by_cat.php'; */

  add_action('rest_api_init', 'registerPageRoutes');
  add_action('rest_api_init', 'registerPostRoutes');
  /* add_action('rest_api_init', 'registerCategoryRoutes'); */
  /* add_action('rest_api_init', 'registerItemsRoutes'); */

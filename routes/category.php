<?php

function getAllCategories() {
  $taxonomy     = 'menu-category';
  $orderby      = 'menu_order';
  $show_count   = 0;
  $pad_counts   = 0;
  $hierarchical = 1;
  $empty        = 0;

  $args = array(
    'taxonomy' => $taxonomy,
    'orderby' => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'hierarchical' => $hierarchical,
    'hide_empty'   => $empty
  );

  $categories = get_categories($args);

  return $categories;
}

function registerCategoryRoutes() {
  register_rest_route(ENDPOINT_V1, '/menu_cat/(?P<slug>[a-zA-Z0-9-]+)', array(
    'method' => 'GET',
    'callback' => 'getAllCategories'
  ));
}

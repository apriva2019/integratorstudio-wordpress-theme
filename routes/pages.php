<?php
  function childrenContent( $children ) {
    foreach ($children as $child) {
      $data['id'] = $child->ID;
      $data['parent_id'] = $child->post_parent;
      $data['title'] = $child->post_title;
      $data['slug'] = $child->post_name;
      $data['api_content'] = get_field('api_content', $child->ID);

      $request[] = $data;
    }

    return new WP_REST_Response($request, 200);
  };

  function _format_page_obj($raw_post) {
    // in case we use WP-Bakery
    /* WPBMap::addAllMappedShortcodes(); */
    $post_id = $raw_post->ID;

    $children = get_pages(array(
      'child_of'    => $post_id,
      'parent'      => $post_id,
      'sort_column' => 'menu_order',
      'order'       => 'ASC'
    ));

    $page = (object) [
      'ID'               => $post_id,
      'title'            => $raw_post->post_title,
      'content'          => apply_filters('the_content', $raw_post->post_content),
      'slug'             => $raw_post->post_name,
      'featured_img'     => get_field('featured_img', $post_id),
      'hero'             => get_field('hero', $post_id),
      'home_content-img' => get_field('home_content_img', $post_id),
      'meta'             => get_yoast_pages( $post_id ),
      'children'         => childrenContent( $children ),
      'api_content'      => get_field('api_content', $post_id)
    ];
    return $page;
  }
  function getPageBySlug($request) {
    $args = array(
      'post_type' => 'page',
      'orderby'   => 'menu_order',
      'order'     => 'ASC',
      'name'      => $request['slug'],
      'status'    => 'publish'
    );
    $query_result = new WP_Query($args);
    if (is_null($query_result->post)) {
      $err_code = 'Not Found';
      $err_msg = 'page'.'('.$request['slug'].')'.'not found.';
      return new WP_Error($err_code, $err_msg, array('status' => 404));
    } else {
      return _format_page_obj($query_result->post);
    }
  }
  function getPageList() {
    $args = array(
      'post_type' => 'page',
      'order_by' => 'menu_order',
      'status' => 'publish',
      'post_per_page' => -1
    );
    $query_result = new WP_Query($args);
    $posts = array_map('_format_page_obj', $query_result->posts);

    return $posts;
  }
  function registerPageRoutes()  {
    register_rest_route(ENDPOINT_V1, '/pages/', array(
      'method'   => 'GET',
      'callback' => 'getPageList'
    ));
    register_rest_route(ENDPOINT_V1, '/page/(?P<slug>[a-zA-Z0-9-]+)', array(
      'method'   => 'GET',
      'callback' => 'getPageBySlug'
    ));
  }



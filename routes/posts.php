<?php

  function excerpt_ellipsis($limit, $excerpt) {
    $words = explode(' ', $excerpt );

    //if excerpt has more than 20 words, truncate it and append ...
    if( count($words) > $limit ){
        return sprintf("%s&hellip;", implode(' ', array_slice($words, 0, $limit)) );
    }

    //otherwise just put it back together and return it
    return implode(' ', $words);

  }

  function _format_post_obj($raw_post) {
    WPBMap::addAllMappedShortcodes();
    $post_id = $raw_post->ID;

    return (object) [
      'ID'           => $post_id,
      'title'        => $raw_post->post_title,
      'date'         => $raw_post->post_date,
      'excerpt'      => excerpt_ellipsis(30, $raw_post->post_excerpt),
      'content'      => apply_filters('the_content', $raw_post->post_content),
      'slug'         => $raw_post->post_name,
      'featured_img' => get_the_post_thumbnail_url($post_id),
      'thumbnail'    => get_the_post_thumbnail_url($post_id, 'thumbnail'),
      'meta'         => get_yoast_pages( $post_id )
    ];
  }
  function getPostBySlug($request) {
    $args = array(
      'post_type' => 'post',
      'name'      => $request['slug'],
      'status'    => 'publish'
    );
    $query_result = new WP_Query($args);
    if (is_null($query_result->post)) {
      $err_code = 'Not Found';
      $err_msg = 'post'.'( '.$request['slug'].' )'.'not found.';
      return new WP_Error($err_code, $err_msg, array('status' => 404));
    } else {
      return _format_post_obj($query_result->post);
    }
  }
  function getPostsList() {
    $args = array(
      'post_type' => 'post',
      'order_by' => 'menu_order',
      'status' => 'publish',
      'post_per_page' => -1
    );
    $query_result = new WP_Query($args);
    $posts = array_map('_format_post_obj', $query_result->posts);

    return $posts;
  }
  function registerPostRoutes()  {
    register_rest_route(ENDPOINT_V1, '/posts/', array(
      'method'   => 'GET',
      'callback' => 'getPostsList'
    ));
    register_rest_route(ENDPOINT_V1, '/post/(?P<slug>[a-zA-Z0-9-]+)', array(
      'method'   => 'GET',
      'callback' => 'getPostBySlug'
    ));
  }


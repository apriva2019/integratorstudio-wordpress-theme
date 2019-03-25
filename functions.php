<?php
require_once 'config.php';

require_once 'routes/index.php';

// Adding menu support to the api.
function add_menu_support() {
    add_theme_support( 'menus' );
}

add_action( 'after_setup_theme', 'add_menu_support' );

// Changing excerpt length
function new_excerpt_length($length) {
	return 20;
}

// Changing excerpt more
function new_excerpt_more($more) {
	return '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">...<strong>read more</strong></a>';
}

add_theme_support('post-thumbnails', array(
    'post',
    'page',
));

/* YOAST SEO SUPPORT */
function get_yoast_pages($post_id){

    $yoastMeta = array(
        'yoast_wpseo_focuskw'               => get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ),
        'yoast_wpseo_title'                 => get_post_meta( $post_id, '_yoast_wpseo_title', true ),
        'yoast_wpseo_metadesc'              => get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ),
        'yoast_wpseo_linkdex'               => get_post_meta( $post_id, '_yoast_wpseo_linkdex', true ),
        'yoast_wpseo_metakeywords'          => get_post_meta( $post_id, '_yoast_wpseo_metakeywords', true ),
        'yoast_wpseo_meta_robots_noindex'   => get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ),
        'yoast_wpseo_meta_robots_nofollow'  => get_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', true ),
        'yoast_wpseo_meta_robots_adv'       => get_post_meta( $post_id, '_yoast_wpseo_meta-robots-adv', true ),
        'yoast_wpseo_canonical'             => get_post_meta( $post_id, '_yoast_wpseo_canonical', true ),
        'yoast_wpseo_redirect'              => get_post_meta( $post_id, '_yoast_wpseo_redirect', true ),
        'yoast_wpseo_opengraph_title'       => get_post_meta( $post_id, '_yoast_wpseo_opengraph-title', true ),
        'yoast_wpseo_opengraph_description' => get_post_meta( $post_id, '_yoast_wpseo_opengraph-description', true ),
        'yoast_wpseo_opengraph_image'       => get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true ),
        'yoast_wpseo_twitter_title'         => get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true ),
        'yoast_wpseo_twitter_description'   => get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true ),
        'yoast_wpseo_twitter_image'         => get_post_meta( $post_id, '_yoast_wpseo_twitter-image', true )
    );
    return $yoastMeta;

}

function remove_wp_seo_meta_box() {
	remove_meta_box('wpseo_meta', 'location', 'normal');
}  
add_action('add_meta_boxes', 'remove_wp_seo_meta_box', 100);

add_action( 'admin_init', 'remove_yoast_seo_posts_filter', 20 );

function remove_yoast_seo_posts_filter() {
    global $wpseo_meta_columns;

    if ( $wpseo_meta_columns ) {
        remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns, 'posts_filter_dropdown' ) );
    }
}



function add_taxonomies_to_pages() {
    register_taxonomy_for_object_type( 'post_tag', 'page' );
    register_taxonomy_for_object_type( 'category', 'page' );
}
add_action( 'init', 'add_taxonomies_to_pages' );

if ( ! is_admin() ) {
    add_action( 'pre_get_posts', 'category_and_tag_archives' );
}

function category_and_tag_archives( $wp_query ) {
   $my_post_array = array('post','page');

    if ( $wp_query->get( 'category_name' ) || $wp_query->get( 'cat' ) )
    $wp_query->set( 'post_type', $my_post_array );

    if ( $wp_query->get( 'tag' ) )
    $wp_query->set( 'post_type', $my_post_array );
}


/**
 * Remove Junk
 */
remove_action ('wp_head', 'rsd_link');
remove_action ('wp_head', 'wlwmanifest_link');
remove_action ('wp_head', 'wp_shortlink_wp_head');
remove_action ('wp_head', 'wp_generator');
remove_action ('wp_head', 'wp_shortlink_wp_head');
remove_action ('wp_head', 'feed_links', 2 );
remove_action ('wp_head', 'feed_links_extra', 3 );
remove_action ('wp_head', 'wp_resource_hints', 2);
remove_action ('wp_head', 'adjacent_posts_rel_link_wp_head');
remove_action ('wp_head', 'rest_output_link_wp_head'); // json api
remove_action ('wp_head', 'wp_oembed_add_discovery_links');
remove_action ('template_redirect', 'rest_output_link_header', 11, 0 );

// remove admin toolbar
add_filter('show_admin_bar', '__return_false');


/**
 * Disable the emoji's
 */
function disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
    add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
   add_action( 'init', 'disable_emojis' );

   /**
    * Filter function used to remove the tinymce emoji plugin.
    *
    * @param array $plugins
    * @return array Difference betwen the two arrays
    */
   function disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
    return array();
    }
   }

   add_filter('tiny_mce_before_init', 'add_my_options');

   function add_my_options($opt) {
       // $opt is the existing array of options for TinyMCE
       // We simply add a new array element where the name is the name
       // of the TinyMCE configuration setting.  The value of the array
       // object is the value to be used in the TinyMCE config.
       $opt['extended_valid_elements'] = '*[*]';
       return $opt;
   }

   /**
    * Remove emoji CDN hostname from DNS prefetching hints.
    *
    * @param array $urls URLs to print for resource hints.
    * @param string $relation_type The relation type the URLs are printed for.
    * @return array Difference betwen the two arrays.
    */
   function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
    if ( 'dns-prefetch' == $relation_type ) {
    /** This filter is documented in wp-includes/formatting.php */
    $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

   $urls = array_diff( $urls, array( $emoji_svg_url ) );
    }

   return $urls;
   }



// class description_walker extends Walker_Nav_Menu {

// 	public function start_lvl( &$output, $depth = 0, $args = array()) {
// 		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
// 			$t = '';
// 			$n = '';
// 		} else {
// 			$t = "\t";
// 			$n = "\n";
//         }
// 		$indent = str_repeat( $t, $depth );
//         global $description;
// 		$classes = array( "menu--". $description );
// 		$class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
// 		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

// 		$output .= "{$n}{$indent}<ul$class_names>{$n}";
// 	}

//     public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0 )
//     {
//         global $wp_query;
//         $indent = ($depth) ? str_repeat( "\t", $depth ) : '';

//         $title = !empty($item->attr_title) ? esc_attr( $item->attr_title ) : false;
//         $title_HTML = ($title) ? 'data-sub="'.$title.'"' : false;

//         // $indent = str_repeat("\t", $depth);
//         // $output .= "\n$indent<ul class=\"my-sub-menu\">\n";

//         // class
//         $classes = empty($item->classes) ? array() : (array) $item->classes;
//         foreach($classes as $class) {
//             if(
//                 !(preg_match("/menu-/i", $class)) &&
//                 !(preg_match("/current-/i", $class)) &&
//                 !(preg_match("/current_/i", $class))
//             ) {
//                 $thisClass .= $class." ";
//             } else {
//                 // $thisClass = "";
//             }
//         }

//         // url
//         $url = ! empty( $item->url ) ? esc_attr( $item->url ) : '';

//         // sub menu class will be the title
//         $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
//         $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
//         $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
//         $description = ! empty( $item->description) ? '<span>'.esc_attr( $item->description ).'</span>' : '';

//         if($depth !== 0) {
//             $output .= $indent . '<li class="">';
//         } elseif($depth == 0) {
//             $output .= $indent . '<li class="menu__item" '.$title_HTML.'>';
//         } else {
//             $output .= $indent . '<li>';
//         }

//         $item_output = $args->before;
//         if($url !== '#') {
//             $item_output .= '<a'. $attributes .' href="'.$url.'" class="'. $thisClass.'">';
//             $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
//             $item_output .= $description.$args->link_after;
//             $item_output .= '</a>';
//         } else {
//             $item_output .= '<strong>'.apply_filters( 'the_title', $item->title, $item->ID ).'</strong>';
//         }

//         $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
//     }
// }

// // replaces the sub-menu ul classes
// function add_menu_description( $item_output, $item, $depth, $args ) {
//     global $description;
//     $description = $item->attr_title;
//     return $item_output;
// }
// add_filter( 'walker_nav_menu_start_el', 'add_menu_description', 10, 4);




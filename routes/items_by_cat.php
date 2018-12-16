<?php

function getAllItemsInCategory($data) {
    
    /* URL Params (slug) */
    $paramdata = $data->get_params();
    
    /* Set Order */
    $order = (!$paramdata['order']) ? 'ASC' : 'DESC';
    
	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'menu-item',
		//'category_name'	=> $paramdata['slug'],
		'tax_query' => array(
    		array(
    			'taxonomy' => 'menu-category',
    			'field'    => 'slug',
    			'terms'    => $paramdata['cat'],
    		),
    	),
		'orderby'		=> 'menu_order',
		'order'			=> $order
	);
	/* GET COUNT */
	$x = get_posts($args);
	$count = count($x);
	
	if($count <= 0)
	    return "error: possible category mis-type.";
	
	$items 	    = []; 
	$itemdata   = [];
	
	$items['total'] = $count;
	
	foreach($x as $i){
    	$catID = get_term_by('slug', $paramdata['cat'], 'menu-category', 'ARRAY_A' )['term_id'];
    	
		$itemdata[] = array(
			'title'			=> $i->post_title,
			'slug'          => $i->post_name,
			'group'         => get_field( 'menu_item_details', $i->ID ),
			'SEO'           => get_yoast_pages($i->ID),
			'order'         => $i->menu_order,
			'hero'         => get_field( 'category_hero', 'menu-category_'.$catID)
		);
	}
	
	$items['rows'] = $itemdata;
    
    return $items;
}

function registerItemsRoutes() {
    register_rest_route(ENDPOINT_V1, '/menu_items/(?P<cat>[a-zA-Z0-9-]+)', array(
        'method' => 'GET',
        'callback' => 'getAllItemsInCategory'
    ));
}

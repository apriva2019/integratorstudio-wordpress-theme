<?php

function extract_zipcode($address, $remove_statecode = false) {
    $zipcode = preg_match("/\b[A-Z]{2}\s+\d{5}(-\d{4})?\b/", $address, $matches);
    return $remove_statecode ? preg_replace("/[^\d\-]/", "", extract_zipcode($matches[0])) : $matches[0];
}

function curl_things($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $obj = json_decode($result);
    return $obj;
}

function getLocationsByStateOrZip($data) {
    
    /* URL Params (slug) */
    $paramdata = $data->get_params();
    
    /* Clean the paramdata */
    $s1clean = str_replace(["-", "–"], '', sanitize_text_field($paramdata['loc']) );
    
    if( is_numeric($s1clean) ){
        $zipData = (strlen($s1clean) > 5) ? substr_replace($s1clean, '-', 5, 0) : $s1clean;
        
        $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=kneaders+bakery+%26+cafe+near+'.$zipData.'&radius=17000&key='.WP_GMAPS_APIKEY;
        $results = curl_things($url)->results;
        
        $addresses = array();
        foreach ($results as $spot) {
            $zip = extract_zipcode($spot->formatted_address, 1);
            $addresses[] = $zip;
        }
        
        if( !empty(array_filter($addresses)) ){
            $args = array(
        		'numberposts'	=> -1,
        		'post_type'		=> 'location',
                'meta_key'      => 'loc_zipcode',
                'meta_value'    => $addresses
        	);
        	
        	$message = "locations found in your area.";
        }
        else {
            
            $url = 'https://zipstates.rrpartnersdev.com/'.$zipData;
            $sttData = curl_things($url)->state;
 
            $message = "No locations found in your area, pulling state info.";
            
            // Query for state
            $args = array(
        		'numberposts'	=> -1,
        		'post_type'		=> 'location',
                'meta_key'      => 'loc_state',
                'meta_value'    => $sttData
        	);
        }
    }
    else {
        /* Make STATE uppercase */
        $sttData = strtoupper($s1clean);
        
        /* Query for state */        
        $args = array(
    		'numberposts'	=> -1,
    		'post_type'		=> 'location',
            'meta_key'      => 'loc_state',
            'meta_value'    => $sttData
    	);
    }
    
	/* GET COUNT */
	$x = get_posts($args);
	$count = count($x);
	
	if($count <= 0){
	    return new WP_Error( 
	        'kneaders_no_location', 
	        'possible state mis-type, or no locations found in this state.', 
	        array( 'status' => 404 ) 
        );
    }
	
	$items 	    = []; 
	$itemdata   = [];
	
	$items['total'] = $count;
	$items['message'] = ($message) ? $message : '';
	
	foreach($x as $i){
    	$state = get_field('loc_state', $i->ID );
    	
		$itemdata[] = array(
			'title'			=> $i->post_title,
			'address'       => get_field('loc_address', $i->ID ),
			'city'          => get_field('loc_city', $i->ID ),
			'state'         => $state,
			'zip'           => get_field('loc_zipcode', $i->ID ),
			'phone'         => get_field('loc_phone', $i->ID ),
			'hours'         => apply_filters('the_content', get_field('loc_hours', $i->ID )),
			'drive_thru'    => get_field('loc_drivethru', $i->ID )[0],
			'geocode'       => json_decode('['.get_field('loc_geocode', $i->ID ).']'),
			'menu_url'      => get_field('loc_menu_url', $i->ID ),
		);
	}
	$items['rows'] = $itemdata;
    
    return $items;
}

function registerLocationRoutes() {
    register_rest_route(ENDPOINT_V1, '/locations/(?P<loc>[a-zA-Z0-9-]+)', array(
        'method' => 'GET',
        'callback' => 'getLocationsByStateOrZip'
    ));
}

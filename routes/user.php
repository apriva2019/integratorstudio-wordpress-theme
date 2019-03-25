<?php
  function user_created($user, $parameters) {
    $user_id = $user->id;
    if (isset($parameters['displayname'])) {
      update_user_meta( $user_id, 'displayname', $parameters['displayname'] );
    }
    if (isset($parameters['telephone'])) {
      update_user_meta( $user_id, 'telephone', $parameters['telephone'] );
    }
    if (isset($parameters['business_name'])) {
      update_user_meta( $user_id, 'business_name', $parameters['business_name'] );
    }
    if (isset($parameters['description'])) {
      update_user_meta( $user_id, 'description', $parameters['description'] );
    }
  }

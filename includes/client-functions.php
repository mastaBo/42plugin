<?php

/**
 * Get all client
 *
 * @param $args array
 *
 * @return array
 */
function test_get_all_client( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'client-all';
    $items     = wp_cache_get( $cache_key, 'wedevs' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . '42_clients ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'wedevs' );
    }

    return $items;
}

/**
 * Fetch all client from database
 *
 * @return array
 */
function test_get_client_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . '42_clients' );
}

/**
 * Fetch a single client from database
 *
 * @param int   $id
 *
 * @return array
 */
function test_get_client( $id = 0 ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . '42_clients WHERE id = %d', $id ) );
}


/**
 * Insert a new client
 *
 * @param array $args
 */
function test_insert_client( $args = array() ) {
    global $wpdb;
    $defaults = array(
        'id'         => null,
        'name' => '',
        'entity' => '',
        'INN' => '',
        'description' => '',

    );

    $args       = wp_parse_args( $args, $defaults );
    $table_name = '42_clients';

    // some basic validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No Имя компании provided.', 'wedevs' ) );
    }
    if ( empty( $args['entity'] ) ) {
        return new WP_Error( 'no-entity', __( 'No Юр лицо provided.', 'wedevs' ) );
    }
    if ( empty( $args['INN'] ) ) {
        return new WP_Error( 'no-INN', __( 'No ИНН provided.', 'wedevs' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {

       // $args['name'] = current_time( 'mysql' );

        // insert a new
        if ( $wpdb->insert( $table_name, $args ) ) {
            return $wpdb->insert_id;
        }

    } else {

        // do update method here
        if ( $wpdb->update( $table_name, $args, array( 'id' => $row_id ) ) ) {
            return $row_id;
        }
    }

    return false;
}
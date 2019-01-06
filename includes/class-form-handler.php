<?php

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form' ) );
    }

    /**
     * Handle the client new and edit form
     *
     * @return void
     */
    public function handle_form() {
        echo "Handle the form";
        if ( ! isset( $_POST['submit_client'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'client-new' ) ) {
            die( __( 'Are you cheating?', 'wedevs' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', 'wedevs' ) );
        }

        $errors   = array();
        $page_url = admin_url( 'admin.php?page=clients' );
        $field_id = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $entity = isset( $_POST['entity'] ) ? sanitize_text_field( $_POST['entity'] ) : '';
        $INN = isset( $_POST['INN'] ) ? sanitize_text_field( $_POST['INN'] ) : '';
        $description = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';

        // some basic validation
        if ( ! $name ) {
            $errors[] = __( 'Error: Имя компании is required', 'wedevs' );
        }

        if ( ! $entity ) {
            $errors[] = __( 'Error: Юр лицо is required', 'wedevs' );
        }

        if ( ! $INN ) {
            $errors[] = __( 'Error: ИНН is required', 'wedevs' );
        }

        // bail out if error found
        if ( $errors ) {
            $first_error = reset( $errors );
            $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }

        $fields = array(
            'name' => $name,
            'entity' => $entity,
            'INN' => $INN,
            'description' => $description,
        );

        // New or edit?
        if ( ! $field_id ) {

            $insert_id = test_insert_client( $fields );

        } else {

            $fields['id'] = $field_id;

            $insert_id = test_insert_client( $fields );
        }

        if ( is_wp_error( $insert_id ) ) {
            $redirect_to = add_query_arg( array( 'message' => 'error' ), $page_url );
        } else {
            $redirect_to = add_query_arg( array( 'message' => 'success' ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }
}

new Form_Handler();
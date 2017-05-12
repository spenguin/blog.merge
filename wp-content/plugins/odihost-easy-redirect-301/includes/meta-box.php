<?php
add_action( 'save_post', 'easy_301_redirect_save_meta_box_data' );
add_action( 'add_meta_boxes', 'easy_301_redirect_add_meta_box' );

function easy_301_redirect_add_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'easy_301_redirect_sectionid',
			__( '301 redirect', 'easy_301_redirect_textdomain' ),
			'easy_301_redirect_meta_box_callback',
			$screen
		);
	}
}

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function easy_301_redirect_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'easy_301_redirect_save_meta_box_data', 'easy_301_redirect_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, 'original_site_url', true );

	echo '<label for="original_site_url">';
	_e( 'Original site url', 'easy_301_redirect_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="original_site_url" name="original_site_url" value="' . esc_attr( $value ) . '" size="75" />';
	echo "<br/>This is used for easy 301 redirect plugin";
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function easy_301_redirect_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['easy_301_redirect_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['easy_301_redirect_meta_box_nonce'], 'easy_301_redirect_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['original_site_url'] ) ) {
		return;
	}

	// Sanitize user input.
	$my_data = esc_url_raw( $_POST['original_site_url'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'original_site_url', $my_data );
}
?>
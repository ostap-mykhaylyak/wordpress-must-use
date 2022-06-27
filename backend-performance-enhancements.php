<?php

// We don't need this query to run if it's asking for all comments (post_id = 0).
add_filter( 'wp_count_comments', function ( $count, $post_id ) {
	if ( 0 === $post_id ) {
		$stats = array(
			'approved'       => 0,
			'moderated'      => 0,
			'spam'           => 0,
			'trash'          => 0,
			'post-trashed'   => 0,
			'total_comments' => 0,
			'all'            => 0,
		);

		return (object) $stats;
	}
}, 9999, 2 );

// Let's unhook the plugin's that are trying to re-query unnecessarily now.
add_action( 'init', function() {
	// WooCommerce
	if ( class_exists( 'WC_Comments' ) ) {
		remove_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ), 10, 2 );
	}

	// Memberships
	if ( function_exists( 'wc_memberships' ) ) {
		remove_filter( 'wp_count_comments',  array( wc_memberships()->get_user_memberships_instance(), 'exclude_membership_notes_from_comments_count' ), 999, 2 );
	}
}, 100 );

// Remove dashboard widgets that require expensive queries.
add_action( 'wp_dashboard_setup', function() {
	// "At a Glance" widget shows the number of comments on the site.
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );

	// "Activity" widget queries for recent comments.
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );

	// "WooCommerce Status" widget. Not comment related, but can save near 20 seconds of load time on dashboard.
	remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
} );
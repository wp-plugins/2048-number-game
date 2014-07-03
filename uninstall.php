<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
		
/**
 * Remove High Scores upon Uninstall
 */
$options = get_option('wp2048_options');
if ( empty($options['delscores']) )
	return;

delete_option('wp2048_highscore');
$scoreboard = new WP_User_Query( array( 
	'number' => $top,
	'orderby' => 'meta_value',
	'order' => 'DESC',
	'meta_key' => 'wp2048_score', 
	'meta_value' => '0',
	'meta_compare' => '>',
	'fields' => 'ID',
) );	
foreach ( $scoreboard->results as $user_id ) {
	delete_user_meta( $user_id, 'wp2048_score' );
}
?>
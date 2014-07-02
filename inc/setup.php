<?php
defined( 'ABSPATH' ) OR exit;

register_activation_hook( __FILE__, array( 'WP2048_Setup', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WP2048_Setup', 'deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WP2048_Setup', 'uninstall' ) );

class WP2048_Setup
{
	/**
	 * Register Default Values upon Activation
	 */
	public static function activation()
	{	
		if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );
		
		$email_template = array(
			'highscore_new' => array(
				'subject' => 'Your 2048 high score',
				'message' => 'Congratulations! You get the high score of %%SCORE%% on 2048 number game at '.home_url()
			),
			'highscore_lost' => array(
				'subject' => 'You lost the 2048 high score',
				'message' => 'Someone beat your high score on 2048 number game. Play 2048 again on '.home_url().' to beat the new score of %%SCORE%%'
			),
			'highscore_user' => array(
					'subject' => 'Your new 2048 high score',
					'message' => 'Congratulations on your high score of %%SCORE%% on 2048 number game. Play 2048 again at '.home_url()
			),
		);
		$default_options = array(
			'customization' => 0, // default to original 2048
			'hide_howto' => 0, // dont hide
			'scoreboard' => 1,
			'guest_highscore' => 1, // allow non logged-in to submit score
			'notify' => 3, // send to both scenarios
			'userscore' => 1,
			'email_template' => $email_template,
			'tx_viewport' => 'width=device-width, target-densitydpi=160dpi, initial-scale=1.0, maximum-scale=1, user-scalable=no, minimal-ui',
		);
		$default_highscore = array(
			'uid' => 0,
			'email' => '',
			'score' => 0
		);
		
		// add_option already include self check if option already exists
		add_option('wp2048_options', $default_options);
		add_option('wp2048_highscore', json_encode($default_highscore));
    }
	 
	/**
	 * Remove Data upon De-activation
	 */
	public static function deactivation() 
	{
		if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );
		
        // check option to remove data upon deactivation
		$options = get_option('wp2048_options');
		if ( !empty($options['delete']) ) {
			delete_option('wp2048_options');
			delete_option('wp2048_custom');
		}
    }
	
	/**
	 * Remove High Scores upon Uninstall
	 */
	public static function uninstall() 
	{
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		check_admin_referer( 'bulk-plugins' );

		// Important: Check if the file is the one that was registered during the uninstall hook.
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
		
        // check option to remove scores upon uninstall
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
    }
}
<?php
/*
Plugin Name: 2048
Plugin URI: http://wordpress.org/plugins/2048-number-game/
Description: 2048 is a number combination game with the aim to achieve 2048 tile.
Version: 0.2
Author: Envigeek Web Services
Author URI: http://www.envigeek.com/

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
if( ! defined( 'WP2048_VERSION' ) )
	define('WP2048_VERSION', '0.2');
/**
 * Require Additional Functions
 */
require_once('inc/settings.php');
require_once('inc/widgets.php');

class WP2048
{
	public $version = WP2048_VERSION;
	protected $tiles, $option, $custom, $highscore;
	
    public function __construct()
    {
		$this->properties();
		
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_shortcode( '2048', array( $this, 'add_shortcode' ) );
		add_filter( 'widget_text', 'do_shortcode' );
		add_action( 'media_buttons', array( $this, 'editor_button' ), 11 );
		add_action( 'wp_ajax_wp2048_highscore', array( $this, 'ajax_highscore' ) );
		add_action( 'wp_ajax_nopriv_wp2048_highscore', array( $this, 'ajax_highscore' ) );
		
		add_filter( 'plugin_action_links_'.plugin_basename(__FILE__) , array( $this, 'plugin_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2 );
    }
	
	/**
	 *	Load Class Values
	 */
	public function properties() {
		$this->tiles = array('2','4','8','16','32','64','128','256','512','1024','2048');
		$this->option 		= get_option('wp2048_options');
		$this->custom 		= get_option('wp2048_custom');
		$this->highscore	= json_decode(get_option('wp2048_highscore'),true);
	}
	
	/**
	 *	Load Plugin Textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp2048', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
	}
	
	/**
	 * Register Default Values upon Activation
	 */
	public function activation() 
	{	
		$email_template = array(
			'highscore_new' => array(
				'subject' => 'Your 2048 high score',
				'message' => 'Congratulations! You get the high score of %%SCORE%% on 2048 number game at '.home_url()
			),
			'highscore_lost' => array(
				'subject' => 'You lost the 2048 high score',
				'message' => 'Someone beat your high score on 2048 number game. Play 2048 again on '.home_url().' to beat the new score of %%SCORE%%'
			)
		);
		$default_options = array(
			'customization' => 0, // default to original 2048
			'hide_howto' => 0, // dont hide
			'guest_highscore' => 1, // allow non logged-in to submit score
			'notify' => 3, // send to both scenarios
			'email_template' => $email_template,
			'tx_viewport' => 'width=device-width, target-densitydpi=160dpi, initial-scale=1.0, maximum-scale=1, user-scalable=no, minimal-ui',
		);
		$default_highscore = array(
			'uid' => 0,
			'email' => '',
			'score' => 0
		);
		
		// add_option already include self check if option already exists
		add_option('wp2048_version', $this->version);
		add_option('wp2048_options', $default_options);
		add_option('wp2048_highscore', json_encode($default_highscore));
	
    }
	 
	/**
	 * Remove Data upon De-activation
	 */
	public function deactivation() 
	{
        // check option to remove data upon deactivation
		if ( !empty($this->option['delete']) ) {
			delete_option('wp2048_options');
			delete_option('wp2048_custom');
			delete_option('wp2048_highscore');
		}
    }
	
	/**
	 * Add links to Plugin list page
	 */
	public function plugin_links($links) {
		return array_merge(
			array('settings' => '<a href="'.site_url('/wp-admin/options-general.php?page=wp2048').'">Settings</a>'),
			$links
		);
	}
	public function plugin_meta( $links, $file ) {
		if ( $file == plugin_basename(__FILE__) ) {
			return array_merge(
				$links, array('<a href="http://wordpress.org/support/plugin/2048-number-game">Plugin support forum</a>')
			);
		}
		return $links;
	}
	
	 
	/**
	 * Properly Register Scripts and Styles
	 */
	public function enqueue_scripts()
	{
		wp_register_script( '2048_bind_polyfill', plugins_url( 'js/bind_polyfill.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_classlist_polyfill', plugins_url( 'js/classlist_polyfill.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_animframe_polyfill', plugins_url( 'js/animframe_polyfill.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_keyboard_input_manager', plugins_url( 'js/keyboard_input_manager.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_html_actuator', plugins_url( 'js/html_actuator.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_grid', plugins_url( 'js/grid.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_tile', plugins_url( 'js/tile.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_local_storage_manager', plugins_url( 'js/local_storage_manager.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_game_manager', plugins_url( 'js/game_manager.js' , __FILE__ ), array(), $this->version, true );
		wp_register_script( '2048_application', plugins_url( 'js/application.js' , __FILE__ ), array() , $this->version, true );
		
		$jsdepends = array(
			'2048_bind_polyfill',
			'2048_classlist_polyfill',
			'2048_animframe_polyfill',
			'2048_keyboard_input_manager',
			'2048_html_actuator',
			'2048_grid',
			'2048_tile',
			'2048_local_storage_manager',
			'2048_game_manager',
			'2048_application',
			'jquery'
		);
		
		//TODO
		$textvars = array(
			'submitbtn' 	=> __('Submit High Score', 'wp2048'),
			'submitted' 	=> __('High Score Submitted', 'wp2048'),
			'loading' 		=> __('loading...', 'wp2048'),
			'failed' 		=> __('Error Submit Score', 'wp2048'),
		);
		
		wp_register_script( '2048_scripts', plugins_url( 'js/scripts.js' , __FILE__ ), $jsdepends, $this->version , true );
		wp_localize_script( '2048_scripts', 'wp2048', 
			array(
				'ajaxurl'		=> admin_url('admin-ajax.php'),
				'nonce' 		=> wp_create_nonce('wp2048_ajax'),
				'viewport' 		=> isset($this->option['tx_viewport']) ? $this->option['tx_viewport'] : '',
				'metahead' 		=> isset($this->option['metahead']) ? $this->option['metahead'] : false,
				'customtext' 	=> !empty($this->custom['text']) ? array_values($this->custom['text']) : '', //array values reset the keys
				'guest' 		=> isset($this->option['guest_highscore']) ? $this->option['guest_highscore'] : false,
				'highscore' 	=> (int)$this->highscore['score'],
				'textvars' 		=> $textvars,
			) 
		);
		
		wp_register_style( '2048_widget', plugins_url( 'css/widget.css' , __FILE__ ), array(), $this->version );
		wp_register_style( '2048_fonts', plugins_url( 'fonts/clear-sans.css' , __FILE__ ), array(), $this->version );
		wp_register_style( '2048_style', plugins_url( 'css/style.css' , __FILE__ ), array('2048_fonts'), $this->version );
	}
	

	/**
	 * Function to Output Game Board Shortcode
	 */
	public function add_shortcode($atts, $content = null) 
	{
		// enqueue as early as possible
		wp_enqueue_script('2048_scripts');
		wp_enqueue_style('2048_style');
		
		$js = $css = '';
		
		// populate attributes with default values
		extract(shortcode_atts(array(
			'custom'	=> isset($this->option['customization']) ? $this->option['customization'] : false,
			'feature'	=> isset($this->custom['feature']) ? implode(",",array_keys($this->custom['feature'])) : '',
			'howto' 	=> isset($this->option['hide_howto']) ? false : true,
			'fontcolor'	=> isset($this->custom['color']['font']) ? $this->custom['color']['font'] : '',
			'bgcolor'	=> isset($this->custom['color']['bg']) ? $this->custom['color']['bg'] : '',
			'gridcolor'	=> isset($this->custom['color']['grid']) ? $this->custom['color']['grid'] : '',
			'text' 		=> ( isset($this->custom['text']) && isset($this->custom['feature']['text']) ) ? implode(",", $this->custom['text']): '',
			'color' 	=> ( isset($this->custom['color']) && isset($this->custom['feature']['color']) ) ? implode(",", $this->custom['color']): '',
			'size' 		=> ( isset($this->custom['size']) && isset($this->custom['feature']['size']) ) ? implode(",", $this->custom['size']): '',
		), $atts));
		
		
		/**
		 * Customized Features
		 */
		if ( !empty($custom) ) {
		
			$features = explode(",",$feature);
			
			$css .= ( !empty($fontcolor) && in_array('fontcolor',$features) ) ? ".wp2048.custom {color: ".$fontcolor.";}" : '';
			$css .= ( !empty($bgcolor) && in_array('bgcolor',$features) ) ? ".wp2048.custom .grid-cell {background: ".$bgcolor.";}" : '';
			$css .= ( !empty($gridcolor) && in_array('gridcolor',$features) ) ? ".wp2048.custom .game-container {background: ".$gridcolor.";}" : '';

			$custom_colors = explode(",", $color);
			$custom_sizes = explode(",", $size);
			$custom_images = ( !empty($content) && in_array('image',$features) ) ? explode(",", $content) : array_values($this->custom['image']) ;
			
			if ( in_array( array('color','image','size'), $features ) ) {
				foreach($this->tiles as $key => $tile) {
					$css .= ".wp2048.custom .tile.tile-".$tile." .tile-inner {";
						$background = '';
						$background .= ( !empty($custom_images[$key]) && in_array('image',$features) ) ? "url(".$custom_images[$key].") no-repeat " : '';
						$background .= ( !empty($custom_colors[$key]) && in_array('color',$features) ) ? $custom_colors[$key] : '';
						$css .= !empty($background) ? "background: ".$background.";" : '';
						
						$css .= ( !empty($custom_images[$key]) && in_array('image',$features) ) ? "background-size: cover; font-size: 0;" : '';
						$css .= ( !empty($custom_sizes[$key]) && in_array('size',$features) ) ? "font-size: ".$custom_sizes[$key]."px;" : '';
					$css .= "}";
				}
			}
			
			if ( !empty($text) && in_array('text',$features) ) {
				$js .= "window.wp2048_customtext = true; ";
				$custom_texts = explode(",", $text);
				// Override if custom text on shortcode is different than default
				if ( $custom_texts != array_values($this->custom['text']) ) {
					//re-sanitize texts just to be careful
					array_map('sanitize_text_field', $custom_texts);
					$js .= "var shortcodetexts = ".json_encode($custom_texts, JSON_FORCE_OBJECT)."; "; //TODO
					$js .= "if (wp2048.length) wp2048.customtext = shortcodetexts; ";
				}
			}
		}
		
		if ( !empty($js) ) {
			echo "<script type='text/javascript'>";
			echo "/* <![CDATA[ */";
			echo $js;
			echo "/* ]]> */";
			echo "</script>";
		}
		if ( !empty($css) ) {
			echo "<style>";
			echo $css;
			echo "</style>";
		}
		
		echo ($custom) ? '<div class="wp2048 custom">' : '<div class="wp2048">';
		echo 
		'<div class="above-game">
		  <p class="game-intro">Join the numbers and get to the <strong>2048 tile!</strong></p>
		  <a class="restart-button">New Game</a>
		</div>
		<div class="heading">
		  <div class="scores-container">
			<div class="score-container">0</div>
			<div class="best-container">0</div>
			<div class="sitescore-container">'.$this->highscore['score'].'</div>
		  </div>
		</div>';
		
		echo '<div class="game-container">';
		
		$email_field = ( !is_user_logged_in() && isset($this->option['guest_highscore']) ) ? '<input type="email" value="" name="email" id="em" class="mailing-list-email-field" placeholder="Your email address" required="required" spellcheck="false">' : '';
		
		echo
		'<div class="game-message">
			<p></p>
			<div class="lower">
				<a class="keep-playing-button">Keep going</a>
			  <a class="retry-button">Try again</a>
			  <div class="mailing-list">
				<form action="" method="post" id="wp2048" name="wp2048" class="mailing-list-form">
				  <strong>You Beat this site High Score</strong>'.$email_field.'
				  <button type="submit" class="highscore-button">Submit High Score</button>
				</form>
			  </div>
			</div>
		</div>';
		
		echo file_get_contents( plugin_dir_path( __FILE__ ).'board.html' );
		echo '</div>'; //close .game-container
		
		if ($howto) {
			echo
			'<p class="game-explanation">
				<strong class="important">How to play:</strong> Use your <strong>arrow keys</strong> to move the tiles. When two tiles with the same number touch, they <strong>merge into one!</strong>
			</p>';
		}
		echo '</div>'; //close .wp2048
	}

	/**
	 * Add Shortcode Button to Post Editor
	 */
	public function editor_button()
	{
		echo '&nbsp;&nbsp;';
		echo '<img src="'.plugins_url( '2048-icon.png' , __FILE__ ).'" height="25px" width="25px" />';
		
		$shortcode_tags = array(
			'2048' => 'Use Defaults',
			'2048 custom=0' => 'Original 2048',
			'2048 custom=1' => 'Custom Features',
		);
		
		echo '&nbsp;<select id="wp2048_sc_select"><option>- Add Shortcode -</option>';
		foreach ($shortcode_tags as $shortcode => $title){	
			echo '<option value="['.$shortcode.']">'.$title.'</option>';		
		}
		echo '</select>';
		echo '&nbsp;&nbsp;<a href="'.site_url('/wp-admin/options-general.php?page=wp2048').'" class="button" style="display:inline-block !important;">Settings</a>';

		echo '<script type="text/javascript">
		jQuery(document).ready(function($){
			$("#wp2048_sc_select").change(function() {
				send_to_editor($("#wp2048_sc_select :selected").val());
				return false;
			});
		});
		</script>';
	}

	/**
	 * AJAX Function Submit Highscore
	 */
	public function ajax_highscore() 
	{
		check_ajax_referer( 'wp2048_ajax', 'security' );
		parse_str($_POST['postdata'], $postdata);
		if ( !empty($postdata) ) {
			$score = (int)$postdata['score'];	
			if ( !empty($score) ) {
				// Logged-in User
				if ( is_user_logged_in() ) {
					global $current_user;
					if ( !isset($current_user) ) get_currentuserinfo();
					$user_id = $current_user->ID;
					$email = $current_user->user_email;
				} else {
					// validate email
					$email = $postdata['email'];
					if ( is_email($email) ) {
						$user_id = 0;
					} else {
						// not valid email
						echo false;
						die();
					}
				}
				do_action( 'wp2048_highscore', $score, $user_id );
				// compare highscore & submit if more than existing
				if ( $score > (int)$this->highscore['score'] )
				{
					$highscore = array(
						'uid' => $user_id,
						'email' => $email,
						'score' => $score
					);
					if ( update_option('wp2048_highscore', json_encode($highscore)) )
					{
						//email those affected
						$notify = $this->option['notify'];
						if ( !empty($notify) ) {
							// new high score email
							if ( $notify == 3 || $notify == 1 ) {
								wp_mail( 
									$highscore['email'], 
									str_replace('%%SCORE%%', $score, $this->option['email_template']['highscore_new']['subject']), 
									str_replace('%%SCORE%%', $score, $this->option['email_template']['highscore_new']['message'])
								);
							}
							// high score lost email
							if ( $notify == 3 || $notify == 2 ) {
								wp_mail( 
									$this->highscore['email'], 
									str_replace('%%SCORE%%', $score, $this->option['email_template']['highscore_lost']['subject']), 
									str_replace('%%SCORE%%', $score, $this->option['email_template']['highscore_lost']['message'])
								);
							}
						}
						// output the score to update DOM
						echo $score;
					}	
				} else {
					echo false;
				}
			}
		}
		die();
	}
	
}
$wp2048 = new WP2048();
?>
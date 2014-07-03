<?php
defined( 'ABSPATH' ) OR exit;

$wp2048_settings = new WP2048_Settings();
class WP2048_Settings extends WP2048
{		
	public function __construct()
    {		
		parent::properties();
		parent::textdomain();
		
        add_action( 'admin_menu', array( $this, 'settings_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'wp_ajax_wp2048_shortcode', array( $this, 'shortcode_generator' ) );
    }

    /**
     * Enqueue scripts
     */
	public function settings_enqueue()
	{
		wp_enqueue_style( '2048_settings', plugins_url( 'css/settings.css' , dirname(__FILE__) ), array('wp-color-picker'), self::VER );
		wp_enqueue_media();
		wp_enqueue_script( '2048_settings', plugins_url( 'js/settings.js' , dirname(__FILE__) ), array('wp-color-picker'), self::VER , true );
		wp_localize_script( '2048_settings', 'wp2048', 
			array(
				'ajaxurl'		=> admin_url('admin-ajax.php'),
				'nonce' 		=> wp_create_nonce('wp2048_ajax'),
				'media_modal_title' => __('Add Image to 2048 Tile', 'wp2048'),
				'media_modal_button' => __('Assign Image', 'wp2048'),
				'alert_image_size' => __('Minimum image dimension is 107px width and height.', 'wp2048'),
				'alert_not_image' => __('Selected media not a valid image format.', 'wp2048'),
			) 
		);
	}
	/**
     * Add settings menu and page
     */
    public function settings_menu()
    {
        // This page will be under "Settings"
        $page = add_options_page(
            'Settings Admin', 
            '2048 Number Game', 
            'manage_options', 
            'wp2048', 
            array( $this, 'settings_page' )
        );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_enqueue' ) );
    }

    /**
     * Settings page callback
     */
    public function settings_page()
    {
		// Set the active tabs
		$active_tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : '';
        ?>
        <div class="wrap">
            <h2><?php _e('2048 Number Game','wp2048') ?></h2>
			<h2 class="nav-tab-wrapper">
				<a href="?page=wp2048" class="nav-tab <?php echo ($active_tab == '') ? 'nav-tab-active' : ''; ?>"><?php _e('Settings','wp2048') ?></a>
				<a href="?page=wp2048&tab=customize" class="nav-tab <?php echo ($active_tab == 'customize') ? 'nav-tab-active' : ''; ?>"><?php _e('Custom Features: Text, Font, Color & Image Tiles','wp2048') ?></a>
			</h2>
            <form method="post" action="options.php" id="wp2048-settings">
            <?php
				if( $active_tab == 'customize' ) {		
					add_thickbox();
					settings_fields( 'wp2048_customize_settings' );   
					do_settings_sections( 'wp2048_customize_page' );;
				} else {
					settings_fields( 'wp2048_options_settings' );   
					do_settings_sections( 'wp2048_options_page' );
				}
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function settings_init()
    {   	
        register_setting(
            'wp2048_options_settings',
            'wp2048_options',
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'wp2048_options_general', // ID
            '', // Title
            array( $this, 'print_section_general' ), // Callback
            'wp2048_options_page' // Page
        );  
		
		add_settings_field(
            'customization', // ID
            'Default to Custom Features', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'customization', 'label' => 'Checking this will make default shortcode to use customizations' )			
        );
		
		add_settings_field(
            'hide_howto', // ID
            'Hide How-To', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'hide_howto', 'label' => 'Hide game instructions below game board' )			
        );
		
		add_settings_field(
            'scoreboard', // ID
            'Score Board', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'scoreboard', 'label' => 'Check to enable storing each user\'s high score' )			
        );
		
		add_settings_field(
            'displayname', // ID
            'Display Name', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'displayname', 'label' => 'Score board will list user by their display name option. Can be overridden in shortcode.' )			
        );
		
		add_settings_field(
            'guest_highscore', // ID
            'Guest High Score', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'guest_highscore', 'label' => 'Allow non logged-in user to submit high score' )			
        );
		
		add_settings_field(
            'notify', // ID
            'Notify High Score', // Title 
            array( $this, 'notify_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general' // Section 		
        );
		
		add_settings_field(
            'userscore', // ID
            'User Score', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_general', // Section 
			array( 'field' => 'userscore', 'label' => 'Notify users when they update own high score' )			
        );
		
		// Email Templates
		add_settings_section(
            'wp2048_options_email', // ID
            'Email Templates', // Title
            array( $this, 'print_section_email' ), // Callback
            'wp2048_options_page' // Page
        );
		
		add_settings_field(
            'highscore_new', 
            'New High Score', 
            array( $this, 'email_template_callback' ), 
            'wp2048_options_page', 
            'wp2048_options_email',
			array( 'field' => 'highscore_new', 'desc' => 'Congratulates on achieving site\'s new high score' )
        );
		
		add_settings_field(
            'highscore_lost', 
            'Lost High Score', 
            array( $this, 'email_template_callback' ), 
            'wp2048_options_page', 
            'wp2048_options_email',
			array( 'field' => 'highscore_lost', 'desc' => 'Sent to previous user their high score just broken' )	
        );
		
		add_settings_field(
            'highscore_user', 
            'User High Score', 
            array( $this, 'email_template_callback' ), 
            'wp2048_options_page', 
            'wp2048_options_email',
			array( 'field' => 'highscore_user', 'desc' => 'Congratulates on updating own high score' )	
        );
		
		// Advanced Options
		add_settings_section(
            'wp2048_options_advanced', // ID
            'Advanced Options', // Title
            array( $this, 'print_section_advanced' ), // Callback
            'wp2048_options_page' // Page
        );
		
		add_settings_field(
            'tx_viewport', 
            'Meta Viewport', 
            array( $this, 'text_callback' ), 
            'wp2048_options_page', 
            'wp2048_options_advanced',
			array( 'field' => 'tx_viewport', 'desc' => 'Only applies to page with [2048] shortcode. If left blank, it will not modify (or add into) your theme\'s viewport.' )	
        );
		
		add_settings_field(
            'metahead', // ID
            'Disable Mobile Friendly', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_advanced', // Section 
			array( 'field' => 'metahead', 'label' => 'Do not add meta for mobile friendly. Only applies to page with [2048] shortcode.' )			
        );
		
		add_settings_field(
            'delete', // ID
            'Delete Plugin Data', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_advanced', // Section 
			array( 'field' => 'delete', 'label' => 'Remove plugin settings and customizations (excluding high scores) upon de-activation. Re-activation will reset plugin settings to default.' )			
        );
		
		add_settings_field(
            'delscores', // ID
            'Delete High Scores', // Title 
            array( $this, 'checkbox_callback' ), // Callback
            'wp2048_options_page', // Page
            'wp2048_options_advanced', // Section 
			array( 'field' => 'delscores', 'label' => 'WARNING! Remove all stored high scores upon plugin un-installation.' )			
        );
		
		/**
		 * Customizations Tab
		 */
		register_setting(
            'wp2048_customize_settings',
            'wp2048_custom',
            array( $this, 'sanitize_customize' )
        );
		
		add_settings_section(
            'wp2048_customize_section', // ID
            '', // Title
            array( $this, 'print_section_customize' ), // Callback
            'wp2048_customize_page' // Page
        );
		
		add_settings_field(
            'feature', // ID
            'Enabled Features', // Title 
            array( $this, 'features_callback' ), // Callback
            'wp2048_customize_page', // Page
            'wp2048_customize_section' // Section			
        );
		
		add_settings_field(
            'color_font', // ID
            'Font Color', // Title 
            array( $this, 'colorpicker_callback' ), // Callback
            'wp2048_customize_page', // Page
            'wp2048_customize_section', // Section		
			array( 'type' => 'font' )
        );
		
		add_settings_field(
            'color_bg', // ID
            'Background Color', // Title 
            array( $this, 'colorpicker_callback' ), // Callback
            'wp2048_customize_page', // Page
            'wp2048_customize_section', // Section	
			array( 'type' => 'bg' )
        );
		
		add_settings_field(
            'color_grid', // ID
            'Grid Color', // Title 
            array( $this, 'colorpicker_callback' ), // Callback
            'wp2048_customize_page', // Page
            'wp2048_customize_section', // Section		
			array( 'type' => 'grid' )
        );
		
		foreach($this->tiles as $tile) {
			add_settings_field(
				$tile, 
				'Tile '.$tile, 
				array( $this, 'customize_callback' ), 
				'wp2048_customize_page', 
				'wp2048_customize_section',
				array( 'tile' => $tile )
			);
		}
		
		add_settings_section(
            'wp2048_custom_shortcode', // ID
            '', // Title
            array( $this, 'print_section_shortcode' ), // Callback
            'wp2048_customize_page' // Page
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
		
		$blank_subject = false;
		foreach ($input as $field => $value) {
			if ($field == 'email_template') {
				$email_template = $value;
				foreach ($email_template as $etype => $template) {
					if ( !empty($template['subject']) ) {
						$sanitized_template[$etype]['subject'] = sanitize_text_field($template['subject']);
					} else {
						$sanitized_template[$etype]['subject'] = $this->option['email_template'][$etype]['subject'];
						$blank_subject = true;
					}
					$sanitized_template[$etype]['message'] = isset($template['message']) ? esc_textarea($template['message']) : '';
				}
				$new_input[$field] = $sanitized_template;
			} else {
				$exp = explode('_',$field);
				if ($exp[0] == 'ta') {
					$new_input[$field] = esc_textarea($value);
				} elseif ($exp[0] == 'tx') {
					$new_input[$field] = sanitize_text_field($value);
				} else {
					$new_input[$field] = absint($value);
				}
			}
		}
		
		// email subject cannot be blank
		if ($blank_subject) {
			add_settings_error(
				'wp2048_blanksubject',
				esc_attr( 'settings_updated' ),
				__('Email subject cannot be left blank. Affected field(s) reverted to previous setting.','wp2048'),
				'error'
			);
		}
		
        return $new_input;
    }
	
	/**
     * Sanitize function for Customizer
     */
    public function sanitize_customize( $input )
    {
        $new_input = array();
		
		if ( !empty($input['feature']) )
			foreach($input['feature'] as $type => $feature) { $new_input['feature'][$type] = absint($feature); }
		
		// Custom Text Sanitization
		$texts = $input['text'];
		foreach ($texts as $tkey => $text) {
			if ( empty($text) ) {
				//fill-in blank input with defaults
				$new_input['text'][$tkey] = (string)$tkey;
				$partial = true;
			} else {
				$new_input['text'][$tkey] = sanitize_text_field($text);
			}
		}
		
		// Custom Font Size
		$error_size = false;
		$sizes = $input['size'];
		foreach ($sizes as $skey => $size) {
			$new_input['size'][$skey] = absint($size);
			if ( !empty($size) && !is_numeric($size) ) {
				$error_size = true;
			}
		}
		if ($error_size) {
			add_settings_error(
				'wp2048_fontsize',
				esc_attr( 'settings_updated' ),
				__('Font size accepts positive integer only. Invalid input has been discarded.','wp2048'),
				'error'
			);
		}
		
		// Image uploads
		$images = $input['image'];
		foreach ($images as $ikey => $image){
			if ( empty($image) ) {
				//fill-in blank input with defaults
				$new_input['image'][$ikey] = 0;
			} else {
				// allow only jpg, png and gif images
				$new_input['image'][$ikey] = in_array(strtolower(end(explode('.', $image))), array('jpg', 'jpeg', 'png', 'gif')) ? $image : '';
			}
		}
		
		// Custom Colors
		$error_color = array();
		$colors = $input['color'];
		foreach ($colors as $ckey => $color) {
			if ( !empty($color) ) {
				$color = sanitize_text_field($color);
				if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color) ) {
					$new_input['color'][$ckey] = $color;
				} else {
					$error_color[] = $ckey;
				}
			}
		}
		if ( !empty($error_color) ) {
			$error_color = implode(", ",$error_color);
			add_settings_error(
				'wp2048_hexcolor',
				esc_attr( 'settings_updated' ),
				__('Some color fields contains invalid HEX values. Invalid input has been discarded. Check tile ','wp2048').$error_color,
				'error'
			);
		}
		
        return $new_input;
    }
	
	/**
     * Function to sanitize color picker
     */
    private function sanitize_colorpicker( $input )
    {
        $output = array();
		$error = false;
		foreach ($input as $key => $color) {
			if ( !empty($color) ) {
				$color = sanitize_text_field($color);
				if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color) ) {
					$output['color'][$key] = $color;
				} else {
					$error = true;
				}
			}
		}
		
		return array( 'output' => $output, 'error' => $error );
	}

    /** 
     * Print the Section text
     */
    public function print_section_general()
    {
		// Some know-how
		
		echo '<p>To add 2048 Number Game on your WordPress site, simply use <code>[2048]</code> shortcode into any page or post. You can also add the shortcode using the button on post editor when you are creating or updating a page or post. Configure the default settings on this page.</p>';
		
		// Show High Score
		if ( 0 === $this->highscore['uid'] ) {
			//$hs_user = 'Guest ('.$this->highscore['email'].')';
			$hs_user = 'Guest';
		} else {
			$get_user = get_user_by( 'id', $this->highscore['uid'] );
			//$hs_user = $get_user->display_name.' ('.$get_user->user_email.')';
			$hs_user = $get_user->display_name;
		}
		
		echo '<h4>How the high score works?</h4>';
		echo
		'<div class="scores-container">
			<div class="highscore-container">'.$this->highscore['score'].'</div>
			<div class="userscore-container">'.$hs_user.'</div>
		</div>';
		echo 'The game keep the site\'s high score and every logged-in user\'s high score when score board is enabled. When someone ended a game (either won or game over) and if their high score more than your site\'s high score or their personal high score record, the game will save the new score. It is automatically saved for any logged-in users. If you enable to allow guest users to submit high score, an email field will appear to them before the score submission. The email address is required to notify them of new high score updates.';
    }
	
    public function print_section_email()
    {
		echo 'Use <code>%%SCORE%%</code> in the email template below to replace with the new high score.';
    }
	
	public function print_section_advanced()
    {
		echo '<p class="advanced-options">Only modify the following options if you have compatibility issues or know what you are doing.<p>';
    }

    public function print_section_customize()
    {
		echo '<p>This settings page is for default values when you are using shortcode <code>[2048 custom=1]</code> or when you have set to default the game to use the custom features on the <a href="'.site_url('/wp-admin/options-general.php?page=wp2048').'">settings</a> tab. You able to customize various cosmetic elements of the game. For any fields you left blank or did not check the box(es) of enabled features, that particular option will fall back into the original 2048 appearance.</p>';
		
		echo '<strong>Tips on using custom image for tiles</strong>';
		echo '<p>Minimum image dimension is 107px width and height. The game tiles resizes to 58px on smaller screens. Recommended to use a perfect square image. The plugin will force your image to appear in perfect square anyway. Do not use transparent PNGs as you will see overlapping image tiles when merging tiles.</p>';
		
		//$this->print_customize_preview();
    }
	
	public function print_section_shortcode()
    {
		echo '<h3 style="display:inline">Custom Shortcode</h3>&nbsp;&nbsp;<input type="button" id="scgen" class="button" value="Generate" />';
		echo '<div id="customsc"></div>';
		echo '<p>By using the shortcode <code>[2048 custom=1]</code> on any page or post, it will display the game based on the saved values on this page. But that only gives you two options, either the original 2048 design or the customized settings here. The <strong>generate button</strong> above provides you unlimited 2048 game designs by generating custom shortcodes.</p>';
		echo '<h4></h4>';
		echo '<ol>';
		echo '<li>Perform your changes as required in the above settings form</li>';
		echo '<li>Check/uncheck the boxes of custom features as needed</li>';
		echo '<li>Do not submit the changes on this page</li>';
		echo '<li>Click the Generate button</li>';
		echo '</ol>';
    }
	
	/**
	 * AJAX Function: Generates Custom Shortcodes in Settings Page
	 */
	public function shortcode_generator()
    {
		check_ajax_referer( 'wp2048_ajax', 'security' );
		parse_str($_POST['postdata'], $postdata);
		$custom = $postdata['wp2048_custom'];
		$feature = isset($custom['feature']) ? array_keys($custom['feature']) : array();
		
		$sc = "[2048 custom=1";
		$sc .= !empty($custom['feature']) ? " feature='".implode(",",$feature)."'" : '';
		$sc .= ( !empty($custom['color']['font']) && in_array('fontcolor',$feature) ) ? " fontcolor=".$custom['color']['font'] : '';
		$sc .= ( !empty($custom['color']['bg']) && in_array('bgcolor',$feature) ) ? " bgcolor=".$custom['color']['bg'] : '';
		$sc .= ( !empty($custom['color']['grid']) && in_array('gridcolor',$feature) ) ? " gridcolor=".$custom['color']['grid'] : '';
		
		$attrs = array('text','color','size');
		foreach($attrs as $attr) {
			if ( !empty($custom[$attr]) && !empty($custom['feature'][$attr]) ) {
				$output = array();
				foreach($this->tiles as $key => $tile) {
					$default = ($attr == 'text') ? $tile : 0;
					$output[$tile] = empty($custom[$attr][$tile]) ? $default : $custom[$attr][$tile];
				}
				$sc .= " ".$attr."='".implode(",", $output)."'";	
			}
		}
		
		if ( !empty($custom['image']) && !empty($custom['feature']['image']) ) {
			$images = array();
			foreach($this->tiles as $key => $tile) {
				$images[$tile] = empty($custom['image'][$tile]) ? 0 : $custom['image'][$tile];
			}
			$sc .= "]".implode(",", $images)."[/2048";	
		}
		
		$sc .= "]";
		echo $sc;
		die();
	}
	
	/** 
     * Get the settings option array and print one of its values
     */
    public function checkbox_callback($args)
    {
		$field = $args['field'];
		$label = $args['label'];
		$checked = isset($this->option[$field]) ? checked( 1, $this->option[$field], false ) : '';
        printf( '<input type="checkbox" id="wp2048_%1$s" name="wp2048_options[%1$s]" value="1" %2$s />',  $field, $checked );
		printf( '<label for="wp2048_%1$s"> %2$s</label>', $field, $label );
    }

    public function number_callback()
    {
        printf(
            '<input type="text" name="wp2048_options[id_number]" class="small-text" value="%s" />',
            isset( $this->option['id_number'] ) ? esc_attr( $this->option['id_number']) : ''
        );
    }

    public function text_callback($args)
    {	
		$field = $args['field'];
		$desc = $args['desc'];
        printf(
            '<input type="text" name="wp2048_options[%1$s]" class="regular-text" value="%2$s" /><span class="description"> %3$s</span>',
            $field, isset( $this->option[$field] ) ? esc_attr( $this->option[$field]) : '', $desc
        );
    }
	
	public function textarea_callback($args)
    {	
		$field = $args['field'];
		$desc = $args['desc'];
        printf(
            '<textarea name="wp2048_options[%1$s]" class="large-text" rows="3">%2$s</textarea><br><span class="description"> %3$s</span>',
            $field, isset( $this->option[$field] ) ? esc_attr( $this->option[$field]) : '', $desc
        );
    }
	
	public function notify_callback()
    {
		$notify = isset($this->option['notify']) ? $this->option['notify']: '';
		
		$html = '<input type="radio" id="notify_0" name="wp2048_options[notify]" value="0"'.checked(0,$notify,false).'/>';
		$html .= '<label for="notify_0">Don\'t send any emails</label>';
		$html .= '<br>';
		$html .= '<input type="radio" id="notify_1" name="wp2048_options[notify]" value="1"'.checked(1,$notify,false).'/>';
		$html .= '<label for="notify_1">Congratulates only</label>';
		$html .= '<br>';
		$html .= '<input type="radio" id="notify_2" name="wp2048_options[notify]" value="2"'.checked(2,$notify,false).'/>';
		$html .= '<label for="notify_2">Previous score broken only</label>';
		$html .= '<br>';
		$html .= '<input type="radio" id="notify_3" name="wp2048_options[notify]" value="3"'.checked(3,$notify,false).'/>';
		$html .= '<label for="notify_3">Both scenarios</label>';
		
		echo $html;
    }
	
	public function email_template_callback($args)
    {	
		$field = $args['field'];
		$desc = $args['desc'];
		
		printf(
            '<input type="text" name="wp2048_options[email_template][%1$s][subject]" class="regular-text" value="%2$s" placeholder="Email Subject" /><span class="description"> Email subject cannot be left blank</span>',
            $field, isset( $this->option['email_template'][$field]['subject'] ) ? esc_attr( $this->option['email_template'][$field]['subject'] ) : ''
        );
		
        printf(
            '<textarea name="wp2048_options[email_template][%1$s][message]" class="large-text" rows="3">%2$s</textarea><br><span class="description"> %3$s</span>',
            $field, isset( $this->option['email_template'][$field]['message'] ) ? esc_attr( $this->option['email_template'][$field]['message'] ) : '', $desc
        );
    }
	
	public function colorpicker_callback($args)
    {
		$colortype = $args['type'];
		$default = array('font' => '#776e65', 'bg' => '#eee4da', 'grid' => '#bbada0');
        printf(
			'<input type="text" name="wp2048_custom[color][%1$s]" class="color-picker" value="%2$s" />',
			$colortype, isset( $this->custom['color'][$colortype] ) ? esc_attr( $this->custom['color'][$colortype] ) : ''
		);
		printf(
			'&nbsp;&nbsp;<p> Default color is <span style="background:%1$s">&nbsp; %2$s &nbsp;</span></p>',
			$default[$colortype], $default[$colortype]
		);
    }
	
	public function features_callback()
    {
        
		printf(
			'<input type="checkbox" id="wp2048_feature_text" name="wp2048_custom[feature][text]" value="1" %s />',
			isset($this->custom['feature']['text']) ? checked( 1, $this->custom['feature']['text'], false ) : ''
		);
		printf( '<label for="wp2048_feature_text"> %s</label><br>', __('Custom Tile\'s Text','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_size" name="wp2048_custom[feature][size]" value="1" %s />',
			isset($this->custom['feature']['size']) ? checked( 1, $this->custom['feature']['size'], false ) : ''
		);
		printf( '<label for="wp2048_feature_size"> %s</label><br>', __('Custom Font Size','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_image" name="wp2048_custom[feature][image]" value="1" %s />',
			isset($this->custom['feature']['image']) ? checked( 1, $this->custom['feature']['image'], false ) : ''
		);
		printf( '<label for="wp2048_feature_image"> %s</label><br>', __('Custom Tile\'s Image','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_color" name="wp2048_custom[feature][color]" value="1" %s />',
			isset($this->custom['feature']['color']) ? checked( 1, $this->custom['feature']['color'], false ) : ''
		);
		printf( '<label for="wp2048_feature_color"> %s</label><br>', __('Custom Tile\'s Color','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_fontcolor" name="wp2048_custom[feature][fontcolor]" value="1" %s />',
			isset($this->custom['feature']['fontcolor']) ? checked( 1, $this->custom['feature']['fontcolor'], false ) : ''
		);
		printf( '<label for="wp2048_feature_fontcolor"> %s</label><br>', __('Custom Font Color','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_bgcolor" name="wp2048_custom[feature][bgcolor]" value="1" %s />',
			isset($this->custom['feature']['bgcolor']) ? checked( 1, $this->custom['feature']['bgcolor'], false ) : ''
		);
		printf( '<label for="wp2048_feature_bgcolor"> %s</label><br>', __('Custom Background Color','wp2048')  );
		
		printf(
			'<input type="checkbox" id="wp2048_feature_gridcolor" name="wp2048_custom[feature][gridcolor]" value="1" %s />',
			isset($this->custom['feature']['gridcolor']) ? checked( 1, $this->custom['feature']['gridcolor'], false ) : ''
		);
		printf( '<label for="wp2048_feature_gridcolor"> %s</label><br>', __('Custom Grid Color','wp2048')  );
		
		echo '<p class="description">'.__('Disabled features will fall back to default values in the original 2048','wp2048').'</p>';
    }
	
    public function customize_callback($args)
    {
		$tile = $args['tile'];
		// Custom Text
		printf(
            '<input type="text" name="wp2048_custom[text][%1$s]" value="%2$s" placeholder="%1$s"/>',
            $tile, ( !empty($this->custom['text'][$tile]) && $this->custom['text'][$tile] != $tile ) ? esc_attr( $this->custom['text'][$tile] ) : ''
        );
		// Font Size
		printf(
            '&nbsp;&nbsp;<label for="wp2048_size_%1$s">Font Size </label><input type="text" id="wp2048_size_%1$s" name="wp2048_custom[size][%1$s]" class="small-text" value="%2$s" /><span class="description">px</span>',
            $tile, !empty( $this->custom['size'][$tile] ) ? esc_attr( $this->custom['size'][$tile] ) : ''
        );
		
		echo '<br/>';
		
		// Image Upload
		$default_img = 'http://placehold.it/107&text='.$tile;
		$img = !empty( $this->custom['image'][$tile] ) ? esc_attr( $this->custom['image'][$tile] ) : $default_img;
		printf( '<div><img class="upload_preview" src="%1$s" data-default="%2$s" /></div>', $img, $default_img );
        printf( '<input type="hidden" class="regular-text upload_field" name="wp2048_custom[image][%1$s]" value="%2$s" />', $tile, $img );
		echo '<input type="button" class="button upload_button" value="Select Image" />&nbsp;&nbsp;<input type="button" class="button upload_clear" value="Remove Image" />';
		
		echo '<br/>';
		
		// Color Picker
		$default = array('#eee4da','#ede0c8','#f2b179','#f59563','#f67c5f','#f65e3b','#edcf72','#edcc61','#edc850','#edc53f','#edc22e');
		$ckey = log((int)$tile,2) - 1;
        printf(
			'<br/><input type="text" name="wp2048_custom[color][%1$s]" class="color-picker" value="%2$s" />',
			$tile, !empty( $this->custom['color'][$tile] ) ? esc_attr( $this->custom['color'][$tile] ) : ''
		);
		printf(
			'&nbsp;&nbsp;<p> Default color is <span style="background:%1$s">&nbsp; %2$s &nbsp;</span></p>',
			$default[$ckey], $default[$ckey]
		);
    }
	
	/**
	 * Displays the Preview of 2048 Game Board
	 */
	private function print_customize_preview()
    {
		print '<div id="wp2048-preview" class="game-container">
		<div class="grid-container">
			<div class="grid-row">
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			</div>
			<div class="grid-row">
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			</div>
			<div class="grid-row">
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			</div>
			<div class="grid-row">
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			  <div class="grid-cell"></div>
			</div>
		  </div>
		  <div class="tile-container">
			<div class="tile tile-2 tile-position-1-1"><div class="tile-inner"><div class="tile-text">2</div></div></div>
			<div class="tile tile-4 tile-position-2-1"><div class="tile-inner"><div class="tile-text">4</div></div></div>
			<div class="tile tile-8 tile-position-3-1"><div class="tile-inner"><div class="tile-text">8</div></div></div>
			<div class="tile tile-16 tile-position-4-1"><div class="tile-inner"><div class="tile-text">16</div></div></div>
			<div class="tile tile-32 tile-position-1-2"><div class="tile-inner"><div class="tile-text">32</div></div></div>
			<div class="tile tile-64 tile-position-2-2"><div class="tile-inner"><div class="tile-text">64</div></div></div>
			<div class="tile tile-128 tile-position-3-2"><div class="tile-inner"><div class="tile-text">128</div></div></div>
			<div class="tile tile-256 tile-position-4-2"><div class="tile-inner"><div class="tile-text">256</div></div></div>
			<div class="tile tile-512 tile-position-1-3"><div class="tile-inner"><div class="tile-text">512</div></div></div>
			<div class="tile tile-1024 tile-position-2-3"><div class="tile-inner"><div class="tile-text">1024</div></div></div>
			<div class="tile tile-2048 tile-position-3-3"><div class="tile-inner"><div class="tile-text">2048</div></div></div>
		  </div>
	  </div>';
    }
}
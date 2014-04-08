<?php
/*
Plugin Name: 2048
Plugin URI: http://www.envigeek.com/
Description: 2048 is a number combination game with the aim to achieve 2048 tile.
Version: 0.1.1
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

define('WP2048_VERSION', '0.1.1');

function wp2048_enqueue() {
	wp_register_script( '2048_bind_polyfill', plugins_url( 'js/bind_polyfill.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_classlist_polyfill', plugins_url( 'js/classlist_polyfill.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_animframe_polyfill', plugins_url( 'js/animframe_polyfill.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_keyboard_input_manager', plugins_url( 'js/keyboard_input_manager.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_html_actuator', plugins_url( 'js/html_actuator.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_grid', plugins_url( 'js/grid.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_tile', plugins_url( 'js/tile.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_local_storage_manager', plugins_url( 'js/local_storage_manager.js' , __FILE__ ), array(), WP2048_VERSION, true );
	wp_register_script( '2048_game_manager', plugins_url( 'js/game_manager.js' , __FILE__ ), array(), WP2048_VERSION, true );
	
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
	);
	wp_register_script( 'js2048', plugins_url( 'js/application.js' , __FILE__ ), $jsdepends , WP2048_VERSION, true );
	
	wp_register_style( '2048_fonts', plugins_url( 'fonts/clear-sans.css' , __FILE__ ), array(), WP2048_VERSION );
	wp_register_style( 'css2048', plugins_url( '2048.css' , __FILE__ ), array('2048_fonts'), WP2048_VERSION );
}
add_action( 'wp_enqueue_scripts', 'wp2048_enqueue' );

function wp2048_shortcode() {

	wp_enqueue_script('js2048');
	wp_enqueue_style('css2048');
	
	$dir = plugin_dir_path( __FILE__ );
	$board = file_get_contents( $dir.'board.html' );
	return $board;
}
add_shortcode( '2048', 'wp2048_shortcode' );

?>
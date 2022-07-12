<?php
/**
 * @package WPPlaylist
 */
/*
Plugin Name: WP PlayList
Plugin URI: https://www.kavavdigital.ga/plugins-wp/wp-playlist
Description: 
Version: 1.0.0
Author: Octavio Martinez
Author URI: https://www.facebook.com/8martinez
License: GPLv2 or later
Text Domain: wp-playlist
*/	


include 'classPlayList.php';
include 'uninstall.php';

register_activation_hook(__FILE__, array('PlayList','activation'));
register_deactivation_hook(__FILE__,  array('PlayList','deactivation'));
register_uninstall_hook(__FILE__, 'wp_pl_uninstall');

add_action('init', array('PlayList','init'));



add_action('add_meta_boxes',array('PlayList','add_meta_boxes_playlist'));

add_action('save_post', array('PlayList', 'save_data_playlist'));
add_action('post_update', array('PlayList', 'save_data_playlist'));
//add_action('add_meta_boxes_playlist','add_meta_boxes_playlist');

add_filter('template_include',array('PlayList','add_template'));


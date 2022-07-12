<?php

	if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();
	
	function wp_pl_uninstall(){
		$posts = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'playlist'
		));

		foreach ($posts as $post) {
			wp_delete_post($post->ID);
		}
	}
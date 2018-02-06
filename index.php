<?php
/*
Plugin Name: BT Fuzzy 404 Redirect
Plugin URI:  https://www.binaryturf.com
Description: 301 redirects for 404 posts when you've changed permalink structure and want smooth transition in SERPS.
Author:      Shivanand Sharma
Author URI:  https://www.binaryturf.com

Version: 1.0
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: btf4r

*/


add_action('template_redirect', 'btfr_init');

function btfr_init(){
	if(! is_404()) {
		return;
	}

	$urlRequest = esc_url(preg_replace( '/\?.*/', '', $_SERVER['REQUEST_URI'] ) );	// Strip query vars?
	
	$urlParts = parse_url( $urlRequest )['path'];
	$urlParts = array_filter(explode('/', $urlParts));	
	$urlslug = array_pop($urlParts);
		
	$post_types = get_post_types( array('public'   => true,'_builtin' => true, 'show_ui' => true) ); 

	global $wpdb;
	$permalinks = array();
	$query = "select id , post_name from $wpdb->posts where post_status='publish' and (post_type='page' or post_type='post')";
	$rows = $wpdb->get_results($query);
	
	foreach ($rows as $row) {
		$id = $row->id;
		$postslug = $row->post_name;

		$scoreBasis = strlen($postslug)?strlen($postslug):1;
		$levscore = levenshtein($urlslug, $postslug,1,1,1);
		//llog($levscore);
		$score = 100 - (($levscore / $scoreBasis)*100);
		//llog($score);
		
		
		if( $score > 50 ) {
			$permalinks[$id] =  $score; //number_format($score,4,'.','');
		}
		
	}

	if($permalinks && is_404()){
		asort( $permalinks );
		end($permalinks);
		//llog($permalinks);
		$id = key($permalinks);
		$permalink = get_permalink($id);
		wp_redirect($permalink, 301);
		exit;
	}
}
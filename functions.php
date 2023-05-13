<?php
/**
 * Theme functions and definitions.
 */
// function wpschool_picture_shortcode() {

//     return;
// }
// add_shortcode( 'picture', 'wpschool_picture_shortcode' );


//canonical
add_filter('wpseo_canonical', 'removeCanonical'); 
function removeCanonical($link) { 
	$link = preg_replace('#\\??/page[\\/=]\\d+#', '', $link); 
	return $link; 
}

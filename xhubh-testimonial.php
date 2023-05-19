<?php
/*
Plugin Name: Xhubh Testimonials
Description: Display customer testimonials.
Version: 1.0
Author: Xhubh
License: GPL2
*/

//enqueueing scripts and styles
function plugin_scripts(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('slickslider', plugins_url( 'js/slickslider-min.js' , __FILE__ ), array('jquery'), '2.2', false);
    wp_enqueue_script('testimonials', plugins_url( 'js/testimonials.js' , __FILE__ ), array('jquery'), '1.0', false);
    wp_enqueue_style('slicksliderCSS', plugins_url( 'css/slick.css' , __FILE__ ), false, '2.2', 'all' );
    wp_enqueue_style('testimonialsCSS', plugins_url( 'css/testimonials.css' , __FILE__ ), false, '1.0', 'all' );
}
add_action("wp_enqueue_scripts","plugin_scripts");

//the black magic to create the post type
function xhubh_create_post_type() {
    register_post_type(
        'testimonials',//new post type
        array(
            'labels' => array(
                'name' => __( 'Testimonials' ),
                'singular_name' => __( 'Testimonial' )
            ),
            'public' => true,/*Post type is intended for public use. This includes on the front end and in wp-admin. */
            'supports' => array('title','editor','thumbnail','custom_fields'),
            'hierarchical' => false
        )
    );    
}
add_action( 'init', 'xhubh_create_post_type' );

//adding the URL meta box field
function xhubh_add_custom_metabox() {
    add_meta_box( 'custom-metabox', __( 'Link' ), 'xhubh_url_custom_metabox', 'testimonials', 'side', 'low' );
	add_meta_box( 'custom-metabox-2', __( 'Location' ), 'xhubh_location_custom_metabox', 'testimonials', 'side', 'low' );
	add_meta_box( 'custom-metabox-3', __( 'Position' ), 'xhubh_position_custom_metabox', 'testimonials', 'side', 'low' );
}
add_action( 'admin_init', 'xhubh_add_custom_metabox' );

// HTML for the admin area
function xhubh_url_custom_metabox() {
	global $post;
	$urllink = get_post_meta( $post->ID, 'urllink', true );
	
	//validating!
	if ( ! preg_match( "/http(s?):\/\//", $urllink ) && $urllink != "") {
		$errors = "This URL isn't valid";
		$urllink = "http://";
	} 
	
	// output invlid url message and add the http:// to the input field
	if( isset($errors) ) { echo $errors; }
?>	
<p>
	<label for="siteurl">URL:<br />
		<input id="siteurl" size="37" name="siteurl" value="<?php if( isset($urllink) ) { echo $urllink; } ?>" />
	</label>
</p>
<?php
}
// HTML for the admin area
function xhubh_location_custom_metabox() {
	global $post;
	$location = get_post_meta( $post->ID, 'location', true );
	?>	
<p>
	<label for="location">Location:<br />
		<input id="location" size="37" name="location" value="<?php if( isset($location) ) { echo $location; } ?>" />
	</label>
</p>
<?php
}
function xhubh_position_custom_metabox() {
	global $post;
	$position = get_post_meta( $post->ID, 'position', true );
	?>	
<p>
	<label for="position">Position:<br />
		<input id="position" size="37" name="position" value="<?php if( isset($position) ) { echo $position; } ?>" />
	</label>
</p>
<?php
}
//saves custom field data
function xhubh_save_custom_url( $post_id ) {
	global $post;	
	
	if( isset($_POST['siteurl']) ) {
		update_post_meta( $post->ID, 'urllink', $_POST['siteurl'] );
	}
}
add_action( 'save_post', 'xhubh_save_custom_url' );

//saves custom field data
function xhubh_save_custom_position( $post_id ) {
	global $post;	
	
	if( isset($_POST['position']) ) {
		update_post_meta( $post->ID, 'position', $_POST['position'] );
	}
}
add_action( 'save_post', 'xhubh_save_custom_position' );

//saves custom field data
function xhubh_save_custom_location( $post_id ) {
	global $post;	
	
	if( isset($_POST['location']) ) {
		update_post_meta( $post->ID, 'location', $_POST['location'] );
	}
}
add_action( 'save_post', 'xhubh_save_custom_location' );

//return URL for a post
function get_url($post) {
	$urllink = get_post_meta( $post->ID, 'urllink', true );

	return $urllink;
}

//return Position for a post
function get_position($post) {
	$position = get_post_meta( $post->ID, 'position', true );

	return $position;
}
//return location for a post
function get_location($post) {
	$location = get_post_meta( $post->ID, 'location', true );

	return $location;
}
//registering the shortcode to show testimonials
function load_testimonials($a){
	
	$args = array(
		"post_type" => "testimonials"
	);
	
	if( isset( $a['rand'] ) && $a['rand'] == true ) {
		$args['orderby'] = 'rand';
	}
	if( isset( $a['max'] ) ) {
		$args['posts_per_page'] = (int) $a['max'];
	}
	//getting all testimonials
	$posts = get_posts($args);
	
	echo '<div class="flexslider">';
		echo '<ul class="slides">';
		
		foreach($posts as $post){
			//getting thumb image
			$url_thumb = wp_get_attachment_thumb_url(get_post_thumbnail_id($post->ID));
			// $link = get_url($post);
			$loc = get_location($post);
			$pos = get_position($post);
			echo '<li class="test-container">';
				
		echo '<span class="qoute">"</span>';
				
				if ( ! empty( $post->post_content ) ) { echo '<p class="testimonial-content">'.wp_filter_nohtml_kses($post->post_content).'</p>'; }
				// if ( ! empty( $link ) ) { echo '<p><a href="'.$link.'">Visit Site</a></p>'; }
				echo '<div class="test-meta">';
			if ( ! empty( $url_thumb ) ) { 
				echo '<div class="test-icon">';
				echo '<img class="post_thumb" src="'.$url_thumb.'" />'; 
				echo '</div>';
			}
			echo '<div class="test-text">';
			echo '<h2>'.$post->post_title.'</h2>';
			if ( ! empty( $pos ) ) { echo '<p class="meta-pos">'.$pos.'</p>'; }
			if ( ! empty( $loc ) ) { echo '<p class="meta-loc">'.$loc.'</p>'; }
			echo '</div>';
			echo '</div>';

			echo '</li>';
		}
		
		echo '</ul>';
	echo '</div>';
}
add_shortcode("testimonials","load_testimonials");
add_filter('widget_text', 'do_shortcode');
?>
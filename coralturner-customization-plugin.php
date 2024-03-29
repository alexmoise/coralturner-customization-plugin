<?php
/**
 * Plugin Name: Coralturner.com customizations plugin
 * Plugin URI: https://github.com/alexmoise/coralturner-customization-plugin
 * GitHub Plugin URI: https://github.com/alexmoise/coralturner-customization-plugin
 * Description: A custom plugin to add required customizations to Coral Turner Woocommerce shop and to style the front end as required. Works based on Woocommerce and Clothing69 theme. For details/troubleshooting please contact me at https://moise.pro/contact/
 * Version: 0.0.23
 * Author: Alex Moise
 * Author URI: https://moise.pro
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// Adding own CSS
add_action( 'wp_enqueue_scripts', 'moctcp_adding_styles', 9999 ); // I want it last!
function moctcp_adding_styles() {
	wp_register_style('ctcp-styles', plugins_url('ctcp.css', __FILE__));
	wp_enqueue_style('ctcp-styles');
}
// change jpeg quality a bit:
add_filter('jpeg_quality', function($arg){return 90;});
// change number of products per page
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );
function new_loop_shop_per_page( $cols ) {
  $cols = 9;
  return $cols;
}

// Moving some WooCommerce stuff around so the products looks like magazine covers :-)
add_action('init', 'moctcp_layout_adjustments');
function moctcp_layout_adjustments() {
	// Remove price and buy button in product lists:
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
	remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	// Remove the title from its original place:
	remove_action('woocommerce_before_shop_loop_item_title', 'clothing69_woocommerce_item_wrapper_start', 9 );
	remove_action('woocommerce_before_shop_loop_item_title', 'clothing69_woocommerce_title_wrapper_start', 20 );
	remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
	remove_action('woocommerce_after_shop_loop_item_title', 'clothing69_woocommerce_title_wrapper_end', 7);
	remove_action('woocommerce_after_shop_loop_item', 'clothing69_woocommerce_item_wrapper_end', 20 );
	// Then add it back above the thumbnail:
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_item_wrapper_start', 90 );
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_title_wrapper_start', 110 );
	add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_title', 120);
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_title_wrapper_end', 130);
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_item_wrapper_end', 140 );
	// Wrap thumbnail with link:
	// ( Thumbnail is at "woocommerce_before_shop_loop_item_title" priority 10, so we'll put the wrapper at 9 and 11 )
	add_action('woocommerce_before_shop_loop_item_title', function(){echo '<a href='; echo get_the_permalink(); echo '>';}, 9);
	add_action('woocommerce_before_shop_loop_item_title', function(){echo '</a>';}, 11);
	// "Magazine Style" Custom Fields in product archives
	add_action('woocommerce_before_shop_loop_item_title', 'moctcp_echo_cover_short_description', 8);
	add_action('woocommerce_before_shop_loop_item_title', 'moctcp_echo_cover_long_description', 12);
	// Now let's move the price just before the Add To Cart button
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
	add_action('woocommerce_before_add_to_cart_button', 'woocommerce_template_single_price', 10);
}
// Echo cover_short_description (used above)
function moctcp_echo_cover_short_description(){
	echo '<div class="cover_short_description"><span class="cover_short_description_inner">';
	the_field("cover_short_description");
	echo '</span></div>';
	moctcp_style_product_title();
}
// Echo cover_long_description (used above)
function moctcp_echo_cover_long_description(){
	echo '<div class="cover_long_description">';
	the_field("cover_long_description");
	echo '</div>';
}

// Output conditional styles, based on the (non)existence of "cover_short_description" field 
// (theme Clothing69 has a filter that moves all styles in <head> anyway, so no pfoblem to output these in the page)
function moctcp_style_product_title() {
	global $product;
	$curr_prod_id = $product->get_id();
	if( !get_field("cover_short_description") ) {
		echo '
			<style>
			body.woocommerce ul.products li.product .post_item.post_layout_thumbs { left: 0; padding: 0 20px; }
			body.woocommerce ul.products li.product.post-'.$curr_prod_id.' .post_data { text-align: center !important; }
			body.woocommerce ul.products li.product.post-'.$curr_prod_id.' .cover_short_description { display: none; }
			</style>
		';
	} else {
		echo '
			<style>
			body.woocommerce ul.products li.product.post-'.$curr_prod_id.' .cover_short_description { display: table; }
			body.woocommerce ul.products li.product.post-'.$curr_prod_id.' .post_item.post_layout_thumbs { 
				    padding: 0 20px 0 0;
					width: calc(50% - 10px);
					display: inline-block;
					float: right !important;
					position: relative;
			}
			</style>
		';
	}
}

// Translate/change some strings as needed
add_filter( 'gettext', 'moctcp_translate_woocommerce_strings', 999, 3 );
function moctcp_translate_woocommerce_strings( $translated, $text, $domain ) {
$translated = str_ireplace( 'All Posts', 'Life & Style', $translated );
return $translated;
}

// === Add custom colors styles in wp_head
// First let's ease "blog" definition
function moctcp_is_blog () { return ( is_archive() || is_author() || is_category() || is_home() || is_tag()) && 'post' == get_post_type(); }
add_action( 'wp_head', 'moctcp_custom_posts_colors', 99999 );
function moctcp_custom_posts_colors() {
	// Let's do this only when it's "blog" situation
    if ( moctcp_is_blog () ) {
		// and let's have some default colors defined in the first place
		$default_post_background_color = '#ebe9e6';
		$default_post_text_color = '#c33442';
		// get the IDs of all the post in the page first
		global $wp_query;
        $displayed_ids = wp_list_pluck( $wp_query->posts, 'ID' );
		// start the styles block
		echo '
		<style type="text/css">
		/* Coral custom post colors START v19 */
		/* Default colors - BKG: '.$default_post_background_color.' TXT: '.$default_post_text_color.' */'; 
		// Now for each post, output its styles
		foreach ($displayed_ids as $displayed_id) {
			// set the colors right first (defined or defaults)
			if ( get_field("post_cover_text_color", $displayed_id) ) { $curr_post_text_color = get_field("post_cover_text_color", $displayed_id); } else { $curr_post_text_color = $default_post_text_color; }
			if ( get_field("post_cover_background_color", $displayed_id) ) { $curr_post_background_color = get_field("post_cover_background_color", $displayed_id); } else { $curr_post_background_color = $default_post_background_color; }
			// then start outputting that CSS
			// text color
			echo '
			article#post-'.$displayed_id.' .post_header.entry-header *, 
			article#post-'.$displayed_id.' .post_content.entry-content .post_content_inner, 
			article#post-'.$displayed_id.' .post_content.entry-content .post_meta * { color: '.$curr_post_text_color.' !important; }';
			// background color
			echo '
			article#post-'.$displayed_id.'.post_layout_excerpt, article#post-'.$displayed_id.' .wrap_post_single { background-color: '.$curr_post_background_color.' !important; }';
			// More Info button, reversed colors background and text
			echo '
			article#post-'.$displayed_id.'.post_layout_excerpt a.more-link { color: '.$curr_post_background_color.' !important; background-color: '.$curr_post_text_color.' !important;  }';
		}
		// end the styles block
		echo '
		/* Coral custom post colors END */</style>
		';
    }
}

// Create a better login name for new customers
add_filter( 'woocommerce_new_customer_data', 'moctcp_custom_new_customer_data', 10, 1 );
function moctcp_custom_new_customer_data( $new_customer_data ){
    // get the first and last billing names
    if(isset($_POST['billing_first_name'])) $first_name = $_POST['billing_first_name'];
    if(isset($_POST['billing_last_name'])) $last_name = $_POST['billing_last_name'];
    // the customer billing complete name
    if( ! empty($first_name) || ! empty($last_name) )
        $complete_name = $first_name . ' ' . $last_name;

    // Replacing 'user_login' in the user data array, before data is inserted
    if( ! empty($complete_name) )
        $new_customer_data['user_login'] = sanitize_user( str_replace( ' ', '-', $complete_name ) );

    return $new_customer_data;
}

?>

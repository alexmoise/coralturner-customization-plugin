<?php
/**
 * Plugin Name: Coralturner.com customizations plugin
 * Plugin URI: https://github.com/alexmoise/coralturner-customization-plugin
 * GitHub Plugin URI: https://github.com/alexmoise/coralturner-customization-plugin
 * Description: A custom plugin to add required customizations to Coral Turner Woocommerce shop and to style the front end as required. Works based on Woocommerce and Clothing69 theme. For details/troubleshooting please contact me at https://moise.pro/contact/
 * Version: 0.0.5
 * Author: Alex Moise
 * Author URI: https://moise.pro
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// Adding own CSS
add_action( 'wp_enqueue_scripts', 'moctcp_adding_styles', 9999 ); // I want it last!
function moctcp_adding_styles() {
	wp_register_style('ctcp-styles', plugins_url('ctcp.css', __FILE__));
	wp_enqueue_style('ctcp-styles');
}

// Moving some WooCommerce stuff around so the products looks like magazine covers :-)
add_action('init', 'moctcp_layout_adjustments');
function moctcp_layout_adjustments() {
	// remove price and buy button in product lists:
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

	// Remove the title from its original place
	remove_action('woocommerce_before_shop_loop_item_title', 'clothing69_woocommerce_item_wrapper_start', 9 );
	remove_action('woocommerce_before_shop_loop_item_title', 'clothing69_woocommerce_title_wrapper_start', 20 );
	remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
	remove_action('woocommerce_after_shop_loop_item_title', 'clothing69_woocommerce_title_wrapper_end', 7);
	remove_action('woocommerce_after_shop_loop_item', 'clothing69_woocommerce_item_wrapper_end', 20 );
	// Then add it back above the thumbnail
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_item_wrapper_start', 100 );
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_title_wrapper_start', 110 );
	add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_title', 120);
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_title_wrapper_end', 130);
	add_action('woocommerce_before_shop_loop_item', 'clothing69_woocommerce_item_wrapper_end', 140 );
}

// change jpeg quality a bit:
add_filter('jpeg_quality', function($arg){return 90;});

// change number of products
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );
function new_loop_shop_per_page( $cols ) {
  $cols = 9;
  return $cols;
}





<?php
/**
 * Plugin Name: WooCommerce Linked Variations Extended
 * Plugin URI: https://jbr.digital
 * Description: Extensions for WooCommerce Linked Variations by IconicWP
 * Version: 0.5.0
 * Author: Joe Bailey-Roberts
 * Author URI: https://jbr.digital
 * Requires PHP: 7.0
 *
 * @package wclve
 */

defined( 'ABSPATH' ) || exit;

if(class_exists('Iconic_WLV_Linked_Variations_Group')){
    require_once __DIR__.'/inc/linked_variations.php';
}
else{
    function wclve_admin_notice__error() {
        $class = 'notice notice-error';
        $message = __( 'Please activate WooCommerce & WooCommerce Linked Variations', 'wclve' );
     
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }
    add_action( 'admin_notices', 'wclve_admin_notice__error' );
}
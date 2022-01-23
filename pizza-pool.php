<?php
/**
 * Plugin Name: PizzaPool
 * Description: Manage PizzaPool nline service
 
 * Author: Jakaria Istauk
 * Version: 1.0.0
 * Author URI: https://profiles.wordpress.org/jakariaistauk/#content-plugins
 * Text Domain: pizza-pool
 * Domain Path: /languages
 *
 */

namespace Jakaria\Pizza_Pool;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Main class for the plugin
 * @package Plugin
 * @author Jakaria <jakariamd35@gmail.com>
 */
final class Plugin {
    
    public static $_instance;

    public function __construct() {
        $this->include();
        $this->define();
        $this->hook();
    }

    /**
     * Includes files
     */
    public function include() {
        require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
    }

    /**
     * Define variables and constants
     */
    public function define() {
        // constants
        define( 'PIZZA_POOL', __FILE__ );
        define( 'PIZZA_POOL_DIR', dirname( PIZZA_POOL ) );
        define( 'PIZZA_POOL_ASSETS', plugins_url( 'assets', PIZZA_POOL ) );
    }

    /**
     * Hooks
     */
    public function hook() {

        if ( is_admin() ):
            $admin = new Admin;
            add_action( 'plugins_loaded', [ $admin, 'i18n'] );
            add_action( 'woocommerce_admin_order_data_after_billing_address', [ $admin, 'display_order_type' ] );
        
        else:
            $front = new Front;
            add_action( 'wp_enqueue_scripts', [ $front, 'enqueue_scripts' ] );
            add_action( 'wp_head', [ $front, 'head' ] );
            add_filter( 'the_content', [ $front, 'prevent_placing_order'] );
            add_filter( 'woocommerce_checkout_fields', [ $front, 'add_extra_fields' ] ); 
            add_filter( 'woocommerce_cart_calculate_fees', [ $front, 'add_fee' ] ); 
            add_filter( 'woocommerce_before_calculate_totals', [ $front, 'set_product_price' ] ); 
            add_filter( 'woocommerce_get_item_data', [ $front, 'display_charge_text'], 25, 2 );
            add_action( 'woocommerce_thankyou', [ $front, 'remove_cookie' ] );
            add_action( 'woocommerce_checkout_update_order_meta', [ $front, 'save_order_type' ] );
        endif;
    }
 
    /**
     * Cloning is forbidden.
     */
    public function __clone() { }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup() { }

    /**
     * Instantiate the plugin
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

Plugin::instance();
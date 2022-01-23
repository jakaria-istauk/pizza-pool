<?php
namespace Jakaria\Pizza_Pool;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Jakaria Istauk <jakariamd35@gmail.com>
 */
class Admin{
    /**
    * Internationalization
    */
   public function i18n() {
       load_plugin_textdomain( 'pizza-pool', false, PIZZA_POOL_DIR . '/languages/' );
   }

   /**
   * Display order Type
   */
   public function display_order_type( $order ) {
        $order_type = $order->get_meta( '_order_type', true );
        if ( $order_type ) {
            $label = pp_order_types()[ $order_type ];
            echo "<p>
            <strong>" . __( 'Order Type: ', 'pizza-pool' ) . "</strong>
            {$label}
            </P>";
        }
   }
}
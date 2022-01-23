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
 * @subpackage Front
 * @author Jakaria Istauk <jakariamd35@gmail.com>
 */
class Front{

    public function enqueue_scripts(){
        wp_enqueue_script( 'pizza-pool', plugins_url( "assets/js/front.js", PIZZA_POOL ), [ 'jquery' ] );
        $localized = [
            'ajaxurl'   => admin_url( 'admin-ajax.php' )
        ];
        wp_localize_script( 'pizza-pool', 'PIZZA_POOL', apply_filters( "pizza-pool-localized", $localized ) );
    }

    public function head(){}

    /**
     * Customers can place orders only within the opening time of PizzaPOOL 
     */
    public function prevent_placing_order( $the_content ){
        if ( !is_checkout() ) return $the_content;

        $thurs_start    ="thu 16:00:00";
        $thurs_end      ="thu 22:00:00";
        $friday_start   ="fri 12:00:00";
        $friday_end     ="fri 22:00:00";
        $saturday_start ="sat 12:00:00";
        $saturday_end   ="sat 22:00:00";

        $is_open = false;

        if ( in_array( date( 'D' ), [ 'Thu', 'Fri', 'Sat' ] ) &&
           ( ( time() >= strtotime( $thurs_start ) && time() < strtotime( $thurs_end ) ) ||
            ( time() >= strtotime( $friday_start ) && time() < strtotime( $friday_end ) ) ||
            ( time() >= strtotime( $saturday_start ) && time() < strtotime( $saturday_end ) ) )
         ) {
          $is_open = true;
        }

        if( $is_open ) return $the_content;

        ?>
        <h1>We are closed now.</h1>
        <h4>We Only take orders for the following times</h4>
        <table>
            <thead>
                <tr>
                    <th><?php _e( 'Weekday', 'pizza-pool' ) ?></th>
                    <th><?php _e( 'Hours', 'pizza-pool' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e( 'Sunday', 'pizza-pool' ) ?></td>
                    <td><?php _e( 'Closed', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Monday', 'pizza-pool' ) ?></td>
                    <td><?php _e( 'Closed', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Tuesday', 'pizza-pool' ) ?></td>
                    <td><?php _e( 'Closed', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Wednesday', 'pizza-pool' ) ?></td>
                    <td><?php _e( 'Closed', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Thursday', 'pizza-pool' ) ?></td>
                    <td><?php _e( '16:00 - 22:00', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Friday', 'pizza-pool' ) ?></td>
                    <td><?php _e( '12:00 - 22:00', 'pizza-pool' ) ?></td>
                </tr>
                <tr>
                    <td><?php _e( 'Saturday', 'pizza-pool' ) ?></td>
                    <td><?php _e( '12:00 - 22:00', 'pizza-pool' ) ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        return ;
    }

    /**
     * Add extrat field to take input order type
     */
    public function add_extra_fields( $fields ){

        $fields['billing']['order_type'] = [
            'label'     => __( 'Order Type', 'pizza-pool' ),
            'required'  => true,
            'type'      => 'select',
            'priority'  => 1,
            'options'   => pp_order_types(),
            'class'     => [ 'form-row-wide' ]
        ];
        return $fields;
    }

    /**
     * For the first online order of a customer, a 40% discount on the total billing amount will be applied.
     *
     */
    public function add_fee(){
        global $woocommerce;

        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        if ( !is_checkout() ) return;

        $is_old_customer = false;

        if ( is_user_logged_in() ) {
            $is_old_customer = get_user_meta( get_current_user_id(), 'is_old_customer', true );
        }
        else if ( isset( $_COOKIE['order_email'] ) && email_exists( sanitize_email(  $_COOKIE['order_email'] ) ) ) {
            $email = sanitize_email( $_COOKIE['order_email'] );
            $user  = get_user_by( 'email', $email );
            $is_old_customer = get_user_meta( $user->ID, 'is_old_customer', true );
        }

        if ( !$is_old_customer ) {
            $cart_total = $woocommerce->cart->subtotal;
            $fee        = $cart_total * ( 0.40 );
            $label      = __( 'First Order Discount(40%)'.$is_old_customer, 'pizza-pool' );
            $woocommerce->cart->add_fee( $label, -$fee );
        }
        
    }

    /**
     * 10% service charge will be added for dine-in orders only. 
     */
    public function set_product_price(){
        global $woocommerce;

        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;
        
        if ( !is_checkout() ) return;

        if( isset( $_COOKIE['order_type'] ) && $_COOKIE['order_type'] == 'dine-in' ){
            foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
                $old_price = $cart_item['data']->get_price();
                $new_price = $old_price + ( $old_price * .10 );
                
                $cart_item['data']->set_price( $new_price );
            }
        } 
    }

    /**
     * 10% service charge display with products for dine-in orders only. 
     */
    public function display_charge_text( $cart_data, $cart_item ) {

        if ( !is_checkout() ) return $cart_data;

        if( isset( $_COOKIE['order_type'] ) && $_COOKIE['order_type'] == 'dine-in' ){      
            $cart_data[] = [
                'key'   => __( 'Service Charge', 'pizza-pool' ),
                'value' => __( '10% for Dine In', 'pizza-pool' ),
            ];
        }

        return $cart_data;
    }

    public function remove_cookie( $order_id ) {
        setcookie( 'order_type', '', time() - 3600 );
        setcookie( 'order_email', '', time() - 3600 );
        update_user_meta( get_current_user_id(), 'is_old_customer', 'yes' );
    }

    public function save_order_type( $order_id ) {
        if ( isset( $_POST['order_type'] ) ) {
           update_post_meta( $order_id, '_order_type', sanitize_text_field( $_POST['order_type'] ) );
       }
    }
}
<?php
if ( !function_exists( 'echo_pri' ) ):
	function echo_pri( $data ){
		echo '<pre>';
		if( is_object( $data ) || is_array( $data ) ) {
			print_r( $data );
		}
		else {
			var_dump( $data );
		}
		echo '</pre>';
	}
endif;

if ( !function_exists( 'pp_order_types' ) ):
	function pp_order_types(){
		$types = [
			'dine-in' 	=> __( 'Dine In', 'pizza-pool' ),
			'take-away' => __( 'Take away', 'pizza-pool' ),
			'delivery' 	=> __( 'Delivery', 'pizza-pool' ),
		];

		return $types;
	}
endif;
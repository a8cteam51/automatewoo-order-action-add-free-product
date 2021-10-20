<?php

/**
  * Plugin Name: AutomateWoo Order Action - Add Free Product
	* Plugin URI:  https://github.com/a8cteam51/automatewoo-order-action-add-free-product
	* Description: Extends the functionality of AutomateWoo with a custom action which allows you to add a product to an order as a line item.
	* Version:     1.0.0
	* Author:      WP Special Projects
	* Author URI:  https://wpspecialprojects.wordpress.com/
	* License:     GPL v2 or later
  */
namespace To51\AW_Action;

class TO51_AW_Order_Add_Product {
	public static function init() {
		add_filter( 'automatewoo/actions', array( __CLASS__, 'register_action' ) );
	}

	function register_action( $actions ) {
		require_once __DIR__ . '/includes/class-action-order-add-free-product.php';

		$actions['to51_add_free_product'] = Action_Order_Add_Free_Product::class;
		return $actions;
	}
}

TO51_AW_Order_Add_Product::init();

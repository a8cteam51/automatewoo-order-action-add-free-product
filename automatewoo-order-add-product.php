<?php

/**
  * Plugin Name: AutomateWoo Order Add Product Action
  */
namespace To51\AW_Action;

class TO51_AW_Order_Add_Product{
	public static function init() {
		add_filter( 'automatewoo/actions',  array( __CLASS__, 'register_action' ) );

	}

	function register_action( $actions ) {
		require_once __DIR__ .'/includes/Order_Add_Product.php';
		require_once __DIR__ .'/includes/Action_Order_Add_Free_Product.php';
		$actions['order_add_product_new'] = Action_Order_Add_Product::class;
		$actions[ 'yr_order_free_product' ] = Action_Order_Add_Free_Product::class;
		return $actions;
	}
}

TO51_AW_Order_Add_Product::init();

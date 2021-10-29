<?php
namespace to51\AW_Action;

use AutomateWoo\Action;
use AutomateWoo\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Action_Order_Add_Free_Product extends Action {

	/**
	 * The data items required by the action.
	 *
	 * @var array
	 */
	public $required_data_items = array( 'order' );

	/**
	 * Flag to define whether variable products should be included in search results for the
	 * product select field.
	 *
	 * @var bool
	 */
	protected $allow_variable_products = true;

	/**
	 * Flag to define whether the quantity input field should be marked as required.
	 *
	 * @var bool
	 */
	protected $require_quantity_field = true;

	/**
	 * Method to load the action's fields.
	 *
	 * TODO make protected method
	 */
	public function load_fields() {
		$this->add_product_select_field();
	}

	/**
	 * Add a product selection field for this action
	 */
	protected function add_product_select_field() {
		$product_select = new Fields\Product();
		$product_select->set_required();
		$product_select->set_allow_variations( true );
		$product_select->set_allow_variable( $this->allow_variable_products );

		$this->add_field( $product_select );
	}

	/**
	 * Method to set the action's admin props.
	 *
	 * Admin props include: title, group and description.
	 */
	protected function load_admin_details() {
		$this->title       = __( 'Add Free Product', 'automatewoo' );
		$this->group       = __( 'Order', 'automatewoo' );
		$this->description = __( 'Add free product to order as a line item. (Caution: Runs after checkout)', 'automatewoo' );
	}

	public function run() {
		if ( $this->workflow->data_layer()->get_order() ) {
			$order = $this->workflow->data_layer()->get_order();
		} else {
			return;
		}
		$product = wc_get_product( $this->get_option( 'product' ) );
		$order->add_product( $product, 1, array(
			'subtotal' => 0,
			'total' => 0,
		) );
	}
}

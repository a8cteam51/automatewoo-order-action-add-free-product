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
		$this->add_check_product_stock_field();
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

	/**
	 * Add a checkbox field to prevent adding out-of-stock products to the order.
	 *
	 */
	protected function add_check_product_stock_field() {
		$field = new \AutomateWoo\Fields\Checkbox();
		$field->set_name( 'check_product_stock' );
		$field->set_title( __( 'Don\'t add out of stock products', 'automatewoo' ) );
		$field->set_description( __( 'Don\'t add the product to the order if it\'s out of stock', 'automatewoo' ) );
		$this->add_field( $field );
	}

	/**
	 * Method to run the action.
	 *
	 */
	public function run() {
		if ( $this->workflow->data_layer()->get_order() ) {
			$order = $this->workflow->data_layer()->get_order();
		} else {
			return;
		}
		$product       = wc_get_product( $this->get_option( 'product' ) );
		$workflow_name = $this->workflow->title;
		$product_name  = $product->get_name();

		// Check if stock management is enabled and backorders are not allowed
		if ( ( $product->managing_stock() && ! $product->backorders_allowed() && ! $product->is_in_stock() ) || ! $product->is_in_stock() ) {
			// Product is out of stock, do not add it to the order.

			// Add a note to the order.
			$order->add_order_note(
				sprintf(
					/* translators: 1: Product name, 2: Workflow name */
					__( 'The product "%1$s" was not added to the order by the "%2$s" workflow because it was out of stock.', 'automatewoo' ),
					$product_name,
					$workflow_name
				)
			);

			// Add a note to the workflow log.
			$actions = $this->workflow->get_actions();
			foreach ( $actions as $action ) {
				if ( 'to51_add_free_product' === $action->get_name() ) {
					$this->workflow->log->add_note(
						sprintf(
							/* translators: %1$s: Product name, %2$s: Order ID */
							__( 'The product "%1$s" was not added to the order #%2$s because it was out of stock.', 'automatewoo' ),
							$product_name,
							$order->ID
						)
					);
					$this->workflow->log->set_has_errors( 1 );
					$this->workflow->log->save();
				}
			}
		} else {
			// Product is in stock or backorders are allowed, proceed to add it to the order.
			$order->add_product(
				$product,
				1,
				array(
					'subtotal' => 0,
					'total'    => 0,
				)
			);

			$order->add_order_note(
				sprintf(
					/* translators: %1$s: Product name, %2$s: Workflow name */
					__( 'The product "%1$s" was added to the order by the "%2$s" workflow.', 'automatewoo' ),
					$product_name,
					$workflow_name
				)
			);
		}
	}
}

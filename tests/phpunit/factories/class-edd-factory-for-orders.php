<?php
namespace EDD\Tests\Factory;

class Order extends \WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'status'          => 'publish',
			'user_id'         => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'customer_id'     => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'email'           => new \WP_UnitTest_Generator_Sequence( 'user%d@edd.test' ),
			'ip'              => '10.1.1.1',
			'gateway'         => 'manual',
			'mode'            => 'live',
			'currency'        => 'USD',
			'payment_key'     => md5( 'edd' ),
			'subtotal'        => 100,
			'tax'             => 25,
			'discount'        => 5,
			'total'           => 120,
		);
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	public function create_object( $args ) {
		return edd_add_order( $args );
	}

	public function update_object( $order_id, $fields ) {
		return edd_update_order( $order_id, $fields );
	}

	public function delete( $order_id ) {
		edd_delete_order( $order_id );
	}

	public function delete_many( $orders ) {
		foreach ( $orders as $order ) {
			$this->delete( $order );
		}
	}

	/**
	 * Stub out copy of parent method for IDE type hinting purposes.
	 *
	 * @param $order_id Order ID.
	 *
	 * @return \EDD\Orders\Order|false
	 */
	public function get_object_by_id( $order_id ) {
		return edd_get_order( $order_id );
	}
}
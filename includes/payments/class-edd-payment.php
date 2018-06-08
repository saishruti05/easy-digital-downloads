<?php
/**
 * Payments Object.
 *
 * This class is for working with payments in EDD.
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Payment Class
 *
 * @since 2.5
 * @since 3.0 Updated to work with new custom tables.
 */
class EDD_Payment {

	/**
	 * The Payment ID
	 *
	 * @since  2.5
	 * @var    integer
	 */
	public $ID = 0;
	protected $_ID = 0;

	/**
	 * Identify if the payment is a new one or existing
	 *
	 * @since  2.5
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * The Payment number (for use with sequential payments)
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $number = '';

	/**
	 * The Gateway mode the payment was made in
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $mode = 'live';

	/**
	 * The Unique Payment Key
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $key = '';

	/**
	 * The total amount the payment is for
	 * Includes items, taxes, fees, and discounts
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $total = 0.00;

	/**
	 * The Subtotal fo the payment before taxes
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $subtotal = 0;

	/**
	 * The amount of tax for this payment
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $tax = 0;

	/**
	 * The amount the payment has been discounted through discount codes
	 *
	 * @since 2.8.7
	 * @var int
	 */
	protected $discounted_amount = 0;

	/**
	 * The tax rate charged on this payment
	 *
	 * @since 2.7
	 * @var float
	 */
	protected $tax_rate = '';

	/**
	 * Array of global fees for this payment
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $fees = array();

	/**
	 * The sum of the fee amounts
	 *
	 * @since  2.5
	 * @var float
	 */
	protected $fees_total = 0;

	/**
	 * Any discounts applied to the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $discounts = 'none';

	/**
	 * The date the payment was created
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $date = '';

	/**
	 * The date the payment was marked as 'complete'
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $completed_date = '';

	/**
	 * The status of the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $status = 'pending';
	protected $post_status = 'pending'; // Same as $status but here for backwards compat

	/**
	 * When updating, the old status prior to the change
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $old_status = '';

	/**
	 * The display name of the current payment status
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $status_nicename = '';

	/**
	 * The customer ID that made the payment
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $customer_id = null;

	/**
	 * The User ID (if logged in) that made the payment
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * The first name of the payee
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $first_name = '';

	/**
	 * The last name of the payee
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $last_name = '';

	/**
	 * The email used for the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $email = '';

	/**
	 * Legacy (not to be accessed) array of user information
	 *
	 * @since  2.5
	 * @var array
	 */
	private $user_info = array();

	/**
	 * Legacy (not to be accessed) payment meta array
	 *
	 * @since  2.5
	 * @var array
	 */
	private $payment_meta = array();

	/**
	 * The physical address used for the payment if provided
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $address = array();

	/**
	 * The transaction ID returned by the gateway
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $transaction_id = '';

	/**
	 * Array of downloads for this payment
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $downloads = array();

	/**
	 * IP Address payment was made from
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $ip = '';

	/**
	 * The gateway used to process the payment
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * The the payment was made with
	 *
	 * @since  2.5
	 * @var string
	 */
	protected $currency = '';

	/**
	 * The cart details array
	 *
	 * @since  2.5
	 * @var array
	 */
	protected $cart_details = array();

	/**
	 * Allows the files for this payment to be downloaded unlimited times (when download limits are enabled)
	 *
	 * @since  2.5
	 * @var boolean
	 */
	protected $has_unlimited_downloads = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_payment_meta calls to be run
	 *
	 * @since  2.5
	 * @var array
	 */
	private $pending;

	/**
	 * The parent payment (if applicable)
	 *
	 * @since  2.5
	 * @var integer
	 */
	protected $parent_payment = 0;

	/**
	 * Order object.
	 *
	 * @since 3.0
	 * @var   EDD\Orders\Order
	 */
	protected $order;

	/**
	 * Setup the EDD Payments class
	 *
	 * @since 2.5
	 *
	 * @param int $payment_id A given payment
	 *
	 * @return mixed void|false
	 */
	public function __construct( $payment_or_txn_id = false, $by_txn = false ) {
		global $wpdb;

		if ( empty( $payment_or_txn_id ) ) {
			return false;
		}

		if ( $by_txn ) {
			$query      = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_transaction_id' AND meta_value = '%s'", $payment_or_txn_id );
			$payment_id = $wpdb->get_var( $query );

			if ( empty( $payment_id ) ) {
				return false;
			}
		} else {
			$payment_id = absint( $payment_or_txn_id );
		}

		$this->setup_payment( $payment_id );
	}

	/**
	 * Magic GET function
	 *
	 * @since  2.5
	 *
	 * @param  string $key The property
	 *
	 * @return mixed        The value
	 */
	public function __get( $key ) {
		if ( method_exists( $this, "get_{$key}" ) ) {
			$value = call_user_func( array( $this, "get_{$key}" ) );
		} elseif ( 'id' === $key ) {
			$value = $this->ID;
		} elseif ( 'post_type' === $key ) {
			$value = 'edd_payment';
		} elseif ( 'post_date' === $key ) {
			$value = $this->date;
		} else {
			$value = $this->$key;
		}

		return $value;
	}

	/**
	 * Magic SET function
	 *
	 * Sets up the pending array for the save method
	 *
	 * @since  2.5
	 *
	 * @param string $key   The property name
	 * @param mixed  $value The value of the property
	 */
	public function __set( $key, $value ) {
		$ignore = array( 'downloads', 'cart_details', 'fees', '_ID' );

		if ( $key === 'status' ) {
			$this->old_status = $this->status;
		}

		if ( ! in_array( $key, $ignore ) ) {
			$this->pending[ $key ] = $value;
		}

		if ( '_ID' !== $key ) {
			$this->$key = $value;
		}
	}

	/**
	 * Magic ISSET function, which allows empty checks on protected elements
	 *
	 * @since  2.5
	 *
	 * @param  string $name The attribute to get
	 *
	 * @return boolean       If the item is set or not
	 */
	public function __isset( $name ) {
		if ( property_exists( $this, $name ) ) {
			return false === empty( $this->$name );
		} else {
			return null;
		}
	}

	/**
	 * Setup payment properties
	 *
	 * @since  2.5
	 *
	 * @param  int $payment_id The payment ID
	 *
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {
		$this->pending = array();

		if ( empty( $payment_id ) ) {
			return false;
		}

		$this->order = edd_get_order( $payment_id );

		if ( ! $this->order || is_wp_error( $this->order ) ) {
			return false;
		}

		// Allow extensions to perform actions before the payment is loaded
		do_action( 'edd_pre_setup_payment', $this, $payment_id );

		// Primary Identifier
		$this->ID = absint( $payment_id );

		// Protected ID that can never be changed
		$this->_ID = absint( $payment_id );

		// Status and Dates
		$this->date           = $this->order->get_date_created();
		$this->completed_date = $this->setup_completed_date();
		$this->status         = $this->order->get_status();
		$this->post_status    = $this->order->get_status();
		$this->mode           = $this->order->get_mode();
		$this->parent_payment = $this->order->get_parent();

		$all_payment_statuses  = edd_get_payment_statuses();
		$this->status_nicename = array_key_exists( $this->status, $all_payment_statuses ) ? $all_payment_statuses[ $this->status ] : ucfirst( $this->status );

		// Items
		$this->fees         = $this->setup_fees();
		$this->cart_details = $this->setup_cart_details();
		$this->downloads    = $this->setup_downloads();

		// Currency Based
		$this->total      = $this->order->get_total();
		$this->tax        = $this->order->get_tax();
		$this->tax_rate   = $this->setup_tax_rate();
		$this->fees_total = $this->setup_fees_total();
		$this->subtotal   = $this->order->get_subtotal();
		$this->currency   = $this->setup_currency();

		// Gateway based
		$this->gateway        = $this->order->get_gateway();
		$this->transaction_id = $this->setup_transaction_id();

		// User based
		$this->ip          = $this->order->get_ip();
		$this->customer_id = $this->order->get_customer_id();
		$this->user_id     = $this->setup_user_id();
		$this->email       = $this->setup_email();
		$this->user_info   = $this->setup_user_info();
		$this->address     = $this->setup_address();
		$this->discounts   = $this->user_info['discount'];
		$this->first_name  = $this->user_info['first_name'];
		$this->last_name   = $this->user_info['last_name'];

		// Other Identifiers
		$this->key    = $this->order->get_payment_key();
		$this->number = $this->setup_payment_number();

		// Additional Attributes
		$this->has_unlimited_downloads = $this->setup_has_unlimited();

		// We have a payment, get the generic payment_meta item to reduce calls to it
		// This only exists for backwards compatibility purposes.
		$this->payment_meta = $this->get_meta();

		// Allow extensions to add items to this object via hook
		do_action( 'edd_setup_payment', $this, $payment_id );

		return true;
	}

	/**
	 * Create the base of a payment.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to insert orders to the new custom tables.
	 *
	 * @return int|bool False on failure, the order ID on success.
	 */
	private function insert_payment() {
		if ( empty( $this->key ) ) {
			$auth_key             = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
			$this->key            = strtolower( md5( $this->email . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'edd', true ) ) );  // Unique key
			$this->pending['key'] = $this->key;
		}

		if ( empty( $this->ip ) ) {
			$this->ip            = edd_get_ip();
			$this->pending['ip'] = $this->ip;
		}

		$payment_data = array(
			'price'        => $this->total,
			'date'         => $this->date,
			'user_email'   => $this->email,
			'purchase_key' => $this->key,
			'currency'     => $this->currency,
			'downloads'    => $this->downloads,
			'user_info'    => array(
				'id'         => $this->user_id,
				'email'      => $this->email,
				'first_name' => $this->first_name,
				'last_name'  => $this->last_name,
				'discount'   => $this->discounts,
				'address'    => $this->address,
			),
			'cart_details' => $this->cart_details,
			'status'       => $this->status,
			'fees'         => $this->fees,
		);

		// Create an order
		$order_args = array(
			'parent'      => $this->parent_payment,
			'status'      => $this->status,
			'user_id'     => $this->user_id,
			'email'       => $this->email,
			'ip'          => $this->ip,
			'gateway'     => $this->gateway,
			'mode'        => $this->mode,
			'currency'    => $this->currency,
			'payment_key' => $this->key,
		);

		$order_id = edd_add_order( $order_args );

		if ( ! empty( $order_id ) ) {
			$this->ID  = $order_id;
			$this->_ID = $order_id;

			$customer = $this->maybe_create_customer();

			$this->customer_id = $customer->id;
			$customer->attach_payment( $this->ID, false );

			$order_data = array(
				'customer_id' => $this->customer_id,
			);

			/**
			 * This run of the edd_payment_meta filter is for backwards compatibility purposes. The filter will also run
			 * in the EDD_Payment::save method. By keeping this here, it retains compatibility of adding payment meta
			 * prior to the payment being inserted, as was previously supported by edd_insert_payment().
			 *
			 * @reference: https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5838
			 */
			$this->payment_meta = apply_filters( 'edd_payment_meta', $this->payment_meta, $payment_data );
			if ( ! empty( $this->payment_meta['fees'] ) ) {
				$this->fees = array_merge( $this->payment_meta['fees'], $this->fees );
				foreach ( $this->fees as $key => $fee ) {
					$adjustment_id = edd_add_order_adjustment( array(
						'object_id'   => $this->ID,
						'object_type' => 'order',
						'type_id'     => '',
						'type'        => 'fee',
						'description' => $fee['label'],
						'amount'      => $fee['amount'],
					) );

					edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $key );
					edd_add_order_adjustment_meta( $adjustment_id, 'no_tax', $fee['no_tax'] );
					edd_add_order_adjustment_meta( $adjustment_id, 'download_id', $fee['download_id'] );
					edd_add_order_adjustment_meta( $adjustment_id, 'price_id', $fee['price_id'] );

					$this->increase_fees( $fee['amount'] );
				}
			}

			if ( edd_get_option( 'enable_sequential' ) ) {
				$number       = edd_get_next_payment_number();
				$this->number = edd_format_payment_number( $number );

				$this->update_meta( '_edd_payment_number', $this->number );
				$order_data['order_number'] = $this->number;

				update_option( 'edd_last_payment_number', $number );
			}

			edd_update_order( $order_id, $order_data );

			$this->update_meta( '_edd_payment_meta', $this->payment_meta );

			$order_meta = array(
				'tax_rate' => $this->tax_rate,
			);

			foreach ( $order_meta as $key => $value ) {
				edd_add_order_meta( $order_id, $key, $value );
			}

			$this->new = true;
		}

		return $this->ID;
	}

	/**
	 * One items have been set, an update is needed to save them to the database.
	 *
	 * @since 3.0 Refactored to work with the new query methods.
	 *
	 * @return bool True of the save occurred, false if it failed or wasn't needed.
	 */
	public function save() {
		global $edd_logs;

		$saved = false;

		if ( empty( $this->ID ) ) {
			$payment_id = $this->insert_payment();

			if ( false === $payment_id ) {
				$saved = false;
			} else {
				$this->ID = $payment_id;
			}
		}

		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		$customer = $this->maybe_create_customer();
		if ( $this->customer_id !== $customer->id ) {
			$this->customer_id            = $customer->id;
			$this->pending['customer_id'] = $this->customer_id;
		}

		// If we have something pending, let's save it
		if ( ! empty( $this->pending ) ) {
			$total_increase = 0;
			$total_decrease = 0;

			foreach ( $this->pending as $key => $value ) {
				switch ( $key ) {
					case 'downloads':
						// Update totals for pending downloads
						foreach ( $this->pending[ $key ] as $cart_index => $item ) {
							switch ( $item['action'] ) {
								case 'add':
									$price = $item['price'];
									$taxes = $item['tax'];

									if ( 'publish' === $this->status || 'complete' === $this->status || 'revoked' === $this->status ) {
										$price_id = isset( $item['item_number']['options']['price_id'] )
											? $item['item_number']['options']['price_id']
											: 0;

										$increase_earnings = $price;
										if ( ! empty( $item['fees'] ) ) {
											foreach ( $item['fees'] as $fee ) {

												// Only let negative fees affect the earnings
												if ( $fee['amount'] > 0 ) {
													continue;
												}

												$increase_earnings += (float) $fee['amount'];
											}
										}

										edd_add_order_item( array(
											'order_id'   => $this->ID,
											'product_id' => $item['id'],
											'price_id'   => $price_id,
											'cart_index' => $cart_index,
											'type'       => 'download',
											'quantity'   => $item['quantity'],
											'amount'     => $item['item_price'],
											'subtotal'   => $item['subtotal'],
											'discount'   => $item['discount'],
											'tax'        => $item['tax'],
											'total'      => $item['price'],
										) );

										$download = new EDD_Download( $item['id'] );
										$download->increase_sales( $item['quantity'] );
										$download->increase_earnings( $increase_earnings );

										$total_increase += $price;
									}
									break;

								case 'remove':
									if ( 'publish' === $this->status || 'complete' === $this->status || 'revoked' === $this->status ) {
										$download = new EDD_Download( $item['id'] );
										$download->decrease_sales( $item['quantity'] );

										$decrease_amount = $item['amount'];
										if ( ! empty( $item['fees'] ) ) {
											foreach ( $item['fees'] as $fee ) {
												// Only let negative fees affect the earnings
												if ( $fee['amount'] > 0 ) {
													continue;
												}
												$decrease_amount += $fee['amount'];
											}
										}
										$download->decrease_earnings( $decrease_amount );

										$total_decrease += $item['amount'];
									}
									break;

								case 'modify':
									if ( 'publish' === $this->status || 'complete' === $this->status || 'revoked' === $this->status ) {
										$log_count_change = 0;

										if ( $item['previous_data']['quantity'] !== $item['quantity'] ) {
											$log_count_change = $item['previous_data']['quantity'] - $item['quantity'];

											// Find existing logs.
											$meta_query   = array();
											$meta_query[] = array(
												'key'     => '_edd_log_payment_id',
												'value'   => $this->ID,
												'compare' => '=',
											);

											if ( isset( $item['price_id'] ) ) {
												if ( ! empty( $item['price_id'] ) || 0 === (int) $item['price_id'] ) {
													$meta_query[] = array(
														'key'     => '_edd_log_price_id',
														'value'   => (int) $item['price_id'],
														'compare' => '=',
													);
												}
											}

											$log_args = array(
												'post_parent' => $item['id'],
												'meta_query'  => $meta_query,
												'log_type'    => 'sale',
											);

											$existing_logs = $edd_logs->get_connected_logs( $log_args );

											if ( count( $existing_logs ) > $item['quantity'] ) {

												// We have to remove some logs, since quantity has been reduced.
												$number_of_logs = count( $existing_logs ) - $item['quantity'];
												$logs_to_remove = array_slice( $existing_logs, 0, $number_of_logs );
												foreach ( $logs_to_remove as $log ) {
													wp_delete_post( $log->ID );
												}
											} elseif ( count( $existing_logs ) < $item['quantity'] ) {

												// We have to add some logs, since quantity has been increased.
												$log_date = date_i18n( 'Y-m-d G:i:s', strtotime( $this->completed_date ) );
												$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : 0;

												$number_of_logs = $item['quantity'] - count( $existing_logs );
												$y              = 0;
												while ( $y < $number_of_logs ) {
													edd_record_sale_in_log( $item['id'], $this->ID, $price_id, $log_date );
													$y ++;
												}

											}

										}

										$download = new EDD_Download( $item['id'] );

										// Change the number of sales for the download.
										if ( $log_count_change > 0 ) {
											$download->decrease_sales( $log_count_change );
										} elseif ( $log_count_change < 0 ) {
											$log_count_change = absint( $log_count_change );
											$download->increase_sales( $log_count_change );
										}

										// Change the earnings for the product.
										$price_change = $item['previous_data']['price'] - $item['price'];
										if ( $price_change > 0 ) {
											$download->decrease_earnings( $price_change );
											$total_increase -= $price_change;
										} elseif ( $price_change < 0 ) {
											$price_change = - ( $price_change );
											$download->increase_earnings( $price_change );
											$total_decrease += $price_change;
										}
									}
									break;
							}
						}
						break;

					case 'fees':
						if ( 'publish' !== $this->status && 'complete' !== $this->status && 'revoked' !== $this->status && ! $this->is_recoverable() ) {
							break;
						}

						if ( empty( $this->pending[ $key ] ) ) {
							break;
						}

						foreach ( $this->pending[ $key ] as $fee ) {
							switch ( $fee['action'] ) {
								case 'add':
									$total_increase += $fee['amount'];
									break;
								case 'remove':
									$total_decrease += $fee['amount'];
									break;
							}
						}
						break;

					case 'status':
						$this->update_status( $this->status );
						break;

					case 'gateway':
						edd_update_order( $this->ID, array(
							'gateway' => $this->gateway,
						) );
						break;

					case 'mode':
						edd_update_order( $this->ID, array(
							'mode' => $this->mode,
						) );
						break;

					case 'transaction_id':
						$this->update_meta( '_edd_payment_transaction_id', $this->transaction_id );
						break;

					case 'ip':
						edd_update_order( $this->ID, array(
							'ip' => $this->ip,
						) );
						break;

					case 'customer_id':
						edd_update_order( $this->ID, array(
							'customer_id' => $this->customer_id,
						) );

						$customer = new EDD_Customer( $this->customer_id );
						$customer->attach_payment( $this->ID, false );
						break;

					case 'user_id':
						edd_update_order( $this->ID, array(
							'$this->user_id' => $this->user_id,
						) );

						$this->user_info['id'] = $this->user_id;
						break;

					case 'first_name':
						$this->user_info['first_name'] = $this->first_name;
						break;

					case 'last_name':
						$this->user_info['last_name'] = $this->last_name;
						break;

					case 'discounts':
						if ( ! is_array( $this->discounts ) ) {
							$this->discounts = explode( ',', $this->discounts );
						}

						$cart_subtotal = 0.00;

						foreach ( $this->cart_details as $item ) {
							$cart_subtotal += $item['subtotal'];
						}

						if ( 'none' === $this->discounts[0] ) {
							break;
						}

						foreach ( $this->discounts as $discount ) {
							/** @var EDD_Discount $discount_obj */
							$discount_obj = edd_get_discount_by( 'code', $discount );

							edd_add_order_adjustment( array(
								'object_id'   => $this->ID,
								'object_type' => 'order',
								'type_id'     => $discount_obj->id,
								'type'        => 'discount',
								'description' => $discount,
								'amount'      => $cart_subtotal - $discount_obj->get_discounted_amount( $cart_subtotal ),
							) );
						}

						$this->user_info['discount'] = implode( ',', $this->discounts );
						break;

					case 'address':
						$this->user_info['address'] = $this->address;
						break;

					case 'email':
						$this->payment_meta['email'] = $this->email;
						$this->user_info['email']    = $this->email;

						edd_update_order( $this->ID, array(
							'email' => $this->email,
						) );
						break;

					case 'key':
						edd_update_order( $this->ID, array(
							'payment_key' => $this->key,
						) );
						break;

					case 'tax_rate':
						$this->update_meta( '_edd_payment_tax_rate', $this->tax_rate );
						break;

					case 'number':
						edd_update_order( $this->ID, array(
							'order_number' => $this->number,
						) );
						break;

					case 'date':
						edd_update_order( $this->ID, array(
							'date_created' => $this->date,
						) );
						break;

					case 'completed_date':
						edd_update_order( $this->ID, array(
							'date_completed' => $this->completed_date,
						) );
						break;

					case 'has_unlimited_downloads':
						$this->update_meta( 'unlimited_downloads', $this->has_unlimited_downloads );
						break;

					case 'parent_payment':
						edd_update_order( $this->ID, array(
							'parent' => $this->parent_payment,
						) );
						break;

					default:
						/**
						 * Used to save non-standard data. Developers can hook here if they want to save
						 * specific payment data when $payment->save() is run and their item is in the $pending array
						 */
						do_action( 'edd_payment_save', $this, $key );
						break;
				}
			}

			if ( ! $this->in_process() ) {
				$customer = new EDD_Customer( $this->customer_id );

				$total_change = $total_increase - $total_decrease;

				if ( $total_change < 0 ) {
					$total_change = - ( $total_change );
					// Decrease the customer's purchase stats
					$customer->decrease_value( $total_change );
					edd_decrease_total_earnings( $total_change );
				} elseif ( $total_change > 0 ) {
					// Increase the customer's purchase stats
					$customer->increase_value( $total_change );
					edd_increase_total_earnings( $total_change );
				}
			}

			$discount = 0.00;

			foreach ( $this->cart_details as $item ) {
				$discount += $item['discount'];
			}

			edd_update_order( $this->ID, array(
				'subtotal' => $this->subtotal,
				'tax'      => $this->tax,
				'discount' => $discount,
				'total'    => $this->total,
			) );

			$this->downloads = array_values( $this->downloads );

			$new_meta = array(
				'downloads'    => $this->downloads,
				'cart_details' => $this->cart_details,
				'fees'         => $this->fees,
				'currency'     => $this->currency,
				'user_info'    => is_array( $this->user_info ) ? $this->user_info : array(),
				'date'         => $this->date,
				'email'        => $this->email,
			);

			// Do some merging of user_info before we merge it all, to honor the edd_payment_meta filter
			if ( ! empty( $this->payment_meta['user_info'] ) ) {
				$stored_discount = ! empty( $new_meta['user_info']['discount'] ) ? $new_meta['user_info']['discount'] : '';

				$new_meta['user_info'] = array_replace_recursive( (array) $this->payment_meta['user_info'], $new_meta['user_info'] );

				if ( 'none' !== $stored_discount ) {
					$new_meta['user_info']['discount'] = $stored_discount;
				}
			}

			$meta        = $this->get_meta();
			$merged_meta = array_merge( $meta, $new_meta );

			$payment_data = array(
				'price'        => $this->total,
				'date'         => $this->date,
				'user_email'   => $this->email,
				'purchase_key' => $this->key,
				'currency'     => $this->currency,
				'downloads'    => $this->downloads,
				'user_info'    => array(
					'id'         => $this->user_id,
					'email'      => $this->email,
					'first_name' => $this->first_name,
					'last_name'  => $this->last_name,
					'discount'   => $this->discounts,
					'address'    => $this->address,
				),
				'cart_details' => $this->cart_details,
				'status'       => $this->status,
				'fees'         => $this->fees,
			);
			$merged_meta  = apply_filters( 'edd_payment_meta', $merged_meta, $payment_data );

			// Only save the payment meta if it's changed
			if ( md5( serialize( $meta ) ) !== md5( serialize( $merged_meta ) ) ) {
				// First, update the order.
				$order_info = array(
					'payment_key' => $this->key,
					'currency'    => $merged_meta['currency'],
					'email'       => $merged_meta['email'],
				);

				if ( isset( $merged_meta['user_info']['id'] ) ) {
					$order_info['user_id'] = $merged_meta['user_info']['id'];
				}

				if ( ! empty( $merged_meta['date'] ) ) {
					$order_info['date'] = $merged_meta['date'];
				}

				edd_update_order( $this->ID, $order_info );

				// We need to check if all of the order items exist in the database.
				$items = edd_get_order_items( array(
					'order_id' => $this->ID,
				) );

				// If an empty set was returned, this is a new payment.
				if ( empty( $items ) ) {
					foreach ( $merged_meta['cart_details'] as $key => $item ) {
						edd_add_order_item( array(
							'order_id'     => $this->ID,
							'product_id'   => $item['id'],
							'product_name' => $item['name'],
							'price_id'     => $item['item_number']['options']['price_id'],
							'cart_index'   => $key,
							'quantity'     => $item['quantity'],
							'amount'       => $item['item_price'],
							'subtotal'     => $item['subtotal'],
							'discount'     => $item['discount'],
							'tax'          => $item['tax'],
							'total'        => $item['price'],
						) );
					}
				}

				$this->update_meta( 'user_info', array(
					'first_name' => $merged_meta['user_info']['first_name'],
					'last_name'  => $merged_meta['user_info']['last_name'],
					'address'    => isset( $merged_meta['user_info']['address'] ) ? $merged_meta['user_info']['address'] : array(),
				) );

				$updated = $this->update_meta( '_edd_payment_meta', $merged_meta );

				if ( false !== $updated ) {
					$saved = true;
				}
			}

			$this->pending = array();
			$saved         = true;
		}

		if ( true === $saved ) {
			$this->setup_payment( $this->ID );

			/**
			 * This action fires anytime that $payment->save() is run, allowing developers to run actions
			 * when a payment is updated
			 */
			do_action( 'edd_payment_saved', $this->ID, $this );
		}

		/**
		 * Update the payment in the object cache
		 */
		$cache_key = md5( 'edd_payment' . $this->ID );
		wp_cache_set( $cache_key, $this, 'payments' );

		return $saved;
	}

	/**
	 * Add a download to a given payment
	 *
	 * @since 2.5
	 *
	 * @param int   $download_id The download to add
	 * @param array $args        Other arguments to pass to the function
	 * @param array $options     List of download options
	 *
	 * @return bool True when successful, false otherwise
	 */
	public function add_download( $download_id = 0, $args = array(), $options = array() ) {
		$download = new EDD_Download( $download_id );

		// Bail if this post isn't a download.
		if ( ! $download || 'download' !== $download->post_type ) {
			return false;
		}

		// Set up defaults.
		$defaults = array(
			'quantity'   => 1,
			'price_id'   => false,
			'item_price' => false,
			'discount'   => 0,
			'tax'        => 0.00,
			'fees'       => array(),
		);

		$args = wp_parse_args( apply_filters( 'edd_payment_add_download_args', $args, $download->ID ), $defaults );

		// Allow overriding the price.
		if ( false !== $args['item_price'] ) {
			$item_price = $args['item_price'];
		} else {

			// Deal with variable pricing.
			if ( edd_has_variable_prices( $download->ID ) ) {
				$prices = get_post_meta( $download->ID, 'edd_variable_prices', true );

				if ( $args['price_id'] && array_key_exists( $args['price_id'], (array) $prices ) ) {
					$item_price = $prices[ $args['price_id'] ]['amount'];
				} else {
					$item_price       = edd_get_lowest_price_option( $download->ID );
					$args['price_id'] = edd_get_lowest_price_id( $download->ID );
				}
			} else {
				$item_price = edd_get_download_price( $download->ID );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later
		$item_price = edd_sanitize_amount( $item_price );
		$quantity   = edd_item_quantities_enabled() ? absint( $args['quantity'] ) : 1;
		$amount     = round( $item_price * $quantity, edd_currency_decimal_filter() );

		// Setup the downloads meta item
		$new_download = array(
			'id'       => $download->ID,
			'quantity' => $quantity,
		);

		$default_options = array(
			'quantity' => $quantity,
		);

		if ( false !== $args['price_id'] ) {
			$default_options['price_id'] = (int) $args['price_id'];
		}

		$options                 = wp_parse_args( $options, $default_options );
		$new_download['options'] = $options;

		$this->downloads[] = $new_download;

		$discount = $args['discount'];
		$subtotal = $amount;
		$tax      = $args['tax'];

		if ( edd_prices_include_tax() ) {
			$subtotal -= round( $tax, edd_currency_decimal_filter() );
		}

		$total = $subtotal - $discount + $tax;

		// Do not allow totals to go negative
		if ( $total < 0 ) {
			$total = 0;
		}

		// Silly item_number array
		$item_number = array(
			'id'       => $download->ID,
			'quantity' => $quantity,
			'options'  => $options,
		);

		$this->cart_details[] = array(
			'name'        => $download->post_title,
			'id'          => $download->ID,
			'item_number' => $item_number,
			'item_price'  => round( $item_price, edd_currency_decimal_filter() ),
			'quantity'    => $quantity,
			'discount'    => $discount,
			'subtotal'    => round( $subtotal, edd_currency_decimal_filter() ),
			'tax'         => round( $tax, edd_currency_decimal_filter() ),
			'fees'        => $args['fees'],
			'price'       => round( $total, edd_currency_decimal_filter() ),
		);

		$added_download           = end( $this->cart_details );
		$added_download['action'] = 'add';

		$this->pending['downloads'][] = $added_download;
		reset( $this->cart_details );

		$this->increase_subtotal( $subtotal - $discount );
		$this->increase_tax( $tax );

		return true;

	}

	/**
	 * Remove a download from the payment
	 *
	 * @since  2.5
	 *
	 * @param  int   $download_id The download ID to remove
	 * @param  array $args        Arguments to pass to identify (quantity, amount, price_id)
	 *
	 * @return bool               If the item was removed or not
	 */
	public function remove_download( $download_id, $args = array() ) {

		// Set some defaults
		$defaults = array(
			'quantity'   => 1,
			'item_price' => false,
			'price_id'   => false,
			'cart_index' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		$download = new EDD_Download( $download_id );

		/**
		 * Bail if this post isn't a download post type.
		 *
		 * We need to allow this to process though for a missing post ID, in case it's a download that was deleted.
		 */
		if ( ! empty( $download->ID ) && 'download' !== $download->post_type ) {
			return false;
		}

		foreach ( $this->downloads as $key => $item ) {
			if ( (int) $download_id !== (int) $item['id'] ) {
				continue;
			}

			if ( false !== $args['price_id'] ) {
				if ( isset( $item['options']['price_id'] ) && (int) $args['price_id'] !== (int) $item['options']['price_id'] ) {
					continue;
				}
			} elseif ( false !== $args['cart_index'] ) {
				$cart_index = absint( $args['cart_index'] );
				$cart_item  = ! empty( $this->cart_details[ $cart_index ] ) ? $this->cart_details[ $cart_index ] : false;

				if ( ! empty( $cart_item ) ) {

					// If the cart index item isn't the same download ID, don't remove it
					if ( $cart_item['id'] !== $item['id'] ) {
						continue;
					}

					// If this item has a price ID, make sure it matches the cart indexed item's price ID before removing
					if ( ( isset( $item['options']['price_id'] ) && isset( $cart_item['item_number']['options']['price_id'] ) )
					     && (int) $item['options']['price_id'] !== (int) $cart_item['item_number']['options']['price_id'] ) {
						continue;
					}
				}
			}

			$item_quantity = $this->downloads[ $key ]['quantity'];

			if ( $item_quantity > $args['quantity'] ) {
				$this->downloads[ $key ]['quantity'] -= $args['quantity'];
				break;
			} else {
				unset( $this->downloads[ $key ] );
				break;
			}
		}

		$found_cart_key = false;

		if ( false === $args['cart_index'] ) {
			foreach ( $this->cart_details as $cart_key => $item ) {
				if ( $download_id !== $item['id'] ) {
					continue;
				}

				if ( false !== $args['price_id'] ) {
					if ( isset( $item['item_number']['options']['price_id'] ) && (int) $args['price_id'] !== (int) $item['item_number']['options']['price_id'] ) {
						continue;
					}
				}

				if ( false !== $args['item_price'] ) {
					if ( isset( $item['item_price'] ) && (float) $args['item_price'] !== (float) $item['item_price'] ) {
						continue;
					}
				}

				$found_cart_key = $cart_key;
				break;
			}
		} else {
			$cart_index = absint( $args['cart_index'] );

			if ( ! array_key_exists( $cart_index, $this->cart_details ) ) {
				return false; // Invalid cart index passed.
			}

			if ( (int) $this->cart_details[ $cart_index ]['id'] !== (int) $download_id ) {
				return false; // We still need the proper Download ID to be sure.
			}

			$found_cart_key = $cart_index;
		}

		$orig_quantity = $this->cart_details[ $found_cart_key ]['quantity'];

		if ( $orig_quantity > $args['quantity'] ) {
			$this->cart_details[ $found_cart_key ]['quantity'] -= $args['quantity'];

			$item_price = $this->cart_details[ $found_cart_key ]['item_price'];
			$tax        = $this->cart_details[ $found_cart_key ]['tax'];
			$discount   = ! empty( $this->cart_details[ $found_cart_key ]['discount'] ) ? $this->cart_details[ $found_cart_key ]['discount'] : 0;

			// The total reduction equals the number removed * the item_price
			$total_reduced = round( $item_price * $args['quantity'], edd_currency_decimal_filter() );
			$tax_reduced   = round( ( $tax / $orig_quantity ) * $args['quantity'], edd_currency_decimal_filter() );

			$new_quantity = $this->cart_details[ $found_cart_key ]['quantity'];
			$new_tax      = $this->cart_details[ $found_cart_key ]['tax'] - $tax_reduced;
			$new_subtotal = $new_quantity * $item_price;
			$new_discount = 0;
			$new_total    = 0;

			$this->cart_details[ $found_cart_key ]['subtotal'] = $new_subtotal;
			$this->cart_details[ $found_cart_key ]['discount'] = $new_discount;
			$this->cart_details[ $found_cart_key ]['tax']      = $new_tax;
			$this->cart_details[ $found_cart_key ]['price']    = $new_subtotal - $new_discount + $new_tax;
		} else {
			$total_reduced = $this->cart_details[ $found_cart_key ]['item_price'];
			$tax_reduced   = $this->cart_details[ $found_cart_key ]['tax'];

			$found_fees = array();

			if ( ! empty( $this->cart_details[ $found_cart_key ]['fees'] ) ) {
				$found_fees = $this->cart_details[ $found_cart_key ]['fees'];

				foreach ( $found_fees as $key => $fee ) {
					$this->remove_fee( $key );
				}
			}

			unset( $this->cart_details[ $found_cart_key ] );
		}

		$pending_args             = $args;
		$pending_args['id']       = $download_id;
		$pending_args['amount']   = $total_reduced;
		$pending_args['price_id'] = false !== $args['price_id'] ? $args['price_id'] : false;
		$pending_args['quantity'] = $args['quantity'];
		$pending_args['action']   = 'remove';
		$pending_args['fees']     = isset( $found_fees ) ? $found_fees : array();

		$this->pending['downloads'][] = $pending_args;

		$this->decrease_subtotal( $total_reduced );
		$this->decrease_tax( $tax_reduced );

		return true;
	}

	/**
	 * Alter a limited set of properties of a cart item
	 *
	 * @since 2.7
	 *
	 * @param bool  $cart_index
	 * @param array $args
	 *
	 * @return bool
	 */
	public function modify_cart_item( $cart_index = false, $args = array() ) {
		if ( false === $cart_index ) {
			return false;
		}

		if ( ! array_key_exists( $cart_index, $this->cart_details ) ) {
			return false;
		}

		$current_args  = $this->cart_details[ $cart_index ];
		$allowed_items = apply_filters( 'edd_allowed_cart_item_modifications', array(
			'item_price',
			'tax',
			'discount',
			'quantity',
		) );

		// Remove any items we don't want to modify.
		foreach ( $args as $key => $arg ) {
			if ( ! in_array( $key, $allowed_items, true ) ) {
				unset( $args[ $key ] );
			}
		}

		$merged_item = array_merge( $current_args, $args );

		// Sort the current and new args, and checksum them. If no changes. No need to fire a modification.
		ksort( $current_args );
		ksort( $merged_item );

		if ( md5( json_encode( $current_args ) ) === md5( json_encode( $merged_item ) ) ) {
			return false;
		}

		// Format the item_price correctly now
		$merged_item['item_price'] = edd_sanitize_amount( $merged_item['item_price'] );

		$new_subtotal                       = floatval( $merged_item['item_price'] ) * $merged_item['quantity'];
		$merged_item['price']               = $new_subtotal + $merged_item['tax'];
		$this->cart_details[ $cart_index ]  = $merged_item;
		$modified_download                  = $merged_item;
		$modified_download['action']        = 'modify';
		$modified_download['previous_data'] = $current_args;

		$this->pending['downloads'][] = $modified_download;

		if ( $new_subtotal > $current_args['subtotal'] ) {
			$this->increase_subtotal( ( $new_subtotal - $modified_download['discount'] ) - $current_args['subtotal'] );
		} else {
			$this->decrease_subtotal( $current_args['subtotal'] - ( $new_subtotal - $modified_download['discount'] ) );
		}

		if ( $modified_download['tax'] > $current_args['tax'] ) {
			$this->increase_tax( $modified_download['tax'] - $current_args['tax'] );
		} else {
			$this->increase_tax( $current_args['tax'] - $modified_download['tax'] );
		}

		return true;
	}

	/**
	 * Add a fee to a given payment.
	 *
	 * @since 2.5
	 *
	 * @param array $args   Array of arguments for the fee to add.
	 * @param bool  $global
	 *
	 * @return bool If the fee was added.
	 */
	public function add_fee( $args, $global = true ) {
		$default_args = array(
			'label'       => '',
			'amount'      => 0,
			'type'        => 'fee',
			'id'          => '',
			'no_tax'      => false,
			'download_id' => 0,
		);

		$fee          = wp_parse_args( $args, $default_args );
		$this->fees[] = $fee;

		$added_fee               = $fee;
		$added_fee['action']     = 'add';
		$this->pending['fees'][] = $added_fee;
		reset( $this->fees );

		$this->increase_fees( $fee['amount'] );

		return true;
	}

	/**
	 * Remove a fee from the payment
	 *
	 * @since  2.5
	 *
	 * @param  int $key The array key index to remove
	 *
	 * @return bool     If the fee was removed successfully
	 */
	public function remove_fee( $key ) {
		$removed = $this->remove_fee_by( 'index', $key );

		return $removed;
	}

	/**
	 * Remove a fee by the defined attributed
	 *
	 * @since 2.5
	 *
	 * @param string     $key    The key to remove by
	 * @param int|string $value  The value to search for
	 * @param boolean    $global False - removes the first value it finds, True - removes all matches
	 *
	 * @return boolean If the item is removed.
	 */
	public function remove_fee_by( $key, $value, $global = false ) {
		$allowed_fee_keys = apply_filters( 'edd_payment_fee_keys', array(
			'index',
			'label',
			'amount',
			'type',
		) );

		if ( ! in_array( $key, $allowed_fee_keys, true ) ) {
			return false;
		}

		$removed = false;

		if ( 'index' === $key && array_key_exists( $value, $this->fees ) ) {
			$removed_fee             = $this->fees[ $value ];
			$removed_fee['action']   = 'remove';
			$this->pending['fees'][] = $removed_fee;

			$this->decrease_fees( $removed_fee['amount'] );

			unset( $this->fees[ $value ] );
			$removed = true;
		} elseif ( 'index' !== $key ) {
			foreach ( $this->fees as $index => $fee ) {
				if ( isset( $fee[ $key ] ) && $fee[ $key ] === $value ) {
					$removed_fee             = $fee;
					$removed_fee['action']   = 'remove';
					$this->pending['fees'][] = $removed_fee;

					$this->decrease_fees( $removed_fee['amount'] );

					unset( $this->fees[ $index ] );
					$removed = true;

					if ( false === $global ) {
						break;
					}
				}
			}
		}

		if ( true === $removed ) {
			$this->fees = array_values( $this->fees );
		}

		return $removed;
	}

	/**
	 * Get the fees, filterable by type.
	 *
	 * @since 2.5
	 *
	 * @param string $type All, item, fee.
	 *
	 * @return array Fees for the type specified.
	 */
	public function get_fees( $type = 'all' ) {
		$fees = array();

		if ( ! empty( $this->fees ) && is_array( $this->fees ) ) {
			foreach ( $this->fees as $fee_id => $fee ) {
				if ( 'all' !== $type && ! empty( $fee['type'] ) && $type !== $fee['type'] ) {
					continue;
				}

				$fee['id'] = $fee_id;
				$fees[]    = $fee;

			}
		}

		return apply_filters( 'edd_get_payment_fees', $fees, $this->ID, $this );
	}

	/**
	 * Add a note to an order.
	 *
	 * @since 2.5
	 * @since 3.0 Return true if note was inserted successfully.
	 *
	 * @param string $note The note to add.
	 *
	 * @return bool Whether or not the note was inserted.
	 */
	public function add_note( $note = '' ) {

		// Bail if no note specified.
		if ( ! $note || empty( $note ) ) {
			return false;
		}

		$note_id = edd_insert_payment_note( $this->ID, esc_html( $note ) );

		if ( $note_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Increase the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment subtotal by
	 *
	 * @return void
	 */
	private function increase_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to decrease the payment subtotal by
	 *
	 * @return void
	 */
	private function decrease_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal -= $amount;

		if ( $this->subtotal < 0 ) {
			$this->subtotal = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Increase the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment subtotal by
	 *
	 * @return void
	 */
	private function increase_fees( $amount = 0.00 ) {
		$amount            = (float) $amount;
		$this->fees_total += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's subtotal
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to decrease the payment subtotal by
	 *
	 * @return void
	 */
	private function decrease_fees( $amount = 0.00 ) {
		$amount            = (float) $amount;
		$this->fees_total -= $amount;

		if ( $this->fees_total < 0 ) {
			$this->fees_total = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set or update the total for a payment
	 *
	 * @since 2.5
	 * @return void
	 */
	private function recalculate_total() {
		$this->total = $this->subtotal + $this->tax + $this->fees_total;
	}

	/**
	 * Increase the payment's tax by the provided amount
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to increase the payment tax by
	 *
	 * @return void
	 */
	public function increase_tax( $amount = 0.00 ) {
		$amount     = (float) $amount;
		$this->tax += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's tax by the provided amount
	 *
	 * @since  2.5
	 *
	 * @param  float $amount The amount to reduce the payment tax by
	 *
	 * @return void
	 */
	public function decrease_tax( $amount = 0.00 ) {
		$amount     = (float) $amount;
		$this->tax -= $amount;

		if ( $this->tax < 0 ) {
			$this->tax = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set the payment status and run any status specific changes necessary
	 *
	 * @since 2.5
	 *
	 * @param string $status The status to set the payment to.
	 *
	 * @return bool Returns if the status was successfully updated
	 */
	public function update_status( $status = '' ) {

		// Bail if an empty status is passed.
		if ( empty( $status ) || ! $status ) {
			return false;
		}

		if ( 'completed' === $status || 'complete' === $status ) {
			$status = 'publish';
		}

		$old_status = ! empty( $this->old_status ) ? $this->old_status : false;

		if ( $old_status === $status ) {
			return false; // Don't permit status changes that aren't changes
		}

		$do_change = apply_filters( 'edd_should_update_payment_status', true, $this->ID, $status, $old_status );

		$updated = false;

		if ( $do_change ) {
			do_action( 'edd_before_payment_status_change', $this->ID, $status, $old_status );

			$update_fields = apply_filters( 'edd_update_payment_status_fields', array(
				'post_status' => $status,
			) );

			/**
			 * Map the array keys to ones accepted by the new methods.
			 *
			 * @since 3.0
			 */
			$update_fields['status'] = $update_fields['post_status'];

			unset( $update_fields['ID'] );
			unset( $update_fields['post_status'] );

			edd_update_order( $this->ID, $update_fields );

			$this->status      = $status;
			$this->post_status = $status;

			$all_payment_statuses  = edd_get_payment_statuses();
			$this->status_nicename = array_key_exists( $status, $all_payment_statuses ) ? $all_payment_statuses[ $status ] : ucfirst( $status );

			// Process any specific status functions
			switch ( $status ) {
				case 'refunded':
					$this->process_refund();
					break;
				case 'failed':
					$this->process_failure();
					break;
				case 'pending' || 'processing':
					$this->process_pending();
					break;
			}

			do_action( 'edd_update_payment_status', $this->ID, $status, $old_status );
		}

		return $updated;

	}

	/**
	 * Change the status of the payment to refunded, and run the necessary changes
	 *
	 * @since  2.5.7
	 * @return void
	 */
	public function refund() {
		$this->old_status        = $this->status;
		$this->status            = 'refunded';
		$this->pending['status'] = $this->status;

		$this->save();
	}

	/**
	 * Get a post meta item for the payment
	 *
	 * @since  2.5
	 *
	 * @param  string  $meta_key The Meta Key
	 * @param  boolean $single   Return single item or array
	 *
	 * @return mixed             The value from the post meta
	 */
	public function get_meta( $meta_key = '_edd_payment_meta', $single = true ) {
		$meta = get_post_meta( $this->ID, $meta_key, $single );

		// Backwards compatibility.
		switch ( $meta_key ) {
			case '_edd_payment_purchase_key':
				$meta = $this->order->get_payment_key();
				break;

			case '_edd_payment_transaction_id':
				$meta = $this->order->get_transaction_id();
				break;

			case '_edd_payment_user_email':
				$meta = $this->order->get_email();
				break;
		}

		if ( '_edd_payment_meta' === $meta_key ) {
			if ( empty( $meta ) ) {
				$meta = array();
			}

			// #5228 Fix possible data issue introduced in 2.6.12
			if ( is_array( $meta ) && isset( $meta[0] ) ) {
				$bad_meta = $meta[0];
				unset( $meta[0] );

				if ( is_array( $bad_meta ) ) {
					$meta = array_merge( $meta, $bad_meta );
				}

				update_post_meta( $this->ID, '_edd_payment_meta', $meta );
			}

			// Payment meta was simplified in EDD v1.5, so these are here for backwards compatibility
			if ( empty( $meta['key'] ) ) {
				$meta['key'] = $this->key;
			}

			if ( empty( $meta['email'] ) ) {
				$meta['email'] = $this->email;
			}

			if ( empty( $meta['date'] ) ) {
				$meta['date'] = $this->date;
			}

			// We need to back fill the returned meta for backwards compatibility purposes.
			$meta['key']          = $this->key;
			$meta['email']        = $this->email;
			$meta['date']         = $this->date;
			$meta['user_info']    = $this->user_info;
			$meta['downloads']    = $this->downloads;
			$meta['cart_details'] = $this->cart_details;
			$meta['fees']         = $this->fees;
			$meta['currency']     = $this->currency;
			$meta['tax']          = $this->tax;
		}

		$meta = apply_filters( 'edd_get_payment_meta_' . $meta_key, $meta, $this->ID );

		if ( is_serialized( $meta ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches );
			if ( ! empty( $matches ) ) {
				$meta = array();
			}
		}

		return apply_filters( 'edd_get_payment_meta', $meta, $this->ID, $meta_key );
	}

	/**
	 * Update the order meta.
	 *
	 * @since 2.5
	 * @since 3.0 Updated to use the new custom tables.
	 *
	 * @param string $meta_key   The meta key to update.
	 * @param string $meta_value The meta value.
	 * @param string $prev_value Previous meta value.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		$meta_value = apply_filters( 'edd_update_payment_meta_' . $meta_key, $meta_value, $this->ID );

		switch ( $meta_key ) {
			case '_edd_payment_meta':
				if ( isset( $meta_value['tax'] ) && ! empty( $meta_value['tax'] ) ) {
					return edd_update_order( $this->ID, array(
						'tax' => $meta_value['tax'],
					) );
				}

				if ( isset( $meta_value['key'] ) && ! empty( $meta_value['key'] ) ) {
					return edd_update_order( $this->ID, array(
						'key' => $meta_value['key'],
					) );
				}

				if ( isset( $meta_value['email'] ) && ! empty( $meta_value['email'] ) ) {
					return edd_update_order( $this->ID, array(
						'email' => $meta_value['email'],
					) );
				}

				if ( isset( $meta_value['currency'] ) && ! empty( $meta_value['currency'] ) ) {
					return edd_update_order( $this->ID, array(
						'currency' => $meta_value['currency'],
					) );
				}

				if ( isset( $meta_value['user_info'] ) && ! empty( $meta_value['user_info'] ) ) {
					$user_info = array(
						'first_name' => $meta_value['user_info']['first_name'],
						'last_name'  => $meta_value['user_info']['last_name'],
						'address'    => $meta_value['user_info']['address'],
					);

					return edd_update_order_meta( $this->ID, 'user_info', $user_info );
				}

				if ( isset( $meta_value['fees'] ) && ! empty( $meta_value['fees'] ) ) {
					foreach ( $meta_value['fees'] as $fee_id => $fee ) {
						if ( ! empty( $fee['download_id'] ) && 0 < $fee['download_id'] ) {
							$order_item_id = edd_get_order_items( array(
								'number'     => 1,
								'order_id'   => $this->ID,
								'product_id' => $fee['download_id'],
								'fields'     => 'ids',
							) );

							if ( is_array( $order_item_id ) ) {
								$order_item_id = (int) $order_item_id[0];
							}

							$adjustment_id = edd_get_order_adjustments( array(
								'number'      => 1,
								'object_id'   => $order_item_id,
								'object_type' => 'order_item',
								'type'        => 'fee',
								'fields'      => 'ids',
								'meta_query'  => array(
									array(
										'key'     => 'fee_id',
										'value'   => $fee_id,
										'compare' => '=',
									),
								),
							) );

							if ( is_array( $adjustment_id ) && ! empty( $adjustment_id ) ) {
								$adjustment_id = $adjustment_id[0];

								edd_update_order_adjustment( $adjustment_id, array(
									'description' => $fee['label'],
									'amount'      => (float) $fee['amount'],
								) );

								if ( isset( $fee['no_tax'] ) && ( true === $fee['no_tax'] ) ) {
									edd_update_order_adjustment_meta( $adjustment_id, 'no_tax', $fee['no_tax'] );
								}

								if ( ! is_null( $fee['price_id'] ) ) {
									edd_update_order_adjustment_meta( $adjustment_id, 'price_id', absint( $fee['price_id'] ) );
								}
							} else {
								$adjustment_id = edd_add_order_adjustment( array(
									'object_id'   => $order_item_id,
									'object_type' => 'order_item',
									'type'        => 'fee',
									'description' => $fee['label'],
									'amount'      => (float) $fee['amount'],
								) );

								edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

								if ( isset( $fee['no_tax'] ) && ( true === $fee['no_tax'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'no_tax', $fee['no_tax'] );
								}

								if ( ! is_null( $fee['price_id'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'price_id', absint( $fee['price_id'] ) );
								}
							}
						} else {
							$adjustment_id = edd_get_order_adjustments( array(
								'number'      => 1,
								'object_id'   => $this->ID,
								'object_type' => 'order',
								'type'        => 'fee',
								'fields'      => 'ids',
								'meta_query'  => array(
									array(
										'key'     => 'fee_id',
										'value'   => $fee_id,
										'compare' => '=',
									),
								),
							) );

							if ( is_array( $adjustment_id ) && ! empty( $adjustment_id ) ) {
								$adjustment_id = $adjustment_id[0];

								edd_update_order_adjustment( $adjustment_id, array(
									'description' => $fee['label'],
									'amount'      => (float) $fee['amount'],
								) );

								if ( isset( $fee['no_tax'] ) && ( true === $fee['no_tax'] ) ) {
									edd_update_order_adjustment_meta( $adjustment_id, 'no_tax', $fee['no_tax'] );
								}

								if ( ! is_null( $fee['price_id'] ) ) {
									edd_update_order_adjustment_meta( $adjustment_id, 'price_id', absint( $fee['price_id'] ) );
								}
							} else {
								$adjustment_id = edd_add_order_adjustment( array(
									'object_id'   => $this->ID,
									'object_type' => 'order',
									'type'        => 'fee',
									'description' => $fee['label'],
									'amount'      => (float) $fee['amount'],
								) );

								edd_add_order_adjustment_meta( $adjustment_id, 'fee_id', $fee_id );

								if ( isset( $fee['no_tax'] ) && ( true === $fee['no_tax'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'no_tax', $fee['no_tax'] );
								}

								if ( ! is_null( $fee['price_id'] ) ) {
									edd_add_order_adjustment_meta( $adjustment_id, 'price_id', absint( $fee['price_id'] ) );
								}
							}
						}
					}
				}

				if ( isset( $meta_value['cart_details'] ) && ! empty( $meta_value['cart_details'] ) ) {
					foreach ( $meta_value['cart_details'] as $key => $item ) {
						$order_item_id = edd_get_order_items( array(
							'number'       => 1,
							'fields'       => 'ids',
							'order_id'     => $this->ID,
							'product_id'   => $item['id'],
							'product_name' => $item['name'],
						) );

						if ( is_array( $order_item_id ) && ! empty( $order_item_id ) ) {
							$order_item_id = $order_item_id[0];

							edd_update_order_item( $order_item_id, array(
								'order_id'     => $this->ID,
								'product_id'   => $item['id'],
								'product_name' => $item['name'],
								'price_id'     => $item['item_number']['options']['price_id'],
								'cart_index'   => $key,
								'quantity'     => $item['quantity'],
								'amount'       => $item['item_price'],
								'subtotal'     => $item['subtotal'],
								'discount'     => $item['discount'],
								'tax'          => $item['tax'],
								'total'        => $item['price'],
							) );
						} else {
							edd_add_order_item( array(
								'order_id'     => $this->ID,
								'product_id'   => $item['id'],
								'product_name' => $item['name'],
								'price_id'     => $item['item_number']['options']['price_id'],
								'cart_index'   => $key,
								'quantity'     => $item['quantity'],
								'amount'       => $item['item_price'],
								'subtotal'     => $item['subtotal'],
								'discount'     => $item['discount'],
								'tax'          => $item['tax'],
								'total'        => $item['price'],
							) );
						}
					}
				}

				break;
			case '_edd_completed_date':
				$meta_value = empty( $meta_value )
					? '0000-00-00 00:00:00'
					: $meta_value;

				return edd_update_order( $this->ID, array(
					'date_completed' => $meta_value,
				) );
			case '_edd_payment_gateway':
				return edd_update_order( $this->ID, array(
					'gateway' => $meta_value,
				) );
			case '_edd_payment_user_id':
				return edd_update_order( $this->ID, array(
					'user_id' => $meta_value,
				) );
			case '_edd_payment_user_email':
			case 'email':
				return edd_update_order( $this->ID, array(
					'email' => $meta_value,
				) );
			case '_edd_payment_user_ip':
				return edd_update_order( $this->ID, array(
					'ip' => $meta_value,
				) );
			case '_edd_payment_purchase_key':
			case 'key':
				return edd_update_order( $this->ID, array(
					'payment_key' => $meta_value,
				) );
			case '_edd_payment_mode':
				return edd_update_order( $this->ID, array(
					'mode' => $meta_value,
				) );
			case '_edd_payment_tax_rate':
				return edd_update_order_meta( $this->ID, 'tax_rate', $meta_value, $prev_value );
			case '_edd_payment_customer_id':
				return edd_update_order( $this->ID, array(
					'customer_id' => $meta_value,
				) );
			case '_edd_payment_total':
				return edd_update_order( $this->ID, array(
					'total' => $meta_value,
				) );
			case '_edd_payment_tax':
				return edd_update_order( $this->ID, array(
					'tax' => $meta_value,
				) );
			case '_edd_payment_number':
				return edd_update_order( $this->ID, array(
					'order_number' => $meta_value,
				) );
			case '_edd_payment_transaction_id':
				return edd_update_order_meta( $this->ID, 'transaction_id', $meta_value );
		}

		$meta_key = str_replace( '_edd_payment_', '', $meta_key );

		return edd_update_order_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Add an item to the payment meta
	 *
	 * @since 2.8
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param bool   $unique
	 *
	 * @return bool|false|int
	 */
	public function add_meta( $meta_key = '', $meta_value = '', $unique = false ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		return edd_add_order_meta( $this->ID, $meta_key, $meta_value, $unique );
	}

	/**
	 * Delete an item from payment meta
	 *
	 * @since 2.8
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		return edd_delete_order_meta( $this->ID, $meta_key, $meta_value );
	}

	/**
	 * Determines if this payment is able to be resumed by the user.
	 *
	 * @since 2.7
	 *
	 * @return bool
	 */
	public function is_recoverable() {
		$recoverable = false;

		$recoverable_statuses = apply_filters( 'edd_recoverable_payment_statuses', array( 'pending', 'abandoned', 'failed' ) );

		if ( in_array( $this->status, $recoverable_statuses, true ) && empty( $this->transaction_id ) ) {
			$recoverable = true;
		}

		return $recoverable;
	}

	/**
	 * Returns the URL that a customer can use to resume a payment, or false if it's not recoverable.
	 *
	 * @since 2.7
	 *
	 * @return bool|string
	 */
	public function get_recovery_url() {
		if ( ! $this->is_recoverable() ) {
			return false;
		}

		$recovery_url = add_query_arg( array(
			'edd_action' => 'recover_payment',
			'payment_id' => $this->ID,
		), edd_get_checkout_uri() );

		return apply_filters( 'edd_payment_recovery_url', $recovery_url, $this );
	}

	/**
	 * When a payment is set to a status of 'refunded' process the necessary actions to reduce stats
	 *
	 * @since 2.5.7
	 * @access private
	 */
	private function process_refund() {
		$process_refund = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented
		if ( ( 'publish' !== $this->old_status && 'revoked' !== $this->old_status ) || 'refunded' !== $this->status ) {
			$process_refund = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments
		$process_refund = apply_filters( 'edd_should_process_refund', $process_refund, $this );

		if ( false === $process_refund ) {
			return;
		}

		do_action( 'edd_pre_refund_payment', $this );

		$decrease_store_earnings = apply_filters( 'edd_decrease_store_earnings_on_refund', true, $this );
		$decrease_customer_value = apply_filters( 'edd_decrease_customer_value_on_refund', true, $this );
		$decrease_purchase_count = apply_filters( 'edd_decrease_customer_purchase_count_on_refund', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );

		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );

		do_action( 'edd_post_refund_payment', $this );
	}

	/**
	 * Process when a payment is set to failed, decrement discount usages and other stats.
	 *
	 * @since 2.5.7
	 */
	private function process_failure() {
		$discounts = $this->discounts;

		if ( 'none' === $discounts || empty( $discounts ) ) {
			return;
		}

		if ( ! is_array( $discounts ) ) {
			$discounts = array_map( 'trim', explode( ',', $discounts ) );
		}

		foreach ( $discounts as $discount ) {
			edd_decrease_discount_usage( $discount );
		}
	}

	/**
	 * Process when a payment moves to pending.
	 *
	 * @since 2.5.10
	 */
	private function process_pending() {
		$process_pending = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented
		if ( ( 'publish' !== $this->old_status && 'revoked' !== $this->old_status ) || ! $this->in_process() ) {
			$process_pending = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments
		$process_pending = apply_filters( 'edd_should_process_pending', $process_pending, $this );

		if ( false === $process_pending ) {
			return;
		}

		$decrease_store_earnings = apply_filters( 'edd_decrease_store_earnings_on_pending', true, $this );
		$decrease_customer_value = apply_filters( 'edd_decrease_customer_value_on_pending', true, $this );
		$decrease_purchase_count = apply_filters( 'edd_decrease_customer_purchase_count_on_pending', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );

		$this->completed_date = false;
		$this->update_meta( '_edd_completed_date', '' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'edd_earnings_this_monththis_month' ) );
	}

	/**
	 * Used during the process of moving to refunded or pending, to decrement stats
	 *
	 * @since 2.5.10
	 *
	 * @param bool $alter_store_earnings          If the method should alter the store earnings
	 * @param bool $alter_customer_value          If the method should reduce the customer value
	 * @param bool $alter_customer_purchase_count If the method should reduce the customer's purchase count
	 */
	private function maybe_alter_stats( $alter_store_earnings, $alter_customer_value, $alter_customer_purchase_count ) {
		edd_undo_purchase( false, $this->ID );

		// Decrease store earnings
		if ( true === $alter_store_earnings ) {
			edd_decrease_total_earnings( $this->total );
		}

		// Decrement the stats for the customer
		if ( ! empty( $this->customer_id ) ) {
			$customer = new EDD_Customer( $this->customer_id );

			if ( true === $alter_customer_value ) {
				$customer->decrease_value( $this->total );
			}

			if ( true === $alter_customer_purchase_count ) {
				$customer->decrease_purchase_count();
			}
		}
	}

	/**
	 * Delete sales logs for this purchase
	 *
	 * @since 2.5.10
	 * @deprecated Deprecated since 3.0 as sales logs are no longer used.
	 */
	private function delete_sales_logs() {
		_doing_it_wrong( __FUNCTION__, 'Sales logs are deprecated and are no longer used.', 'EDD 3.0' );
	}

	/**
	 * Setup functions only, these are not to be used by developers.
	 * These functions exist only to allow the setup routine to be backwards compatible with our old
	 * helper functions.
	 *
	 * These will run whenever setup_payment is called, which should only be called once.
	 * To update an attribute, update it directly instead of re-running the setup routine
	 */

	/**
	 * Setup the payment completed date
	 *
	 * @since 2.5
	 * @since 3.0 Updated to use the new custom tables.
	 *
	 * @return string The date the payment was completed
	 */
	private function setup_completed_date() {
		/** @var EDD\Orders\Order $order */
		$order = edd_get_order( $this->ID );

		if ( 'pending' === $order->get_status() || 'preapproved' === $order->get_status() || 'processing' === $order->get_status() ) {
			return false; // This payment was never completed
		}

		$date = ( $date = $order->get_date_completed() )
			? $date
			: $order->get_date_created();

		return $date;
	}

	/**
	 * Setup the payment total
	 *
	 * @since  2.5
	 * @return float The payment total
	 */
	private function setup_total() {
		$amount = $this->get_meta( '_edd_payment_total', true );

		if ( empty( $amount ) && '0.00' !== $amount ) {
			$meta = $this->get_meta( '_edd_payment_meta', true );
			$meta = maybe_unserialize( $meta );

			if ( isset( $meta['amount'] ) ) {
				$amount = $meta['amount'];
			}
		}

		return $amount;
	}

	/**
	 * Setup the payment tax rate
	 *
	 * @since  2.7
	 * @return float The tax rate for the payment
	 */
	private function setup_tax_rate() {
		return $this->get_meta( 'tax_rate', true );
	}

	/**
	 * Setup the payment fees
	 *
	 * @since  2.5.10
	 * @return float The fees total for the payment
	 */
	private function setup_fees_total() {
		$fees_total = (float) 0.00;

		$payment_fees = isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();
		if ( ! empty( $payment_fees ) ) {
			foreach ( $payment_fees as $fee ) {
				$fees_total += (float) $fee['amount'];
			}
		}

		return $fees_total;

	}

	/**
	 * Setup the payment subtotal
	 *
	 * @since  2.5
	 * @return float The subtotal of the payment
	 */
	private function setup_subtotal() {
		$subtotal     = 0;
		$cart_details = $this->cart_details;

		if ( is_array( $cart_details ) ) {
			foreach ( $cart_details as $item ) {
				if ( isset( $item['subtotal'] ) ) {
					$subtotal += $item['subtotal'];
				}
			}
		} else {
			$subtotal = $this->total;
			$tax      = edd_use_taxes() ? $this->tax : 0;
			$subtotal -= $tax;
		}

		return $subtotal;
	}

	/**
	 * Setup the payments discount codes
	 *
	 * @since  2.5
	 * @return array               Array of discount codes on this payment
	 */
	private function setup_discounts() {
		$discounts = ! empty( $this->payment_meta['user_info']['discount'] ) ? $this->payment_meta['user_info']['discount'] : array();

		return $discounts;
	}

	/**
	 * Setup the currency code
	 *
	 * @since  2.5
	 * @return string              The currency for the payment
	 */
	private function setup_currency() {
		$currency = $this->order->get_currency();

		return ! empty( $currency )
			? $currency
			: apply_filters( 'edd_payment_currency_default', edd_get_currency(), $this );
	}

	/**
	 * Setup any fees associated with the payment
	 *
	 * @since  2.5
	 * @return array               The Fees
	 */
	private function setup_fees() {
		$payment_fees = isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();

		return $payment_fees;
	}

	/**
	 * Setup the transaction ID
	 *
	 * @since  2.5
	 * @return string The transaction ID for the payment
	 */
	private function setup_transaction_id() {
		$transaction_id = $this->get_meta( '_edd_payment_transaction_id', true );

		if ( empty( $transaction_id ) || (int) $transaction_id === (int) $this->ID ) {

			$gateway        = $this->gateway;
			$transaction_id = apply_filters( 'edd_get_payment_transaction_id-' . $gateway, $this->ID );

		}

		return $transaction_id;
	}

	/**
	 * Setup the User ID associated with the purchase
	 *
	 * @since  2.5
	 * @return int The User ID
	 */
	private function setup_user_id() {
		$user_id  = $this->get_meta( '_edd_payment_user_id', true );
		$customer = new EDD_Customer( $this->customer_id );

		// Make sure it exists, and that it matches that of the associated customer record
		if ( ! empty( $customer->user_id ) && ( empty( $user_id ) || (int) $user_id !== (int) $customer->user_id ) ) {

			$user_id = $customer->user_id;

			// Backfill the user ID, or reset it to be correct in the event of data corruption
			$this->update_meta( '_edd_payment_user_id', $user_id );

		}

		return $user_id;
	}

	/**
	 * Setup the email address for the purchase
	 *
	 * @since  2.5
	 * @return string The email address for the payment
	 */
	private function setup_email() {
		$email = $this->order->get_email();

		if ( empty( $email ) ) {
			$email = EDD()->customers->get_column( 'email', $this->customer_id );
		}

		return $email;
	}

	/**
	 * Setup the user info
	 *
	 * @since  2.5
	 * @return array               The user info associated with the payment
	 */
	private function setup_user_info() {

		$user_info = isset( $this->payment_meta['user_info'] )
			? $this->payment_meta['user_info']
			: array();

		if ( is_serialized( $user_info ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				$user_info = array();
			}
		}

		// As per Github issue #4248, we need to run maybe_unserialize here still.
		$user_info = wp_parse_args( maybe_unserialize( $user_info ), array(
			'first_name' => $this->first_name,
			'last_name'  => $this->last_name,
			'discount'   => $this->discounts,
		) );

		// Ensure email index is in the old user info array
		if ( empty( $user_info['email'] ) ) {
			$user_info['email'] = $this->email;
		}

		if ( empty( $user_info ) ) {
			// Get the customer, but only if it's been created
			$customer = new EDD_Customer( $this->customer_id );

			if ( $customer->id > 0 ) {
				$name      = explode( ' ', $customer->name, 2 );
				$user_info = array(
					'first_name' => $name[0],
					'last_name'  => $name[1],
					'email'      => $customer->email,
					'discount'   => 'none',
				);
			}
		} else {
			// Get the customer, but only if it's been created
			$customer = new EDD_Customer( $this->customer_id );
			if ( $customer->id > 0 ) {
				foreach ( $user_info as $key => $value ) {
					if ( ! empty( $value ) ) {
						continue;
					}

					switch ( $key ) {
						case 'first_name':
							$name = explode( ' ', $customer->name, 2 );

							$user_info[ $key ] = $name[0];
							break;

						case 'last_name':
							$name      = explode( ' ', $customer->name, 2 );
							$last_name = ! empty( $name[1] ) ? $name[1] : '';

							$user_info[ $key ] = $last_name;
							break;

						case 'email':
							$user_info[ $key ] = $customer->email;
							break;
					}
				}

			}
		}

		return $user_info;
	}

	/**
	 * Setup the Address for the payment
	 *
	 * @since  2.5
	 * @return array               The Address information for the payment
	 */
	private function setup_address() {
		$address  = ! empty( $this->payment_meta['user_info']['address'] ) ? $this->payment_meta['user_info']['address'] : array();
		$defaults = array( 'line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '' );

		$address = wp_parse_args( $address, $defaults );

		return $address;
	}

	/**
	 * Setup the payment number
	 *
	 * @since  2.5
	 * @return int|string Integer by default, or string if sequential order numbers is enabled
	 */
	private function setup_payment_number() {
		$number = $this->ID;

		if ( edd_get_option( 'enable_sequential' ) ) {
			$number = $this->order->get_number();

			if ( ! $number ) {
				$number = $this->ID;
			}
		}

		return $number;
	}

	/**
	 * Setup the cart details
	 *
	 * @since 2.5
	 * @since 3.0 Refactored as cart_details is no longer used and this is here for backwards compatibility purposes.
	 *
	 * @return array Cart details of an order.
	 */
	private function setup_cart_details() {
		$order_items = $this->order->get_items();

		$cart_details = array();

		foreach ( $order_items as $item ) {
			/** @var EDD\Orders\Order_Item $item */

			$item_fees = array();

			foreach ( $item->get_fees() as $key => $item_fee ) {
				/** @var EDD\Orders\Order_Adjustment $item_fee */

				$fee_id      = edd_get_order_adjustment_meta( $item_fee->get_id(), 'fee_id', true );
				$download_id = edd_get_order_adjustment_meta( $item_fee->get_id(), 'download_id', true );
				$price_id    = edd_get_order_adjustment_meta( $item_fee->get_id(), 'price_id', true );
				$no_tax      = edd_get_order_adjustment_meta( $item_fee->get_id(), 'price_id', true );

				$item_fees[ $fee_id ] = array(
					'amount'      => $item_fee->get_amount(),
					'label'       => $item_fee->get_description(),
					'no_tax'      => $no_tax ? $no_tax : false,
					'type'        => 'fee',
					'download_id' => $download_id,
					'price_id'    => $price_id ? $price_id : null,
				);
			}

			$cart_details[ $item->get_cart_index() ] = array(
				'name'        => $item->get_product_name(),
				'id'          => $item->get_product_id(),
				'item_number' => array(
					'id'       => $item->get_product_id(),
					'quantity' => $item->get_quantity(),
					'options'  => array(
						'quantity' => $item->get_quantity(),
						'price_id' => $item->get_price_id(),
					),
				),
				'item_price'  => $item->get_amount(),
				'quantity'    => $item->get_quantity(),
				'discount'    => $item->get_discount(),
				'subtotal'    => $item->get_subtotal(),
				'tax'         => $item->get_tax(),
				'fees'        => $item_fees,
				'price'       => $item->get_amount(),
			);
		}


		return $cart_details;
	}

	/**
	 * Setup the downloads array
	 *
	 * @since  2.5
	 * @return array               Downloads associated with this payment
	 */
	private function setup_downloads() {
		$order_items = $this->order->get_items();

		$downloads = array();

		foreach ( $order_items as $item ) {
			/** @var EDD\Orders\Order_Item $item */

			$downloads[ $item->get_cart_index() ] = array(
				'id'       => $item->get_product_id(),
				'quantity' => $item->get_quantity(),
				'options'  => array(
					'quantity' => $item->get_quantity(),
					'price_id' => $item->get_price_id(),
				),
			);
		}

		return $downloads;
	}

	/**
	 * Setup the Unlimited downloads setting
	 *
	 * @since  2.5
	 * @return bool If this payment has unlimited downloads
	 */
	private function setup_has_unlimited() {
		$unlimited = (bool) $this->order->has_unlimited_downloads();

		return $unlimited;
	}

	/**
	 * Converts this ojbect into an array for special cases
	 *
	 * @return array The payment object as an array
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}

	/**
	 * Retrieve payment cart details
	 *
	 * @since  2.5.1
	 * @return array Cart details array
	 */
	private function get_cart_details() {
		return apply_filters( 'edd_payment_cart_details', $this->cart_details, $this->ID, $this );
	}

	/**
	 * Retrieve payment completion date
	 *
	 * @since 2.5.1
	 * @since 3.0 Updated for backwards compatibility.
	 * @return string Date payment was completed
	 */
	private function get_completed_date() {
		if ( '0000-00-00 00:00:00' === $this->completed_date ) {
			$date = false;
		} else {
			$date = $this->completed_date;
		}

		return apply_filters( 'edd_payment_completed_date', $date, $this->ID, $this );
	}

	/**
	 * Retrieve payment tax
	 *
	 * @since  2.5.1
	 * @return float Payment tax
	 */
	private function get_tax() {
		return apply_filters( 'edd_get_payment_tax', $this->tax, $this->ID, $this );
	}

	/**
	 * Retrieve payment subtotal
	 *
	 * @since  2.5.1
	 * @return float Payment subtotal
	 */
	private function get_subtotal() {
		return apply_filters( 'edd_get_payment_subtotal', $this->subtotal, $this->ID, $this );
	}

	/**
	 * Retrieve payment discounts
	 *
	 * @since  2.5.1
	 * @return array Discount codes on payment
	 */
	private function get_discounts() {
		return apply_filters( 'edd_payment_discounts', $this->discounts, $this->ID, $this );
	}

	/**
	 * Return the discounted amount of the payment.
	 *
	 * @since 2.8.7
	 * @return float
	 */
	private function get_discounted_amount() {
		$total = $this->total;
		$fees  = $this->fees_total;
		$tax   = $this->tax;

		return floatval( apply_filters( 'edd_payment_discounted_amount', $total - ( $fees + $tax ), $this ) );
	}

	/**
	 * Retrieve payment currency
	 *
	 * @since  2.5.1
	 * @return string Payment currency code
	 */
	private function get_currency() {
		return apply_filters( 'edd_payment_currency_code', $this->currency, $this->ID, $this );
	}

	/**
	 * Retrieve payment gateway
	 *
	 * @since  2.5.1
	 * @return string Gateway used
	 */
	private function get_gateway() {
		return apply_filters( 'edd_payment_gateway', $this->gateway, $this->ID, $this );
	}

	/**
	 * Retrieve payment transaction ID
	 *
	 * @since  2.5.1
	 * @return string Transaction ID from merchant processor
	 */
	private function get_transaction_id() {
		return apply_filters( 'edd_get_payment_transaction_id', $this->transaction_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment IP
	 *
	 * @since  2.5.1
	 * @return string Payment IP address
	 */
	private function get_ip() {
		return apply_filters( 'edd_payment_user_ip', $this->ip, $this->ID, $this );
	}

	/**
	 * Retrieve payment customer ID
	 *
	 * @since  2.5.1
	 * @return int Payment customer ID
	 */
	private function get_customer_id() {
		return apply_filters( 'edd_payment_customer_id', $this->customer_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment user ID
	 *
	 * @since  2.5.1
	 * @return int Payment user ID
	 */
	private function get_user_id() {
		return apply_filters( 'edd_payment_user_id', $this->user_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment email
	 *
	 * @since  2.5.1
	 * @return string Payment customer email
	 */
	private function get_email() {
		return apply_filters( 'edd_payment_user_email', $this->email, $this->ID, $this );
	}

	/**
	 * Retrieve payment user info
	 *
	 * @since  2.5.1
	 * @return array Payment user info
	 */
	private function get_user_info() {
		return apply_filters( 'edd_payment_meta_user_info', $this->user_info, $this->ID, $this );
	}

	/**
	 * Retrieve payment billing address
	 *
	 * @since  2.5.1
	 * @return array Payment billing address
	 */
	private function get_address() {
		return apply_filters( 'edd_payment_address', $this->address, $this->ID, $this );
	}

	/**
	 * Retrieve payment key
	 *
	 * @since  2.5.1
	 * @return string Payment key
	 */
	private function get_key() {
		return apply_filters( 'edd_payment_key', $this->key, $this->ID, $this );
	}

	/**
	 * Retrieve payment number
	 *
	 * @since  2.5.1
	 * @return int|string Payment number
	 */
	private function get_number() {
		return apply_filters( 'edd_payment_number', $this->number, $this->ID, $this );
	}

	/**
	 * Retrieve downloads on payment
	 *
	 * @since  2.5.1
	 * @return array Payment downloads
	 */
	private function get_downloads() {
		return apply_filters( 'edd_payment_meta_downloads', $this->downloads, $this->ID, $this );
	}

	/**
	 * Retrieve unlimited file downloads status
	 *
	 * @since  2.5.1
	 * @return bool Is unlimited
	 */
	private function get_unlimited() {
		return apply_filters( 'edd_payment_unlimited_downloads', $this->unlimited, $this->ID, $this );
	}

	/**
	 * Easily determine if the payment is in a status of pending some action. Processing is specifically used for
	 * eChecks.
	 *
	 * @since 2.7
	 * @return bool
	 */
	private function in_process() {
		$in_process_statuses = array( 'pending', 'processing' );

		return in_array( $this->status, $in_process_statuses, true );
	}

	/**
	 * Determines if a customer needs to be created given the current payment details.
	 *
	 * @since 2.8.4
	 *
	 * @return EDD_Customer The customer object of the existing customer or new customer.
	 */
	private function maybe_create_customer() {
		$customer = new stdClass();

		if ( did_action( 'edd_pre_process_purchase' ) && is_user_logged_in() ) {
			$customer = new EDD_customer( get_current_user_id(), true );

			// Customer is logged in but used a different email to purchase with so assign to their customer record
			if ( ! empty( $customer->id ) && $this->email !== $customer->email ) {
				$customer->add_email( $this->email );
			}
		}

		if ( empty( $customer->id ) ) {
			$customer = new EDD_Customer( $this->email );
		}

		if ( empty( $customer->id ) ) {
			if ( empty( $this->first_name ) && empty( $this->last_name ) ) {
				$name = $this->email;
			} else {
				$name = $this->first_name . ' ' . $this->last_name;
			}

			$customer_data = array(
				'name'    => $name,
				'email'   => $this->email,
				'user_id' => $this->user_id,
			);

			$customer->create( $customer_data );
		}

		return $customer;
	}
}
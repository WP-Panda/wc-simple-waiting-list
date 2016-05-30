<?php
/**
 * Version: 1.0.0
 * Author: Bob Ong Swee San
 * Author URI: www.imakewoocommerce.site
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'woocommerce_simple_waiting_list_email' ) ){

	class woocommerce_simple_waiting_list_email extends WC_Email {
		
		public function __construct() {
			$this->id 				= 'woocommerce_simple_waiting_list_email';
			$this->title 			= __( 'Waiting List', 'woocommerce_simple_waiting_list' );
			$this->description		= __( 'Send an email to customers when items is in stock', 'woocommerce_simple_waiting_list' );

			$this->template_base 	= WOOCOMMERCE_SIMPLE_WAITING_LIST_PLUGIN_DIR . '/templates/emails/'; 
			$this->template_html 	= 'waiting-list.php';
			$this->template_plain 	= 'plain/waiting-list.php';

			$this->subject 			= __( '{item} is in stock now!', 'woocommerce_simple_waiting_list' );
			$this->heading      	= __( '{item} is in stock now!', 'woocommerce_simple_waiting_list' );
			
			// Triggers
			add_action( 'woocommerce_simple_waiting_list_email_send_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor
			parent::__construct();

		}

		public function trigger( $product_id,  $user_email) {
			$product   = wc_get_product( $product_id );

			if ( ! is_object( $product ) ) {
				return;
			}
		
			if ( $product ) {
				$this->object 		= $product;
				$this->recipient	= $user_email;

				$this->find[] = '{item}';
				$this->replace[] = $product->get_title();
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient()  ) {
				return;
			}

			$this->send( $user_email , $this->get_subject(), $this->format_string( $this->get_content() ), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get content html.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html( $this->template_html, array(
				'product_name' 		=> $this->object->get_title(),
				'product_url' 		=> get_permalink( $this->object->id ),
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'			=> $this
			) , false, $this->template_base );
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html( $this->template_plain, array(
				'product_name' 		=> $this->object->get_title(),
				'product_url' 		=> get_permalink( $this->object->id ),
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'			=> $this
			) , false, $this->template_base );
		}


		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'         => __( 'Enable/Disable', 'woocommerce_simple_waiting_list' ),
					'type'          => 'checkbox',
					'label'         => __( 'Enable this email notification', 'woocommerce_simple_waiting_list' ),
					'default'       => 'yes'
				),
				'subject' => array(
					'title'         => __( 'Subject', 'woocommerce_simple_waiting_list' ),
					'type'          => 'text',
					'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce_simple_waiting_list' ), $this->subject ),
					'placeholder'   => '',
					'default'       => '',
					'desc_tip'      => true
				),
				'heading' => array(
					'title'         => __( 'Email Heading', 'woocommerce_simple_waiting_list' ),
					'type'          => 'text',
					'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce_simple_waiting_list' ), $this->heading ),
					'placeholder'   => '',
					'default'       => '',
					'desc_tip'      => true
				),
				'email_type' => array(
					'title'         => __( 'Email type', 'woocommerce_simple_waiting_list' ),
					'type'          => 'select',
					'description'   => __( 'Choose which format of email to send.', 'woocommerce_simple_waiting_list' ),
					'default'       => 'html',
					'class'         => 'email_type wc-enhanced-select',
					'options'       => $this->get_email_type_options(),
					'desc_tip'      => true
				)
			);
		}

	}

}

return new woocommerce_simple_waiting_list_email();
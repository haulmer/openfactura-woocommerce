<?php
/**
 * Plugin Name: Woocommerce OpenFactura
 * Plugin URI: https://www.haulmer.com/
 * Description: Creación de boletas y facturas electrónicas
 * Version: 1.0.26-alpha.4
 * Author: Haulmer
 * Author URI: https://www.haulmer.com/
 * Developer: Haulmer
 * Developer URI: https://www.haulmer.com/
 * Text Domain: woocommerce-openfactura
 * Domain Path: /languages
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 3.8
 * WC tested up to: 3.8
 *
 * Copyright: 2020 Haulmer
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include('OpenFactura.php');
/**
 * Create table openfactura_registry
 */
register_activation_hook( __FILE__, 'openfactura_registry' );

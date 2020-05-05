<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}


global $wpdb;
$openfactura_registry = $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "openfactura_registry");
delete_option("my_plugin_db_version");


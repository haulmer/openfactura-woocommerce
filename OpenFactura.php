<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function writeLogs($txt){
    if (is_array($txt) || is_object($txt)) {
        error_log(print_r($txt, true));
    } else {
        error_log($txt);
    }
}

/**
 * Registration of the plugin
 * 
 * Creation of the table inside the woocommerce database.
 */
function openfactura_registry(){
    #   ╔══════════════════════════════════════════════════╗
    #   ║ Create the table inside the WooCommerce Database ║
    #   ╚══════════════════════════════════════════════════╝
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'openfactura_registry';
    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
        apikey varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        is_active tinyint(1) DEFAULT NULL,
        is_demo tinyint(1) DEFAULT NULL,
        generate_boleta tinyint(1) DEFAULT NULL,
        allow_factura tinyint(1) DEFAULT NULL,
        is_description tinyint(1) DEFAULT NULL,
        is_email_link_selfservice tinyint(1) DEFAULT NULL,
        show_logo tinyint(1) DEFAULT NULL,
        link_logo varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        rut varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        razon_social varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        glosa_descriptiva varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        sucursales text COLLATE utf8_unicode_ci DEFAULT NULL,
        sucursal_active varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        actividad_economica_active varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        actividades_economicas text COLLATE utf8_unicode_ci DEFAULT NULL,
        codigo_actividad_economica_active int(11) DEFAULT NULL,
        direccion_origen varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        comuna_origen varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        json_info_contribuyente text COLLATE utf8_unicode_ci DEFAULT NULL,
        url_doc_base varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        name_doc_base varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        url_send varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
        cdgSIISucur varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    insert_demo_data();
}

/**
 * Insert demo data
 * 
 * Insert the demo's information inside the previously created table.
 * @see openfactura_registry
 */
function insert_demo_data(){
    global $wpdb;
    $demo_apikey = '928e15a2d14d4a6292345f04960f4bd3';
    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey = " . "'$demo_apikey'");
    
    #   ╔═══════════════════════════════════╗
    #   ║ Check if exist an active registry ║
    #   ║         and if it's empty         ║
    #   ╚═══════════════════════════════════╝
    if (isset($openfactura_registry) && empty($openfactura_registry)) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev-api.haulmer.com/v2/dte/organization",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "apikey:" . $demo_apikey
            ),
        ));

        #   ╔═════════════════════════════════════════════╗
        #   ║ get the info of the company with the apikey ║
        #   ╚═════════════════════════════════════════════╝
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);

        #   ╔═════════════════════════════════════╗
        #   ║ Get the activities of the demo data ║
        #   ╚═════════════════════════════════════╝
        $actividades_array = array();
        foreach ($response['actividades'] as $actividad) {
            array_push($actividades_array, $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica']);
        }
        $actividades_array = json_encode($actividades_array);
        $actividad_economica = $response['actividades'][0]['actividadEconomica'] . "|" . $response['actividades'][0]['codigoActividadEconomica'];
        $codigo_actividad_economica_active = $response['actividades'][0]['codigoActividadEconomica'];

        #   ╔══════════════════════════════════════╗
        #   ║ Insert the demo data to the database ║
        #   ╚══════════════════════════════════════╝
        $wpdb->insert($wpdb->prefix . 'openfactura_registry', array(
            'apikey' => $demo_apikey,
            'is_active' => 1,
            'is_demo' => 1,
            'generate_boleta' => 1,
            'allow_factura' => 1,
            'is_description' => 1,
            'is_email_link_selfservice' => 1,
            'show_logo' => 0,
            'rut' => $response['rut'],
            'razon_social' => $response['razonSocial'],
            'glosa_descriptiva' => $response['glosaDescriptiva'],
            'sucursal_active' => $response['direccion'],
            'actividad_economica_active' => $actividad_economica,
            'codigo_actividad_economica_active' => $codigo_actividad_economica_active,
            'actividades_economicas' => $actividades_array,
            'direccion_origen' => $response['direccion'],
            'comuna_origen' => $response['comuna'],
            'cdgSIISucur' => $response['cdgSIISucur'],
            'json_info_contribuyente' => json_encode($response)
        ));
    }
}

/**
 * Register the ajax action for authenticated users in function save-data-openfactura-ajax
 */
add_action('wp_ajax_save-data-openfactura-ajax', 'save_data_openfactura_registry');

/**
 * Register the ajax action for unauthenticated users in fuction save-data-openfactura-ajax
 */
add_action('wp_ajax_nopriv_save-data-openfactura-ajax', 'save_data_openfactura_registry');

/**
 * Save new configuration
 * 
 * Save the new information about the plugin like the ApiKey and the permissions for the emission of bills and invoices.
 */
function save_data_openfactura_registry(){
    //writeLogs("entra aca para actualizar 2");
    #   ╔═════════════════════════════╗
    #   ║   Check the checkbox info   ║
    #   ╚═════════════════════════════╝
    if (!isset($_REQUEST['demo']) || empty($_REQUEST['demo'])) { return wp_send_json_error([400]); }
    if (!isset($_REQUEST['automatic39']) || empty($_REQUEST['automatic39'])) { return wp_send_json_error([400]); }
    if (!isset($_REQUEST['allow33']) || empty($_REQUEST['allow33'])) { return wp_send_json_error([400]); }
    if (!isset($_REQUEST['enableLogo']) || empty($_REQUEST['enableLogo'])) { return wp_send_json_error([400]); }

    #   ╔══════════════════════════╗
    #   ║ Initialize the variables ║
    #   ╚══════════════════════════╝
    $demo = false;
    $automatic39 = false;
    $allow33 = false;
    $enableLogo = false;
    $link_logo = '';
    $description = false;
    $emailLinkSelfservice = false;

    #   ╔═══════════════════════════════╗
    #   ║ Check the config in the front ║
    #   ║  and save into previous vars  ║
    #   ╚═══════════════════════════════╝
    if ($_REQUEST['demo'] == "true") { $demo = true; }
    if ($_REQUEST['automatic39'] == "true") { $automatic39 = true; }
    if ($_REQUEST['allow33'] == "true") { $allow33 = true; }
    if ($_REQUEST['enableLogo'] == "true") { $enableLogo = true; }
    if ($_REQUEST['description'] == "true") { $description = true; }
    if ($_REQUEST['emailLinkSelfservice'] == "true") { $emailLinkSelfservice = true; }
    if ($enableLogo && $_POST['urlLogo'] != '') { $link_logo = str_replace(' ', '', $_POST['urlLogo']); }

    #   ╔══════════════════════════════╗
    #   ║   Get the new apikey of all  ║
    #   ║  registries and get from the ║
    #   ║ database the active register ║
    #   ╚══════════════════════════════╝

    global $wpdb;
    $demoApiKey = '928e15a2d14d4a6292345f04960f4bd3';
    $demoApiKey2 = '41eb78998d444dbaa4922c410ef14057';
    $newApiKey = str_replace(' ', '', $_REQUEST['apikey']);
    $registry_newapikey = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'openfactura_registry WHERE apikey = "' . $newApiKey . '";')[0];
    $exist_demo = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'openfactura_registry WHERE apikey = "' . $demoApiKey . '";');
    $openfactura_registry_active = $wpdb->get_results('SELECT * FROM  ' . $wpdb->prefix . 'openfactura_registry WHERE is_active = 1;')[0];

    #   ╔═══════════════════════════════════╗
    #   ║ Check if exist an active registry ║
    #   ║       and if it's not empty       ║
    #   ╚═══════════════════════════════════╝
    if (isset($openfactura_registry_active) && !empty($openfactura_registry_active)) {
        $url_emisor = '';
        if ($newApiKey == $demoApiKey || $newApiKey == $demoApiKey2) {
            $url_emisor = 'https://dev-api.haulmer.com/v2/dte/organization';
        } else {
            $url_emisor = 'https://api.haulmer.com/v2/dte/organization';
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_emisor,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "apikey:" . $newApiKey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        #   ╔═════════════════════════════════╗
        #   ║     'response' get the info     ║
        #   ╚═════════════════════════════════╝
        $response = json_decode($response, true);

        #   ╔══════════════════════════════════╗
        #   ║ Parse the offices and activities ║
        #   ║          UPDATE VERSION          ║
        #   ╚══════════════════════════════════╝
        if($registry_newapikey){
            if($openfactura_registry_active->apikey == $registry_newapikey->apikey){
                $sucursal_active = $_POST['sucursal'];
                $data_sucur = explode("|",$sucursal_active);
                $direccion = $data_sucur[0];
                $codigo_direccion = $data_sucur[1];
    
                $actividad_economica_active = $_POST['actividad'];
                $data_act = explode("|",$actividad_economica_active);
                $actividad = $data_act[0];
                $codigo_actividad = $data_act[1];
                //writeLogs("demo");
                //writeLogs($demo);
                $wpdb->update($wpdb->prefix . 'openfactura_registry',
                    array(
                        'is_demo' => $demo,
                        'generate_boleta' => $automatic39,
                        'allow_factura' => $allow33,
                        'is_description' => $description,
                        'is_email_link_selfservice' => $emailLinkSelfservice,
                        'show_logo' => $enableLogo,
                        'link_logo' => $link_logo,
                        'sucursal_active' => $sucursal_active,
                        'actividad_economica_active' => $actividad_economica_active,
                        'codigo_actividad_economica_active' => $codigo_actividad,
                        'direccion_origen' => $direccion,
                        'json_info_contribuyente' => json_encode($response),
                        'cdgSIISucur' => $codigo_direccion),
                    array('apikey' => $newApiKey));
            }
            else{
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array('is_active' => 0), array('is_active' => 1));
                $wpdb->update($wpdb->prefix . 'openfactura_registry',
                    array(
                        'is_active' => 1,
                        'is_demo' => $demo,
                        'generate_boleta' => $automatic39,
                        'allow_factura' => $allow33,
                        'is_description' => $description,
                        'is_email_link_selfservice' => $emailLinkSelfservice,
                        'show_logo' => $enableLogo,
                        'link_logo' => $link_logo,
                        'json_info_contribuyente' => json_encode($response)),
                    array('apikey' => $newApiKey));
            }
            return wp_send_json_success(['refresh']);
        }
        else{

            #   ╔══════════════════════════════════╗
            #   ║ Parse the offices and activities ║
            #   ║          INSERT VERSION          ║
            #   ╚══════════════════════════════════╝
            $actividades_economicas = array();
            foreach ($response['actividades'] as $actividad) {
                array_push($actividades_economicas, $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica']);
            }
            if (!empty($actividades_economicas)) {
                $actividades_economicas = json_encode($actividades_economicas);
                $actividad_economica_active = $response['actividades'][0]['actividadEconomica'] . "|" . $response['actividades'][0]['codigoActividadEconomica'];
            }

            $sucursales = array();
            foreach ($response['sucursales'] as $sucursal) {
                array_push($sucursales, $sucursal['direccion'] . "|" . $sucursal['cdgSIISucur']);
            }

            $sucursal_active = $response['direccion'] . "|" . $response['cdgSIISucur'];
            array_push($sucursales, $sucursal_active);
            
            $sucursales = json_encode($sucursales);
            $codigo_actividad_economica_active = $response['actividades'][0]['codigoActividadEconomica'];

            #   ╔════════════════════════════════════╗
            #   ║ Deactivate the active registry and ║
            #   ║   insert the new active registry   ║
            #   ╚════════════════════════════════════╝
            $wpdb->update($wpdb->prefix . 'openfactura_registry', array('is_active' => 0), array('is_active' => 1));
            $wpdb->insert($wpdb->prefix . 'openfactura_registry', array(
                'is_active' => 1, 
                'apikey' => $newApiKey, 
                'rut' => $response['rut'], 
                'is_demo' => $demo, 
                'generate_boleta' => $automatic39, 
                'allow_factura' => $allow33, 
                'is_description' => $description, 
                'is_email_link_selfservice' => $emailLinkSelfservice, 
                'show_logo' => $enableLogo, 
                'link_logo' => $link_logo, 
                'razon_social' => $response['razonSocial'], 
                'glosa_descriptiva' => $response['glosaDescriptiva'], 
                'sucursales' => $sucursales,
                'sucursal_active' => $sucursal_active,
                'actividad_economica_active' => $actividad_economica_active,
                'codigo_actividad_economica_active' => $codigo_actividad_economica_active,
                'actividades_economicas' => $actividades_economicas,
                'direccion_origen' => $response['direccion'], 
                'comuna_origen' => $response['comuna'], 
                'json_info_contribuyente' => json_encode($response), 
                'url_doc_base' => 'url orden de compra', 
                'name_doc_base' => 'orden de compra', 
                'url_send' => $url_emisor, 
                'cdgSIISucur' => $response['cdgSIISucur'] 
            ));
            return wp_send_json_success(['insert']);
        }
    }

    #   ╔══════════════════════════════════╗
    #   ║   If doesn't exist some active   ║
    #   ║ registry check if exist the demo ║
    #   ║    and set this like active.     ║
    #   ║    The last case if doesnt't     ║
    #   ║   exist the demo registry set    ║
    #   ║       again the demo data.       ║
    #   ╚══════════════════════════════════╝
    else {
        if ($exist_demo) {
            $wpdb->update($wpdb->prefix . 'openfactura_registry',
                        array(
                                'is_active' => 1,
                                'is_demo' => 1, 
                                'generate_boleta' => 1, 
                                'allow_factura' => 1,
                                'is_description' => 1,
                                'is_email_link_selfservice' => 1,
                                'show_logo' => 0,
                            ),
                            array(
                                'apikey' => $demoApiKey
                            )
                        );
            return wp_send_json_success(['refresh']);
        }
        else {
            $wpdb->update($wpdb->prefix . 'openfactura_registry', array('is_active' => 0), array(1));
            insert_demo_data();
            return wp_send_json_success(['refresh']);
        }
    }
    return wp_send_json_success(['error']);
}

/**
 * register the ajax action for authenticated users 
 * */
add_action('wp_ajax_update-data-openfactura-ajax', 'update_data_openfactura_registry');

/**
 * register the ajax action for unauthenticated users
 */
add_action('wp_ajax_nopriv_update-data-openfactura-ajax', 'update_data_openfactura_registry');

/**
 * Update data table openfactura_registry
 */
function update_data_openfactura_registry(){
    global $wpdb;
    $newApiKey = str_replace(' ', '', $_REQUEST['apikey']);
    $demoApiKey = '928e15a2d14d4a6292345f04960f4bd3';
    $demoApiKey2 = '41eb78998d444dbaa4922c410ef14057';
    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey=" . "'$newApiKey'");

    #   ╔═════════════════════════╗
    #   ║  Check if the registry  ║
    #   ║  exist in the database  ║
    #   ╚═════════════════════════╝
    if (isset($openfactura_registry) && !empty($openfactura_registry)) {

        #   ╔═════════════════════════════╗
        #   ║ Ask if the registry is demo ║
        #   ╚═════════════════════════════╝
        if ($newApiKey == $demoApiKey || $newApiKey == $demoApiKey2) {
            $url_organization = 'https://dev-api.haulmer.com/v2/dte/organization';
        } else {
            $url_organization = 'https://api.haulmer.com/v2/dte/organization';
        }

        #   ╔═════════════════════════════╗
        #   ║ Get the info of the company ║
        #   ╚═════════════════════════════╝
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_organization,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "apikey:" . $newApiKey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);
        //writeLogs($response);

        #   ╔═════════════════════════╗
        #   ║ Get the activities info ║
        #   ╚═════════════════════════╝
        $actividades_economicas = array();
        $flag_actvidad = false;
        if (is_array($response['actividades']) || is_object($response['actividades'])) {
            foreach ($response['actividades'] as $actividad) {
                array_push($actividades_economicas, $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica']);
                if ($openfactura_registry[0]->actividad_economica_active == ($actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica'])) {
                    $flag_actvidad = true;
                    $actividad_economica_active = $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica'];
                    $codigo_actividad_active = $actividad['codigoActividadEconomica'];
                }
            }
        }
        if ($flag_actvidad == false) {
            $actividad_economica_active = $response['actividades'][0]['actividadEconomica'] . "|" . $response['actividades'][0]['codigoActividadEconomica'];
            $codigo_actividad_active = $response['actividades'][0]['codigoActividadEconomica'];
        }
        $actividades_economicas = json_encode($actividades_economicas);

        #   ╔══════════════════════╗
        #   ║ Get the offices info ║
        #   ╚══════════════════════╝
        $sucursales = array();
        $flag_sucursales = false;
        if (is_array($response['sucursales']) || is_object($response['sucursales'])) {
            foreach ($response['sucursales'] as $sucursal) {
                array_push($sucursales, $sucursal['direccion'] . "|" . $sucursal['cdgSIISucur']);
                if ($openfactura_registry[0]->sucursal_active == ($sucursal['direccion'] . "|" . $sucursal['cdgSIISucur'])) {
                    $flag_sucursales = true;
                    $sucursal_active = $sucursal['direccion'] . "|" . $sucursal['cdgSIISucur'];
                    $codigo_sucursal_active = $sucursal['cdgSIISucur'];
                }
            }
        }
        if ($flag_sucursales == false) {
            if (!empty($response['sucursales'])) {
                $sucursal_active = $response['sucursales'][0]['direccion'] . "|" . $response['sucursales'][0]['cdgSIISucur'];
                $codigo_sucursal_active = $response['sucursales'][0]['cdgSIISucur'];
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'cdgSIISucur' => $codigo_sucursal_active
                ), array('id' => $openfactura_registry[0]->id));
            } else {
                $sucursal_active = $response['direccion'] . "|" . $response['cdgSIISucur'];
                $codigo_sucursal_active = $response['cdgSIISucur'];
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'cdgSIISucur' => $codigo_sucursal_active
                ), array('id' => $openfactura_registry[0]->id));
            }
        }
        $sucursales = json_encode($sucursales);

        #   ╔═════════════════════════════════╗
        #   ║ Update the info in the database ║
        #   ╚═════════════════════════════════╝
        $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
            'apikey' => $newApiKey,
            'rut' => $response['rut'],
            'razon_social' => $response['razonSocial'],
            'glosa_descriptiva' => $response['glosaDescriptiva'],
            'sucursales' => $sucursales,
            'sucursal_active' => $sucursal_active,
            'actividades_economicas' => $actividades_economicas,
            'actividad_economica_active' => $actividad_economica_active,
            'codigo_actividad_economica_active' => $codigo_actividad_active,
            'direccion_origen' => $response['direccion'],
            'comuna_origen' => $response['comuna'],
            'json_info_contribuyente' => json_encode($response),
            'url_doc_base' => 'url orden de compra',
            'name_doc_base' => 'orden de compra',
            'url_send' => $url_organization,
            'cdgSIISucur' => $codigo_sucursal_active, 
        ), array('id' => $openfactura_registry[0]->id));
        wp_send_json_success(['refresh']);
    }
    else{
        
        #   ╔══════════════════════════╗
        #   ║ Initialize the variables ║
        #   ╚══════════════════════════╝
        $demo = false;
        $automatic39 = false;
        $allow33 = false;
        $enableLogo = false;
        $link_logo = '';
        $description = false;
        $emailLinkSelfservice = false;

        #   ╔═══════════════════════════════╗
        #   ║ Check the config in the front ║
        #   ║  and save into previous vars  ║
        #   ╚═══════════════════════════════╝
        if ($_REQUEST['demo'] == "true") { $demo = true; }
        if ($_REQUEST['automatic39'] == "true") { $automatic39 = true; }
        if ($_REQUEST['allow33'] == "true") { $allow33 = true; }
        if ($_REQUEST['enableLogo'] == "true") { $enableLogo = true; }
        if ($_REQUEST['description'] == "true") { $description = true; }
        if ($_REQUEST['emailLinkSelfservice'] == "true") { $emailLinkSelfservice = true; }
        if ($enableLogo && $_POST['urlLogo'] != '') {
            $link_logo = str_replace(' ', '', $_POST['urlLogo']);
        }

        if ($newApiKey == $demoApiKey || $newApiKey == $demoApiKey2) {
            $url_organization = 'https://dev-api.haulmer.com/v2/dte/organization';
        }
        else {
            $url_organization = 'https://api.haulmer.com/v2/dte/organization';
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_organization,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "apikey:" . $newApiKey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);
        
        #   ╔══════════════════════════════════╗
        #   ║ Parse the offices and activities ║
        #   ╚══════════════════════════════════╝

        $actividades_economicas = array();
        foreach ($response['actividades'] as $actividad) {
            array_push($actividades_economicas, $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica']);
        }
        if (!empty($actividades_economicas)) {
            $actividades_economicas = json_encode($actividades_economicas);
            $actividad_economica_active = $response['actividades'][0]['actividadEconomica'] . "|" . $response['actividades'][0]['codigoActividadEconomica'];
        }

        $sucursales = array();
        foreach ($response['sucursales'] as $sucursal) {
            array_push($sucursales, $sucursal['direccion'] . "|" . $sucursal['cdgSIISucur']);
        }

        $sucursal_active = $response['direccion'] . "|" . $response['cdgSIISucur'];
        array_push($sucursales, $sucursal_active);
        
        $sucursales = json_encode($sucursales);
        $codigo_actividad_economica_active = $response['actividades'][0]['codigoActividadEconomica'];


        #   ╔═════════════════════════════╗
        #   ║ Insert the new registry and ║
        #   ║      update the active      ║
        #   ╚═════════════════════════════╝
        try {
            $wpdb->update($wpdb->prefix . 'openfactura_registry', array('is_active' => 0), array('is_active' => 1));
        } catch (\Throwable $th) {
        }
        $wpdb->insert($wpdb->prefix . 'openfactura_registry', array(
            'is_active' => 1, 
            'apikey' => $newApiKey, 
            'rut' => $response['rut'], 
            'is_demo' => true, 
            'generate_boleta' => true, 
            'allow_factura' => true, 
            'is_description' => true, 
            'is_email_link_selfservice' => true, 
            'show_logo' => false, 
            'link_logo' => '', 
            'razon_social' => $response['razonSocial'], 
            'glosa_descriptiva' => $response['glosaDescriptiva'], 
            'sucursales' => $sucursales, 
            'sucursal_active' => $sucursal_active, 
            'actividad_economica_active' => $actividad_economica_active, 
            'codigo_actividad_economica_active' => $codigo_actividad_economica_active, 
            'actividades_economicas' => $actividades_economicas, 
            'direccion_origen' => $response['direccion'], 
            'comuna_origen' => $response['comuna'], 
            'json_info_contribuyente' => json_encode($response), 
            'url_doc_base' => 'url orden de compra', 
            'name_doc_base' => 'orden de compra', 
            'url_send' => 'https://dev-api.haulmer.com/v2/dte/organization', 
            'cdgSIISucur' => $response['cdgSIISucur'] 
        ));
        wp_send_json_success(['insert']);
    }
}

/**
 * Enqueue javascript in WordPress
 */
function enqueue_scripts()
{
    wp_enqueue_script('main', plugins_url('/js/main.js', __FILE__));
    wp_localize_script('main', 'main_vars', ['ajaxurl' => admin_url('admin-ajax.php')]);
    wp_enqueue_script('tinyModal', plugins_url('/js/tinyModal.min.js', __FILE__));
    wp_localize_script('tinyModal', 'myScript', array(
        'pluginsUrl' => plugins_url(),
    ));
}


/**
 * Enqueue styles in WordPress
 */
function enqueue_styles()
{
    wp_enqueue_style('main6', plugins_url('/css/forms.css', __FILE__));
    wp_enqueue_style('main7', plugins_url('/css/links.css', __FILE__));
    wp_enqueue_style('main8', plugins_url('/css/main.css', __FILE__));
    wp_enqueue_style('main9', plugins_url('/css/modal.css', __FILE__));
}

/**
 * Option menu Openfactura hook
 */
add_action('admin_menu', 'admin_menu_option_openfactura');

/**
 * Add option menu page Openfactura
 */
function admin_menu_option_openfactura()
{
    add_menu_page('Header & Footer Scripts', 'OpenFactura', 'manage_options', 'openfactura', 'sub_menu_option_openfactura', '', 200);
}

/**
 * Main view menu Openfactura
 */
function sub_menu_option_openfactura()
{
    enqueue_styles();
    enqueue_scripts();
    global $wpdb;
    $apikey = '928e15a2d14d4a6292345f04960f4bd3';
    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where is_active=1");
?>
    <div class="of-whmcs">
        <div class="wrapper">
            <div class="wrapper_content">
                <h1>Configuración</h1>
                <p>
                    La Integración de OpenFactura enviará un correo al cliente para que pueda generar su propio documento electrónico,
                    ya sea boleta o factura, a través del Autoservicio de Emisión, ingresando únicamente los datos de receptor.
                    Este correo se envía al momento de darse por pagado el 'Invoice' (InvoicePaid = True).
                    Si tienes alguna duda acerca de los campos del Invoice que se utilizan para generar el documento,
                    puedes revisar nuestra Documentación de Integración con WooCommerce.
                </p>
            </div>
            <form action="" onsubmit="return sendForm(event)" id="form1" method="post">
                <section>
                    <h2>Opciones generales</h2>


                    <div class="s-row">
                        <div class="col-4">
                            <div>
                                <div class="form-field">
                                    <div class="form-field__control">
                                        <label for="apikey" class="form-field__label">API Key</label>
                                        <input id="apikey" name="apikey" type="text t" class="form-field__input" value="<?php if (!empty($openfactura_registry)) {
                                                                                                                            echo $openfactura_registry[0]->apikey;
                                                                                                                        } ?> ">
                                    </div>
                                    <div class="form-field__hint">
                                        Ingresa tu API Key para para utilizar tus datos almacenados en OpenFactura.
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-4">
                            <div class="form-apikey" id='get-apikey'>
                                <a class="get-apikey" href="https://www.openfactura.cl/">¿Dónde obtengo mi API Key?</a>
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check0" name="demo" value=<?php if (!empty($openfactura_registry)) {
                                                                                        echo $openfactura_registry[0]->is_demo;
                                                                                        if ($openfactura_registry[0]->is_demo == 1) { ?> checked <?php
                                                                                                                                                }
                                                                                                                                            } ?> />
                            <label for="check0" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check0" class="checkLabel">Usar demostración</label>
                            <div>
                                Al seleccionar esta opción, se habilitarán los datos de demostración almacenados en OpenFactura.
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check1" name="automatic39" value=<?php if (!empty($openfactura_registry)) {
                                                                                            echo $openfactura_registry[0]->generate_boleta;
                                                                                            if ($openfactura_registry[0]->generate_boleta == 1) { ?> checked <?php
                                                                                                                                                            }
                                                                                                                                                        } ?> />
                            <label for="check1" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check1" class="checkLabel">Habilitar emisión y envío automático de boletas</label>
                            <div>
                                Al seleccionar esta opción, una boleta electrónica se emitirá por
                                defecto y será adjuntada al correo que se enviará al cliente.
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check2" name="allow33" value=<?php if (!empty($openfactura_registry)) {
                                                                                        echo $openfactura_registry[0]->allow_factura;
                                                                                        if ($openfactura_registry[0]->allow_factura == 1) { ?> checked <?php
                                                                                                                                                    }
                                                                                                                                                } ?> />
                            <label for="check2" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check2" class="checkLabel">Permitir al cliente ingresar datos de receptor para generar su
                                factura</label>
                            <div>
                                Se le permitirá al cliente la posibilidad de emitir su propia factura electrónica o bien convertir una boleta
                                a factura electrónica, según sea el caso, ingresando sus datos de facturación. Se generará una Nota de
                                Crédito.
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check5" name="product-description" value=<?php if (!empty($openfactura_registry)) {
                                                                                                    echo $openfactura_registry[0]->is_description;
                                                                                                    if ($openfactura_registry[0]->is_description == 1) { ?> checked <?php
                                                                                                                                                                }
                                                                                                                                                            } ?> />
                            <label for="check5" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check5" class="checkLabel">Usar Descripción</label>
                            <div>
                                Al seleccionar esta opción, se mostrará la descripción de cada producto en la boleta o factura.
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check6" name="email-link-selfservice" value=<?php if (!empty($openfactura_registry)) {
                                                                                                        echo $openfactura_registry[0]->is_email_link_selfservice;
                                                                                                        if ($openfactura_registry[0]->is_email_link_selfservice == 1) { ?> checked <?php
                                                                                                                                                                                }
                                                                                                                                                                            } ?> />
                            <label for="check6" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check6" class="checkLabel">Insertar en el correo de “Pedido Completado” el enlace de los documentos</label>
                            <div>
                                Al seleccionar esta opción, se incorporará en el correo que tiene por asunto “Pedido completado” el link desde donde el cliente podrá visualizar o emitir la boleta o factura. Aun cuando se desactive esta opción Haulmer enviará un correo con el link respectivo.
                            </div>
                        </div>
                    </div>

                    <div class="checkBoxContainer">
                        <div class="container">
                            <input type="checkbox" id="check3" name="enableLogo" value=<?php if (!empty($openfactura_registry)) {
                                                                                            echo $openfactura_registry[0]->show_logo;
                                                                                            if ($openfactura_registry[0]->show_logo == 1) { ?> checked <?php
                                                                                                                                                    }
                                                                                                                                                } ?> />
                            <label for="check3" class="md-checkbox"></label>
                        </div>

                        <div>
                            <label for="check3" class="checkLabel">Habilitar logotipo personalizado</label>
                            <div>
                                El enlace de autoservicio que se enviará al cliente podrá ir con
                                un logotipo personalizado de la empresa.
                                <a href="#" class="linkBlue _openDialog-preview">Ver ejemplo.</a>
                            </div>

                            <div class="form-field">
                                <div class="form-field__control">
                                    <label for="logo-url" class="form-field__label">URL logo empresa</label>
                                    <input id="logo-url" name="logo-url" type="text t" class="form-field__input" value="<?php if (!empty($openfactura_registry)) {
                                                                                                                            echo $openfactura_registry[0]->link_logo;
                                                                                                                        } ?> ">
                                </div>
                                <div class="form-field__hint">
                                    No se mostrará el logotipo si la URL no es https. Proporciones 16:9, Dimensiones ideales de 128 X 72px.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="progressBar">
                        <div class="indeterminate"></div>
                    </div>
                    <div class="flex-menu">
                        <h2>Información del emisor</h2>
                        <button id="update-button" class="button-flat">Actualizar</button>
                    </div>
                    <p>
                        Los siguientes campos se obtienen desde el SII, a través de
                        OpenFactura, y no pueden ser modificados desde acá. Si cuentas con
                        sucursales, puedes seleccionar la que desees ocupar. Si has realizado
                        cambios en el SII, recuerda hacer clic en 'Actualizar' para que se
                        vean reflejados.
                    </p>

                    <div class="s-row">
                        <div class="col-2">
                            <div class="form-field">
                                <div class="form-field__control">
                                    <label for="rut" class="form-field__label">RUT</label>
                                    <input id="rut" type="tex t" class="form-field__input" value="<?php if (!empty($openfactura_registry)) {
                                                                                                        echo $openfactura_registry[0]->rut;
                                                                                                    } ?>" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-field">
                                <div class="form-field__control">
                                    <label for="company-name" class="form-field__label">Razón Social</label>
                                    <input id="company-name" type="text t" class="form-field__input" value="<?php
                                                                                                            if (!empty($openfactura_registry)) {
                                                                                                                echo $openfactura_registry[0]->razon_social;
                                                                                                            } ?>" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-field">
                                <div class="form-field__control">
                                    <label for="description" class="form-field__label">Glosa descriptiva (Ex Giro)</label>
                                    <input id="description" type="text t" class="form-field__input" value="<?php
                                                                                                            if (!empty($openfactura_registry)) {
                                                                                                                echo $openfactura_registry[0]->glosa_descriptiva;
                                                                                                            } ?>" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="s-row">
                        <div class="col-2">
                            <div class="form-field">
                                <div class="form-field__select">
                                    <div class="form__group">
                                        <label for="branch" class="form-field__label">Sucursal</label>
                                        <div class="form__dropdown">
                                            <select name="sucursal" id="sucursal">
                                                <?php
                                                if (!empty($openfactura_registry)) {
                                                    $sucursales = json_decode($openfactura_registry[0]->sucursales, true);
                                                    $sucursal_active = $openfactura_registry[0]->sucursal_active;
                                                    $sucursal_active_and_code = explode("|", $sucursal_active);
                                                    echo '<option value="' . $sucursal_active . '">' . $sucursal_active_and_code[0] . '</option>';
                                                    if (!empty($sucursales)) {
                                                        foreach ($sucursales as $sucursal) {
                                                            $sucursal_and_code = explode("|", $sucursal);
                                                            if ($sucursal_active_and_code[0] != $sucursal_and_code[0] && !empty($sucursal_and_code[0])) {
                                                                echo '<option value="' . $sucursal . '">' . $sucursal_and_code[0] . '</option>';
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-field">
                                <div class="form-field__select">
                                    <div class="form__group">
                                        <label for="branch" class="form-field__label">Actividad económica</label>
                                        <div class="form__dropdown">
                                            <select name="actividad" id="actividad">
                                                <?php
                                                if (!empty($openfactura_registry)) {
                                                    $actividades = json_decode($openfactura_registry[0]->actividades_economicas, true);
                                                    $actividad_active = $openfactura_registry[0]->actividad_economica_active;
                                                    $actividad_active_and_code = explode("|", $actividad_active);
                                                    echo '<option value="' . $actividad_active . '">' . $actividad_active_and_code[0] . '</option>';
                                                    if (!empty($actividades)) {
                                                        foreach ($actividades as $actividad) {
                                                            $actividad_and_code = explode("|", $actividad);
                                                            if ($actividad_active_and_code[0] != $actividad_and_code[0] && !empty($actividad_and_code[0])) {
                                                                echo '<option value="' . $actividad . '">' . $actividad_and_code[0] . '</option>';
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </section>

                <div class="wrapper_content">
                    <button type="submit" class="button-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php

}

function get_private_order_notes($order_id)
{
    global $wpdb;
    $order_note = null;
    $table_perfixed = $wpdb->prefix . 'comments';
    $results = $wpdb->get_results("
        SELECT *
        FROM $table_perfixed
        WHERE  `comment_post_ID` = $order_id
        AND  `comment_type` LIKE  'order_note'
    ");
    foreach ($results as $note) {
        $order_note[] = array(
            'note_id' => $note->comment_ID,
            'note_date' => $note->comment_date,
            'note_author' => $note->comment_author,
            'note_content' => $note->comment_content,
        );
    }
    return $order_note;
}

/**
 * Hook that is executed when paying an order creating a document to be issued
 */
add_action('woocommerce_order_status_completed', 'so_payment_complete', 10, 1);
function so_payment_complete($order_id)
{
    $order_notes = get_private_order_notes($order_id);
    //verificar que enlace de autoservicio no este creado previamente
    if (isset($order_notes) && !empty($order_notes)) {
        foreach ($order_notes as $note) {
            $note_content = $note['note_content'];
            if (stristr($note_content, 'Obten tu documento tributario')) {
                return;
            }
        }
    }
    $order = wc_get_order($order_id);
    //debug_log($order);
    global $wpdb;
    $openfactura_registry = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "openfactura_registry where is_active=1");
    create_json_openfactura($order, $openfactura_registry[0]);
    return $order;
}

/**
 * Create a new document to issue
 */
function create_json_openfactura($order, $openfactura_registry){

    //writeLogs("Registry");
    //writeLogs($openfactura_registry);

    /**
     * Array used to prepare request document to be sent to issue backend.
     */
    $document_send = array();

    /**
     * Response header for issue request
     */
    $response["response"] = ["FOLIO", "SELF_SERVICE"];

    /**
     * The next block gets the customer info.
     */
    if (!empty($order->get_billing_first_name()) && !empty($order->get_billing_last_name()) && !empty($order->get_billing_email())) {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name() . " " . $order->get_billing_last_name(), 0, 100), "email" => substr($order->get_billing_email(), 0, 80)];
    } elseif (!empty($order->get_billing_first_name()) && !empty($order->get_billing_email())) {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name(), 0, 100), "email" => substr($order->get_billing_email(), 0, 80)];
    } elseif (!empty($order->get_billing_first_name())) {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name(), 0, 100)];
    } elseif (!empty($order->get_billing_email())) {
        $customer["customer"] = ["email" => substr($order->get_billing_email(), 0, 100)];
    }

    /**
     * Handle custom logo and order url
     */
    if (!empty($openfactura_registry->link_logo) && $openfactura_registry->show_logo) {
        $customize_page["customizePage"] = ["urlLogo" => $openfactura_registry->link_logo, 'externalReference' => ["hyperlinkText" => "Orden de Compra #" . $order->get_id(), "hyperlinkURL" => wc_get_checkout_url() .
            "order-received/" . $order->get_order_number() . "/key="
            . $order->get_order_key()]];
    } else {
        $customize_page["customizePage"] = ['externalReference' => ["hyperlinkText" => "Orden de Compra #" . $order->get_id(), "hyperlinkURL" => wc_get_checkout_url() .
            "order-received/" . $order->get_order_number() . "/key="
            . $order->get_order_key()]];
    }

    /**
     * Get order date
     */
    $date = $order->get_date_paid('date');
    $date = $date->date_i18n($format = 'Y-m-d');

    /**
     * Get openfactura's plugin config. Handles permissions to get boleta and to allow factura.
     */
    if ($openfactura_registry->generate_boleta == "1") {
        $generate_boleta = true;
    } else {
        $generate_boleta = false;
    }
    if ($openfactura_registry->allow_factura == "1") {
        $allow_factura = true;
    } else {
        $allow_factura = false;
    }

    /**
     * Prepares selfservice header for issue request.
     */
    $self_service["selfService"] = [
        "issueBoleta" => $generate_boleta,
        "allowFactura" => $allow_factura,
        "documentReference" => [
            [
                "type" => "801",
                "ID" => $order->get_id(),
                "date" => $date
            ]
        ]
    ];

    /**
     * Defines variables to handle order.
     */
    $is_exe = $order->get_total_tax() == 0;
    $is_afecta = !$is_exe;
    $document_type = '';
    $mnt_exe = 0;
    $mnt_neto = 0;
    $detalle = array();
    $dsctos = array();
    $items = null;
    $note = '';

    //Loop through order tax items searching is taxable
    /*foreach ($order->get_items() as $item) {
        if ($item->get_total_tax() == 0) {
            $is_exe = true;
        } else {
            $is_afecta = true;
        }
    }
    //Loop through order shipping items searching is taxable
    foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
        if ($shipping_item->get_total_tax() == 0) {
            $is_shipping_exe = true;
        } else {
            $falg_prices_include_tax = true; evisar
        }
    }
    //Loop through order fee items searching is taxable
    foreach ($order->get_items('fee') as $item_id => $item_fee) {
        $fee_total_tax = $item_fee->get_total_tax();
        if ($fee_total_tax == 0) {
            $is_fee_exe = true;
        } else {
            $falg_prices_include_tax = true; evisar
        }
    }*/

    /**
     * Defines item (i) and discounts (idsto) counters.
     */
    $i = 1;
    $idsto = 0;

    /**
     * For each item in the order
     */
    foreach ($order->get_items() as $item) {

        /**
         * Get item name and description
         */
        $product = $item->get_product();
        $name_product = $product->get_name();
        $description_product = strip_tags(html_entity_decode($product->get_description()));

        /**
         * Determine if item is tax free.
         */
        $is_exe = $item->get_total_tax() == 0;

        /** 
         * Sanitize item name and description. Assign a default item name should it be empty. 
         */
        if (empty($name_product)) {
            $name_product = "item";
        } else {
            $name_product = trim(
                filter_var(
                    filter_var($name_product, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES),
                    FILTER_UNSAFE_RAW,
                    FILTER_FLAG_STRIP_HIGH
                )
            );
        }
        if (!empty($description_product)) {
            $description_product = trim(
                filter_var(
                    filter_var($description_product, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES),
                    FILTER_UNSAFE_RAW,
                    FILTER_FLAG_STRIP_HIGH
                )
            );
        }

        /**
         * If item is tax free:
         */
        if ($item->get_total_tax() == 0) {
            /**
             * Calculate item's individual value based on total and quantity; item's subtotal and discount.
             * The total value will be lower than the subtotal when a coupon is applied.
             */
            $MontoItem = $item->get_subtotal();
            $PrcItem = $MontoItem / $item->get_quantity();
            $descuento = $MontoItem - $item->get_total();

            /**
             * Create item map for issue request. Add description to items map if item has one.
             */
            if ($openfactura_registry->is_description == '1' && !empty($description_product)) {
                $items = [
                    "NroLinDet" => $i,
                    'NmbItem' => substr($name_product, 0, 80),
                    'DscItem' => substr($description_product, 0, 990),
                    'QtyItem' => $item->get_quantity(),
                    'PrcItem' => round($PrcItem, 4),
                    'MontoItem' => round($MontoItem - $descuento, 0),
                    'IndExe' => 1
                ];
            } else {
                $items = [
                    "NroLinDet" => $i,
                    'NmbItem' => substr($name_product, 0, 80),
                    'QtyItem' => $item->get_quantity(),
                    'PrcItem' => round($PrcItem, 4),
                    'MontoItem' => round($MontoItem - $descuento, 0),
                    'IndExe' => 1
                ];
            }

            /**
             * Add discount to item map.
             */
            if ($descuento > 0) {
                $items['DescuentoMonto'] = round($descuento, 0);
            }

            /**
             * Add discounted item value to tax free amount counter.
             * If the item's net amount is negative, it's a global discount.
             * The value of the discount also has to be added to the tax free net total.
             */
            $mnt_exe += round($MontoItem - $descuento, 0);
        
        /**
        * If the item has tax:
        */
        } else {
            /**
             * Calculate item's individual value based on total and quantity; item's subtotal and discount.
             */
            /*$PrcItem = $item->get_subtotal() / $item->get_quantity();
            $MontoItem = $item->get_subtotal();
            $descuento = $item->get_subtotal() - $item->get_total();
            */

            $MontoItem = $item->get_subtotal() + $item->get_subtotal_tax();
            $PrcItem = $MontoItem / $item->get_quantity();
            $descuento = $MontoItem - ($item->get_total() + $item->get_total_tax());

            /**
             * Create item map for issue request. Add description to items map if item has one.
             */
            if ($openfactura_registry->is_description == '1' && !empty($description_product)) {
                $items = [
                    "NroLinDet" => $i,
                    'NmbItem' => substr($name_product, 0, 80),
                    'DscItem' => substr($description_product, 0, 990),
                    'QtyItem' => $item->get_quantity(),
                    'PrcItem' => round($PrcItem, 4),
                    'MontoItem' => round($MontoItem - $descuento, 0)
                ];
            } else {
                $items = [
                    "NroLinDet" => $i,
                    'NmbItem' => substr($name_product, 0, 80),
                    'QtyItem' => $item->get_quantity(),
                    'PrcItem' => round($PrcItem, 4),
                    'MontoItem' => round($MontoItem - $descuento, 0)
                ];
            }

            /**
             * Add discount amount to item map
             */
            if ($descuento > 0) {
                $items['DescuentoMonto'] = round($descuento, 0);
            }
            
            /**
             * Add discounted item value to net amount counter.
             * If the item's net amount is negative, it's a global discount.
             * The value of the discount also has to be added to the net total.
             */
            $mnt_neto += round($MontoItem - $descuento, 0);
        }

        /**
         * If the item is free, add a note pointing it out.
         */
        if (intval($MontoItem) == 0) {
            if ($note == '') {
                $note = 'Incluido sin costo en la compra:';
            }
            $note .= '<br/> *';
            $note .= ' ' . 1 . ' ' . substr($name_product, 0, 80);
        
        /**
         * If the item amount is less that zero, create a discount map. Increase idsto counter.
         */
        } elseif (intval($MontoItem) < 0) {
            $idsto++;
            $dcto = [
                "NroLinDR" => $idsto,
                "TpoMov" => "D",
                "TpoValor" => "$",
                "ValorDR" => strval($MontoItem * -1)
            ];

            /**
             * Specify if discount is tax free.
             */
            if ($is_exe) {
                $dcto["IndExeDR"] = 1;
            }

            /**
             * Push map into dsctos array for issue request.
             */
            array_push($dsctos, $dcto);
        } else {
            /**
             * Increase i counter and push items map into detalle array for issue request.
             */
            $i++;
            array_push($detalle, $items);
        }
    }

   /**
    * For each fee item:
    */
    foreach ($order->get_items('fee') as $item_id => $item_fee) {
        /**
         * Get fee's data.
         */
        $fee_total_tax = $item_fee->get_total_tax();
        $fee_name = $item_fee->get_name();
        $fee_amount = round($item_fee->get_amount());
        $fee_total = round($item_fee->get_total());

        /**
         * If fee doesn't have a name assign a default one.
         */
        if (empty($fee_name)) {
            $fee_name = "impuesto";
        }

        /**
         * If there's no fee tax, handle as a tax free item.
         */
        if ($fee_total_tax == 0) {
            /**
             * Add fee amount to exempt amount counter.
             */
            $mnt_exe += $fee_total;

            /**
             * Create fee map for issue request.
             */
            $items = [
                "NroLinDet" => $i,
                'NmbItem' => substr($fee_name, 0, 80),
                'QtyItem' => 1, 
                'PrcItem' => $fee_amount,
                'MontoItem' => $fee_total,
                'IndExe' => 1
            ];
        
        /**
         * If the item has a tax, handle it as a taxable item.
         */
        } else {
            /**
             * Taxable and exempt failsafe. Flips flags if this item has a tax
             * and the tax free flags is enabled. This flags controls the whole
             * order, not the item.
             */
            if ($is_exe) {
                $is_exe = false;
                $is_afecta = true;
            }

            /**
             * Add fee amount to net amount counter.
             */
            $mnt_neto += $fee_total;

            /**
             * Create fee map for issue request.
             */
            $items = [
                "NroLinDet" => $i,
                'NmbItem' => substr($fee_name, 0, 80),
                'QtyItem' => 1,
                'PrcItem' => $fee_amount,
                'MontoItem' => $fee_total
            ];
        }
        /**
         * If the fee is free add a note pointing it out.
         */
        if (intval($fee_total) == 0) {
            if ($note == '') {
                $note = 'Incluido sin costo en la compra:';
            }
            $note .= '<br/> *';
            $note .= ' ' . 1 . ' ' . substr($fee_name, 0, 80);
        
        /**
         * If the fee amount is less than zero, create a discount map. Increase idcsto counter.
         */
        } elseif (intval($fee_total) < 0) {
            $idsto++;
            $dcto = [
                "NroLinDR" => $idsto,
                "TpoMov" => "D",
                "TpoValor" => "$",
                "ValorDR" => strval($fee_total * -1)
            ];

            /**
             * Push map into dsctos array for issue request.
             */
            array_push($dsctos, $dcto);
        } else {
            /**
             * Increase i counter and push items map into detalle array for issue request.
             */
            $i++;
            array_push($detalle, $items);
        }
    }

    /**
     * For each shipping item:
     */
    foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
        /**
         * Get shipping amount and price. Assign as free should the shipping be free.
         * I did not code this.
         */
        if ($shipping_item->get_total() == 0) {
            $monto_item = 0;
            $prc_item = 1;
        } else {
            $monto_item = round($shipping_item->get_total());
            $prc_item = round($shipping_item->get_total());
        }

        /**
         * Get shipping item's name. Assign a default name if it doesn't have one.
         */
        $shipping_item_get_name = $shipping_item->get_name();
        if (empty($shipping_item_get_name)) {
            $shipping_item_get_name = "Reparto";
        }

        /**
         * If shipping is tax free, handle as such:
         */
        if ($shipping_item->get_total_tax() == 0) {
            /**
             * Add shipping amount to tax free amount counter.
             */
            $mnt_exe = $mnt_exe + $monto_item;

            /**
             * Create an items map for the shipping item.
             */
            $items = [
                "NroLinDet" => $i,
                'NmbItem' => substr($shipping_item->get_name(), 0, 80),
                'QtyItem' => 1,
                'PrcItem' => round($prc_item, 4),
                'MontoItem' => round($monto_item),
                'IndExe' => 1
            ];
        /**
         * If the shipping is taxable
         */
        } else {
            /**
             * Taxable and exempt failsafe. Flips flags if this item has a tax
             * and the tax free flags is enabled. This flags controls the whole
             * order, not the item.
             */
            if ($is_exe) {
                $is_exe = false;
                $is_afecta = true;
            }

            /**
             * Add shipping amount to net amount counter.
             */
            $mnt_neto += round($monto_item);

            /**
             * Create an items map for the shipping item.
             */
            $items = [
                "NroLinDet" => $i,
                'NmbItem' => substr($shipping_item->get_name(), 0, 80),
                'QtyItem' => 1,
                'PrcItem' => round($prc_item, 6),
                'MontoItem' => round($monto_item)
            ];
        }

        /**
         * If the shipping is free add a note pointing it out.
         */
        if (intval($monto_item) == 0) {
            if ($note == '') {
                $note = 'Incluido sin costo en la compra:';
            }
            $note .= '<br/> *';
            $note .= ' ' . 1 . ' ' . substr($shipping_item->get_name(), 0, 80);
        
        /**
         * If the shipping amount is less than zero, create a discount map. Increase idcsto counter.
         */
        } elseif (intval($monto_item) < 0) {
            $idsto++;
            $dcto = [
                "NroLinDR" => $idsto,
                "TpoMov" => "D",
                "TpoValor" => "$",
                "ValorDR" => strval($monto_item * -1)
            ];

            /**
             * Push map into dsctos array for issue request.
             */
            array_push($dsctos, $dcto);
        } else {
            /**
             * Increase i counter and push items map into detalle array for issue request.
             */
            $i++;
            array_push($detalle, $items);
        }
    }

    /**
     * If the order's total amount is less than $10 pesos, return a note to the order.
     */
    if (intval($order->get_total()) < 10) {
        $note = "No se permiten emisiones con un valor menor a 10 CLP.";
        $order->add_order_note($note);
        return $order;
    }

    /**
     * If the afecta flag is active:
     * This will trigger if there was at least one item with tax,
     * of if the total tax amount isn't zero.
     */
    if ($is_afecta) {
        /**
         * Calculate IVA from the net total and a fixed value, Chile's current IVA.
         */
        $iva = round($mnt_neto * 0.19 / 1.19); 
        /**
         * Handle issue request's headers. This defines the net amount, the
         * exempt amount and the tax of the order. 
         * IndMntNeto = 1 is a constant that the issue backend expects to receive.
         */
        $id_doc = ["FchEmis" => $date ];
        $totales = [
            "MntNeto" => round($mnt_neto / (1 + (19 /100))),
            "TasaIVA" => "19.00",
            "IVA" => $iva,
            'MntExe' => round($mnt_exe),
            "MntTotal" => round($order->get_total())
        ];

        /**
         * Define document type and code for issue request.
         */
        $document_type = 'Boleta Electrónica Afecta';
        $document_code = "39";
    
    /**
     * If the afecta flag isn't active, handle order as tax free.
     */
    } else {
        /**
         * Get date for issue request header.
         */
        $date = $order->get_date_paid('date');
        $date = $date->date_i18n('Y-m-d');

        /**
         * Handle issue request's headers. This defines the net amount and the tax free amount.
         */
        $id_doc = ["FchEmis" => substr($date, 0, 10)];
        $totales = [
            "MntTotal" => round($order->get_total()),
            'MntExe' => round($mnt_exe)
        ];

        /**
         * Define document type and code for issue request.
         */
        $document_type = 'Boleta Electrónica Exenta (41)';
        $document_code = "41";
    }
    //$order->add_order_note(json_encode($detalle));
    //$order->add_order_note(json_encode($totales));
    //writeLogs("cdgSIISucur ANTES DEL EMISOR");
    //writeLogs($openfactura_registry->cdgSIISucur);

    /**
     * Prepare emisor map for issue request headers. Get info from openfactura's
     * config table.
     */
    $emisor = [
        "RUTEmisor" => substr($openfactura_registry->rut, 0, 10),
        "RznSocEmisor" => substr($openfactura_registry->razon_social, 0, 100),
        "GiroEmisor" => substr($openfactura_registry->glosa_descriptiva, 0, 80),
        "CdgSIISucur" => $openfactura_registry->cdgSIISucur,
        "DirOrigen" => substr($openfactura_registry->direccion_origen, 0, 60),
        "CmnaOrigen" => substr($openfactura_registry->comuna_origen, 0, 20),
        "Acteco" => $openfactura_registry->codigo_actividad_economica_active
    ];
    //writeLogs("cdgSIISucur DESPUES DEL EMISOR");
    //writeLogs($openfactura_registry->cdgSIISucur);

    /**
     * Replace values in emisor with data from current active sucursal.
     */
    $sucursal_and_code = explode("|", $openfactura_registry->sucursal_active);
    if (count($sucursal_and_code) == 2) {
        $emisor["DirOrigen"] = substr($sucursal_and_code[0], 0, 60);
        $emisor["CdgSIISucur"] = $sucursal_and_code[1];
    }
    //writeLogs("cdgSIISucur DESPUES DEL EXPLODE");
    //writeLogs($emisor['CdgSIISucur']);

    /**
     * Prepare document headers for issue request.
     */
    $dte["dte"] = [
        "Encabezado" => [
            "IdDoc" => $id_doc,
            "Emisor" => $emisor,
            "Totales" => $totales
        ],
        "Detalle" => $detalle,
        "DscRcgGlobal" => $dsctos
    ];
    $custom['custom'] = [
        'origin' => 'WOOCOMMERCE'
    ];

    /**
     * Merge arrays as needed by the api.
     */
    $document_send = array_merge($document_send, $response);
    if (!empty($customer)) {
        $document_send = array_merge($document_send, $customer);
    }
    $document_send = array_merge($document_send, $customize_page);
    $document_send = array_merge($document_send, $self_service);
    $document_send = array_merge($document_send, $dte);
    $document_send = array_merge($document_send, $custom);
    //$order->add_order_note(json_encode($document_send));
    if ($note != '') {
        $document_send = array_merge($document_send, ["notaInf" => $note]);
    }

    error_log(print_r($order, true));
    error_log(print_r($document_send, true));

    /**
     * Encode document send object as a json object
     */
    $document_send = json_encode($document_send, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    /*if (is_array($document_send) || is_object($document_send)) {
        error_log(print_r($document_send, true));
    } else {
        error_log($document_send);
    }*/

    /**
     * Define API url based on current environment
     */
    $url_generate = '';
    if ($openfactura_registry->is_demo == "1") {
        //dev environment
        $url_generate = 'https://dev-api.haulmer.com/v2/dte/document';
    } else {
        //prod environment
        $url_generate = 'https://api.haulmer.com/v2/dte/document';
    }

    /**
     * Prepare POST request to API
     */
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url_generate,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $document_send,
        CURLOPT_HTTPHEADER => array(
            "Content-type: application/json",
            "apikey:" . $openfactura_registry->apikey,
            "Idempotency-Key:" . "WOOCOMMERCE" . "_" . $emisor['rut'] . "_" . date("Y/m/d_H:i:s") . "_" . $order->get_order_key(),
        ),
    ));

    /**
     * Handle POST rquest and handle response
     */
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    /**
     * Log response.
     */
    /*if (is_array($response) || is_object($response)) {
        error_log(print_r($response, true));
    } else {
        error_log($response);
    }*/

    /**
     * Handle response in woocommerce's order details interface in wordpress' admin panel
     */
    $response = json_decode($response, true);
    if (!empty($response['SELF_SERVICE']['url'])) {
        $note = __("Obten tu documento tributario en: " . $response['SELF_SERVICE']['url']);
        $order->add_order_note($note);
        if (!empty($document_type)) {
            add_post_meta($order->get_id(), '_document_type', $document_type);
            $order->add_order_note('Tipo de documento: ' . $document_type);
        }
        if (!empty($response['FOLIO'])) {
            add_post_meta($order->get_id(), '_invoice_serial', $response['FOLIO']);
            add_post_meta($order->get_id(), '_document_code', $document_code);
            $order->add_order_note('Folio: ' . $response['FOLIO']);
        } else {
            add_post_meta($order->get_id(), '_invoice_serial', 'No Generado');
            add_post_meta($order->get_id(), '_document_code', 'No Generado');
            $order->add_order_note('Folio: No generado');
        }
    } else {
        $info = "No se pudo generar tu documento tributario. \n";
        $error = "Error: " . $response['error']['message'] . "\n";
        $code = "Código: " . $response['error']['code'] . "\n";
        $details = "Detalles:\n";
        foreach ($response['error']['details'][0] as $key => $value) {
            $details = $details . "\r" . $key . " = " . $value . "\n";
        }
        $note = $info . $error . $code . $details;
        $order->add_order_note($note);

        if (!empty($document_type)) {
            add_post_meta($order->get_id(), '_document_type', $document_type);
            $order->add_order_note('Tipo de documento: ' . $document_type);
        }

        if (empty($response['FOLIO'])) {
            add_post_meta($order->get_id(), '_invoice_serial', 'No Generado');
            add_post_meta($order->get_id(), '_document_code', 'No Generado');
            $order->add_order_note('Folio: No generado');
        }
    }
    return $order;
}

add_action('woocommerce_admin_order_data_after_billing_address', 'misha_editable_order_meta_general');
function misha_editable_order_meta_general($order)
{
    $document_type = get_post_meta($order->get_id(), '_document_type', true);
    $serial_number = get_post_meta($order->get_id(), '_invoice_serial', true);
    $document_code = get_post_meta($order->get_id(), '_document_code', true);
    if (!empty($document_type)) {
    ?> <p>Tipo de documento: <?php echo $document_type; ?> </p>
    <?php
    }
    if (!empty($serial_number)) {
    ?> <p>Folio: <?php echo $serial_number; ?> </p>
<?php
    }
}

add_action('woocommerce_email_order_details', 'my_completed_order_email_instructions', 10, 4);
function my_completed_order_email_instructions($order, $sent_to_admin, $plain_text, $email)
{
    // Only for processing and completed email notifications to customer
    /*if (is_array($order) || is_object($order)) {
        error_log(print_r($order, true));
    } else {
        error_log($order);
    }*/
    if (!('customer_completed_order' == $email->id)) {
        return;
    }
    global $wpdb;
    $openfactura_registry_active = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where is_active=1");
    if ($openfactura_registry_active[0]->is_email_link_selfservice == '1') {
        $order_notes = get_private_order_notes($order->get_id());
        //verificar que enlace de autoservicio no este creado previamente
        if (isset($order_notes) && !empty($order_notes)) {
            foreach ($order_notes as $note) {
                $note_content = $note['note_content'];
                if (stristr($note_content, 'Obten tu documento tributario')) {
                    $link = str_replace("Obten tu documento tributario en:", "", $note_content);
                    echo '<p>Obtén tu documento tributario <a href=' . $link . '>aquí.</a></p>';
                }
            }
        }
    }
}

/**
 * Debug log
 */
function debug_log($order)
{
    error_log(print_r("************ customer **************", true));
    if (!empty($order->get_billing_first_name()) && !empty($order->get_billing_last_name()) && !empty($order->get_billing_email())) {
        error_log(print_r($order->get_billing_first_name(), true));
        error_log(print_r($order->get_billing_last_name(), true));
        error_log(print_r($order->get_billing_email(), true));
    } elseif (!empty($order->get_billing_email()) && !empty($order->get_billing_email())) {
        error_log(print_r($order->get_billing_first_name(), true));
        error_log(print_r($order->get_billing_email(), true));
    } else {
        error_log(print_r($order->get_billing_first_name(), true));
    }

    error_log(print_r("************ order **************", true));
    $orderi = $order->get_data();
    error_log(print_r($orderi, true));

    error_log(print_r("************ coupons **************", true));
    foreach ($order->get_coupon_codes() as $coupon_code) {
        $c = new WC_Coupon($coupon_code);
        error_log(print_r($c, true));
    }

    error_log(print_r("************ item **************", true));
    foreach ($order->get_items() as $item_id => $item) {
        $item = $item->get_data();
        error_log(print_r($item, true));
    }
    error_log(print_r("************ fee **************", true));
    foreach ($order->get_items('fee') as $item_id => $item_fee) {
        $item_fee = $item_fee->get_data();
        error_log(print_r($item_fee, true));
    }
    error_log(print_r("************ shipping **************", true));
    foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
        $shipping_item = $shipping_item->get_data();
        error_log(print_r($shipping_item, true));
    }
}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function openfactura_registry()
{
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
 */
function insert_demo_data()
{
    global $wpdb;
    $apikey = '928e15a2d14d4a6292345f04960f4bd3';
    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey = " . "'$apikey'");
    if (isset($openfactura_registry) && empty($openfactura_registry)) {
        //apikey demo dev 928e15a2d14d4a6292345f04960f4bd3 https://dev-api.haulmer.com/v2/dte/organization
        $apikey = '928e15a2d14d4a6292345f04960f4bd3';
        //insert demo data dev
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
                "apikey:" . $apikey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);
        $actividades_array = array();
        foreach ($response['actividades'] as $actividad) {
            array_push($actividades_array, $actividad['actividadEconomica'] . "|" . $actividad['codigoActividadEconomica']);
        }
        $actividades_array = json_encode($actividades_array);
        $actividad_economica = $response['actividades'][0]['actividadEconomica'] . "|" . $response['actividades'][0]['codigoActividadEconomica'];
        $codigo_actividad_economica_active = $response['actividades'][0]['codigoActividadEconomica'];
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'openfactura_registry', array(
            'apikey' => $apikey,
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
 * save data in table openfactura_registry
 */
function save_data_openfactura_registry()
{
    if (!isset($_REQUEST['demo']) || empty($_REQUEST['demo'])) {
        return wp_send_json_error([400]);
    }
    if (!isset($_REQUEST['automatic39']) || empty($_REQUEST['automatic39'])) {
        return wp_send_json_error([400]);
    }
    if (!isset($_REQUEST['allow33']) || empty($_REQUEST['allow33'])) {
        return wp_send_json_error([400]);
    }
    if (!isset($_REQUEST['enableLogo']) || empty($_REQUEST['enableLogo'])) {
        return wp_send_json_error([400]);
    }
    if ($_REQUEST['demo'] == "true") {
        $demo = true;
    } else {
        $demo = false;
    }
    if ($_REQUEST['automatic39'] == "true") {
        $automatic39 = true;
    } else {
        $automatic39 = false;
    }
    if ($_REQUEST['allow33'] == "true") {
        $allow33 = true;
    } else {
        $allow33 = false;
    }
    if ($_REQUEST['enableLogo'] == "true") {
        $enableLogo = true;
    } else {
        $enableLogo = false;
    }
    if ($_REQUEST['description'] == "true") {
        $description = true;
    } else {
        $description = false;
    }
    if ($_REQUEST['emailLinkSelfservice'] == "true") {
        $email_link_selfservice = true;
    } else {
        $email_link_selfservice = false;
    }

    global $wpdb;
    $apikey = str_replace(' ', '', $_REQUEST['apikey']);
    $apikey_demo = '928e15a2d14d4a6292345f04960f4bd3';
    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey=" . "'$apikey'");
    $openfactura_registry_active = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where is_active=1");
    if (isset($openfactura_registry) && empty($openfactura_registry) && !empty($apikey)) {
        //insert prod data
        $url_emision = 'https://api.haulmer.com/v2/dte/organization';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_emision,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "apikey:" . $apikey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);
        $apikey_demo = '928e15a2d14d4a6292345f04960f4bd3';
        $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey=" . "'$apikey_demo'");
        $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
            'is_active' => 0,
        ), array('id' => $openfactura_registry[0]->id));

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
        $sucursales = json_encode($sucursales);
        $sucursal_active = $response['direccion'] . "|" . $response['cdgSIISucur'];
        $num = $wpdb->get_var("SELECT COUNT(*) FROM  " . $wpdb->prefix . "openfactura_registry");

        if (is_array($num) || is_object($num)) {
            error_log(print_r($num, true));
        } else {
            error_log($num);
        }
        $codigo_actividad_economica_active = $response['actividades'][0]['codigoActividadEconomica'];
        if ($num == 1) {
            $wpdb->insert($wpdb->prefix . 'openfactura_registry', array(
                'is_active' => 1,
                'apikey' => $apikey,
                'rut' => $response['rut'],
                'is_demo' => false,
                'generate_boleta' => false,
                'allow_factura' => false,
                'is_description' => false,
                'is_email_link_selfservice' => false,
                'show_logo' => false,
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
                'url_send' => $url_emision,
                'cdgSIISucur' => $response['cdgSIISucur']
            ));
        } else {
            $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey!=" . "'$apikey_demo'");
            $id = $openfactura_registry[0]->id;
            $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                'is_active' => 1,
                'apikey' => $apikey,
                'rut' => $response['rut'],
                'is_demo' => false,
                'generate_boleta' => false,
                'allow_factura' => false,
                'is_description' => false,
                'is_email_link_selfservice' => false,
                'show_logo' => false,
                'razon_social' => $response['razonSocial'],
                'glosa_descriptiva' => $response['glosaDescriptiva'],
                'sucursales' => $sucursales,
                'sucursal_active' => $sucursal_active,
                'actividad_economica_active' => $actividad_economica_active,
                'actividades_economicas' => $actividades_economicas,
                'direccion_origen' => $response['direccion'],
                'comuna_origen' => $response['comuna'],
                'json_info_contribuyente' => json_encode($response),
                'url_doc_base' => 'url orden de compra',
                'name_doc_base' => 'orden de compra',
                'url_send' => $url_emision,
                'cdgSIISucur' => $response['cdgSIISucur']
            ), array('id' => $id));
        }
        wp_send_json_success(['insert']);
    } else {
        if (empty($apikey)) {
            $apikey = '928e15a2d14d4a6292345f04960f4bd3';
            $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey=" . "'$apikey'");
            if (!empty($openfactura_registry)) {
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'is_active' => 1
                ), array('id' => $openfactura_registry[0]->id));
            }
            wp_send_json_success(['insert']);
        } else {
            $is_demo = false;
            if ($openfactura_registry_active[0]->is_demo == "1") {
                $is_demo = true;
            } else {
                $is_demo = false;
            }
            $switch = false;
            if ($openfactura_registry_active[0]->apikey != $openfactura_registry[0]->apikey || $is_demo != $demo) {
                $id = $openfactura_registry[0]->id;
                $switch = true;
            } else {
                $id = $openfactura_registry_active[0]->id;
                $switch = false;
            }
            if (is_array(($openfactura_registry[0]->is_demo)) || is_object(($openfactura_registry[0]->is_demo))) {
                error_log(print_r(($openfactura_registry[0]->is_demo), true));
            } else {
                error_log(($openfactura_registry[0]->is_demo));
            }
        }

        if (isset($_REQUEST['actividad']) && !empty($_REQUEST['actividad'])) {
            $actividad_array = explode("|", $_REQUEST['actividad']);
            if (count($actividad_array) >= 2) {
                $codigo_actividad_active = $actividad_array[1];
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'codigo_actividad_economica_active' => $codigo_actividad_active,
                    'actividad_economica_active' => $actividad_array[0]
                ), array('id' => $id));
            }
        }
        if (isset($_REQUEST['sucursal']) && !empty($_REQUEST['sucursal'])) {
            $sucursal_array = explode("|", $_REQUEST['sucursal']);
            if (count($sucursal_array) >= 2) {
                $codigo_sucursal_active = $sucursal_array[1];
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'cdgSIISucur' => $codigo_sucursal_active,
                    'sucursal_active' => $sucursal_array[0]
                ), array('id' => $id));
            }
        }
        $url_logo = str_replace(' ', '', $_REQUEST['urlLogo']);
        $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
            'generate_boleta' => $automatic39,
            'allow_factura' => $allow33,
            'link_logo' => $url_logo,
            'show_logo' => $enableLogo,
            'is_description' => $description,
            'is_email_link_selfservice' => $email_link_selfservice,
            'sucursal_active' => $_REQUEST['sucursal']
        ), array('id' => $id));
        if ($switch) {
            $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where id!=" . "'$id'");
            if (!empty($openfactura_registry)) {
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'is_active' => !$switch,
                ), array('id' => $id));
                $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
                    'is_active' => $switch,
                ), array('id' => $openfactura_registry[0]->id));
            }
            wp_send_json_success(['insert']);
        } else {
            wp_send_json_success(['update']);
        }
    }
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
function update_data_openfactura_registry()
{
    global $wpdb;

    $apikey = str_replace(' ', '', $_REQUEST['apikey']);

    $openfactura_registry = $wpdb->get_results("SELECT * FROM  " . $wpdb->prefix . "openfactura_registry where apikey=" . "'$apikey'");
    if (isset($openfactura_registry) && !empty($openfactura_registry)) {
        if ($openfactura_registry[0]->is_demo == 1) {
            //dev environment
            $url_organization = 'https://dev-api.haulmer.com/v2/dte/organization';
        } else {
            //prod environment
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
                "apikey:" . $apikey
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            return wp_send_json_success(['error']);
        }
        $response = json_decode($response, true);

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

        $wpdb->update($wpdb->prefix . 'openfactura_registry', array(
            'apikey' => $apikey,
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
            'url_send' => $url_organization
        ), array('id' => $openfactura_registry[0]->id));
        wp_send_json_success(['update']);
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
                    puedes revisar nuestra Documentación de Integración con WHMCS.
                </p>
            </div>
            <form action="" onsubmit="return sendForm(event)" id="form1">
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
    debug_log($order);
    global $wpdb;
    $openfactura_registry = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "openfactura_registry where is_active=1");
    create_json_openfactura($order, $openfactura_registry[0]);
    return $order;
}

/**
 * Create a new document to issue
 */
function create_json_openfactura($order, $openfactura_registry)
{
    $falg_prices_include_tax = false;
    $document_send = array();
    $response["response"] = ["FOLIO", "SELF_SERVICE"];
    if (!empty($order->get_billing_first_name()) && !empty($order->get_billing_last_name()) && !empty($order->get_billing_email())) {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name() . " " . $order->get_billing_last_name(), 0, 100), "email" => substr($order->get_billing_email(), 0, 80)];
    } elseif (!empty($order->get_billing_email()) && !empty($order->get_billing_email())) {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name(), 0, 100), "email" => substr($order->get_billing_email(), 0, 80)];
    } else {
        $customer["customer"] = ["fullName" => substr($order->get_billing_first_name(), 0, 100)];
    }

    if (!empty($openfactura_registry->link_logo) && $openfactura_registry->show_logo) {
        $customize_page["customizePage"] = ["urlLogo" => $openfactura_registry->link_logo, 'externalReference' => ["hyperlinkText" => "Orden de Compra #" . $order->get_id(), "hyperlinkURL" => wc_get_checkout_url() .
            "order-received/" . $order->get_order_number() . "/key="
            . $order->get_order_key()]];
    } else {
        $customize_page["customizePage"] = ['externalReference' => ["hyperlinkText" => "Orden de Compra #" . $order->get_id(), "hyperlinkURL" => wc_get_checkout_url() .
            "order-received/" . $order->get_order_number() . "/key="
            . $order->get_order_key()]];
    }
    $date = $order->get_date_paid('date');
    $date = $date->date_i18n($format = 'Y-m-d');
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
    $self_service["selfService"] = ["issueBoleta" => $generate_boleta, "allowFactura" => $allow_factura, "documentReference" => [["type" => "801", "ID" => $order->get_id(), "date" => $date]]];
    $is_exe = false;
    $is_afecta = false;
    $document_type = '';
    $mnt_exe = 0;
    $mnt_total = 0;
    $detalle = array();
    $items = null;

    //Loop through order tax items searching is taxable
    foreach ($order->get_items() as $item) {
        if ($item->get_total_tax() == 0) {
            $is_exe = true;
        } else {
            $falg_prices_include_tax = true;
            $is_afecta = true;
        }
    }
    //Loop through order shipping items searching is taxable
    foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
        if ($shipping_item->get_total_tax() == 0) {
            $is_exe = true;
        } else {
            $falg_prices_include_tax = true;
            $is_afecta = true;
        }
    }
    //Loop through order fee items searching is taxable
    foreach ($order->get_items('fee') as $item_id => $item_fee) {
        $fee_total_tax = $item_fee->get_total_tax();
        if ($fee_total_tax == 0) {
            $is_exe = true;
        } else {
            $falg_prices_include_tax = true;
            $is_afecta = true;
        }
    }
    $i = 1;
    $isCoupon = false;
    foreach ($order->get_coupon_codes() as $coupon_code) {
        $isCoupon = true;
        $coupon = new WC_Coupon($coupon_code);
    }

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $name_product = $product->get_name();
        $description_product = $product->get_description();
        if (empty($name_product)) {
            $name_product = "item";
        }
        if ($item->get_total_tax() == 0) {
            //exenta
            $PrcItem = round($item->get_subtotal() / $item->get_quantity(), 6);
            $MontoItem = round(($item->get_quantity() * ($PrcItem)), 0);
            $mnt_exe = $mnt_exe + $MontoItem;
            if ($openfactura_registry->is_description == '1' && !empty($description_product)) {
                if ($item->get_subtotal() == $item->get_total()) {
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'DscItem' => substr($description_product, 0, 1000), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem, 'IndExe' => 1];
                } else {
                    $descuento = $item->get_subtotal() - $item->get_total();
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'DscItem' => substr($description_product, 0, 1000), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem - round($descuento, 0), 'IndExe' => 1, 'DescuentoMonto' => round($descuento, 0)];
                    $mnt_exe = $mnt_exe - round($descuento, 0);
                }
            } else {
                if ($item->get_subtotal() == $item->get_total()) {
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem, 'IndExe' => 1];
                } else {
                    $descuento = $item->get_subtotal() - $item->get_total();
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem - round($descuento, 0), 'IndExe' => 1, 'DescuentoMonto' => round($descuento, 0)];
                    $mnt_exe = $mnt_exe - round($descuento, 0);
                }
            }
        } else {
            //afecta
            $PrcItem = round($item->get_subtotal() / $item->get_quantity(), 6);
            $MontoItem = round(($item->get_quantity() * ($PrcItem)), 0);
            $mnt_total = $mnt_total + $MontoItem;
            if ($openfactura_registry->is_description == '1' && !empty($description_product)) {
                if ($item->get_subtotal() == $item->get_total()) {
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'DscItem' => substr($description_product, 0, 1000), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem];
                } else {
                    $descuento = $item->get_subtotal() - $item->get_total();
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'DscItem' => substr($description_product, 0, 1000), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem - round($descuento, 0), 'DescuentoMonto' => round($descuento, 0)];
                    $mnt_total = $mnt_total - round($descuento, 0);
                }
            } else {
                if ($item->get_subtotal() == $item->get_total()) {
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem];
                } else {
                    $descuento = $item->get_subtotal() - $item->get_total();
                    $items = ["NroLinDet" => $i, 'NmbItem' => substr($name_product, 0, 80), 'QtyItem' => $item->get_quantity(), 'PrcItem' => $PrcItem, 'MontoItem' => $MontoItem - round($descuento, 0), 'DescuentoMonto' => round($descuento, 0)];
                    $mnt_total = $mnt_total - round($descuento, 0);
                }
            }
        }
        $i++;
        array_push($detalle, $items);
    }

    //Loop through order fee items 
    foreach ($order->get_items('fee') as $item_id => $item_fee) {
        $fee_total_tax = $item_fee->get_total_tax();
        $fee_name = $item_fee->get_name();
        $fee_amount = round($item_fee->get_amount());
        $fee_total = round($item_fee->get_total());
        if (empty($fee_name)) {
            $fee_name = "impuesto";
        }
        if ($fee_total_tax == 0) {
            $mnt_exe = $mnt_exe + $fee_total;
            $items = ["NroLinDet" => $i, 'NmbItem' => substr($fee_name, 0, 80), 'QtyItem' => 1, 'PrcItem' => $fee_amount, 'MontoItem' => $fee_total, 'IndExe' => 1];
        } else {
            $mnt_total = $mnt_total + $fee_total;
            $items = ["NroLinDet" => $i, 'NmbItem' => substr($fee_name, 0, 80), 'QtyItem' => 1, 'PrcItem' => $fee_amount, 'MontoItem' => $fee_total];
        }

        $i++;
        array_push($detalle, $items);
    }

    //Loop through order shipping items
    foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
        if ($shipping_item->get_total() == 0) {
            $monto_item = 0;
            $prc_item = 1;
        } else {
            $monto_item = round($shipping_item->get_total());
            $prc_item = round($shipping_item->get_total());
        }
        $shipping_item_get_name = $shipping_item->get_name();
        if (empty($shipping_item_get_name)) {
            $shipping_item_get_name = "Reparto";
        }
        if ($shipping_item->get_total_tax() == 0) {
            $mnt_exe = $mnt_exe + $monto_item;
            $items = ["NroLinDet" => $i, 'NmbItem' => substr($shipping_item->get_name(), 0, 80), 'QtyItem' => 1, 'PrcItem' => round($prc_item, 6), 'MontoItem' => intval($monto_item), 'IndExe' => 1];
        } else {
            $mnt_total = $mnt_total + intval($monto_item);
            $items = ["NroLinDet" => $i, 'NmbItem' => substr($shipping_item->get_name(), 0, 80), 'QtyItem' => 1, 'PrcItem' => round($prc_item, 6), 'MontoItem' => intval($monto_item)];
        }
        $i++;
        array_push($detalle, $items);
    }
    if ($falg_prices_include_tax == true) {
        if ($is_exe == true && $is_afecta == true) {
            //afecta and exenta
            $iva = round($mnt_total * 0.19);
            $id_doc = array("FchEmis" => $date, "IndMntNeto" => 2);
            $totales = array("MntNeto" => intval($mnt_total), "TasaIVA" => "19.00", "IVA" => $iva, "MntTotal" => (intval($mnt_total) + $iva + intval($mnt_exe)), 'MntExe' => intval($mnt_exe));
            $document_type = 'Boleta Electrónica Afecta';
        } else {
            //only afecta
            $iva = round($mnt_total * 0.19);
            $date = $order->get_date_paid('date');
            $date = $date->date_i18n($format = 'Y-m-d');
            $id_doc = array("FchEmis" => $date, "IndMntNeto" => 2);
            $totales = array("MntNeto" => intval($mnt_total), "TasaIVA" => "19.00", "IVA" => $iva, "MntTotal" => intval($mnt_total + $iva));
            $document_type = 'Boleta Electrónica Afecta';
        }
        $document_code = "39";
    } else {
        //only exenta
        $date = $order->get_date_paid('date');
        $date = $date->date_i18n($format = 'Y-m-d');
        $id_doc = array("FchEmis" => substr($date, 0, 10));
        $totales = array("MntTotal" => intval($order->get_total()), 'MntExe' => intval($mnt_exe));
        $document_type = 'Boleta Electrónica Exenta (41)';
        $document_code = "41";
    }
    $emisor = array("RUTEmisor" => substr($openfactura_registry->rut, 0, 10), "RznSocEmisor" => substr($openfactura_registry->razon_social, 0, 100), "GiroEmisor" => substr($openfactura_registry->glosa_descriptiva, 0, 80), "CdgSIISucur" => $openfactura_registry->cdgSIISucur, "DirOrigen" => substr($openfactura_registry->direccion_origen, 0, 60), "CmnaOrigen" => substr($openfactura_registry->comuna_origen, 0, 20), "Acteco" => $openfactura_registry->codigo_actividad_economica_active);
    $dte["dte"] = [
        "Encabezado" => [
            "IdDoc" => $id_doc,
            "Emisor" => $emisor,
            "Totales" => $totales
        ], "Detalle" => $detalle
    ];
    $document_send = array_merge($document_send, $response);
    $document_send = array_merge($document_send, $customer);
    $document_send = array_merge($document_send, $customize_page);
    $document_send = array_merge($document_send, $self_service);
    $document_send = array_merge($document_send, $dte);
    $document_send = json_encode($document_send, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (is_array($document_send) || is_object($document_send)) {
        error_log(print_r($document_send, true));
    } else {
        error_log($document_send);
    }
    //generate document
    $url_generate = '';
    if ($openfactura_registry->is_demo == "1") {
        //dev environment
        $url_generate = 'https://dev-api.haulmer.com/v2/dte/document';
    } else {
        //prod environment
        $url_generate = 'https://api.haulmer.com/v2/dte/document';
    }
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
            "Idempotency-Key:" . $order->get_id()
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if (is_array($response) || is_object($response)) {
        error_log(print_r($response, true));
    } else {
        error_log($response);
    }
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
        ?>  <p>Tipo de documento: <?php echo $document_type; ?> </p>
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
    if (is_array($order) || is_object($order)) {
        error_log(print_r($order, true));
    } else {
        error_log($order);
    }
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
    } else if (!empty($order->get_billing_email()) && !empty($order->get_billing_email())) {
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

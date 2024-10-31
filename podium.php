<?php
/**
 * @package Podium
 */

/*
 * Plugin Name: Podium
 * Version: 2.0.7
 * Stable Tag: 2.0.7
 * Description: Allow customers to text you right from your website, capture them as leads, and convert them to customers. Install Podium on your WordPress site in just a few clicks.
 * Author: Podium
 * Author URI: https://www.podium.com/
 */

defined('ABSPATH') or die("Restricted access!");

define('PODIUM_ENDPOINT', 'https://mind-flayer.podium.com/api');
define('PODIUM_CLIENT_HOST', parse_url( get_site_url(), PHP_URL_HOST ));

##################################################################################################
## GLOBAL FUNCTIONS
##################################################################################################

/**
 * Handle WordPress option create/update
 */
function podium_handle_option($option_str, $option_value)
{
    if ( get_option($option_str) !== false ) {
        update_option($option_str, $option_value);
    } else {
        add_option($option_str, $option_value);
    }
}

/**
 * Handle callback when WordPress activate the plugin
 */
function podium_handle_feature_flag()
{
    ## PRE-EXECUTION - FEATURE FLAG ##
    $feature_flag = false;
    $req_scripts = wp_remote_get(PODIUM_ENDPOINT  . "/" . "scripts" . "/" .  PODIUM_CLIENT_HOST);
    $res_scripts = json_decode(wp_remote_retrieve_body($req_scripts));
    
    if ( $res_scripts !== null && json_last_error() === JSON_ERROR_NONE && isset($res_scripts->{'data'}) ) {
        $data = $res_scripts->{'data'};

        $feature_flag = isset($data->{'flag'}) ? $data->{'flag'} : false;
        $support = isset($data->{'message'}) ? true : false;

        ## SETS THE FEATURE FLAG TO FALSE IF SUPPORT IS NEEDED
        if( $feature_flag === true && $support === true ) {
            $feature_flag = false;
        }

        $token = $support !== true ? strval($data->{'organizationToken'}) : '';
        
        podium_handle_option('podium-script-code', $token);
    }

    podium_handle_option('podium-feature-flag', $feature_flag);
}

/**
 * Handle callback when WP deactivate the plugin
 */
function podium_handle_deactivation()
{
    podium_handle_option('podium-script-code', '');
    delete_option('podium-installation', false);
}

##################################################################################################
## APP INIT
##################################################################################################

add_action('activated_plugin', 'podium_handle_feature_flag', 10, 2);
add_action('deactivated_plugin', 'podium_handle_deactivation', 10, 2);

$feature_flag = get_option('podium-feature-flag');

if ( $feature_flag == false ) {
    ## OLD PODIUM PLUGIN ##
    /*
    * Define
    */
    define('podium_4f050d29b8BB9_VERSION', '2.0.7');
    define('podium_4f050d29b8BB9_DIR', plugin_dir_path(__FILE__));
    define('podium_4f050d29b8BB9_URL', plugin_dir_url(__FILE__));
    defined('podium_4f050d29b8BB9_PATH') or define('podium_4f050d29b8BB9_PATH', untrailingslashit(plugins_url('', __FILE__)));

    require_once(podium_4f050d29b8BB9_DIR . 'includes/core.php');
    require_once(podium_4f050d29b8BB9_DIR . 'includes/menus.php');
    require_once(podium_4f050d29b8BB9_DIR . 'includes/admin.php');
    require_once(podium_4f050d29b8BB9_DIR . 'includes/embed.php');
    
} else {
    ## NEW PODIUM PLUGIN ##
    if (!defined('ABSPATH')) die("Restricted access!");
    
    /**
     * Constants
     */
    define('PODIUM_KEY_CODE', 'podium');
    define('PODIUM_DIR_PATH', plugin_dir_path(__FILE__));
    define('PODIUM_DIR_URL', plugin_dir_url(__FILE__));
    define('PODIUM_FLASH_MESSAGE', 'flash-message');

    /**
     * Admin Menu
     */
    function podium_admin_menu()
    {
        add_options_page(
            'Podium',
            'Podium',
            'manage_options',
            PODIUM_KEY_CODE,
            'podium_options_page'
        );
    }

    /**
     * Options Page
     */
    function podium_options_page()
    {
        if (isset($_GET['settings-updated'])) {
            if (get_option('podium-script-code') == '') {
                include(PODIUM_DIR_PATH . 'views/flash-messages/warning.php');
            }
        }

        podium_add_page_template();
    }

    /**
     * Load custom styles.
     */
    function podium_admin_css()
    {
        $plugin_url = PODIUM_DIR_URL . 'assets/style.css';
        wp_enqueue_style('style', $plugin_url);
    }

    /**
     * Page Settings Link
     * @param $links
     * @return mixed
     */
    function podium_add_plugin_page_settings_link($links)
    {
        $plugin_page_url = admin_url('options-general.php?page=' . PODIUM_KEY_CODE);

        $links[] = '<a href="'. $plugin_page_url .'">' . __('Settings') . '</a>';
        return $links;
    }

    /**
     *  Load content template HTML.
     */
    function podium_add_page_template()
    {
        $podium_script_code = get_option('podium-script-code') != null ? get_option('podium-script-code') : '';
        $podium_installation = get_option('podium-installation') != null ? get_option('podium-installation') : false;

        include(PODIUM_DIR_PATH . 'views/content.php');
    }

    /**
     * Message Flash
     */
    function podium_flash_message()
    {
        if (! isset($_GET[PODIUM_FLASH_MESSAGE])) {
            return;
        }

        $key = sanitize_text_field($_GET[PODIUM_FLASH_MESSAGE]);

        $flash_actions = array(
            'success' => function() {
                include(PODIUM_DIR_PATH . 'views/flash-messages/success.php');
            },
            'error' => function() {
                include(PODIUM_DIR_PATH . 'views/flash-messages/error.php');
            },
            'warning' => function() {
                include(PODIUM_DIR_PATH . 'views/flash-messages/warning.php');
            }
        );

        $flash_actions[$key]();
    }

    /**
     * @param $code
     * @param $redirect_url
     */
    function podium_redirect_notices($code, $redirect_url)
    {
        if (! empty($code) ) {
            wp_safe_redirect(
                esc_url_raw(
                    add_query_arg(PODIUM_FLASH_MESSAGE, $code, $redirect_url)
                )
            );
        } else {
            wp_safe_redirect(
                esc_url_raw($redirect_url)
            );
        }
    }

    /**
     * Sets properties and redirects to the manually token request
     */
    function podium_request_manually_token()
    {
        $redirect_to = admin_url('/options-general.php?page=' . PODIUM_KEY_CODE);
        podium_handle_option('podium-script-code', '');
        podium_handle_option('podium-installation', false);
        podium_redirect_notices('error', $redirect_to);
    }

    /**
     * Sets properties and redirects to the success screen
     */
    function podium_set_sucess_result()
    {
        $redirect_to = admin_url('/options-general.php?page=' . PODIUM_KEY_CODE);
        podium_handle_option('podium-installation', true);
        podium_redirect_notices('success', $redirect_to);
    }

    /**
     * Executes submit actions
     */
    function podium_script_code_action()
    {

        try{
            $script_code = get_option('podium-script-code');

            ## SET PLUGIN INSTALLED
            if ( isset($_POST['connect']) ) {
                wp_cache_flush();
                $req_plugins = wp_remote_post(PODIUM_ENDPOINT . "/" . "plugins" . "/" . $script_code);
                $res_plugins = json_decode(wp_remote_retrieve_body($req_plugins));

                if ( $res_plugins === null && json_last_error() !== JSON_ERROR_NONE ) {
                    podium_request_manually_token();
                    return;
                }

                if ( isset($res_plugins->{'data'}) ) {
                    $data = $res_plugins->{'data'};

                    if ( isset($data->{'message'}) && $data->{'message'} !== 'Saved' ) {
                        podium_request_manually_token();
                        return;
                    }

                    podium_set_sucess_result();
                    return;
                }

                podium_request_manually_token();
                return;

            ## VALIDATE MANUALLY PLUGIN INSERTATION
            } else if ( isset($_POST['user-organization-token']) ) {
                $token = wp_is_uuid(strval($_POST['user-organization-token'])) == true ? $_POST['user-organization-token'] : '';

                if ( empty($token) ) { 
                    podium_request_manually_token();
                    return;
                 }
                
                podium_handle_option('podium-script-code', $token);
                podium_set_sucess_result();
                return;

            } else if ( isset($_POST['disconnect']) ) {
                $redirect_to = admin_url('/plugins.php');
                podium_handle_deactivation();
                podium_redirect_notices('', $redirect_to);
                return;

            } else {
                podium_request_manually_token();
                return;
            }

        }catch (Exception $exception) {
            podium_request_manually_token();
        }
    }

    /**
     * Include script to footer
     */
    function podium_add_widget_to_footer()
    {
        $podium_script_code = get_option('podium-script-code');

        if (! empty($podium_script_code)){
            include(PODIUM_DIR_PATH . 'views/script-code.php');
        }
    }

    ##################################################################################################
    ## ACTIONS
    ##################################################################################################

    ## MENU ##
    add_action('admin_menu', 'podium_admin_menu');

    add_action('admin_notices', 'podium_flash_message');

    ## OPTIONS ##
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'podium_add_plugin_page_settings_link');

    add_action('admin_print_styles', 'podium_admin_css');

    add_action('admin_post_podium_script_code', 'podium_script_code_action');

    add_action('wp_footer', 'podium_add_widget_to_footer');
}
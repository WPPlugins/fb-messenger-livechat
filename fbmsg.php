<?php
/*
Plugin Name: Facebook Messenger LiveChat
Plugin URI: https://wordpress.org/plugins/fb-messenger-livechat
Description: Live chat with your website users using Facebook Messenger.
Author: WidgetPack <contact@widgetpack.com>
Version: 1.2
Author URI: https://widgetpack.com
*/

require(ABSPATH . 'wp-includes/version.php');

define('FBMSG_VERSION',    '1.2');
define('FBMSG_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__ )), basename(__FILE__)));

function fbmsg_options() {
    return array(
        'fbmsg_title',
        'fbmsg_page',
        'fbmsg_timeline',
        'fbmsg_events',
        'fbmsg_pos',
        'fbmsg_lang',
    );
}

/*-------------------------------- Menu --------------------------------*/
function fbmsg_setting_menu() {
     add_submenu_page(
         'options-general.php',
         'Facebook Messenger',
         'Facebook Messenger',
         'moderate_comments',
         'fbmsg',
         'fbmsg_setting'
     );
}
add_action('admin_menu', 'fbmsg_setting_menu', 10);

function fbmsg_setting() {
    include_once(dirname(__FILE__) . '/fbmsg-setting.php');
}

/*-------------------------------- Links --------------------------------*/
function fbmsg_plugin_action_links($links, $file) {
    $plugin_file = basename(__FILE__);
    if (basename($file) == $plugin_file) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=fbmsg') . '">'.fbmsg_i('Settings').'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'fbmsg_plugin_action_links', 10, 2);

function fbmsg_deactivation() {
    fbmsg_delete_all_options();
}
register_deactivation_hook( __FILE__, 'fbmsg_deactivation');

/*-------------------------------- Footer --------------------------------*/
function fbmsg_static() {
    $settings_vars = array(
        'page' => esc_attr(get_option('fbmsg_page')),
        'timeline' => get_option('fbmsg_timeline') == true,
        'events' => get_option('fbmsg_events') == true,
    );
    wp_register_script('fbmsg-js', plugins_url('static/js/fbmsg.js', __FILE__), array('jquery'), '', false);
    wp_localize_script('fbmsg-js', 'settingsVars', $settings_vars );
    wp_enqueue_script('fbmsg-js');
    wp_register_style('fbmsg-css', plugins_url('static/css/fbmsg.css', __FILE__));
    wp_enqueue_style('fbmsg-css');
}
add_action('wp_enqueue_scripts', 'fbmsg_static' );

function fbmsg_output_footer() {

?><!-- Facebook Messenger Bar -->
<div id="fbmsg">
  <div class="fbmsg-badge">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Facebook_Messenger" x="0px" y="0px" width="322px" height="324px" viewBox="96 93 322 324" style="enable-background:new 96 93 322 324;" xml:space="preserve" class="fbmsg-badge-btn">
      <path style="fill:#0084FF;" d="M257,93c-88.918,0-161,67.157-161,150c0,47.205,23.412,89.311,60,116.807V417l54.819-30.273    C225.449,390.801,240.948,393,257,393c88.918,0,161-67.157,161-150S345.918,93,257,93z M273,295l-41-44l-80,44l88-94l42,44l79-44    L273,295z"/>
    </svg>
  </div>
  <div class="wp-sheet" style="display:none">
    <div class="wp-sheet-head">
      <div class="wp-sheet-head-inner"><?php echo esc_attr(get_option('fbmsg_title')); ?></div>
      <a href="#" class="wp-sheet-head-close">×</a>
    </div>
    <div class="wp-sheet-body"></div>
    <div class="wp-sheet-content">
      <div class="wp-sheet-content-inner">
        <div class="wp-sheet-content-part"></div>
      </div>
    </div>
    <div class="wp-sheet-footer"></div>
  </div>
</div>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php if (get_option('fbmsg_lang')=='') { echo "en_US"; } else { echo esc_attr(get_option('fbmsg_lang')); } ?>/sdk.js#xfbml=1&version=v2.6";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- End Facebook Messenger --><?php

}
add_action('wp_footer', 'fbmsg_output_footer');

/*-------------------------------- Helpers --------------------------------*/
function fbmsg_delete_all_options() {
    foreach (fbmsg_options() as $opt) {
        delete_option($opt);
    }
}

function fbmsg_enabled() {
    global $id, $post;

    if (get_option('fbmsg_active') === '0'){ return false; }

    return true;
}

function fbmsg_i($text, $params=null) {
    if (!is_array($params)) {
        $params = func_get_args();
        $params = array_slice($params, 1);
    }
    return vsprintf(__($text, 'fbmsg'), $params);
}

if (!function_exists('esc_html')) {
function esc_html( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    return apply_filters( 'esc_html', $safe_text, $text );
}
}

if (!function_exists('esc_attr')) {
function esc_attr( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    return apply_filters( 'attribute_escape', $safe_text, $text );
}
}

/**
 * JSON ENCODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode
 * to use php_json_encode in its stead
 * Works on iteratable objects as well - stdClass is iteratable, so all WP objects are gonna be iteratable
 */
if(!function_exists('cf_json_encode')) {
    function cf_json_encode($data) {

        // json_encode is sending an application/x-javascript header on Joyent servers
        // for some unknown reason.
        return cfjson_encode($data);
    }

    function cfjson_encode_string($str) {
        if(is_bool($str)) {
            return $str ? 'true' : 'false';
        }

        return str_replace(
            array(
                '\\'
                , '"'
                //, '/'
                , "\n"
                , "\r"
            )
            , array(
                '\\\\'
                , '\"'
                //, '\/'
                , '\n'
                , '\r'
            )
            , $str
        );
    }

    function cfjson_encode($arr) {
        $json_str = '';
        if (is_array($arr)) {
            $pure_array = true;
            $array_length = count($arr);
            for ( $i = 0; $i < $array_length ; $i++) {
                if (!isset($arr[$i])) {
                    $pure_array = false;
                    break;
                }
            }
            if ($pure_array) {
                $json_str = '[';
                $temp = array();
                for ($i=0; $i < $array_length; $i++) {
                    $temp[] = sprintf("%s", cfjson_encode($arr[$i]));
                }
                $json_str .= implode(',', $temp);
                $json_str .="]";
            }
            else {
                $json_str = '{';
                $temp = array();
                foreach ($arr as $key => $value) {
                    $temp[] = sprintf("\"%s\":%s", $key, cfjson_encode($value));
                }
                $json_str .= implode(',', $temp);
                $json_str .= '}';
            }
        }
        else if (is_object($arr)) {
            $json_str = '{';
            $temp = array();
            foreach ($arr as $k => $v) {
                $temp[] = '"'.$k.'":'.cfjson_encode($v);
            }
            $json_str .= implode(',', $temp);
            $json_str .= '}';
        }
        else if (is_string($arr)) {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        else if (is_numeric($arr)) {
            $json_str = $arr;
        }
        else if (is_bool($arr)) {
            $json_str = $arr ? 'true' : 'false';
        }
        else {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        return $json_str;
    }
}
?>
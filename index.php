<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Plugin Name: Chat Forms
 * Plugin URI: https://chat-forms.com
 * Description: Adds embedded Chat Forms to a WordPress website directly into your posts, pages or sidebar.
 * Version: 1.0.2
 * Build: 1.0.2
 * Last Modified:  09/28/2019
 * Author: Yiftach Haramati
 * Author URI: http://www.inter-act.io
 * License: GPL
 * 
 *
 * (c) 2019 by Yiftach Haramati
 *
 * @author Yiftach Haramati <yiftach.haramati@gmail.com>
 * @package wpCForm
 * @subpackage admin
 * @version 1.0.2
 * @lastmodified 09/28/2019
 * @lastmodifiedby yiftachh
 * Text Domain: wpcform
 *
 */

define('WPCFORM_VERSION', '1.0.2') ;

require_once('wpcform-core.php') ;
require_once('wpcform-post-type.php') ;

// Use the register_activation_hook to set default values
register_activation_hook(__FILE__, 'wpcform_activate');
register_deactivation_hook(__FILE__, 'wpcform_deactivate');

// Use the init action
add_action('init', 'wpcform_init' );

// Use the admin_menu action to add options page
add_action('admin_menu', 'wpcform_admin_menu');

// Use the admin_init action to add register_setting
add_action('admin_init', 'wpcform_admin_init' );

?>

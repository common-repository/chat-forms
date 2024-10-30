<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * CForm functions.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpCForm
 * @subpackage functions
 * @version $Revision$
 * @lastmodified $Date$
 * @lastmodifiedby $Author$
 *
 */

// Filesystem path to this plugin.
define('WPCFORM_PREFIX', 'wpcform_') ;
define('WPCFORM_PATH', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__))) ;
define('WPCFORM_EMAIL_FORMAT_HTML', 'html') ;
define('WPCFORM_EMAIL_FORMAT_PLAIN', 'plain') ;
define('WPCFORM_CONFIRM_AJAX', 'ajax') ;
define('WPCFORM_CONFIRM_LIGHTBOX', 'lightbox') ;
define('WPCFORM_CONFIRM_REDIRECT', 'redirect') ;
define('WPCFORM_CONFIRM_NONE', 'none') ;
define('WPCFORM_LOG_ENTRY_META_KEY', '_wpcform_log_entry') ;
define('WPCFORM_FORM_TRANSIENT', 'wpcform_form_response') ;
define('WPCFORM_FORM_TRANSIENT_EXPIRE', 5) ;

// i18n plugin domain
define( 'WPCFORM_I18N_DOMAIN', 'wpcform' );

/**
 * Initialise the internationalisation domain
 */
$is_wpcform_i18n_setup = false ;
function wpcform_init_i18n()
{
	global $is_wpcform_i18n_setup;

	if ($is_wpcform_i18n_setup == false) {
		load_plugin_textdomain(WPCFORM_I18N_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/') ;
		$is_wpcform_i18n_setup = true;
	}
}

//  Need the plugin options to initialize debug
$wpcform_options = wpcform_get_plugin_options() ;

//  Disable fsockopen transport?
if ($wpcform_options['fsockopen_transport'] == 1)
    add_filter('use_fsockopen_transport', '__return_false') ;

//  Disable streams transport?
if ($wpcform_options['streams_transport'] == 1)
    add_filter('use_streams_transport', '__return_false') ;

//  Disable curl transport?
if ($wpcform_options['curl_transport'] == 1)
    add_filter('use_curl_transport', '__return_false') ;

//  Disable local ssl verify?
if ($wpcform_options['local_ssl_verify'] == 1)
    add_filter('https_local_ssl_verify', '__return_false') ;

//  Disable ssl verify?
if ($wpcform_options['ssl_verify'] == 1)
    add_filter('https_ssl_verify', '__return_false') ;

//  Change the HTTP Time out?
if ($wpcform_options['http_request_timeout'] == 1)
{
    if (is_int($wpcform_options['http_request_timeout_value'])
        || ctype_digit($wpcform_options['http_request_timeout_value']))
        add_filter('http_request_timeout', 'wpcform_http_request_timeout') ;
}

/**
 * Optional filter to change HTTP Request Timeout
 *
 */
function wpcform_http_request_timeout($timeout) {
    $wpcform_options = wpcform_get_plugin_options() ;
    return $wpcform_options['http_request_timeout'] ;
}


/**
 * wpcform_init()
 *
 * Init actions to enable shortcodes.
 *
 * @return null
 */
function wpcform_init()
{
    $wpcform_options = wpcform_get_plugin_options() ;

    if ($wpcform_options['sc_posts'] == 1)
    {
        add_shortcode('cform', array('wpCForm', 'cform_sc')) ;
        add_shortcode('wpcform', array('wpCForm', 'wpcform_sc')) ;
    }

    if ($wpcform_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode') ;

    //add_filter('the_content', 'wpautop');
    //add_filter('the_content', 'wpcform_the_content');
    //add_action('template_redirect', 'wpcform_head') ;
    add_action( 'wp_enqueue_scripts', 'wpcform_head' );
    add_action('wp_footer', 'wpcform_footer') ;
}

/**
 * Filter to render a Chat Form in preview
 *
 * @param $content string post content
 * @since v0.46
 */
function wpcform_set_preview($post){
    if ( ! is_object( $post ) ) {
        return $post;
    }

    $preview = wp_get_post_autosave( $post->ID );
    if ( ! is_object( $preview ) ) {
        return $post;
    }

    $preview = sanitize_post( $preview );

    if($post->post_type == 'wpcform'){
        $post->post_content = $preview->post_content."[wpcform id='$post->ID']";
    }else{
        $post->post_content = $preview->post_content;
    }
    
    $post->post_title   = $preview->post_title;
    $post->post_excerpt = $preview->post_excerpt;

    add_filter( 'get_the_terms', '_wp_preview_terms_filter', 10, 3 );
    add_filter( 'get_post_metadata', '_wp_preview_post_thumbnail_filter', 10, 3 );

    return $post;
}

add_filter( 'the_preview', 'wpcform_set_preview', 999 );

/**
 * Filter to render a Chat Form when a public CPT URL is
 * requested.  The filter will inject the proper shortcode into
 * the content which is then in turn processed by WordPress to
 * render the form as a regular short code would be processed.
 *
 * @param $content string post content
 * @since v0.46
 */
function wpcform_the_content($content)
{
    return (WPCFORM_CPT_FORM == get_post_type(get_the_ID())) ?
        sprintf('[wpcform id=\'%s\']', get_the_ID()) : $content ;
}


/**
 * Register and add settings
 */
function wpcform_save_chat_form_id(){
    $form_id = isset($_POST['chat_form_id'])? sanitize_text_field($_POST['chat_form_id']): '';
    $pid = isset($_POST['pid'])? intval($_POST['pid']): 0;
    
    if($pid && !empty($form_id)){
        update_post_meta($pid, 'wpcform_form_id', $form_id);
    }
    
    wp_send_json( array(
        'status' => 'success',
        'form_id' => $form_id
    ), 200 );
}
add_action( 'wp_ajax_wpcform_save_chat_form_id', 'wpcform_save_chat_form_id' );

/**
 * Returns the default options for wpCForm.
 *
 * @since wpCForm 0.11
 */
function wpcform_get_default_plugin_options()
{
	$default_plugin_options = array(
        'sc_posts' => 1
       ,'sc_widgets' => 1
       ,'default_css' => 1
       ,'custom_css' => 0
       ,'custom_css_styles' => ''
       ,'donation_message' => 0
       ,'curl_transport_missing_message' => 0
       ,'email_format' => WPCFORM_EMAIL_FORMAT_PLAIN
       ,'http_api_timeout' => 5
       ,'form_submission_log' => 0
       ,'disable_html_filtering' => 0
       ,'browser_check' => 0
       ,'enable_debug' => 0
       ,'serialize_post_vars' => 0
       ,'bcc_blog_admin' => 1
       ,'fsockopen_transport' => 0
       ,'streams_transport' => 0
       ,'curl_transport' => 0
       ,'local_ssl_verify' => 0
       ,'ssl_verify' => 0
       ,'http_request_timeout' => 0
       ,'http_request_timeout_value' => 30
       ,'required_text_override' => __('Required', WPCFORM_I18N_DOMAIN)
       ,'submit_button_text_override' => __('Submit', WPCFORM_I18N_DOMAIN)
       ,'back_button_text_override' => __('Back', WPCFORM_I18N_DOMAIN)
       ,'continue_button_text_override' => __('Continue', WPCFORM_I18N_DOMAIN)
       ,'radio_buttons_text_override' => __('Mark only one oval.', WPCFORM_I18N_DOMAIN)
       ,'radio_buttons_other_text_override' => __('Other:', WPCFORM_I18N_DOMAIN)
       ,'check_boxes_text_override' => __('Check all that apply.', WPCFORM_I18N_DOMAIN)
	) ;

	return apply_filters('wpcform_default_plugin_options', $default_plugin_options) ;
}

/**
 * Returns the options array for the wpCForm plugin.
 *
 * @since wpCForm 0.11
 */
function wpcform_get_plugin_options()
{
    //  Get the default options in case anything new has been added
    $default_options = wpcform_get_default_plugin_options() ;

    //  If there is nothing persistent saved, return the default

    if (get_option('wpcform_options') === false)
        return $default_options ;

    //  One of the issues with simply merging the defaults is that by
    //  using checkboxes (which is the correct UI decision) WordPress does
    //  not save anything for the fields which are unchecked which then
    //  causes wp_parse_args() to incorrectly pick up the defaults.
    //  Since the array keys are used to build the form, we need for them
    //  to "exist" so if they don't, they are created and set to null.

    $plugin_options = wp_parse_args(get_option('wpcform_options'), $default_options) ;

    //  If the array key doesn't exist, it means it is a check box option
    //  that is not enabled so the array element(s) needs to be set to zero.

    //foreach ($default_options as $key => $value)
    //    if (!array_key_exists($key, $plugin_options)) $plugin_options[$key] = 0 ;

    return $plugin_options ;
}

/**
 * Returns the options array for the wpCForm plugin.
 *
 * @param input mixed input to validate
 * @return input mixed validated input
 * @since wpCForm 0.58-beta-4
 *
 */
function wpcform_options_validate($input)
{
    if (isset($_POST['action']) && 'update' === $_POST['action'])
    {
        // Get the options array defined for the form
        $options = wpcform_get_default_plugin_options();

        //  Loop through all of the default options
        foreach ($options as $key => $value)
        {
            //  If the default option doesn't exist, which it
            //  won't if it is a checkbox, default the value to 0
            //  which means the checkbox is turned off.

            if (!array_key_exists($key, $input))
                $input[$key] = 0 ;
        }
    }

    //  Was the Reset button pushed?
    if (__('Reset', WPCFORM_I18N_DOMAIN) === $_POST['Submit'])
        $input = wpcform_get_default_plugin_options();

    return $input ;
}

/**
 * wpcform_admin_menu()
 *
 * Adds admin menu page(s) to the Dashboard.
 *
 * @return null
 */
function wpcform_admin_menu()
{
    wpcform_init_i18n() ;
    require_once(WPCFORM_PATH . '/wpcform-options.php') ;

    $wpcform_options_page = add_options_page(
        __('Chat Forms', WPCFORM_I18N_DOMAIN),
        __('Chat Forms', WPCFORM_I18N_DOMAIN),
        'manage_options', 'wpcform-options.php', 'wpcform_options_page') ;
    add_action('admin_footer-'.$wpcform_options_page, 'wpcform_options_admin_footer') ;
    add_action('admin_print_scripts-'.$wpcform_options_page, 'wpcform_options_print_scripts') ;
    add_action('admin_print_styles-'.$wpcform_options_page, 'wpcform_options_print_styles') ;

    add_submenu_page(
        'edit.php?post_type=wpcform',
        __('Chat Forms Submission Log', WPCFORM_I18N_DOMAIN), /*page title*/
        __('Form Submission Log', WPCFORM_I18N_DOMAIN), /*menu title*/
        'manage_options', /*roles and capabiliyt needed*/
        'wpcform-entry-log-page',
        'wpcform_entry_log_page' /*replace with your own function*/
    );
}

function wpcform_entry_log_page()
{
    require_once('wpcform-logging.php') ;
}



/**
 * wpcform_admin_init()
 *
 * Init actions for the Dashboard interface.
 *
 * @return null
 */
function wpcform_admin_init()
{
    register_setting('wpcform_options', 'wpcform_options', 'wpcform_options_validate') ;
    wpcform_routine_maintenance() ;
}

/**
 * wpcform_activate()
 *
 * Adds the default options so WordPress options are
 * configured to a default state upon plugin activation.
 *
 * @return null
 */
function wpcform_activate()
{
    wpcform_init_i18n() ;
    add_option('wpcform_options', wpcform_get_default_plugin_options()) ;
    add_filter('widget_text', 'do_shortcode') ;
    flush_rewrite_rules() ;
}

/**
 * wpcform_deactivate()
 *
 * Adds the default options so WordPress options are
 * configured to a default state upon plugin activation.
 *
 * @return null
 */
function wpcform_deactivate()
{
    flush_rewrite_rules() ;
}

/**
 * wpCForm class definition
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @access public
 * @see RenderChatForm()
 * @see ConstructChatForm()
 */
class wpCForm
{
    /**
     * Property to hold Browser Check response
     */
    static $browser_check ;

    /**
     * Property to hold Google Form Response
     */
    static $response ;

    /**
     * Property to hold Google Form Post Error
     */
    static $post_error = false ;

    /**
     * Property to hold Google Form Post Status
     */
    static $posted = false ;

    /**
     * Property to indicate Javascript output state
     */
    static $wpcform_js = false ;

    /**
     * Property to hold global plugin Javascript output
     */
    static $wpcform_plugin_js = '' ;

    /**
     * Property to hold form specific Javascript output
     */
    static $wpcform_form_js = array() ;

    /**
     * Property to store Javascript output in footer
     */
    static $wpcform_footer_js = '' ;

    /**
     * Property to store state of Javascript output in footer
     */
    static $wpcform_footer_js_printed = false ;

    /**
     * Property to indicate CSS output state
     */
    static $wpcform_css = false ;

    /**
     * Property to indicate Debug output state
     */
    static $wpcform_debug = false ;

    /**
     * Property to store unique form id
     */
    static $wpcform_form_id = 1 ;

    /**
     * Property to store unique form id
     */
    static $wpcform_submitted_form_id = null ;

    /**
     * Property to user email address to send email confirmation to
     */
    static $wpcform_user_sendto = null ;

    /**
     * Property to store jQuery Validation messages
     */
    //static $vMsgs_js = array() ;

    /**
     * Property to store jQuery Validation rules
     */
    //static $vRules_js = array() ;

    /**
     * Property to store the various options which control the
     * HTML manipulation and generation.  These array keys map
     * to the meta data stored with the wpCForm Custom Post Type.
     *
     * The Unite theme from Paralleus mucks with the submit buttons
     * which breaks the ability to submit the form to Google correctly.
     * This "special" hack will "unbreak" the submit buttons.
     *
     */
    protected static $options = array(
        'form'           => false,           // Google Form URL
        'uid'            => '',              // Unique identifier string to prepend to id and name attributes
        'confirm'        => null,            // Custom confirmation page URL to redirect to
        'alert'          => null,            // Optional Alert Message
        'class'          => 'wpcform',       // Container element's custom class value
        'br'             => 'off',           // Insert <br> tags between labels and inputs
        'columns'        => '1',             // Number of columns to render the form in
        'minvptwidth'    => '0',             // Minimum viewport width for columnization, 0 to ignore
        'columnorder'    => 'left-to-right', // Order to show columns - Left to Right or Right to Left
        'css_suffix'     => null,            // Add suffix character(s) to all labels
        'css_prefix'     => null,            // Add suffix character(s) to all labels
        'readonly'       => 'off',           // Set all form elements to disabled
        'title'          => 'on',            // Remove the H1 element(s) from the Form
        'maph1h2'        => 'off',           // Map H1 element(s) on the form to H2 element(s)
        'email'          => 'off',           // Send an email confirmation to blog admin on submission
        'sendto'         => null,            // Send an email confirmation to a specific address on submission
        'user_email'     => 'off',           // Send an email confirmation to user on submission
        'user_sendto'    => null,            // Send an email confirmation to a specific address on submission
        'results'        => false,           // Results URL
        'validation'     => 'off',           // Use jQuery validation for required fields
        'unitethemehack' => 'off',           // Send an email confirmation to blog admin on submission
        'style'          => null,            // How to present the custom confirmation after submit
        'use_transient'  => false,           // Toogles the use of WP Transient API for form caching
        'transient_time' => WPCFORM_FORM_TRANSIENT_EXPIRE,  // Sets how long (in minutes) the forms will be cached using WP Transient
    ) ;

    /**
     * Constructor
     */
    //function wpCForm()
    //{
    //    // empty for now - this syntax is deprecated in PHP7!
    //}

    /**
     * 'cform' short code handler
     *
     * @since 0.1
     * @deprecated
     * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
     */
    static function cform_sc($options)
    {
        wpcform_enqueue_scripts() ;
        if (self::ProcessShortCodeOptions($options))
            return self::ConstructChatForm() ;
        else
            return sprintf('<div class="wpcform-google-error cform-google-error">%s</div>',
               __('Unable to process Chat Form short code.', WPCFORM_I18N_DOMAIN)) ;
    }

    /**
     * 'wpcform' short code handler
     *
     * @since 1.0
     * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
     */
    static function wpcform_sc($options)
    {
        wpcform_enqueue_scripts() ;
        if (self::ProcessWpCFormCPT($options))
            return self::ConstructChatForm() ;
        else
            return sprintf('<div class="wpcform-google-error cform-google-error">%s</div>',
               __('Unable to process Chat Form short code.', WPCFORM_I18N_DOMAIN)) ;
    }

    /**
     * wpcform_calc2() - perform math form CAPTCHA
     *
     * @since 0.94
     * @see https://wordpress.org/support/topic/warning-about-eval/#post-9941118
     */
    static function wpcform_calc2( $a, $op, $b ) {
        switch( $op ){
            case '+': return $a + $b;
            case '-': return $a - $b;
            case '*': return $a * $b;
        }
        return null;
    }

    /**
     * Function ProcessShortcode loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @see cform_sc
     * @return boolean - abort processing when false
     */
    static function ProcessShortCodeOptions($options)
    {
        //  Property short cut
        $o = &self::$options ;

        //  Override default options based on the short code attributes

        foreach ($o as $key => $value)
        {
            if (array_key_exists($key, $options))
                $o[$key] = $options[$key] ;
        }

        //  If a confirm has been supplied but a style has not, default to redirect style.

        if (!array_key_exists('confirm', $o) || is_null($o['confirm']) || empty($o['confirm']))
        {
            $o['style'] = WPCFORM_CONFIRM_NONE ;
        }
        elseif ((array_key_exists('confirm', $o) && !array_key_exists('style', $o)) ||
            (array_key_exists('confirm', $o) && array_key_exists('style', $o) && $o['style'] == null))
        {
            $o['style'] = WPCFORM_CONFIRM_REDIRECT ;
        }

        //  Validate columns - make sure it is a reasonable number
 
        if (is_numeric($o['columns']) && ($o['columns'] > 1) && ($o['columns'] == round($o['columns'])))
            $o['columns'] = (int)$o['columns'] ;
        else
            $o['columns'] = 1 ;

        if (WPCFORM_DEBUG) wpcform_whereami(__FILE__, __LINE__, 'ProcessShortCodeOptions') ;
        if (WPCFORM_DEBUG) wpcform_preprint_r($o) ;

        //  Have to have a form URL otherwise the short code is meaningless!

        return (!empty($o['form'])) ;
    }

    /**
     * Function ProcessShortcode loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @see RenderChatForm
     * @return boolean - abort processing when false
     */
    static function ProcessWpCFormCPT($options)
    {
        //  Property short cut
        $o = &self::$options ;

        //  Id?  Required - make sure it is reasonable.

        if ($options['id'])
        {
            $o['id'] = $options['id'] ;

            //  Make sure we didn't get something nonsensical
            if (is_numeric($o['id']) && ($o['id'] > 0) && ($o['id'] == round($o['id'])))
                $o['id'] = (int)$o['id'] ;
            else
                return false ;
        }
        else
            return false ;

        if (array_key_exists('uid', $options)) $o['uid'] = $options['uid'] ;

        // get current form meta data fields

        $fields = array_merge(
            wpcform_primary_meta_box_content(true),
            wpcform_notification_meta_box_content(true),
            wpcform_reporting_meta_box_content(true)
        ) ;

        foreach ($fields as $field)
        {
            //  Only show the fields which are not hidden
            if ($field['type'] !== 'hidden')
            {
                // get current post meta data
                $meta = get_post_meta($o['id'], $field['id'], true);

                //  If a meta value is found, strip off the prefix
                //  from the meta key so the id matches the options
                //  used by the form rendering method.

                if ($meta)
                    $o[substr($field['id'], strlen(WPCFORM_PREFIX))] = $meta ;
            }
        }

        //  Validate columns - make sure it is a reasonable number
 
        if (is_numeric($o['columns']) && ($o['columns'] > 1) && ($o['columns'] == round($o['columns'])))
            $o['columns'] = (int)$o['columns'] ;
        else
            $o['columns'] = 1 ;

//        if (WPCFORM_DEBUG) wpcform_whereami(__FILE__, __LINE__, 'ProcessWpCFormCPT') ;
//        if (WPCFORM_DEBUG) wpcform_preprint_r($o) ;

        //  Have to have a form URL otherwise the short code is meaningless!

        return (!empty($o['form'])) ;
    }


    /**
     * Function ConstructChatForm loads chat form from shortcode,
     * @see RenderChatForm
     */
    static function ConstructChatForm(){
        $o = &self::$options ;
        $wpcform_form_id = '';

        if(isset($o['form_id']) && $o['form_id']){
            $wpcform_form_id = $o['form_id'];
        }else{
            $wpcform_form_id_meta = get_post_meta($o['id'], 'wpcform_form_id', true);
            $wpcform_form_id = $wpcform_form_id_meta? $wpcform_form_id_meta: '';
        }

        if($wpcform_form_id){
            $url = "https://chat-forms.com/forms/$wpcform_form_id/index.html?embedded=true";
            if ($o['uid']) {
                $html = '<iframe id="wpcform-ifm-' . $o['uid'] . '" class="wpcform-ifm" src="'. esc_url($url) . '" width="640" height="1065" frameborder="0" marginheight="0" marginwidth="0"></iframe>';
            } else {
                $html = '<iframe id="wpcform-ifm" class="wpcform-ifm" src="' . esc_url($url) . '" width="640" height="1065" frameborder="0" marginheight="0" marginwidth="0"></iframe>';
            }
        }else{
            $html = esc_html__('No Chat ID found!', WPCFORM_I18N_DOMAIN); 
        } 

        return $html;
    }


    /**
     * Get Page URL
     *
     * @return string
     */
    function GetPageURL()
    {
        global $pagenow ;
        $pageURL = 'http' ;

        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on') $pageURL .= 's' ;

        $pageURL .= '://' ;

        if ($_SERVER['SERVER_PORT'] != '80')
            $pageURL .= $_SERVER['SERVER_NAME'] .
                ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'] ;
        else
            $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ;

        return $pageURL ;
    }
            
    /**
     * WordPress Shortcode handler.
     *
     * @return HTML
     */
    function RenderChatForm($atts) {
        /*
        $params = shortcode_atts(array(
            'form'           => false,                   // Google Form URL
            'confirm'        => false,                   // Custom confirmation page URL to redirect to
            'alert'          => null,                    // Optional Alert Message
            'class'          => 'wpcform',                 // Container element's custom class value
            'br'             => 'off',                   // Insert <br> tags between labels and inputs
            'columns'        => '1',                     // Number of columns to render the form in
            'suffix'         => null,                    // Add suffix character(s) to all labels
            'prefix'         => null,                    // Add suffix character(s) to all labels
            'readonly'       => 'off',                   // Set all form elements to disabled
            'title'          => 'on',                    // Remove the H1 element(s) from the Form
            'maph1h2'        => 'off',                   // Map H1 element(s) on the form to H2 element(s)
            'email'          => 'off',                   // Send an email confirmation to blog admin on submission
            'sendto'         => null,                    // Send an email confirmation to a specific address on submission
            'validation'     => 'off',                   // Use jQuery validation for required fields
            'unitethemehack' => 'off',                   // Send an email confirmation to blog admin on submission
            'style'          => WPCFORM_CONFIRM_REDIRECT // How to present the custom confirmation after submit
        ), $atts) ;
         */
        $params = shortcode_atts(wpCForm::$options) ;

        return wpCForm::ConstructChatForm($params) ;
    }
}

/**
 * wpcform_head()
 *
 * WordPress header actions
 * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
 */
function wpcform_head()
{
    //  Need to enqueue jQuery or inline jQuery script will fail
    //  Everything else is enqueued if/when the shortcode is processed
    wp_enqueue_script('jquery') ;
    wpcform_register_scripts() ;
    wpcform_enqueue_styles() ;
}

/**
 * wpcform_register_scripts()
 *
 * WordPress script registration for wpcform
 */
function wpcform_register_scripts()
{
    //  Load the jQuery Validate from the Microsoft CDN, it isn't
    //  available from the Google CDN or I'd load it from there!

    if (defined('SCRIPT_DEBUG')) {
        wp_register_script('jquery-validate',
            plugin_basename(dirname(__FILE__).'/js/jquery.validate.js'),
            array('jquery'), false, true) ;
    } else {
        wp_register_script('jquery-validate',
            plugin_basename(dirname(__FILE__). '/js/jquery.validate.min.js'),
            array('jquery'), false, true) ;
    }

    //  Load the jQuery Columnizer script from the plugin
    wp_register_script('jquery-columnizer',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/jquery.columnizer.js')),
        array('jquery'), false, true) ;

    //  Load the Chat Forms jQuery Validate script from the plugin
    wp_register_script('wpcform-jquery-validate',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/wpcform.js')),
        array('jquery', 'jquery-validate'), false, true) ;
}

/**
 * wpcform_enqueue_scripts()
 *
 * WordPress script enqueuing for wpcform
 */
function wpcform_enqueue_scripts()
{
    //  wpCForm needs jQuery!
    //wp_enqueue_script('jquery') ;
    
    //  Enqueue the jQuery Validate script
    wp_enqueue_script('jquery-validate') ;

    //  Enqueue the jQuery Columnizer script
    wp_enqueue_script('jquery-columnizer') ;

    //  Enqueue the Chat Forms jQuery Validate script from the plugin
    wp_enqueue_script('wpcform-jquery-validate') ;
    wp_localize_script('wpcform-jquery-validate', 'wpcform_script_vars', array(
        'required' => __('This field is required.', WPCFORM_I18N_DOMAIN),
        'remote' => __('Please fix this field.', WPCFORM_I18N_DOMAIN),
        'email' => __('Please enter a valid email address.', WPCFORM_I18N_DOMAIN),
        'url' => __('Please enter a valid URL.', WPCFORM_I18N_DOMAIN),
        'date' => __('Please enter a valid date.', WPCFORM_I18N_DOMAIN),
        'dateISO' => __('Please enter a valid date (ISO).', WPCFORM_I18N_DOMAIN),
        'number' => __('Please enter a valid number.', WPCFORM_I18N_DOMAIN),
        'digits' => __('Please enter only digits.', WPCFORM_I18N_DOMAIN),
        'creditcard' => __('Please enter a valid credit card number.', WPCFORM_I18N_DOMAIN),
        'equalTo' => __('Please enter the same value again.,', WPCFORM_I18N_DOMAIN),
        'accept' => __('Please enter a value with a valid extension.', WPCFORM_I18N_DOMAIN),
        'maxlength' => __('Please enter no more than {0} characters.', WPCFORM_I18N_DOMAIN),
        'minlength' => __('Please enter at least {0} characters.', WPCFORM_I18N_DOMAIN),
        'rangelength' => __('Please enter a value between {0} and {1} characters long.', WPCFORM_I18N_DOMAIN),
        'range' => __('Please enter a value between {0} and {1}.', WPCFORM_I18N_DOMAIN),
        'max' => __('Please enter a value less than or equal to {0}.', WPCFORM_I18N_DOMAIN),
        'min' => __('Please enter a value greater than or equal to {0}.', WPCFORM_I18N_DOMAIN),
        'regex' => __('Please enter a value which matches {0}.', WPCFORM_I18N_DOMAIN)
    )) ;
}

/**
 * wpcform_enqueue_styles()
 *
 * WordPress style enqueuing for wpcform
 */
function wpcform_enqueue_styles()
{
    $wpcform_options = wpcform_get_plugin_options() ;

    //  Load default cForm CSS?
    if ($wpcform_options['default_css'] == 1)
    {
        wp_enqueue_style('wpcform-css',
            plugins_url(plugin_basename(dirname(__FILE__) . '/css/wpcform.css'))) ;
    }
}

/**
 * wpcform_footer()
 *
 * WordPress footer actions
 *
 */
function wpcform_footer()
{
    //  Output the generated jQuery script as part of the footer

    if (!wpCForm::$wpcform_footer_js_printed)
    {
        print wpCForm::$wpcform_footer_js ;
        wpCForm::$wpcform_footer_js_printed = true ;
    }
}

function wpcform_pre_http_request($args)
{
    error_log(sprintf('%s::%s -->  %s', basename(__FILE__), __LINE__, print_r($args, true))) ;
    return $args ;
}

//add_filter('pre_http_request', 'wpcform_pre_http_request') ;


function wpcform_http_api_transports($args)
{
    $args = array('fsockopen') ;
    error_log(sprintf('%s::%s -->  %s', basename(__FILE__), __LINE__, print_r($args, true))) ;
    return $args ;
}

//add_filter('http_api_transports', 'wpcform_http_api_transports') ;

function wpcform_curl_transport_missing_notice()
{
    $wpcform_options = wpcform_get_plugin_options() ;

    //  Skip check if disabled in settings
    if ($wpcform_options['curl_transport_missing_message']) return ;

    //  Test for cURL transport

    $t = new WP_Http() ;

    if (strtolower($t->_get_first_available_transport('')) != 'wp_http_curl')
    {
?>
<div class="update-nag">
<?php
        _e('The <a href="http://codex.wordpress.org/HTTP_API">WordPress HTTP API</a> cURL transport was not detected.  The Chat Forms plugin may not operate correctly.', WPCFORM_I18N_DOMAIN) ;
?>
<br />
<small>
<?php
        printf(__('This notification may be hidden via a setting on the <a href="%s">Chat Forms settings page</a>.',
            WPCFORM_I18N_DOMAIN), admin_url('options-general.php?page=wpcform-options.php')) ;
?>
</small>
</div>
<?php
    }

    unset ($t) ;
}

add_action( 'admin_notices', 'wpcform_curl_transport_missing_notice' );

//
//  EOL Notice - show waring before 8/22/18, error after.
//
function wpcform_eol_notification_warning() {

    if ( strtotime('now') > strtotime('2018-08-22') )
	    $class = 'notice notice-error is-dismissible';
    else
	    $class = 'notice notice-warning is-dismissible';

	$message = __( 'Google has announced plans to <a href="https://gsuiteupdates.googleblog.com/2018/07/migration-to-new-google-forms-ui_19.html">remove the Google Forms downgrade option</a>.  The <a href="https://wordpress.org/plugins/wpcform/">Google Forms plugin</a> may no longer work after August 22, 2018.  The <a href="https://wordpress.org/plugins/wpcform/">Google Forms plugin</a> does not work with the new version of Google Forms.', WPCFORM_I18N_DOMAIN );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
}
//add_action( 'admin_notices', 'wpcform_eol_notification_warning' );

?>

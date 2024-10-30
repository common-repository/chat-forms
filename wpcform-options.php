<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * CForm options.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mpwalsh8@gmail.com>
 * @package wpCForm
 * @subpackage options
 * @version $Revision$
 * @lastmodified $Date$
 * @lastmodifiedby $Author$
 *
 */

/**
 * wpcform_options_admin_footer()
 *
 * Hook into Admin head when showing the options page
 * so the necessary jQuery script that controls the tabs
 * is executed.
 *
 * @return null
 */
function wpcform_options_admin_footer()
{
    ?>
    <!-- Setup jQuery Tabs -->
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery("#wpcform-tabs").tabs();
        });
    </script>
<?php
} /* function wpcform_options_admin_footer() */

/**
 * wpcform_options_print_scripts()
 *
 * Hook into Admin Print Scripts when showing the options page
 * so the necessary scripts that controls the tabs are loaded.
 *
 * @return null
 */
function wpcform_options_print_scripts()
{
    //  Need to load jQuery UI Tabs to make the page work!

    wp_enqueue_script('jquery-ui-tabs');
}

/**
 * wpcform_options_print_styles()
 *
 * Hook into Admin Print Styles when showing the options page
 * so the necessary style sheets that control the tabs are
 * loaded.
 *
 * @return null
 */
function wpcform_options_print_styles()
{
    //  Need the jQuery UI CSS to make the tabs look correct.
    //  Load them from Google - should not be an issue since
    //  this plugin is all about consuming Google content!

    wp_enqueue_style('xtra-jquery-ui-css', plugins_url( plugin_basename(dirname(__FILE__) . '/css/jquery-ui.css')));
}

/**
 * wpcform_options_page()
 *
 * Build and render the options page.
 *
 * @return null
 */
function wpcform_options_page()
{
    ?>
    <div class="wrap">

        <?php
            if (function_exists('screen_icon') && version_compare(get_bloginfo('version'), '3.8', '<=')) screen_icon();
            ?>
        <h2><?php _e('WordPress Chat Form Plugin Settings'); ?></h2>
        <?php
            $wpcform_options = wpcform_get_plugin_options();
            if (!$wpcform_options['donation_message']) {
                ?>
            <small><?php printf(__('Please consider making a <a href="%s" target="_blank">PayPal donation</a> if you find this plugin useful.', WPCFORM_I18N_DOMAIN), 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC'); ?></small>
        <?php
            }
            ?>
        <br /><br />
        <div class="container">
            <div id="wpcform-tabs">
                <ul>
                    <li><a href="#wpcform-tabs-1"><?php _e('Options', WPCFORM_I18N_DOMAIN); ?></a></li>
                    <li><a href="#wpcform-tabs-2"><?php _e('Advanced Options', WPCFORM_I18N_DOMAIN); ?></a></li>
                    <li><a href="#wpcform-tabs-3"><?php _e('FAQs', WPCFORM_I18N_DOMAIN); ?></a></li>
                    <li><a href="#wpcform-tabs-4"><?php _e('Usage', WPCFORM_I18N_DOMAIN); ?></a></li>
                    <li><a href="#wpcform-tabs-5"><?php _e('About', WPCFORM_I18N_DOMAIN); ?></a></li>
                </ul>
                <div id="wpcform-tabs-1">
                    <form method="post" action="options.php">
                        <?php settings_fields('wpcform_options'); ?>
                        <?php wpcform_settings_input(); ?>
                        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                    </form>
                </div>
                <div id="wpcform-tabs-2">
                    <form method="post" action="options.php">
                        <?php settings_fields('wpcform_options'); ?>
                        <?php wpcform_settings_advanced_options(); ?>
                        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Reset') ?>" />
                    </form>
                </div>
                <div id="wpcform-tabs-3">
                    <?php
                        //
                        //  Instead of duplicating the FAQ and Other Notes content in the ReadMe.txt file,
                        //  let's simply extract it from the WordPress plugin repository!
                        //
                        //  Fetch via the content via the WordPress Plugins API which is largely undocumented.
                        //
                        //  @see http://dd32.id.au/projects/wordpressorg-plugin-information-api-docs/
                        //
                        //  We want just the 'sections' content of the ReadMe file which will yield an array
                        //  which contains an element for each section of the ReadMe file.  We'll use 'faq' and
                        //  'other_notes'.
                        //

                        require_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
                        $readme = plugins_api('plugin_information', array('slug' => 'wpcform', 'fields' => array('sections')));

                        if (is_wp_error($readme)) {
                            ?>
                        <div class="updated fade"><?php _e('Unable to retrive FAQ content from WordPress plugin repository.', WPCFORM_I18N_DOMAIN); ?></div>
                    <?php
                        } else {
                            echo $readme->sections['faq'];
                        }
                        ?>
                </div>
                <div id="wpcform-tabs-4">
                    <?php

                        if (is_wp_error($readme)) {
                            ?>
                        <div class="updated error"><?php _e('Unable to retrive Usage content from WordPress plugin repository.', WPCFORM_I18N_DOMAIN); ?></div>
                    <?php
                        } else {
                            echo $readme->sections['other_notes'];
                        }
                        ?>
                </div>
                <div id="wpcform-tabs-5">
                    <h4><?php _e('About WordPress Chat Form', WPCFORM_I18N_DOMAIN); ?></h4>
                    <div style="margin-left: 25px; text-align: center; float: right;" class="postbox">
                        <h3 class="hndle"><span><?php _e('Make a Donation', WPCFORM_I18N_DOMAIN); ?></span></h3>
                        <div class="inside">
                            <div style="text-align: center; font-size: 0.75em;padding:0px 5px;margin:0px auto;">
                                <!-- PayPal box wrapper -->
                                <div>
                                    <!-- PayPal box-->
                                    <p style="margin: 0.25em 0"><b>WordPress Chat Form plugin<?php echo WPCFORM_VERSION; ?></b></p>
                                    <p style="margin: 0.25em 0"><a href="http://wordpress.org/extend/plugins/wpcform/" target="_blank"><?php _e('Plugin\'s Home Page', WPCFORM_I18N_DOMAIN); ?></a></p>
                                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                        <input type="hidden" name="cmd" value="_s-xclick">
                                        <input type="hidden" name="hosted_button_id" value="FGLJXAATGV2K2">
                                        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                    </form>
                                </div><!-- PayPal box -->
                            </div>
                        </div><!-- inside -->
                    </div><!-- postbox -->
                    <div>

                        <p><?php _e('An easy to implement integration of a Chat Form with WordPress. This plugin allows you to leverage the power of Google Docs Spreadsheets and Forms to collect data while retaining the look and feel of your WordPress based web site and the Interface of a Chat.', WPCFORM_I18N_DOMAIN); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }


    /**
     * wpcform_settings_input()
     *
     * Build the form content and populate with any current plugin settings.
     *
     * @return none
     */
    function wpcform_settings_input()
    {
        $wpcform_options = wpcform_get_plugin_options();
        //error_log(print_r($wpcform_options, true)) ;
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label><b><i>wpCForm</i></b> Shortcode</label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_sc_posts">
                            <input name="wpcform_options[sc_posts]" type="checkbox" id="wpcform_sc_posts" value="1" <?php checked('1', $wpcform_options['sc_posts']); ?> />
                            <?php _e('Enable shortcodes for posts and pages', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_sc_widgets">
                            <input name="wpcform_options[sc_widgets]" type="checkbox" id="wpcform_sc_widgets" value="1" <?php checked('1', $wpcform_options['sc_widgets']); ?> />
                            <?php _e('Enable shortcodes in text widget', WPCFORM_I18N_DOMAIN); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><b><i>wpCForm</i></b> CSS</label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_default_css">
                            <input name="wpcform_options[default_css]" type="checkbox" id="wpcform_default_css" value="1" <?php checked('1', $wpcform_options['default_css']); ?> />
                            <?php _e('Enable default WordPress Chat Form CSS', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_custom_css">
                            <input name="wpcform_options[custom_css]" type="checkbox" id="wpcform_custom_css" value="1" <?php checked('1', $wpcform_options['custom_css']); ?> />
                            <?php _e('Enable custom WordPress Chat Form CSS', WPCFORM_I18N_DOMAIN); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php printf(__('Custom %s CSS', WPCFORM_I18N_DOMAIN), 'wpCForm'); ?></label><br /><small><i><?php _e('Optional CSS styles to control the appearance of the Chat Form.', WPCFORM_I18N_DOMAIN); ?></i></small></th>
                <td>
                    <textarea class="regular-text code" name="wpcform_options[custom_css_styles]" rows="15" cols="80" id="wpcform_custom_css_styles"><?php echo $wpcform_options['custom_css_styles']; ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Confirmation<br />Email Format', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_email_format">
                            <input name="wpcform_options[email_format]" type="radio" id="wpcform_email_format" value="<?php echo WPCFORM_EMAIL_FORMAT_HTML; ?>" <?php checked(WPCFORM_EMAIL_FORMAT_HTML, $wpcform_options['email_format']); ?> />
                            <?php _e('Send confirmation email (when used) in HTML format.', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <input name="wpcform_options[email_format]" type="radio" id="wpcform_email_format" value="<?php echo WPCFORM_EMAIL_FORMAT_PLAIN; ?>" <?php checked(WPCFORM_EMAIL_FORMAT_PLAIN, $wpcform_options['email_format']); ?> />
                        <?php _e('Send confirmation email (when used) in Plain Text format.', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="bcc_blog_admin">
                            <input name="wpcform_options[bcc_blog_admin]" type="checkbox" id="wpcform_bcc_blog_admin" value="1" <?php checked('1', $wpcform_options['bcc_blog_admin']); ?> />
                            <?php _e('Bcc Blog Admin on Confirmation Email (when used)', WPCFORM_I18N_DOMAIN); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('cURL Transport<br />Notification', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_curl_transport_missing_message">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[curl_transport_missing_message]" type="checkbox" id="wpcform_curl_transport_missing_message" value="1" <?php checked('1', $wpcform_options['curl_transport_missing_message']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php _e('Hide the cURL Transport Missing notification message.<br /><small>The cURL transport is critical for proper operation of the Chat Forms plugin.</small>', WPCFORM_I18N_DOMAIN); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Donation Request', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_donation_message">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[donation_message]" type="checkbox" id="wpcform_donation_message" value="1" <?php checked('1', $wpcform_options['donation_message']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php _e('Hide the request for donation at the top of this page.<br/><small>The donation request will remain on the <b>About</b> tab.</small>', WPCFORM_I18N_DOMAIN); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <br /><br />
        <input name="wpcform_options[required_text_override]" type="hidden" id="wpcform_required_text_override" value="<?php echo $wpcform_options['required_text_override']; ?>" />
        <input name="wpcform_options[submit_button_text_override]" type="hidden" id="wpcform_submit_button_text_override" value="<?php echo $wpcform_options['submit_button_text_override']; ?>" />
        <input name="wpcform_options[back_button_text_override]" type="hidden" id="wpcform_back_button_text_override" value="<?php echo $wpcform_options['back_button_text_override']; ?>" />
        <input name="wpcform_options[continue_button_text_override]" type="hidden" id="wpcform_continue_button_text_override" value="<?php echo $wpcform_options['continue_button_text_override']; ?>" />
        <input name="wpcform_options[radio_buttons_text_override]" type="hidden" id="wpcform_radio_buttons_text_override" value="<?php echo $wpcform_options['radio_buttons_text_override']; ?>" />
        <input name="wpcform_options[radio_buttons_other_text_override]" type="hidden" id="wpcform_radio_buttons_other_text_override" value="<?php echo $wpcform_options['radio_buttons_other_text_override']; ?>" />
        <input name="wpcform_options[check_boxes_text_override]" type="hidden" id="wpcform_check_boxes_text_override" value="<?php echo $wpcform_options['check_boxes_text_override']; ?>" />
        <input name="wpcform_options[enable_debug]" type="hidden" id="wpcform_enable_debug" value="<?php echo $wpcform_options['enable_debug']; ?>" />
        <input name="wpcform_options[fsockopen_transport]" type="hidden" id="wpcform_fsockopen_transport" value="<?php echo $wpcform_options['fsockopen_transport']; ?>" />
        <input name="wpcform_options[streams_transport]" type="hidden" id="wpcform_streams_transport" value="<?php echo $wpcform_options['streams_transport']; ?>" />
        <input name="wpcform_options[curl_transport]" type="hidden" id="wpcform_curl_transport" value="<?php echo $wpcform_options['curl_transport']; ?>" />
        <input name="wpcform_options[ssl_verify]" type="hidden" id="wpcform_ssl_verify" value="<?php echo $wpcform_options['ssl_verify']; ?>" />
        <input name="wpcform_options[local_ssl_verify]" type="hidden" id="wpcform_local_ssl_verify" value="<?php echo $wpcform_options['local_ssl_verify']; ?>" />
        <input name="wpcform_options[http_request_timeout]" type="hidden" id="wpcform_http_request_timeout" value="<?php echo $wpcform_options['http_request_timeout']; ?>" />
        <input name="wpcform_options[http_request_timeout_value]" type="hidden" id="wpcform_http_request_timeout_value" value="<?php echo $wpcform_options['http_request_timeout_value']; ?>" />
        <input name="wpcform_options[browser_check]" type="hidden" id="wpcform_browser_check" value="<?php echo $wpcform_options['browser_check']; ?>" />
        <input name="wpcform_options[form_submission_log]" type="hidden" id="wpcform_form_submission_log" value="<?php echo $wpcform_options['form_submission_log']; ?>" />
    <?php
    }

    /**
     * wpcform_settings_advanced_options()
     *
     * Build the form content and populate with any current plugin settings.
     *
     * @return none
     */
    function wpcform_settings_advanced_options()
    {
        $wpcform_options = wpcform_get_plugin_options();
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label><?php _e('Disable HTML Filtering', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_disable_html_filtering">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[disable_html_filtering]" type="checkbox" id="wpcform_disable_html_filtering" value="1" <?php checked('1', $wpcform_options['disable_html_filtering']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php _e('Disable HTML Filtering?<br/><small>Chat Forms filters HTML retrieved from Google using <a href="https://codex.wordpress.org/Function_Reference/wp_kses">wp_kses()</a> to eliminate unnecessary HTML code.</small>', WPCFORM_I18N_DOMAIN); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Enable Form Submission Logging', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_form_submission_log">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[form_submission_log]" type="checkbox" id="wpcform_form_submission_log" value="1" <?php checked('1', $wpcform_options['form_submission_log']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php _e('Log WordPress Chat Form Submissions?<br/><small>Form submissions can be logged which will track a number of client related metrics upon form submission.</small>', WPCFORM_I18N_DOMAIN); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('HTTP API Timeout', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_http_api_timeout">
                            <select style="width: 150px;" name="wpcform_options[http_api_timeout]" id="wpcform_http_api_timeout">
                                <option value="5" <?php selected($wpcform_options['http_api_timeout'], 5); ?>>5 Seconds</option>
                                <option value="10" <?php selected($wpcform_options['http_api_timeout'], 10); ?>>10 Seconds</option>
                                <option value="15" <?php selected($wpcform_options['http_api_timeout'], 15); ?>>15 Seconds</option>
                                <option value="25" <?php selected($wpcform_options['http_api_timeout'], 25); ?>>25 Seconds</option>
                                <option value="30" <?php selected($wpcform_options['http_api_timeout'], 30); ?>>30 Seconds</option>
                                <option value="45" <?php selected($wpcform_options['http_api_timeout'], 45); ?>>45 Seconds</option>
                                <option value="60" <?php selected($wpcform_options['http_api_timeout'], 60); ?>>60 Seconds</option>
                            </select>
                            <br />
                            <small><?php _e('Change the default HTTP API Timeout setting (default is 5 seconds).', WPCFORM_I18N_DOMAIN); ?></small></label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Browser Check', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_browser_check">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[browser_check]" type="checkbox" id="wpcform_browser_check" value="1" <?php checked('1', $wpcform_options['browser_check']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php _e('Check browser compatibility?<br/><small>The WordPress Chat Form plugin may not work as expected with older browser versions (e.g. IE6, IE7, etc.).</small>', WPCFORM_I18N_DOMAIN); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Enable Debug', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_enable_debug">
                            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0px; vertical-align: top;">
                                        <input name="wpcform_options[enable_debug]" type="checkbox" id="wpcform_enable_debug" value="1" <?php checked('1', $wpcform_options['enable_debug']); ?> />
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php printf(__('Enabling debug will collect data during the form rendering and processing process.<p>The data is added to the page footer but hidden with a link appearing above the form which can toggle the display of the debug data.  This data is useful when trying to understand why the plugin isn\'t operating as expected.</p><p>When debugging is enabled, specific transports employed by the <a href="%s">WordPress HTTP API</a> can optionally be disabled.  While rarely required, disabling transports can be useful when the plugin is not communcating correctly with the Google Docs API.  <i>Extra care should be taken when disabling transports as other aspects of WordPress may not work correctly.</i>  The <a href="%s">WordPress Core Control</a> plugin is recommended for advanced debugging of <a href="%s">WordPress HTTP API issues.</a></p>', WPCFORM_I18N_DOMAIN), 'http://codex.wordpress.org/HTTP_API', 'http://wordpress.org/extend/plugins/core-control/', 'http://codex.wordpress.org/HTTP_API'); ?>
                                    </td>
                                </tr>
                            </table>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('WordPress HTTP API<br/>Transport Control', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_fsockopen_transport">
                            <input name="wpcform_options[fsockopen_transport]" type="checkbox" id="wpcform_fsockopen_transport" value="1" <?php checked('1', $wpcform_options['fsockopen_transport']); ?> />
                            <?php _e('Disable <i><b>FSockOpen</b></i> Transport', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_streams_transport">
                            <input name="wpcform_options[streams_transport]" type="checkbox" id="wpcform_streams_transport" value="1" <?php checked('1', $wpcform_options['streams_transport']); ?> />
                            <?php _e('Disable <i><b>Streams</b></i> Transport', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_curl_transport">
                            <input name="wpcform_options[curl_transport]" type="checkbox" id="wpcform_curl_transport" value="1" <?php checked('1', $wpcform_options['curl_transport']); ?> />
                            <?php _e('Disable <i><b>cURL</b></i> Transport', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_ssl_verify">
                            <input name="wpcform_options[ssl_verify]" type="checkbox" id="wpcform_ssl_verify" value="1" <?php checked('1', $wpcform_options['ssl_verify']); ?> />
                            <?php _e('Disable <i><b>SSL Verify</b></i>', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_local_ssl_verify">
                            <input name="wpcform_options[local_ssl_verify]" type="checkbox" id="wpcform_local_ssl_verify" value="1" <?php checked('1', $wpcform_options['local_ssl_verify']); ?> />
                            <?php _e('Disable <i><b>Local SSL Verify</b></i>', WPCFORM_I18N_DOMAIN); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('HTTP Request Timeout', WPCFORM_I18N_DOMAIN); ?></label></th>
                <td>
                    <fieldset>
                        <label for="wpcform_http_request_timeout">
                            <input name="wpcform_options[http_request_timeout]" type="checkbox" id="wpcform_http_request_timeout" value="1" <?php checked('1', $wpcform_options['http_request_timeout']); ?> />
                            <?php _e('Change <i><b>HTTP Request Timeout</b></i>', WPCFORM_I18N_DOMAIN); ?></label>
                        <br />
                        <label for="wpcform_http_request_timeout_value">
                            <input name="wpcform_options[http_request_timeout_value]" type="text" id="wpcform_http_request_timeout_value" value="<?php echo $wpcform_options['http_request_timeout_value']; ?>" /><br />
                            <small><?php _e('(in seconds)', WPCFORM_I18N_DOMAIN); ?></small></label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <br /><br />
        <input name="wpcform_options[sc_posts]" type="hidden" id="wpcform_sc_posts" value="<?php echo $wpcform_options['sc_posts']; ?>" />
        <input name="wpcform_options[sc_widgets]" type="hidden" id="wpcform_sc_widgets" value="<?php echo $wpcform_options['sc_widgets']; ?>" />
        <input name="wpcform_options[default_css]" type="hidden" id="wpcform_default_css" value="<?php echo $wpcform_options['default_css']; ?>" />
        <input name="wpcform_options[custom_css]" type="hidden" id="wpcform_custom_css" value="<?php echo $wpcform_options['custom_css']; ?>" />
        <input name="wpcform_options[custom_css_styles]" type="hidden" id="wpcform_custom_css_styles" value="<?php echo $wpcform_options['custom_css_styles']; ?>" />
        <input name="wpcform_options[donation_message]" type="hidden" id="wpcform_donation_message" value="<?php echo $wpcform_options['donation_message']; ?>" />
        <input name="wpcform_options[email_format]" type="hidden" id="wpcform_email_format" value="<?php echo $wpcform_options['email_format']; ?>" />
        <input name="wpcform_options[serialize_post_vars]" type="hidden" id="wpcform_serialize_post_vars" value="<?php echo $wpcform_options['serialize_post_vars']; ?>" />
        <input name="wpcform_options[bcc_blog_admin]" type="hidden" id="wpcform_bcc_blog_admin" value="<?php echo $wpcform_options['bcc_blog_admin']; ?>" />
    <?php
    }
    ?>
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 *
 * post-type-extensions.php template file
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpCForm
 * @subpackage post-types
 * @version $Revision$
 * @lastmodified $Author$
 * @lastmodifiedby $Date$
 *
 */

// WordPress Chat Form Plugin 'Team' Custom Post Type
define('WPCFORM_CPT_FORM', 'wpcform') ;
define('WPCFORM_CPT_QV_FORM', WPCFORM_CPT_FORM . '_qv') ;
define('WPCFORM_CPT_SLUG_FORM', WPCFORM_CPT_FORM . 's') ;

/** Set up the post type(s) */
add_action('init', 'wpcform_register_post_types') ;
//add_action('init', 'wpcform_register_taxonomies') ;

/** Register post type(s) */
function wpcform_register_post_types()
{
    /** Set up the arguments for the WPCFORM_CPT_FORM post type. */
    $wpcform_args = array(
        'public' => true,
        'query_var' => WPCFORM_CPT_QV_FORM,
        'has_archive' => false,
        'rewrite' => array(
            'slug' => WPCFORM_CPT_SLUG_FORM,
            'with_front' => false,
        ),
        'supports' => array(
            'title',
            //'thumbnail',
            //'editor',
            'excerpt'
        ),
        'labels' => array(
            'name' => __('Chat Forms', WPCFORM_I18N_DOMAIN),
            'singular_name' => __('Chat Form', WPCFORM_I18N_DOMAIN),
            'add_new' => __('Add New Chat Form', WPCFORM_I18N_DOMAIN),
            'add_new_item' => __('Add New Chat Form', WPCFORM_I18N_DOMAIN),
            'edit_item' => __('Edit Chat Form', WPCFORM_I18N_DOMAIN),
            'new_item' => __('New Chat Form', WPCFORM_I18N_DOMAIN),
            'view_item' => __('View Chat Form', WPCFORM_I18N_DOMAIN),
            'search_items' => __('Search Chat Forms', WPCFORM_I18N_DOMAIN),
            'not_found' => __('No Chat Forms Found', WPCFORM_I18N_DOMAIN),
            'not_found_in_trash' => __('No Chat Forms Found In Trash', WPCFORM_I18N_DOMAIN),
        ),
        'menu_icon' => plugins_url('/images/wp_chat_icon.svg', __FILE__)
    );

    //  Register the WordPress Chat Form post type
    register_post_type(WPCFORM_CPT_FORM, $wpcform_args) ;
    flush_rewrite_rules();
}

/** Perform routine maintenance */
function wpcform_routine_maintenance()
{
    global $post;

    //  Save the state of the global $post variable as the query will change it.

    $gblpost = $post;

    //  Post type is registered, do some hygiene on any that exist in the database.
    //  Insert the "wpcform" shortcode for that post into the post content. This
    //  ensures the form will be displayed properly when viewed through the CPT URL.

    $args = array('post_type' => WPCFORM_CPT_FORM, 'posts_per_page' => -1) ;

    // unhook this function so it doesn't update the meta data incorrectly
    remove_action('save_post_' . WPCFORM_CPT_FORM, 'wpcform_save_meta_box_data');
	
    $loop = new WP_Query($args);

    while ($loop->have_posts()) :
        $loop->the_post() ;
        $content = sprintf('[wpcform id=\'%d\']', get_the_ID()) ;

        if ($content !== get_the_content())
            wp_update_post(array('ID' => get_the_ID(), 'post_content' => $content)) ;
    endwhile ;

    // re-hook this function
    add_action('save_post_' . WPCFORM_CPT_FORM, 'wpcform_save_meta_box_data');

    //  Reset the Post Data after running WP_Query ...
    wp_reset_postdata() ;

    //  Restore the state of the global $post variable
    $post = $gblpost;
}

//  Build custom meta box support
//
//  There are three (3) meta boxes.  The primary meta box collects
//  the key fields and longer text input fields.  The secondary meta
//  box provides on/off settings and other selectable options.  The
//  third meta box allows entry of advanced validation rules and is
//  hidden by default.
//

/**
 * Define the WordPress Chat Form Primary Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpcform_primary_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpcform-primary-meta-box',
        'title' => __('Chat Form Details', WPCFORM_I18N_DOMAIN),
        'page' => WPCFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Form URL', WPCFORM_I18N_DOMAIN),
                'desc' => __('The full URL to the published Google Form', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'form',
                'type' => 'lgtext_auto',
                'std' => '',
                'placeholder' => __('Google Form URL', WPCFORM_I18N_DOMAIN),
                'required' => true
            ),

            array(
                'name' => __('Language', WPCFORM_I18N_DOMAIN),
                'desc' => __('Select your preferred chat language from the list', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'language',
                'type' => 'select_lang',
                'options' => array('None'),
                'required' => true,
                'br' => true
            ),

            array(
                'type'       => 'button_gennerator',
                'name'       => 'Conversational forms generator',
                'id'         => 'conversational-forms-generator',
                // Button text.
                'std'        => 'Begin Generator',
                // Custom HTML attributes.
                'attributes' => array(
                    'data-section' => 'form-generator-sections',
                    'class'        => 'form-generator',
                ),
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}


/**
 * Define the WordPress Chat Form Validation Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpcform_notification_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpcform-notification-meta-box',
        'title' => __('Notification', WPCFORM_I18N_DOMAIN),
        'page' => WPCFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Enable', WPCFORM_I18N_DOMAIN),
                'desc' => __('Enable Send notification Email before the chat expires', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'enable_notifi',
                'type' => 'radio',
                'options' => array('on' => __('On', WPCFORM_I18N_DOMAIN), 'off' => __('Off', WPCFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Email', WPCFORM_I18N_DOMAIN),
                'desc' => __('Emaill address to send conversation report to.', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'notification_email',
                'type' => 'medtext',
                'std' => __('Email', WPCFORM_I18N_DOMAIN),
                'placeholder' => __('example@domain.com', WPCFORM_I18N_DOMAIN),
                'required' => true
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}


/**
 * Define the WordPress Chat Form Preset Meta Box fields so they
 * can be used to send a complet/patrial conversation report.
 *
 */
function wpcform_reporting_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpcform-reporting-meta-box',
        'title' => __('Reporting', WPCFORM_I18N_DOMAIN),
        'page' => WPCFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Enable', WPCFORM_I18N_DOMAIN),
                'desc' => __('Enable Send reporting Email after the chat expires', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'enable_reporting',
                'type' => 'radio',
                'options' => array('on' => __('On', WPCFORM_I18N_DOMAIN), 'off' => __('Off', WPCFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Email', WPCFORM_I18N_DOMAIN),
                'desc' => __('Emaill address to send conversation report to.', WPCFORM_I18N_DOMAIN),
                'id' => WPCFORM_PREFIX . 'reporting_email',
                'type' => 'medtext',
                'std' => __('Email', WPCFORM_I18N_DOMAIN),
                'placeholder' => __('example@domain.com', WPCFORM_I18N_DOMAIN),
                'required' => true
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

add_action('admin_menu', 'wpcform_add_primary_meta_box') ;
//add_action('admin_menu', 'wpcform_add_player_profile_meta_box') ;

// Add form meta box
function wpcform_add_primary_meta_box()
{
    $mb = wpcform_primary_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpcform_show_primary_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpcform_notification_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpcform_show_notification_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpcform_reporting_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpcform_show_reporting_meta_box', $mb['page'], $mb['context'], $mb['priority']);
}

// Callback function to show fields in meta box
function wpcform_show_primary_meta_box()
{
    $mb = wpcform_primary_meta_box_content() ;
    wpcform_build_meta_box($mb) ;
}

// Callback function to show validation in meta box
function wpcform_show_notification_meta_box()
{
    $mb = wpcform_notification_meta_box_content() ;
    wpcform_build_meta_box($mb) ;
}

// Callback function to show reporting in meta box
function wpcform_show_reporting_meta_box()
{
    $mb = wpcform_reporting_meta_box_content() ;
    wpcform_build_meta_box($mb) ;
}

/**
 * Build meta box form
 *
 * @see http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
 * @see http://wp.tutsplus.com/tutorials/reusable-custom-meta-boxes-part-3-extra-fields/
 * @see http://wp.tutsplus.com/tutorials/reusable-custom-meta-boxes-part-4-using-the-data/
 *
 */
function wpcform_build_meta_box($mb)
{
    global $post;

    // Use nonce for verification
    echo '<input type="hidden" name="' . WPCFORM_PREFIX .
        'meta_box_nonce" value="', wp_create_nonce(plugin_basename(__FILE__)), '" />';

    echo '<table class="form-table">';

    foreach ($mb['fields'] as $field)
    {
        //  Only show the fields which are not hidden
        if ($field['type'] !== 'hidden')
        {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);
    
            echo '<tr>',
                    '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                    '<td>';
            switch ($field['type']) {
                case 'text':
                case 'lgtext':
                    $readonly = isset($field['readonly']) && $field['readonly']? 'readonly': '';
                    printf('<input type="text" name="%s" id="%s" style="width: 97%%;" value="%s" placeholder="%s" %s /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', 
                        $readonly, $field['desc']
                    ) ;
                    break;

                case 'lgtext_auto':
                    printf('<textarea name="%s" id="%s" cols="%s" rows="%s" style="width: 97%%;" placeholder="%s">%s</textarea><br /><small>%s</small>',
                        $field['id'], $field['id'], array_key_exists('cols', $field) ? $field['cols'] : 60,
                        array_key_exists('rows', $field) ? $field['rows'] : 1,
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', 
                        $meta ? $meta : $field['std'], $field['desc']) ;
                    break;
                    break;

                case 'medtext':
                    printf('<input type="text" name="%s" id="%s" size="30" style="width: 47%%;" value="%s" placeholder="%s" /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', $field['desc']) ;
                    break;

                case 'smtext':
                    printf('<input type="text" name="%s" id="%s" size="30" style="width: 27%%;" value="%s" placeholder="%s" /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', $field['desc']) ;
                    break;

                case 'textarea':
                    printf('<textarea name="%s" id="%s" cols="%s" rows="%s" style="width: 97%%;" placeholder="%s">%s</textarea><br /><small>%s</small>',
                        $field['id'], $field['id'], array_key_exists('cols', $field) ? $field['cols'] : 60,
                        array_key_exists('rows', $field) ? $field['rows'] : 4,
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', 
                        $meta ? $meta : $field['std'], $field['desc']) ;
                    break;

                case 'select':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option => $value) {
                        echo '<option ', $meta == strtolower($value) ? ' selected="selected"' : '', 'value="', strtolower($value), '">', $value . '&nbsp;&nbsp;', '</option>';
                    }
                    echo '</select>';
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;

                case 'radio':
                    foreach ($field['options'] as $option => $value) {
                        echo '<input type="radio" name="', $field['id'], '" value="', strtolower($value), '"', $meta == strtolower($value) ? ' checked="checked"' : empty($meta) && $field['std'] === $option ? ' checked="checked"' : '', ' />&nbsp;', $value, $field['br'] === true ? '<br />' : '&nbsp;&nbsp;';
                    }
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;

                case 'checkbox':
                    echo '<span><input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />', '&nbsp;', $field['label'], '</span>';
                    break;

                case 'validation':
                case 'hiddenfield':
                case 'placeholder':
	                $meta_field = get_post_meta($post->ID, $field['id'], true);
                    $meta_type = get_post_meta($post->ID, $field['type_id'], true);
                    $meta_value = get_post_meta($post->ID, $field['value_id'], true);

                    echo '<a class="repeatable-add button" href="#">+</a>
			                <ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';

	                $i = 0;

	                if ($meta_field) {
		                foreach($meta_field as $key => $value) {
			                echo '<li>' ;

						    printf('<label for="%s">%s:&nbsp;</label>', $field['id'].'['.$i.']', __('Name', WPCFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$meta_field[$key].'" size="30" />' ;

                            if ('placeholder' !== $field['type']) {
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['type_id'].'['.$i.']', ('hiddenfield' === $field['type']) ? __('Type', WPCFORM_I18N_DOMAIN) : __('Check', WPCFORM_I18N_DOMAIN)) ;
                                echo '<select name="', $field['type_id'].'['.$i.']', '" id="', $field['type_id'], '">';
                                foreach ($field['options'] as $option) {
                                    echo '<option ', $meta_type[$key] == $option ? 'selected="selected" ' : '', 'value="', $option, '">', $option . '&nbsp;&nbsp;', '</option>';
                                }
                                echo '</select>';
                            }

                            if ('placeholder' !== $field['type'])
						        printf('<i><label for="%s">&nbsp;%s:&nbsp;</label></i>', $field['value_id'].'['.$i.']', __('Value', WPCFORM_I18N_DOMAIN)) ;
                            else
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['value_id'].'['.$i.']', __('Value', WPCFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['value_id'].'['.$i.']" id="'.$field['value_id'].'" value="'.$meta_value[$key].'" size="15" />' ;
						    echo '<a class="repeatable-remove button" href="#">-</a></li>';

			                $i++;
		                }
	                } else {
			                echo '<li>' ;
						    printf('<label for="%s">%s:&nbsp;</label>', $field['id'].'['.$i.']', __('Field', WPCFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />' ;
                            if ('placeholder' !== $field['type']) {
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['type_id'].'['.$i.']', ('hiddenfield' === $field['type']) ? __('Type', WPCFORM_I18N_DOMAIN) : __('Check', WPCFORM_I18N_DOMAIN)) ;
                                echo '<select name="', $field['type_id'].'['.$i.']', '" id="', $field['type_id'], '">';
                                foreach ($field['options'] as $option) {
                                    echo '<option value="', $option, '">', $option . '&nbsp;&nbsp;', '</option>';
                                }
                                echo '</select>';
                            }

                            if ('placeholder' !== $field['type'])
						        printf('<i><label for="%s">&nbsp;%s:&nbsp;</label></i>', $field['value_id'].'['.$i.']', __('Value', WPCFORM_I18N_DOMAIN)) ;
                            else
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['value_id'].'['.$i.']', __('Value', WPCFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['value_id'].'['.$i.']" id="'.$field['value_id'].'" value="" size="15" />' ;
						    echo '<a class="repeatable-remove button" href="#">-</a></li>';
	                }
	                echo '</ul>
		                <small>'.$field['desc'].'</small>';
                    break;

                case 'select_lang':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option => $value) {
                        echo '<option ', $meta == strtolower($value) ? ' selected="selected"' : '', 'value="', strtolower($value), '">', $value . '&nbsp;&nbsp;', '</option>';
                    }
                    echo '</select><button id="select-language" class="button button-primary button-large">Get languages supported</button><div class="lang-error-show"></div>';
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;

                case 'button_gennerator':
                    $class_section_arr = array('wpc-form-button-wrapper');
                    $class_button_arr = array('button', 'button-primary', 'button-large');
                    $attributes = isset($field['attributes'])? $field['attributes']: [];

                    if(isset($attributes['data-section'])){
                        $class_section_arr = array_merge($class_section_arr, explode(' ', $attributes['data-section']));
                    }

                    if(isset($attributes['class'])){
                        $class_button_arr = array_merge($class_button_arr, explode(' ', $attributes['class']));
                    }

                    $button_section_class = implode(' ', $class_section_arr);
                    $button_class = implode(' ', $class_button_arr);

                    echo '<div class="'. esc_attr($button_section_class) .'">';
                    echo '<button disabled="disabled" id="'. esc_attr($field['id']) .'" class="'. esc_attr($button_class) .'">'.$field['std'].'</button><span class="loading"></span>';
                    echo '</div><div class="gen-error-show"></div>';
                    break;

                default :
                    break ;
            }
            echo     '<td>',
                '</tr>';
        }
    }

    echo '</table>';
}

add_action( 'quick_edit_custom_box', 'wpcform_add_quick_edit_nonce', 10, 2 );
/**
 * Action to add a nonce to the quick edit form for the custom post types
 *
 */
function wpcform_add_quick_edit_nonce($column_name, $post_type)
{
    //wpcform_whereami(__FILE__, __LINE__) ;
    static $printNonce = true ;

    if ($post_type == WPCFORM_CPT_FORM)
    {
        if ($printNonce)
        {
            $printNonce = false ;
            wp_nonce_field( plugin_basename( __FILE__ ), WPCFORM_PREFIX . 'meta_box_qe_nonce' ) ;
        }
    }
}

add_action('save_post_' . WPCFORM_CPT_FORM, 'wpcform_save_meta_box_data');
/**
 * Action to save WordPress Chat Form meta box data for CPT.
 *
 */
function wpcform_save_meta_box_data($post_id)
{
    global $post ;

    // verify nonce - needs to come from either a CPT Edit screen or CPT Quick Edit

    if ((isset( $_POST[WPCFORM_PREFIX . 'meta_box_nonce']) &&
        wp_verify_nonce($_POST[WPCFORM_PREFIX . 'meta_box_nonce'], plugin_basename(__FILE__))) ||
        (isset( $_POST[WPCFORM_PREFIX . 'meta_box_qe_nonce']) &&
        wp_verify_nonce($_POST[WPCFORM_PREFIX . 'meta_box_qe_nonce'], plugin_basename(__FILE__))))
    {
        //wpcform_whereami(__FILE__, __LINE__) ;
        // check for autosave - if autosave, simply return

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        {
            return $post_id ;
        }

        // check permissions - make sure action is allowed to be performed

        if ('page' == $_POST['post_type'])
        {
            if (!current_user_can('edit_page', $post_id))
            {
                return $post_id ;
            }
        }
        elseif (!current_user_can('edit_post', $post_id))
        {
            return $post_id ;
        }

        //  Get the meta box fields for the appropriate CPT and
        //  return if the post isn't a CPT which shouldn't happen

        if (get_post_type($post_id) == WPCFORM_CPT_FORM)
            $fields = array_merge(
                wpcform_primary_meta_box_content(true),
                wpcform_notification_meta_box_content(true),
                wpcform_reporting_meta_box_content(true)
            ) ;
        else
            return $post_id ;

        //  Loop through all of the fields and update what has changed
        //  accounting for the fact that Short URL fields are always
        //  updated and CPT fields are ignored in Quick Edit except for
        //  the Short URL field.

        foreach ($fields as $field)
        {
            //  Only update other Post Meta fields when on the edit screen - ignore in quick edit mode

            if (isset($_POST[WPCFORM_PREFIX . 'meta_box_nonce']))
            {
                if (array_key_exists($field['id'], $_POST))
                {
                    $new = sanitize_text_field($_POST[$field['id']]);

                    $old = get_post_meta($post_id, sanitize_text_field($field['id']), true) ;

                    if ($new && $new != $old)
                    {
                        update_post_meta($post_id, sanitize_text_field($field['id']), $new) ;
                    }
                    elseif ('' == $new && $old)
                    {
                        delete_post_meta($post_id, sanitize_text_field($field['id']), $old) ;
                    }
                    else
                    {
                         if( ($field['id'] == WPCFORM_PREFIX.'form') || ($field['id'] == WPCFORM_PREFIX . 'transient_reset') )
                         {
                             // If form cache reset was selected, or the URL was updated
                             // let's delete the transient and uncheck the "reset" option
                             delete_transient(WPCFORM_FORM_TRANSIENT.$post_id);
                             if( ($field['id'] == WPCFORM_PREFIX . 'transient_reset') && ($new == 'on') )
                             {
                                 $new = '';
                             }
                         }
                        //wpcform_whereami(__FILE__, __LINE__);
                    }
                }
                else
                {
                    delete_post_meta($post_id, sanitize_text_field($field['id'])) ;
                }
            }
        }

        //  Set the post content to the shortcode for the form for rendering the CPT URL slug

        if (!wp_is_post_revision($post_id))
        {
		    // unhook this function so it doesn't loop infinitely
		    remove_action('save_post_' . WPCFORM_CPT_FORM, 'wpcform_save_meta_box_data');
	
		    // update the post, which calls save_post again
            wp_update_post(array('ID' => $post_id,
                'post_content' => sprintf('[wpcform id=\'%d\']', $post_id))) ;

		    // re-hook this function
		    add_action('save_post_' . WPCFORM_CPT_FORM, 'wpcform_save_meta_box_data');
	    }
    }
    else
    {
        return $post_id ;
    }
}


/**
 * CPT Admin Notices - action to note if an invalid form has been detected.
 */
function wpcform_invalid_form_error($post_id) {  
    if (false !== ($value = get_transient(WPCFORM_CPT_FORM . '_admin_notice'))) {
?>
<div class="updated error"><?php echo $value ; ?></div>
<?php
        delete_transient(WPCFORM_CPT_FORM . '_admin_notice') ;
    }
}
add_action( 'admin_notices', 'wpcform_invalid_form_error', 10, 1 );

/**
 * CPT Update/Edit form
 */
function wpcform_update_edit_form() {  
    echo ' enctype="multipart/form-data"';  
}
add_action('post_edit_form_tag', 'wpcform_update_edit_form');  

// Add to admin_init function
add_filter('manage_edit-wpcform_columns', 'wpcform_add_new_form_columns');

/**
 * Add more columns
 */
function wpcform_add_new_form_columns($cols)
{
    //  The "Title" column is re-labeled as "Form Name"!
    $cols['title'] = __('Form Name', WPCFORM_I18N_DOMAIN) ;

	return array_merge(
		array_slice($cols, 0, 2),
        array(
            WPCFORM_PREFIX . 'shortcode' => __('Short Code', WPCFORM_I18N_DOMAIN),
            WPCFORM_PREFIX . 'excerpt' => __('Form Description', WPCFORM_I18N_DOMAIN),
        ),
        array_slice($cols, 2)
	) ;
}

/**
 * Display custom columns
 */
function wpcform_form_custom_columns($column, $post_id)
{
    switch ($column)
    {
        case WPCFORM_PREFIX . 'excerpt':
            $p = get_post($post_id);
            echo $p->post_excerpt;
            break;

        case WPCFORM_PREFIX . 'shortcode':
            printf('[wpcform id=\'%d\']', $post_id) ;
            break;

        case 'id':
            echo $post_id ;
            break ;
    }
}
add_action('manage_posts_custom_column', 'wpcform_form_custom_columns', 10, 2) ;
 
/**
 * Make these columns sortable
 */
function wpcform_form_sortable_columns()
{
    return array(
        'title' => 'title',
        WPCFORM_PREFIX . 'shortcode' => WPCFORM_PREFIX . 'shortcode',
        WPCFORM_PREFIX . 'excerpt' => WPCFORM_PREFIX . 'excerpt',
        'date' => 'date',
    ) ;
}
add_filter('manage_edit-wpcform_sortable_columns', 'wpcform_form_sortable_columns') ;

/**
 * Set up a footer hook to rearrange the post editing screen
 * for the WPCFORM_CPT_FORM custom post type.  The meta box which has all
 * of the custom fields in it will appear before the Visual Editor.
 * This is accomplished using a simple jQuery script once the
 * document is loaded. p_hBpWYxH5MWjtCmmD
 * 
 *
 */
function wpcform_admin_footer_hook()
{
    global $post ;
    $screen = get_current_screen() ;

    if ($screen->post_type == WPCFORM_CPT_FORM && $screen->id == WPCFORM_CPT_FORM)
    {
        //  wpCForm needs jQuery!
        wp_enqueue_script('jquery') ;

        wp_register_script('wpcform-autosize',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/autosize.min.js')),
            array('jquery'), false, true) ;

        //  Load the WordPress Chat Form jQuery Admin script from the plugin
        wp_register_script('wpcform-post-type',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/wpcform-post-type.js')),
            array('jquery'), false, true) ;
        
        wp_enqueue_script('wpcform-autosize') ;
        wp_enqueue_script('wpcform-post-type') ;
        return;

?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#normal-sortables').insertBefore('#postdivrich') ;
    }) ;
</script>

<?php
    }
}

/**  Hook into the Admin Footer */
add_action('admin_footer','wpcform_admin_footer_hook');

/**  Filter to change the Title field for the Player post type */
add_filter('enter_title_here', 'wpcform_enter_title_here_filter') ;

function wpcform_enter_title_here_filter($title)
{
    global $post ;

    if (get_post_type($post) == WPCFORM_CPT_FORM)
        return __('Enter WordPress Chat Form Title', WPCFORM_I18N_DOMAIN) ;
    else
        return $title ;
}

/**
 * wpcform_admin_css()
 *
 */
function wpcform_admin_css()
{
    global $post_type;
    if ((array_key_exists('post_type', $_GET) && ($_GET['post_type'] == WPCFORM_CPT_FORM)) || ($post_type == WPCFORM_CPT_FORM))
    {
        wp_enqueue_style('wpcform-admin-css',
            plugins_url(plugin_basename(dirname(__FILE__) . '/css/wpcform-admin.css'))) ;
    }
}
add_action('admin_head', 'wpcform_admin_css');
?>

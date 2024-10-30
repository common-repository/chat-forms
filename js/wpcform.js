/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Localization of jQuery Validate error messages.
 *
 */
jQuery.extend(jQuery.validator.messages, {
    required: wpcform_script_vars.required,
    remote: wpcform_script_vars.remote,
    email: wpcform_script_vars.email,
    url: wpcform_script_vars.url,
    date: wpcform_script_vars.date,
    dateISO: wpcform_script_vars.dateISO,
    number: wpcform_script_vars.number,
    digits: wpcform_script_vars.digits,
    creditcard: wpcform_script_vars.creditcard,
    equalTo: wpcform_script_vars.equalTo,
    accept: wpcform_script_vars.accept,
    maxlength: jQuery.validator.format(wpcform_script_vars.maxlength),
    minlength: jQuery.validator.format(wpcform_script_vars.minlength),
    rangelength: jQuery.validator.format(wpcform_script_vars.rangelength),
    range: jQuery.validator.format(wpcform_script_vars.range),
    max: jQuery.validator.format(wpcform_script_vars.max),
    min: jQuery.validator.format(wpcform_script_vars.min),
});


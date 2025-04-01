<?php
/**
 * Plugin Name:     VIES VAT Validation Plugin
 * Plugin URI:      
 * Description:     This is a VIES VAT Validation plugin for WordPress.
 * Author:          wp-cli
 * Author URI:      
 * Text Domain:     vies-vat-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Vies_Vat_Plugin
 */

function add_vat_number_register_form()
{
    ?>
    <p>
        <label for="vat_number"><?php _e('VAT Number', 'vies-vat-validation'); ?><br />
            <input type="text" name="vat_number" id="vat_number" class="input" placeholder="XXXXXXXXXXXX" value="<?php if (!empty($_POST['vat_number']))
                echo esc_attr($_POST['vat_number']); ?>" />
    </p>
    <?php
}
add_action('register_form', 'add_vat_number_register_form');


function validate_vat_number_registration($errors, $sanitized_user_login, $user_email)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        if (validate_vat_number($_POST['vat_number']) === false) {
            $errors->add('vat_number_error', __('<strong>Error</strong>: Invalid VAT Number.', 'vies-vat-validation'));
        }
    }
    return $errors;
}
add_filter('registration_errors', 'validate_vat_number_registration', 10, 3);

function save_vat_number($user_id)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        $vat_number = sanitize_text_field($_POST['vat_number']);
        $validation_result = validate_vat_number($vat_number);

        if ($validation_result === false) {
            return;
        }

        update_user_meta($user_id, 'vat_number', $vat_number);
        update_user_meta($user_id, 'billing_company', sanitize_text_field($validation_result['name']));
    }
}
add_action('user_register', 'save_vat_number');

function validate_vat_number($vat_number)
{
    $country_code = substr($vat_number, 0, 2);
    $vat_number = substr($vat_number, 2);

    $url = 'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/' . $country_code . '/vat/' . $vat_number;

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return new WP_Error('vies_error', 'There was an error with the request.');
    }

    $data = wp_remote_retrieve_body($response);
    $data = json_decode($data);

    if (isset($data->isValid) && $data->isValid) {
        return [
            'valid' => true,
            'name' => $data->name,
        ];
    }

    return false;
}

function custom_user_profile_fields($user)
{
    ?>
    <table class="form-table">
        <tr>
            <th><label for="vat_number"><?php _e('VAT Number', 'vies-vat-validation'); ?></label></th>
            <td>
                <input type="text" name="vat_number" id="vat_number" placeholder="XXXXXXXXXXXX"
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'vat_number', true)); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');

function validate_vat_number_on_profile_update($errors, $update, $user)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        $vat_number = $_POST['vat_number'];

        $validation_result = validate_vat_number($vat_number);

        if ($validation_result === false) {
            $errors->add('vat_number_error', __('Invalid VAT number.', 'vies-vat-validation'));
        } elseif (is_wp_error($validation_result)) {
            $errors->add('vat_number_error', $validation_result->get_error_message());
        }
    }

    return $errors;
}
add_filter('user_profile_update_errors', 'validate_vat_number_on_profile_update', 10, 3);

function save_custom_user_profile_fields($user_id)
{
    if (isset($_POST['vat_number'])) {
        $validation_result = validate_vat_number($_POST['vat_number']);
        if ($validation_result && isset($validation_result['name'])) {
            update_user_meta($user_id, 'billing_company', sanitize_text_field($validation_result['name']));
        }

        update_user_meta($user_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
    }
}
add_action('personal_options_update', 'save_custom_user_profile_fields', 20);
add_action('edit_user_profile_update', 'save_custom_user_profile_fields', 20);

/**
 * Default show company for block themes
 */
add_filter('default_option_woocommerce_checkout_company_field', function() {
    return 'optional';
});

add_filter('option_woocommerce_checkout_company_field', function() {
    return 'optional';
});
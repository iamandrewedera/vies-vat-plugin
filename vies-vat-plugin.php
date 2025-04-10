<?php
/**
 * Plugin Name:     VIES VAT Validation Plugin
 * Plugin URI:      
 * Description:     This is a VIES VAT Validation plugin for WordPress.
 * Author:          Andrew Edera
 * Author URI:      https://github.com/iamandrewedera/
 * Text Domain:     vies-vat-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Vies_Vat_Plugin
 */

function add_vat_number_register_form()
{
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="vat_number">VAT Number</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number"
            autocomplete="vat_number" value="" placeholder="XXXXXXXXXXXX">
    </p>
    <?php
}
add_action('register_form', 'add_vat_number_register_form');
add_action('woocommerce_register_form', 'add_vat_number_register_form');

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

function wc_validate_vat_number_registration($username, $email, $validation_errors)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        if (validate_vat_number($_POST['vat_number']) === false) {
            $validation_errors->add('vat_number_error', __('Invalid VAT Number.', 'vies-vat-validation'));
        }
    }
}
add_action('woocommerce_register_post', 'wc_validate_vat_number_registration', 10, 3);

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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return new WP_Error('curl_error', $error_msg);
    }

    curl_close($ch);

    $data = json_decode($response);

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

add_action('woocommerce_edit_account_form_start', 'vies_add_vat_field_to_edit_account');
function vies_add_vat_field_to_edit_account()
{
    $user_id = get_current_user_id();
    $vat_number = get_user_meta($user_id, 'vat_number', true);
    $billing_company = get_user_meta($user_id, 'billing_company', true);

    woocommerce_form_field('vat_number', array(
        'type' => 'text',
        'label' => __('VAT Number'),
        'required' => false,
        'default' => $vat_number,
    ));

    woocommerce_form_field('billing_company', array(
        'type' => 'text',
        'label' => __('Company name'),
        'required' => false,
        'default' => $billing_company,
    ));
}

function validate_vat_number_on_profile_update($errors, $update, $user)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        $vat_number = sanitize_text_field($_POST['vat_number']);
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

function validate_vat_on_account_update($args, $user)
{
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        $vat_number = sanitize_text_field($_POST['vat_number']);
        $validation_result = validate_vat_number($vat_number);

        if ($validation_result === false) {
            $args->add('vat_number_error', __('Invalid VAT number.', 'vies-vat-validation'));
        } elseif (is_wp_error($validation_result)) {
            $args->add('vat_number_error', $validation_result->get_error_message());
        }
    }
}
add_action('woocommerce_save_account_details_errors', 'validate_vat_on_account_update', 10, 2);

function save_custom_user_profile_fields($user_id)
{
    if (isset($_POST['billing_company']) && !empty($_POST['billing_company'])) {
        update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['billing_company']));
    }
    if (isset($_POST['vat_number'])) {
        $vat_number = sanitize_text_field($_POST['vat_number']);
        $validation_result = validate_vat_number($vat_number);

        if ($validation_result === false && !empty($_POST['vat_number'])) {
            return;
        }

        if ($validation_result && isset($validation_result['name'])) {
            update_user_meta($user_id, 'billing_company', sanitize_text_field($validation_result['name']));
        }

        if ($validation_result && isset($validation_result['valid']) && $validation_result['valid'] == true) {
            update_user_meta($user_id, 'vat_number', $vat_number);
            update_user_meta($user_id, '_vat_update_success', true);
        }
    }
}
add_action('personal_options_update', 'save_custom_user_profile_fields', 20);
add_action('edit_user_profile_update', 'save_custom_user_profile_fields', 20);
add_action('woocommerce_save_account_details', 'save_custom_user_profile_fields', 10, 1);

add_action('woocommerce_created_customer', function ($customer_id) {
    if (isset($_POST['vat_number']) && !empty($_POST['vat_number'])) {
        update_user_meta($customer_id, '_vat_update_success', true);
    }
});

function display_vat_validation_message()
{
    $user_id = get_current_user_id();

    if (get_user_meta($user_id, '_vat_update_success', true)) {
        wc_add_notice('VAT number validated successfully.', 'success');
        delete_user_meta($user_id, '_vat_update_success');
    }
}
add_action('woocommerce_save_account_details', 'display_vat_validation_message');

add_action('admin_notices', function () {
    if (!isset($_GET['user_id']) || !current_user_can('edit_users')) {
        return;
    }

    $user_id = intval($_GET['user_id']);
    if (get_user_meta($user_id, '_vat_update_success', true)) {
        echo '<div class="notice notice-success is-dismissible"><p>VAT number validated successfully.</p></div>';
        delete_user_meta($user_id, '_vat_update_success');
    }
});

function check_and_add_vat_validation_notice()
{
    // Only run on the my-account page
    if (!is_account_page()) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    display_vat_validation_message();
}
add_action('template_redirect', 'check_and_add_vat_validation_notice', 5);

/**
 * Default show company for block themes
 */
add_filter('default_option_woocommerce_checkout_company_field', function () {
    return 'optional';
});

add_filter('option_woocommerce_checkout_company_field', function () {
    return 'optional';
});

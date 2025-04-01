# WooCommerce VIEST VAT Validation Plugin

## Description
Users can enter their VAT number during registration, and it will be validated using the VAT Information Exchange System (VIES). The VAT number and company name (if validated) will be stored and can be edited in the user's profile.

## Installation

1. Ensure that **WooCommerce** is installed and activated.
2. Upload the plugin to the `/wp-content/plugins/` directory or install it via the WordPress plugin manager.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### User Registration
1. Go to `/wp-login.php?action=register` to create a new user account.
2. Enter the required details, including the optional **VAT Number** field.
3. If a VAT number is entered, it will be validated against the VIES database before registration is completed.
   - For testing purposes, you can use the following VAT Number: FR40303265045 (This is a valid French VAT number that can be used for testing).

### Updating VAT Information
1. Log in as an administrator.
2. Go to `/wp-admin/user-edit.php?user_id=<USER_ID>`.
3. Locate the **VAT Number** and **Company Name** fields.
4. Update the fields as needed and save changes.

## Features
- Adds a **VAT Number** field to the registration page.
- Validates VAT numbers via **VIES**.
- Stores VAT information in user meta.
- Allows administrators to edit VAT details in the user profile.
- Integrates seamlessly with WooCommerce checkout.

# WooCommerce VIEST VAT Validation Plugin

## Description
Users can enter their VAT number during registration, and it will be validated using the VAT Information Exchange System (VIES). The VAT number and company name (if validated) will be stored and can be edited in the user's profile.

## Installation

1. Ensure that **WooCommerce** is installed and activated.
2. Upload the plugin to the `/wp-content/plugins/` directory or install it via the WordPress plugin manager.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### User Registration
1. Go to `/wp-login.php?action=register` or `/my-account/` to create a new user account.
2. Enter the required details, including the optional **VAT Number** field.
3. If a VAT number is entered, it will be validated against the VIES database before registration is completed.
   - For testing purposes, you can use the following VAT Number: FR40303265045 (This is a valid French VAT number that can be used for testing).

### Updating VAT Information
1. **In Dashboard Page**:
   - Log in on wp-admin.
   - Go to `/wp-admin/user-edit.php?user_id=<USER_ID>`.
   - Locate the **VAT Number** and **Company Name** fields.
   - Update the fields as needed and save changes.

2. **In Account Page**:
   - Navigate to My Account > Account Details
   - Enter your VAT number in the "VAT Number" field
   - Click "Save changes"
   - A success message will be displayed if your VAT number is validated successfully

## Features
- Adds a **VAT Number** field to the registration page.
- Validates VAT numbers via **VIES**.
- Stores VAT information in user meta.
- Allows administrators to edit VAT details in the user profile.
- Display validation status for entered VAT numbers
- Show success message after successful validation
- Adds a VAT number field to the WooCommerce account page

## Installation

1. Download the plugin zip file
2. Go to your WordPress admin area and navigate to Plugins > Add New
3. Click the "Upload Plugin" button at the top of the page
4. Click "Choose File" and select the downloaded zip file
5. Click "Install Now"
6. After installation, click "Activate Plugin"

## Requirements

- WordPress 5.0 or higher
- WooCommerce 4.0 or higher
- PHP 7.2 or higher

## Changelog

### 1.1.0
- Added success message after VAT validation
- Code cleanup and improved error handling
- Better validation flow and user feedback

### 1.0.0
- Initial release

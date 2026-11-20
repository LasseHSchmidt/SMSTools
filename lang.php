<?php
// Handle language switch via URL parameter (?lang=da or ?lang=en)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'en'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Fallback to environment variable DEFAULT_LANG or 'da'
$current_lang = $_SESSION['lang'] ?? (getenv('DEFAULT_LANG') ?: 'da');
if (!in_array($current_lang, ['da', 'en'], true)) {
    $current_lang = 'da';
}

// Translation dictionary
$translations = [
    'da' => [
        // Navigation
        'nav_receivers'     => 'Navneliste',
        'nav_new_receiver'  => 'Ny modtager',
        'nav_send_sms'      => 'Send SMS',
        'nav_sms_log'       => 'SMS log',
        'nav_logout'        => 'Log af',

        // Login
        'login_title'       => 'Log ind',
        'login_username'    => 'Brugernavn',
        'login_password'    => 'Adgangskode',
        'login_btn'         => 'Log ind',
        'login_error'       => 'Ugyldigt brugernavn eller adgangskode.',

        // Receivers list
        'col_name'          => 'Navn',
        'col_firstname'     => 'Fornavn',
        'col_mobile'        => 'Mobil',
        'btn_edit'          => 'Rediger',
        'btn_delete'        => 'Slet',
        'confirm_delete'    => 'Er du sikker på, at du vil slette denne modtager?',

        // Insert / Update receiver
        'insert_title'      => 'Opret ny kontakt',
        'update_title'      => 'Opdater kontakt',
        'label_fullname'    => 'Fuldt navn',
        'ph_fullname'       => 'f.eks. Jens Jensen',
        'label_shortname'   => 'Fornavn (Kaldenavn)',
        'ph_shortname'      => 'f.eks. Jens',
        'label_cellphone'   => 'Mobilnummer (8 tal)',
        'btn_save_contact'  => 'Gem kontakt',
        'btn_save_changes'  => 'Gem ændringer',
        'err_not_found'     => 'Modtager blev ikke fundet.',

        // Send SMS
        'sms_title'         => 'Send SMS til alle',
        'sms_label_msg'     => 'Din besked (Afsender: %s)',
        'sms_ph_msg'        => 'Skriv din besked her...',
        'sms_chars'         => 'tegn',
        'sms_btn_send'      => 'Send SMS nu',
        'sms_btn_sending'   => 'Sender... Vent venligst',
        'sms_success'       => 'Succes! %d SMSer sendt fra <strong>%s</strong>. Pris: %s %s (ID: %s)',
        'sms_no_recipients' => 'Ingen modtagere fundet på denne liste.',
        'sms_api_error'     => 'Fejl fra GatewayAPI (HTTP %s): ',

        // SMS Log
        'log_sent_to'       => 'Sendt til',
        'log_message'       => 'Besked',
        'log_status'        => 'Status',
        'log_date'          => 'Dato',
        'log_recipients'    => 'modt.',
    ],
    'en' => [
        // Navigation
        'nav_receivers'     => 'Contact List',
        'nav_new_receiver'  => 'New Contact',
        'nav_send_sms'      => 'Send SMS',
        'nav_sms_log'       => 'SMS Log',
        'nav_logout'        => 'Log out',

        // Login
        'login_title'       => 'Log in',
        'login_username'    => 'Username',
        'login_password'    => 'Password',
        'login_btn'         => 'Log in',
        'login_error'       => 'Invalid username or password.',

        // Receivers list
        'col_name'          => 'Full Name',
        'col_firstname'     => 'First Name',
        'col_mobile'        => 'Mobile',
        'btn_edit'          => 'Edit',
        'btn_delete'        => 'Delete',
        'confirm_delete'    => 'Are you sure you want to delete this contact?',

        // Insert / Update receiver
        'insert_title'      => 'Create New Contact',
        'update_title'      => 'Update Contact',
        'label_fullname'    => 'Full Name',
        'ph_fullname'       => 'e.g. John Doe',
        'label_shortname'   => 'First Name (Nickname)',
        'ph_shortname'      => 'e.g. John',
        'label_cellphone'   => 'Mobile Number (8 digits)',
        'btn_save_contact'  => 'Save Contact',
        'btn_save_changes'  => 'Save Changes',
        'err_not_found'     => 'Contact not found.',

        // Send SMS
        'sms_title'         => 'Send SMS to All',
        'sms_label_msg'     => 'Your message (Sender: %s)',
        'sms_ph_msg'        => 'Write your message here...',
        'sms_chars'         => 'characters',
        'sms_btn_send'      => 'Send SMS Now',
        'sms_btn_sending'   => 'Sending... Please wait',
        'sms_success'       => 'Success! %d SMS sent from <strong>%s</strong>. Cost: %s %s (ID: %s)',
        'sms_no_recipients' => 'No contacts found in this list.',
        'sms_api_error'     => 'GatewayAPI Error (HTTP %s): ',

        // SMS Log
        'log_sent_to'       => 'Sent to',
        'log_message'       => 'Message',
        'log_status'        => 'Status',
        'log_date'          => 'Date',
        'log_recipients'    => 'rec.',
    ]
];

/**
 * Helper function to retrieve translated strings.
 */
function __(string $key, ...$args): string {
    global $translations, $current_lang;
    $text = $translations[$current_lang][$key] ?? $translations['da'][$key] ?? $key;
    return !empty($args) ? vsprintf($text, $args) : $text;
}

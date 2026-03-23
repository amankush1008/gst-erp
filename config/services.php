<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─── GST Verification API ──────────────────────────────────────────────
    'gst' => [
        'base_url' => env('GST_API_BASE_URL', 'https://api.gst.gov.in/commonapi/v1.1'),
        'username' => env('GST_API_USERNAME'),
        'password' => env('GST_API_PASSWORD'),
        'client_id'     => env('GST_CLIENT_ID'),
        'client_secret' => env('GST_CLIENT_SECRET'),
        'otp_channel'   => env('GST_OTP_CHANNEL', 'SMS'),
    ],

    // ─── E-Way Bill API ────────────────────────────────────────────────────
    'eway' => [
        'base_url'      => env('EWAY_API_BASE_URL', 'https://einvapi.trail.einvoice1.gst.gov.in'),
        'username'      => env('EWAY_USERNAME'),
        'password'      => env('EWAY_PASSWORD'),
        'gstin'         => env('EWAY_GSTIN'),
        'client_id'     => env('EWAY_CLIENT_ID'),
        'client_secret' => env('EWAY_CLIENT_SECRET'),
    ],

    // ─── E-Invoice API ─────────────────────────────────────────────────────
    'einvoice' => [
        'base_url'      => env('EINVOICE_API_BASE_URL', 'https://einvapi.trail.einvoice1.gst.gov.in'),
        'username'      => env('EINVOICE_USERNAME'),
        'password'      => env('EINVOICE_PASSWORD'),
        'client_id'     => env('EINVOICE_CLIENT_ID'),
        'client_secret' => env('EINVOICE_CLIENT_SECRET'),
    ],

    // ─── Razorpay (Payment Gateway) ────────────────────────────────────────
    'razorpay' => [
        'key'    => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],

    // ─── SMS / OTP (e.g. MSG91) ────────────────────────────────────────────
    'sms' => [
        'provider'  => env('SMS_PROVIDER', 'msg91'),
        'auth_key'  => env('MSG91_AUTH_KEY'),
        'sender_id' => env('MSG91_SENDER_ID', 'GSTERP'),
        'template_id' => env('MSG91_TEMPLATE_ID'),
    ],

    // ─── WhatsApp (e.g. WA Business API) ──────────────────────────────────
    'whatsapp' => [
        'provider'    => env('WA_PROVIDER', 'wablas'),
        'token'       => env('WA_TOKEN'),
        'domain'      => env('WA_DOMAIN'),
        'from_number' => env('WA_FROM_NUMBER'),
    ],

];

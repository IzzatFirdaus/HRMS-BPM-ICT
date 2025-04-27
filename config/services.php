<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

  'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
    'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    'scheme' => 'https',
  ],

  'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
  ],

  'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  ],

  // Add configurations for other third-party services here.
  // For example, if you were using a specific external email provisioning API
  // that isn't covered by the standard mail drivers (configured in config/mail.php),
  // you would add its credentials here.
  //
  // 'custom_email_provisioning_service' => [
  //     'api_key' => env('CUSTOM_EMAIL_PROVISIONING_API_KEY'),
  //     'api_secret' => env('CUSTOM_EMAIL_PROVISIONING_API_SECRET'),
  //     'base_url' => env('CUSTOM_EMAIL_PROVISIONING_BASE_URL'),
  // ],

  // Example for a different service like GitHub for OAuth
  // 'github' => [
  //     'client_id' => env('GITHUB_CLIENT_ID'),
  //     'client_secret' => env('GITHUB_CLIENT_SECRET'),
  //     'redirect' => env('GITHUB_REDIRECT_URI'),
  // ],

  // Example for an ICT Equipment Loan related service
  // 'external_inventory_system' => [
  //     'api_key' => env('INVENTORY_API_KEY'),
  //     'base_url' => env('INVENTORY_BASE_URL'),
  // ],

];

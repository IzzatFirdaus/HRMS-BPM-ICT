<?php
return [
  // Minimum length for the temporary password generated for new email accounts
  'temp_password_length' => 12,

  // Statuses of EmailApplication that are considered ready for provisioning by this service
  'provisionable_statuses' => ['ready_for_processing', 'approved'], // Adjust based on your workflow statuses

  // Other email provisioning specific settings can go here
  // 'external_api_endpoint' => env('EXTERNAL_EMAIL_API_ENDPOINT'),
  // 'external_api_key' => env('EXTERNAL_EMAIL_API_KEY'),
];

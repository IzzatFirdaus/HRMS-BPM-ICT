<?php

// config/motac.php

return [
  'approval' => [
    // Minimum grade level required to be an approver (e.g., 41)
    'min_approver_grade_level' => env('MIN_APPROVER_GRADE_LEVEL', 41),
  ],
  'email_provisioning' => [
    // Configuration for integrating with the email system API
    'api_endpoint' => env('MOTAC_EMAIL_PROVISIONING_API_ENDPOINT'),
    'api_key' => env('MOTAC_EMAIL_PROVISIONING_API_KEY'),
    // Add other email provisioning related settings like default domain, etc.
    'default_domain' => 'motac.gov.my',
  ],
  'equipment_loan' => [
    // Default duration for equipment loans in days
    'default_loan_duration_days' => env('DEFAULT_LOAN_DURATION_DAYS', 7),
    // Email address or notification channel for BPM staff alerts
    'bpm_notification_recipient' => env('BPM_NOTIFICATION_RECIPIENT', 'bpm@motac.gov.my'),
  ],
  // Add other MOTAC-specific configuration values here
  'organization_name' => 'MOTAC',
  'organization_full_name' => 'Ministry of Tourism, Arts and Culture Malaysia',
];

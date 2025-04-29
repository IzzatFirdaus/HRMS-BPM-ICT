<?php

return [

  /*
    |--------------------------------------------------------------------------
    | User Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the application's user
    | management features, including editable attributes, roles, permissions,
    | and other user-related settings.
    |
    */

  /**
   * Attributes that users can edit on their own profile page.
   * Password change is typically handled separately but often associated with profile editing.
   */
  'profile_editable_attributes' => [
    'name',             // Display Name/Username
    'full_name',
    'personal_email',
    'phone_number',
    // Add other fields users can safely change themselves
  ],

  /**
   * Attributes that administrators can edit for any user.
   * This list is typically a superset of 'profile_editable_attributes'.
   * Sensitive fields like NRIC might require specific policies/permissions.
   */
  'admin_editable_attributes' => [
    'name',
    'full_name',
    'email',            // Often editable by admin, maybe not self
    'personal_email',
    'motac_email',      // New MOTAC attribute
    'employee_id',      // New MOTAC attribute
    'user_id_assigned', // New MOTAC attribute
    'phone_number',
    'identification_number', // NRIC - sensitive, often admin-only
    'department_id',    // Relationship - assumes Department model
    'position_id',      // Relationship - assumes Position/Designation model
    'grade_id',         // Relationship - assumes Grade model
    'service_status',   // New MOTAC attribute
    'appointment_type', // New MOTAC attribute
    'status',           // Account status (Active/Inactive)
    // Password is typically reset/changed via a separate form/action,
    // not directly editable in an admin form for security reasons.
  ],

  /**
   * List of sensitive attributes that require special permission to view.
   * Used in policies (e.g., UserPolicy).
   */
  'sensitive_attributes' => [
    'identification_number', // NRIC
    // Add other highly sensitive fields here
  ],

  /**
   * Default role(s) assigned to a new user upon creation.
   * Requires your application to use roles (e.g., Spatie/laravel-permission).
   */
  'default_roles' => [
    'user', // Example: Assign the 'user' role by default
  ],

  /**
   * Fixed options for select/dropdown fields if they are not
   * populated from a database table (like departments, grades, which usually are).
   * Use translation keys (__()) for labels.
   */
  'dropdown_options' => [
    'status' => [
      'Active' => 'Active',   // Or translate: __('Active')
      'Inactive' => 'Inactive', // Or translate: __('Inactive')
      // Add other status options
    ],
    'service_status' => [
      'Active' => 'Active',   // Or translate: __('Active')
      'Inactive' => 'Inactive', // Or translate: __('Inactive')
      // Add other service status options like '__("On Leave")'
    ],
    'appointment_type' => [
      'Permanent' => 'Permanent', // Or translate: __('Permanent')
      'Contract' => 'Contract', // Or translate: __('Contract')
      // Add other appointment types
    ],
    // Note: Department, Position, Grade options are typically fetched
    // from their respective database tables/models.
  ],

  /**
   * Default values for specific user attributes when a new user is created.
   */
  'defaults' => [
    'status' => 'Active', // Default account status
    'service_status' => 'Active', // Default service status
    // Add other default values
  ],

  /**
   * Feature flags to enable or disable specific user-related features.
   */
  'features' => [
    'profile_photos' => true, // Enable/disable profile photo uploads
    'password_change' => true, // Enable/disable user password change feature
    'admin_password_reset' => true, // Enable/disable admin password reset feature
    'soft_deletes' => true, // Enable/disable soft deleting users
    'audit_trail' => true, // Enable/disable tracking who created/updated/deleted users
  ],

];

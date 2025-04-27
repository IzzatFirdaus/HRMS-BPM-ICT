<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

  'defaults' => [
    'guard' => 'web', // Default web guard for session-based authentication
    'passwords' => 'users', // Default password broker for the 'users' provider
  ],

  /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token" (for stateless APIs like Sanctum)
    |
    */

  'guards' => [
    'web' => [
      'driver' => 'session', // Uses session for stateful authentication
      'provider' => 'users', // Uses the 'users' provider defined below
    ],

    // If you needed a separate guard for API users or other types, you would define it here.
    // Sanctum typically handles API authentication without needing a separate guard definition here,
    // relying on the 'sanctum' middleware and the default user provider.
    // 'api' => [
    //     'driver' => 'token', // Example token-based driver
    //     'provider' => 'users', // Could use the same or a different provider
    //     'hash' => false, // Set to true if tokens should be hashed in the database
    // ],
  ],

  /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

  'providers' => [
    'users' => [
      'driver' => 'eloquent', // Uses the Eloquent ORM
      'model' => App\Models\User::class, // Specifies the User model to use
    ],

    // If you had a different type of user (e.g., 'admins') or a different table,
    // you would define another provider here.
    // 'admins' => [
    //     'driver' => 'eloquent',
    //     'model' => App\Models\Admin::class,
    // ],

    // Example using the database provider (if not using Eloquent models)
    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

  'passwords' => [
    'users' => [
      'provider' => 'users', // Refers to the 'users' provider defined above
      'table' => 'password_reset_tokens', // Table used to store password reset tokens
      'expire' => 60, // Token valid for 60 minutes
      'throttle' => 60, // 60 seconds before generating a new token
    ],

    // If you had other password reset configurations for different providers, define them here.
    // 'admins' => [
    //     'provider' => 'admins',
    //     'table' => 'password_reset_tokens', // Could use the same or a different table
    //     'expire' => 60,
    //     'throttle' => 60,
    // ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours (10800 seconds).
    |
    */

  'password_timeout' => 10800, // 3 hours in seconds

];

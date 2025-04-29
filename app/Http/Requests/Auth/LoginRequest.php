<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// This Form Request is provided by Laravel Fortify.
// It handles the validation and the initial authentication attempt
// for the login process.

class LoginRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * For login, we typically allow all guests to make this request
   * to attempt authentication.
   */
  public function authorize(): bool
  {
    // Allow all guests to attempt login.
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * These rules validate the input fields submitted in the login form.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
  public function rules(): array
  {
    // Default validation rules for login.
    // 'email' is validated as a required string and valid email format.
    // 'password' is validated as a required string.
    // If you've configured Fortify to use 'username' instead of 'email'
    // for login, you would change the 'email' rule key to 'username'.
    return [
      'email' => ['required', 'string', 'email'], // Or 'username' if using that for login
      'password' => ['required', 'string'],
      'remember' => ['boolean'], // Validate the optional 'remember me' checkbox
    ];
  }

  /**
   * Attempt to authenticate the request's credentials.
   *
   * This method performs the actual authentication logic.
   * It first checks for rate limiting to prevent brute force attacks.
   * Then, it attempts to log the user in using the provided credentials
   * and the default web guard. If authentication fails, it increments
   * the rate limiter and throws a validation exception.
   *
   * This method is typically called from the AuthenticatedSessionController@store.
   *
   * @throws \Illuminate\Validation\ValidationException If authentication fails or rate limit is exceeded.
   */
  public function authenticate(): void
  {
    // Ensure the login request is not rate limited.
    // If too many attempts have been made, a ValidationException is thrown.
    $this->ensureIsNotRateLimited();

    // Attempt to authenticate the user using the default web guard ('web').
    // It uses the 'email' and 'password' fields from the request.
    // The `Auth::attempt` method internally triggers Fortify's `AttemptToAuthenticate` action
    // if Fortify is configured to handle authentication attempts.
    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
      // If the authentication attempt fails:
      // 1. Increment the rate limiter counter for this login attempt key.
      RateLimiter::hit($this->throttleKey());

      // 2. Throw a validation exception with an error message for the 'email' field.
      //    'auth.failed' is a translation key (defined in resources/lang/en/auth.php).
      throw ValidationException::withMessages([
        'email' => trans('auth.failed'), // Standard authentication failure message
      ]);
    }

    // If authentication is successful, clear the rate limiter for this login key.
    RateLimiter::clear($this->throttleKey());
  }

  /**
   * Ensure the login request is not rate limited.
   *
   * This method checks if the rate limit for login attempts has been exceeded
   * for the specific login key (usually email + IP address).
   *
   * @throws \Illuminate\Validation\ValidationException If the rate limit is exceeded.
   */
  public function ensureIsNotRateLimited(): void
  {
    // Check if the user has exceeded the maximum allowed login attempts (e.g., 5 attempts).
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) { // Default: 5 attempts
      return; // If not too many attempts, proceed
    }

    // If the rate limit IS exceeded:
    // 1. Fire a `Lockout` event. You can listen to this event to perform actions
    //    like notifying an administrator or logging the lockout attempt.
    event(new Lockout($this));

    // 2. Get the number of seconds remaining until the lockout expires.
    $seconds = RateLimiter::availableIn($this->throttleKey());

    // 3. Throw a validation exception with a rate limit error message.
    //    'auth.throttle' is a translation key (defined in resources/lang/en/auth.php).
    throw ValidationException::withMessages([
      'email' => trans('auth.throttle', [
        'seconds' => $seconds,
        'minutes' => ceil($seconds / 60),
      ]),
    ]);
  }

  /**
   * Get the rate limiting throttle key for the request.
   *
   * This key is used by the rate limiter to track login attempts.
   * It's typically a combination of the submitted email/username and the user's IP address
   * to uniquely identify the source of login attempts.
   *
   * @return string  The unique key for rate limiting.
   */
  protected function throttleKey(): string
  {
    // Create a unique key by lowercasing the submitted email/username,
    // combining it with the user's IP address, and transliterating it
    // to handle non-ASCII characters.
    return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    // If using 'username' for login, replace $this->input('email') with $this->input('username')
  }
}

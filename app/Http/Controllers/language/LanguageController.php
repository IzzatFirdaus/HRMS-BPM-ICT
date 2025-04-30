<?php

namespace App\Http\Controllers\language; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\Support\Facades\App; // Import the App facade for setting locale
use Illuminate\Support\Facades\Session; // Import Session facade for putting locale in session
use Illuminate\Http\RedirectResponse; // Import RedirectResponse for type hinting
use Illuminate\Http\Request; // Import Request for type hinting (less needed for this method)
use Illuminate\Support\Facades\Config; // Import Config facade for accessing config values
use Illuminate\Support\Facades\Log; // Import Log facade for logging - ADDED
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting authenticated user ID - ADDED


class LanguageController extends Controller
{
  /**
   * Swap the application locale (language).
   *
   * This method takes a locale string, validates it against the supported locales,
   * sets the locale in the user's session, sets the application locale,
   * and redirects the user back.
   *
   * @param string $locale The locale code (e.g., 'en', 'ms', 'ar').
   * @return \Illuminate\Http\RedirectResponse A redirect response back to the previous page.
   */
  public function swap($locale): RedirectResponse
  {
    // Define supported locales. It's best practice to get these from a configuration file.
    // Add an array like 'available_locales' => ['en' => 'English', 'ms' => 'Bahasa Melayu', 'ar' => 'العربية']
    // or a simple array like ['en', 'ms', 'ar'] in your config/app.php or a custom config file.
    // Example using config/app.php:
    $supportedLocales = Config::get('app.available_locales', ['en', 'ms']); // Default to ['en', 'ms'] if config not found

    // Validate if the provided locale is supported
    // If using key-value pairs in config, validate against keys: array_keys($supportedLocales)
    // If using a simple array in config: $supportedLocales
    if (! in_array($locale, is_array($supportedLocales) ? array_keys($supportedLocales) : $supportedLocales)) {
      // Log warning for invalid locale attempt
      Log::warning("Attempted to set unsupported locale: " . $locale, [ // Changed \Illuminate\Support\Facades\Log to Log
        'ip_address' => request()->ip(),
        'user_id' => Auth::check() ? Auth::id() : null, // Auth:: usage is now valid with the import
      ]);
      // Abort with a 400 Bad Request error if the locale is not supported
      abort(400, 'Unsupported language locale.');
    }

    // Store the selected locale in the user's session
    Session::put('locale', $locale);

    // Set the application locale for the current request
    App::setLocale($locale);

    // Redirect the user back to the previous page
    return redirect()->back();
  }
}

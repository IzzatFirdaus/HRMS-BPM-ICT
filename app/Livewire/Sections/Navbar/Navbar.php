<?php

namespace App\Livewire\Sections\Navbar;

use App\Models\Import; // Assumed to exist with 'status', 'current', 'total' attributes
use App\Models\User; // Assumed to exist
use Illuminate\Queue\Failed\FailedJobProviderInterface; // Keep if still needed elsewhere, but not for progress logic
use Illuminate\Support\Facades\Auth; // For accessing the authenticated user
use Illuminate\Support\Facades\DB; // For database interaction (removed failed_jobs truncate)
use Illuminate\Support\Facades\Log; // For logging errors
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Attributes\On; // For Livewire v3+ event listeners
use Livewire\Component; // Base Livewire component
use Illuminate\Support\Collection; // For notification collection type hint
use Exception; // For general exception handling


// Assumptions:
// 1. Import model exists and has 'status' (e.g., 'processing', 'completed', 'failed'),
//    'current', and 'total' attributes, updated by a background import job.
// 2. User model has built-in Laravel notifications (`Illuminate\Notifications\Notifiable` trait).
// 3. Livewire v3+ is used for attributes.
// 4. The view uses JS dispatch events like 'toastr'.
// 5. updateProgressBar is intended to be called via wire:poll="updateProgressBar" or a similar mechanism.

class Navbar extends Component
{
  // 👉 State Variables (Public properties that sync with the view)

  // Removed public $unreadNotifications property - now a computed property

  public bool $activeProgressBar = false; // Flag indicating if a process is active

  public int $percentage = 0; // Progress percentage (0-100)

  // Removed public $imports property - not used


  // 👉 Computed Property for Unread Notifications

  /**
   * Get the authenticated user's unread notifications.
   *
   * @return \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Support\Collection
   */
  #[Computed] // Define as a computed property
  public function unreadNotifications(): Collection // Use Collection type hint, or specific NotificationCollection
  {
    // Safely get unread notifications for the authenticated user
    // Replaced auth()->user() with Auth::user() to resolve IDE error
    return Auth::user()?->unreadNotifications ?? collect(); // Use null-safe operator and default to empty collection
  }


  // 👉 Lifecycle Hook

  public function mount(): void // Void return type
  {
    // Initial state setup
    $this->activeProgressBar = false;
    $this->percentage = 0;
    // Notifications are fetched by computed property on first render.
    // Progress bar state will be updated by the polling method or triggered event.
  }


  // 👉 Render method
  public function render()
  {
    // REMOVED DANGEROUS: DB::table('failed_jobs')->truncate(); // DO NOT TRUNCATE FAILED JOBS HERE!

    // Unread notifications are accessed via the computed property $this->unreadNotifications
    return view('livewire.sections.navbar.navbar');
  }


  // 👉 Listener to Refresh Notifications

  #[On('refreshNotifications')] // Listen for the 'refreshNotifications' event
  public function refreshNotificationsList(): void // Use a more descriptive name, void return type
  {
    // Livewire automatically re-evaluates computed properties when state changes or
    // when a method is called via an event listener. Accessing the computed property
    // or ensuring the user's notifications relationship is reloaded might be needed
    // depending on caching. simplest is just calling the method.
    // A direct call to re-fetch might be needed if Livewire's automatic refresh isn't sufficient
    // $this->unreadNotifications; // Accessing it might trigger re-evaluation
    // Auth::user()?->refresh(); // Refresh the user model itself if needed
    // Auth::user()?->unreadNotifications()->get(); // Force re-fetch relationship

    // For basic refresh, just letting Livewire re-evaluate the computed property is often enough.
    // If needed, uncomment: $this->resetComputed('unreadNotifications'); // Explicitly tell Livewire to re-evaluate
  }


  // 👉 Method for Progress Bar Update (Intended for wire:poll)

  // Assumes this method is called periodically by wire:poll="updateProgressBar"
  // Or it could be triggered by an event when import starts/updates
  #[On('updateProgressBar')] // Example listener if triggered by event instead of polling
  public function updateProgressBar(): void // Void return type
  {
    try {
      // Find the latest import record to check its status and progress
      // WARNING: This assumes the LATEST import record is the one currently processing.
      // This is unreliable if multiple imports can happen or if records aren't cleaned up.
      // A better approach is to track a specific import ID, potentially passed via event.
      $latestImport = Import::latest()->first();

      if ($latestImport) {
        if ($latestImport->status === 'processing') {
          $this->activeProgressBar = true;
          // Calculate percentage, handle total being zero to avoid division by zero
          $this->percentage = ($latestImport->total > 0) ? (int) round($latestImport->current / $latestImport->total * 100) : 0;
          // Ensure percentage doesn't exceed 100
          if ($this->percentage > 100) $this->percentage = 100;
        } elseif ($latestImport->status === 'completed') {
          // Process finished successfully
          $this->activeProgressBar = false;
          $this->percentage = 100; // Ensure it goes to 100%
          // Flash success message only once when it completes
          // Use a session key to track if the completion message has been shown for this import
          if (session()->get('import_status') !== 'completed' || session()->get('latest_import_id') !== $latestImport->id) {
            session()->flash('success', __('Import Completed Successfully!')); // Translated
            $this->dispatch('toastr', type: 'success', message: __('Import Done!'));
            session(['import_status' => 'completed', 'latest_import_id' => $latestImport->id]); // Mark status and import ID in session
          }
        } elseif ($latestImport->status === 'failed') {
          // Process failed
          $this->activeProgressBar = false;
          $this->percentage = 0; // Reset percentage or set to a specific error value
          // Flash error message only once when it fails
          if (session()->get('import_status') !== 'failed' || session()->get('latest_import_id') !== $latestImport->id) {
            // Assuming the import record might have an error message field
            $errorMessage = $latestImport->error_message ?? __('An unknown error occurred during import.');
            session()->flash('error', __('Import Failed: :message', ['message' => $errorMessage])); // Translated with message
            $this->dispatch('toastr', type: 'error', message: __('Import Failed!'));
            session(['import_status' => 'failed', 'latest_import_id' => $latestImport->id]); // Mark status and import ID in session
          }
        } else {
          // Handle other potential statuses or unknown state
          $this->activeProgressBar = false;
          $this->percentage = 0;
          // Reset session status if process is no longer active based on this import record
          if (session()->has('import_status') && session()->get('latest_import_id') === $latestImport->id) {
            session()->forget(['import_status', 'latest_import_id']);
          }
        }
      } else {
        // No import record found, process is likely not active or hasn't started
        $this->activeProgressBar = false;
        $this->percentage = 0;
        // Reset session status if any was set
        if (session()->has('import_status')) {
          session()->forget(['import_status', 'latest_import_id']);
        }
      }
    } catch (Exception $e) {
      Log::error('Navbar component: Error updating progress bar state from Import model.', ['exception' => $e]);
      // Handle error during update logic itself
      $this->activeProgressBar = false;
      $this->percentage = 0;
      session()->flash('error', __('An error occurred while updating import progress.')); // Translated
      $this->dispatch('toastr', type: 'error', message: __('Progress Update Error!'));
      // Reset session status
      if (session()->has('import_status')) {
        session()->forget(['import_status', 'latest_import_id']);
      }
    }
    // If the import status is 'processing', wire:poll will keep calling this method.
    // When status changes to 'completed' or 'failed', the logic here will set activeProgressBar to false,
    // which should stop the polling if wire:poll is conditional on activeProgressBar.
  }


  // 👉 Notification Actions

  public function markNotificationAsRead(string $notificationId): void // Use string type hint, void return type
  {
    // Safely get the authenticated user and their unread notifications
    // Replaced auth()->user() with Auth::user() to resolve IDE error
    $user = Auth::user();
    if ($user) {
      $notification = $user->unreadNotifications->where('id', $notificationId)->first();
      if ($notification) {
        $notification->markAsRead();
        // No need to manually update $this->unreadNotifications if it's a computed property.
        // The refresh event dispatched below will cause Livewire to re-evaluate it.
      }
    }

    // Dispatch event to refresh notifications (potentially other components too)
    // Dispatching to self is often sufficient if only this component's UI needs update.
    $this->dispatch('refreshNotifications')->self();
    // If other components need to know notifications changed, dispatch globally:
    // $this->dispatch('refreshNotifications');
  }

  public function markAllNotificationsAsRead(): void // Void return type
  {
    // Safely get the authenticated user
    // Replaced auth()->user() with Auth::user() to resolve IDE error
    $user = Auth::user();
    if ($user) {
      // Mark all unread notifications as read
      $user->unreadNotifications->markAsRead(); // markAsRead() is chainable on collections

      // No need to manually update $this->unreadNotifications if it's a computed property.
      // The refresh event dispatched below will cause Livewire to re-evaluate it.
    }

    // Dispatch event to refresh notifications (potentially other components too)
    // Dispatching to self is often sufficient.
    $this->dispatch('refreshNotifications')->self();
    // If other components need to know notifications changed, dispatch globally:
    // $this->dispatch('refreshNotifications');
  }
}

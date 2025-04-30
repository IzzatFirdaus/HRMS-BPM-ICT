<?php

namespace App\Livewire\Sections\Navbar;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Ensure User model is imported
use Illuminate\Support\Facades\Log;
// Removed: use Livewire\Attributes\Computed; // No longer needed for this approach
use Livewire\Attributes\On; // Keep for event listeners
use Illuminate\Support\Collection; // Ensure Collection is imported
use Exception;


class Navbar extends Component
{
  // 👉 Public Property for Unread Notifications - This will be available in the view
  // Initialize as an empty collection to prevent errors before mount runs
  public Collection $unreadNotifications;

  public bool $activeProgressBar = false;
  public int $percentage = 0;


  // 👉 Lifecycle Hook: mount() - Called when the component is initialized
  // Fetch initial data needed for the view here.
  public function mount(): void
  {
    $user = Auth::user();

    // Fetch the authenticated user's unread notifications
    // Assuming your User model uses the Notifiable trait
    // Assign directly to the public property
    $this->unreadNotifications = $user?->unreadNotifications ?? collect(); // Use ?? collect() for safety

    // Initial state setup for progress bar (can also be done here)
    $this->activeProgressBar = false;
    $this->percentage = 0;

    // If you need to check initial import status on mount:
    // $this->updateProgressBar(); // Uncomment if progress bar state should be checked on page load
  }


  // 👉 Render method
  public function render()
  {
    // Public properties ($this->unreadNotifications, $this->activeProgressBar, $this->percentage)
    // are automatically available to the view.
    return view('livewire.sections.navbar.navbar');
  }


  // 👉 Listener to Refresh Notifications (Optional: could re-call mount or just let Livewire refresh state)

  #[On('refreshNotifications')] // Listen for the 'refreshNotifications' event
  public function refreshNotificationsList(): void
  {
    // Re-fetch notifications and update the public property
    $user = Auth::user();
    $this->unreadNotifications = $user?->unreadNotifications ?? collect();

    // Livewire will automatically detect the change to the public property
    // and update the relevant parts of the view.
  }


  // 👉 Method for Progress Bar Update (Intended for wire:poll)

  // Assumes this method is called periodically by wire:poll="updateProgressBar"
  #[On('updateProgressBar')] // Example listener if triggered by event instead of polling
  public function updateProgressBar(): void
  {
    try {
      $latestImport = \App\Models\Import::latest()->first(); // Fully qualify Import model

      if ($latestImport) {
        if ($latestImport->status === 'processing') {
          $this->activeProgressBar = true;
          $this->percentage = ($latestImport->total > 0) ? (int) round($latestImport->current / $latestImport->total * 100) : 0;
          if ($this->percentage > 100) $this->percentage = 100;
        } elseif ($latestImport->status === 'completed') {
          $this->activeProgressBar = false;
          $this->percentage = 100;
          if (session()->get('import_status') !== 'completed' || session()->get('latest_import_id') !== $latestImport->id) {
            session()->flash('success', __('Import Completed Successfully!'));
            $this->dispatch('toastr', type: 'success', message: __('Import Done!'));
            session(['import_status' => 'completed', 'latest_import_id' => $latestImport->id]);
          }
        } elseif ($latestImport->status === 'failed') {
          $this->activeProgressBar = false;
          $this->percentage = 0;
          if (session()->get('import_status') !== 'failed' || session()->get('latest_import_id') !== $latestImport->id) {
            $errorMessage = $latestImport->error_message ?? __('An unknown error occurred during import.');
            session()->flash('error', __('Import Failed: :message', ['message' => $errorMessage]));
            $this->dispatch('toastr', type: 'error', message: __('Import Failed!'));
            session(['import_status' => 'failed', 'latest_import_id' => $latestImport->id]);
          }
        } else {
          $this->activeProgressBar = false;
          $this->percentage = 0;
          if (session()->has('import_status') && session()->get('latest_import_id') === $latestImport->id) {
            session()->forget(['import_status', 'latest_import_id']);
          }
        }
      } else {
        $this->activeProgressBar = false;
        $this->percentage = 0;
        if (session()->has('import_status')) {
          session()->forget(['import_status', 'latest_import_id']);
        }
      }
    } catch (Exception $e) {
      Log::error('Navbar component: Error updating progress bar state from Import model.', ['exception' => $e]);
      $this->activeProgressBar = false;
      $this->percentage = 0;
      session()->flash('error', __('An error occurred while updating import progress.'));
      $this->dispatch('toastr', type: 'error', message: __('Progress Update Error!'));
      if (session()->has('import_status')) {
        session()->forget(['import_status', 'latest_import_id']);
      }
    }
  }


  // 👉 Notification Actions

  public function markNotificationAsRead(string $notificationId): void
  {
    $user = Auth::user();
    if ($user) {
      // Find the notification directly on the user's notifications relationship
      // Use find() which returns null if not found, preventing errors
      $notification = $user->notifications()->find($notificationId);
      if ($notification) {
        // Use markAsRead() method provided by the Notifiable trait
        $notification->markAsRead();

        // After marking as read, refresh the public property
        $this->unreadNotifications = $user->unreadNotifications ?? collect(); // Re-fetch unread ones
      } else {
        Log::warning('Navbar: Attempted to mark non-existent or already read notification as read.', ['notification_id' => $notificationId, 'user_id' => $user->id]);
      }
    } else {
      Log::warning('Navbar: Attempted to mark notification as read for unauthenticated user.', ['notification_id' => $notificationId]);
    }

    // Dispatch event to refresh notifications list in the view
    // Dispatching to self is usually sufficient if only this component's list needs update.
    $this->dispatch('refreshNotifications')->self();
  }

  public function markAllNotificationsAsRead(): void
  {
    $user = Auth::user();
    if ($user) {
      // Mark all unread notifications as read
      // The markAsRead() method on the collection returns the number of notifications marked
      $countMarked = $user->unreadNotifications->markAsRead();

      if ($countMarked > 0) {
        // After marking as read, refresh the public property
        $this->unreadNotifications = $user->unreadNotifications ?? collect(); // Re-fetch unread ones
        // Optional: Show a success message if any notifications were marked
        // session()->flash('success', __(':count notifications marked as read.', ['count' => $countMarked]));
        // $this->dispatch('toastr', type: 'success', message: __('Notifications updated.'));
      } else {
        // Optional: Show a message if there were no unread notifications
        // $this->dispatch('toastr', type: 'info', message: __('No unread notifications to mark.'));
      }
    } else {
      Log::warning('Navbar: Attempted to mark all notifications as read for unauthenticated user.');
    }


    // Dispatch event to refresh notifications list in the view
    $this->dispatch('refreshNotifications')->self();
  }
}

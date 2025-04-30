<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\Http\Request; // Standard Request object (optional, not strictly needed for index)
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\View\View; // Import View for type hinting


class NotificationController extends Controller
{
  /**
   * Apply authentication middleware to this controller.
   */
  public function __construct()
  {
    // Ensure the user is authenticated to view their notifications
    $this->middleware('auth');
  }

  /**
   * Display a listing of the authenticated user's notifications.
   *
   * Fetches the latest notifications for the logged-in user with pagination.
   *
   * @return \Illuminate\View\View The view displaying the list of notifications.
   */
  public function index(): View // Added return type hint
  {
    // Get the authenticated user using the Auth facade.
    // The PHPDoc comment helps linters understand the type of $user.
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Fetch the authenticated user's notifications
    // Order by latest and paginate the results for performance.
    // You might eager load relationships if your notification data includes references to other models.
    $notifications = $user->notifications()
      ->latest() // Order by the latest notification
      // ->with('someRelationship') // Optional: Eager load relationships if needed for display
      ->paginate(15); // Paginate the notifications (e.g., 15 per page)

    // Optional: Mark notifications as read when the user views the index page.
    // You might only want to mark *unread* notifications as read.
    // $user->unreadNotifications->markAsRead();


    // Return the view with the paginated list of notifications
    // Ensure your view file name matches: resources/views/notifications/index.blade.php
    return view('notifications.index', compact('notifications'));
  }

  // Optional: Add a method to mark a specific notification as read (e.g., via AJAX)
  // public function markAsRead(Request $request, $notificationId)
  // {
  //      $notification = Auth::user()->notifications()->where('id', $notificationId)->first(); // Use Auth::user() here too
  //      if ($notification) {
  //          $notification->markAsRead();
  //          return response()->json(['status' => 'success']);
  //      }
  //      return response()->json(['status' => 'not found'], 404);
  // }

  // Optional: Add a method to mark all notifications as read
  // public function markAllAsRead()
  // {
  //      Auth::user()->unreadNotifications->markAsRead(); // Use Auth::user() here too
  //      return redirect()->back()->with('status', 'All notifications marked as read.');
  // }
}

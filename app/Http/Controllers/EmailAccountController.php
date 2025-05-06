<?php

namespace App\Http\Controllers;

use App\Models\EmailApplication;
use App\Services\EmailProvisioningService; // Injected
use App\Services\EmailApplicationService; // Inject EmailApplicationService for workflow logic
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

// This controller handles IT Admin actions for email provisioning.
// It complements EmailApplicationController which handles the application lifecycle.

class EmailAccountController extends Controller
{
  use AuthorizesRequests;

  // Inject both services needed
  protected $emailProvisioningService;
  protected $emailApplicationService; // Service for core workflow actions like processing

  /**
   * Inject services and apply authentication/authorization middleware.
   *
   * @param \App\Services\EmailProvisioningService $emailProvisioningService
   * @param \App\Services\EmailApplicationService $emailApplicationService
   */
  public function __construct(EmailProvisioningService $emailProvisioningService, EmailApplicationService $emailApplicationService)
  {
    $this->middleware('auth');
    // Apply middleware to restrict access to IT Admin role or permission for this controller
    // Assumes a 'process email application' permission or similar is defined and assigned to IT Admins.
    $this->middleware('can:process email application'); // Example permission name

    $this->emailProvisioningService = $emailProvisioningService;
    $this->emailApplicationService = $emailApplicationService;
  }

  /**
   * Handle the IT Admin action to process a pending email application.
   * Receives IT admin input, delegates to service for processing logic and transaction handling.
   *
   * @param \Illuminate\Http\Request $request The request containing provisioning outcome data.
   * @param \App\Models\EmailApplication $emailApplication The application being processed (Route Model Binding).
   * @return \Illuminate\Http\RedirectResponse
   */
  public function process(Request $request, EmailApplication $emailApplication): \Illuminate\Http\RedirectResponse
  {
    // 1. Authorization Check (Policy)
    // Verify the authenticated user is an IT Administrator and has permission to process this specific application.
    // Assumes a 'process' method exists in EmailApplicationPolicy for IT Admin.
    $this->authorize('process', $emailApplication);

    // 2. Ensure the application is in the correct status for processing (e.g., pending_admin)
    // Assumes EmailApplication model has STATUS_PENDING_ADMIN constant and isPendingAdmin() method
    if ($emailApplication->status !== EmailApplication::STATUS_PENDING_ADMIN) {
      Log::warning('Attempted to process email application ID ' . ($emailApplication->id ?? 'N/A') . ' not in pending_admin status.', [
        'user_id' => Auth::id(),
        'current_status' => $emailApplication->status,
      ]);
      return redirect()->back()->with('error', 'Permohonan ini tidak dalam status "Pending IT Admin" dan tidak boleh diproses.');
    }

    // 3. Validation for Processing Data
    // Validate the input received from the IT Admin form.
    // RECOMMENDATION: Use a dedicated Form Request (e.g., ProcessEmailApplicationRequest).
    $validatedData = $request->validate([
      'final_assigned_email' => 'required|email|max:255',
      'user_id_assigned' => 'nullable|string|max:255', // User ID assigned in external system
      'status' => 'required|in:' . EmailApplication::STATUS_PROCESSING . ',' . EmailApplication::STATUS_COMPLETED, // NOTE: This status validation might be better handled *within* the service based on the provisioning outcome, not directly from request. Consider if you really want the admin to set 'processing' or 'completed' status directly via a dropdown/input.
      'admin_notes' => 'nullable|text', // Assuming admin_notes column is added via migration later
    ]);

    Log::info('IT Admin processing request received for application ID ' . ($emailApplication->id ?? 'N/A'), [
      'processing_user_id' => Auth::id(),
      'validated_data_keys' => array_keys($validatedData),
    ]);

    try {
      // 4. Delegate Processing Logic to EmailApplicationService
      // Call the service method that handles the state transition, user update,
      // calls the provisioning service, and manages the transaction.
      // Pass the application instance (Route Model Binding), the validated data, and the acting admin user.
      $updatedApplication = $this->emailApplicationService->process( // *** CORRECTED METHOD NAME HERE ***
        $emailApplication,
        $validatedData,
        Auth::user() // Pass the IT Admin user
      );

      // 5. Redirect based on the final status of the application after the service call
      // Check against the status constants defined in EmailApplication model
      if ($updatedApplication->status === EmailApplication::STATUS_COMPLETED) { // Check against constant
        Log::info('Email application processing and provisioning completed via service.', [
          'application_id' => $updatedApplication->id,
          'final_status' => $updatedApplication->status,
        ]);
        return redirect()->route('email-applications.show', $updatedApplication)
          ->with('success', 'Permohonan e-mel berjaya diproses dan akaun ditetapkan.');
      } elseif ($updatedApplication->status === EmailApplication::STATUS_PROVISIONING_FAILED) { // Check against constant
        Log::warning('Email application processing failed via service.', [
          'application_id' => $updatedApplication->id,
          'final_status' => $updatedApplication->status,
          'rejection_reason' => $updatedApplication->rejection_reason,
        ]);
        return redirect()->route('email-applications.show', $updatedApplication)
          ->with('error', 'Gagal memproses permohonan e-mel: ' . ($updatedApplication->rejection_reason ?? 'Ralat tidak diketahui.'));
      } else {
        // Should ideally not happen if service sets final status, but as a fallback:
        Log::error("EmailApplicationService::process did not result in a final status for application ID: " . ($emailApplication->id ?? 'N/A') . ". Final status was: " . ($updatedApplication->status ?? 'N/A'));
        return redirect()->back()->with('error', 'Terdapat masalah dalam memproses permohonan. Sila cuba lagi.');
      }
    } catch (Exception $e) {
      // Catch any exceptions thrown by the service layer (e.g., transaction failed, API call error).
      Log::error('Exception caught in EmailAccountController@process for application ID ' . ($emailApplication->id ?? 'N/A') . ': ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
      return back()->withInput()->with('error', 'Gagal memproses permohonan e-mel disebabkan ralat: ' . $e->getMessage());
    }
  }

  // Optional methods for listing provisioned accounts or other admin tasks...
}

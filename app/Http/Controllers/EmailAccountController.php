<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\EmailApplication; // Need to interact with applications
use App\Services\EmailProvisioningService; // Inject the service for provisioning logic
use Illuminate\Http\Request; // For handling the processing form submission
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\SupportFacades\Validator; // Might need manual validation or use a Form Request
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for manual authorization
use Exception;

// This controller is intended to handle actions related to *provisioning* email accounts,
// primarily performed by IT Administrators, as per the system design (Section 5.1, step 4).
// It complements EmailApplicationController which manages the application lifecycle from submission to completion.
// It is *not* a resource controller managing EmailAccount models in the DB.

class EmailAccountController extends Controller
{
  use AuthorizesRequests; // Use this trait for manual authorize() calls

  protected $emailProvisioningService;

  /**
   * Inject the EmailProvisioningService and apply authentication/authorization middleware.
   *
   * @param \App\Services\EmailProvisioningService $emailProvisioningService
   */
  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->middleware('auth');
    // Apply middleware to restrict access to IT Admin role or permission
    // Assuming a 'process-email-applications' permission or 'is_it_admin' role check
    // This middleware could check Gate or Policy before specific methods are even reached
    // $this->middleware('can:process-email-applications');
    // Or a simpler check: $this->middleware(function ($request, $next) {
    //     if (!Auth::user()->is_it_admin) { // Assuming 'is_it_admin' property on User model
    //         abort(403, 'Unauthorized action.');
    //     }
    //     return $next($request);
    // });

    $this->emailProvisioningService = $emailProvisioningService;
=======
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
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
  }

  /**
   * Handle the IT Admin action to process a pending email application.
<<<<<<< HEAD
   * This method receives the application ID and the provisioning outcome data from an IT admin form.
   * It updates the application with the assigned details and triggers the external provisioning via the service.
   *
   * Based on Section 5.1 (step 4) and Section 9.2 (EmailProvisioningService).
   * This action would typically be triggered by a form submission from an IT admin interface
   * (e.g., from the EmailApplication show page when viewed by an admin).
   *
   * @param \Illuminate\Http\Request $request The request containing provisioning outcome data (e.g., final email, user ID string).
   * @param \App\Models\EmailApplication $emailApplication The application being processed.
=======
   * Receives IT admin input, delegates to service for processing logic and transaction handling.
   *
   * @param \Illuminate\Http\Request $request The request containing provisioning outcome data.
   * @param \App\Models\EmailApplication $emailApplication The application being processed (Route Model Binding).
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
   * @return \Illuminate\Http\RedirectResponse
   */
  public function process(Request $request, EmailApplication $emailApplication): \Illuminate\Http\RedirectResponse
  {
<<<<<<< HEAD
    // 1. Authorization Check
    // Verify the authenticated user is an IT Administrator and has permission to process this application stage.
    // Use the AuthorizesRequests trait's authorize() method with a Policy action.
    // Assumes a 'process' method exists in EmailApplicationPolicy for IT Admin.
    $this->authorize('process', $emailApplication);

    // Ensure the application is in the correct status for processing (e.g., pending_admin)
    if (!$emailApplication->isPendingAdmin()) {
      Log::warning('Attempted to process email application not in pending_admin status.', [
        'application_id' => $emailApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $emailApplication->status,
      ]);
      return redirect()->back()->with('error', 'Permohonan ini tidak dalam status "Pending IT Admin" dan tidak boleh diproses.'); // Malay error
    }

    // 2. Validation for Processing Data
    // Validate the input received from the IT Admin form (e.g., the final assigned email/ID, and the next status).
    // Consider using a dedicated Form Request for cleaner validation (e.g., ProcessEmailApplicationRequest).
    $validatedData = $request->validate([
      'final_assigned_email' => 'required|email|max:255', // Ensure this matches the users.motac_email field requirements
      'user_id_assigned' => 'nullable|string|max:255', // Use nullable as User ID might not apply to all types (e.g., only email accounts)
      // The IT admin might also select the next status (processing or completed)
      'status' => 'required|in:' . EmailApplication::STATUS_PROCESSING . ',' . EmailApplication::STATUS_COMPLETED,
      // Add any other fields the IT admin might submit (e.g., notes, assigned resource ID)
    ]);

    // Log the processing attempt details
    Log::info('Attempting IT Admin processing for email application.', [
      'application_id' => $emailApplication->id,
      'processing_user_id' => Auth::id(),
      'validated_data_keys' => array_keys($validatedData), // Log keys, not sensitive values
    ]);

    try {
      // 3. Update the Application Model with Processed Data
      // Before calling the service, update the application model instance with the data provided by the IT admin.
      // The service method (provisionAccount) will then work with this updated model.
      $emailApplication->final_assigned_email = $validatedData['final_assigned_email'];
      $emailApplication->final_assigned_user_id = $validatedData['user_id_assigned'] ?? null; // Ensure this matches the model/migration field name/type
      $emailApplication->status = $validatedData['status']; // Update status based on IT admin input
      // Set provisioned_at timestamp if status is 'completed' or 'processing' depending on your flow
      if ($emailApplication->status === EmailApplication::STATUS_COMPLETED) {
        // This column needs to be added to your email_applications migration if it doesn't exist!
        // Based on previous review, it was NOT in the migration but was in the model.
        // Assuming you WILL add it via a new migration later, keep this line.
        $emailApplication->provisioned_at = now();
      }
      // Save the updates to the database BEFORE triggering the external provisioning
      $emailApplication->save();


      // 4. Delegate External Provisioning Logic to the Service
      // Call the service method that interacts with the external email system.
      // The service will use the updated $emailApplication model instance.
      // The service also handles updating the related User model and sending notifications.
      // Note: The service method is named 'provisionAccount' based on the provided code.
      $this->emailProvisioningService->provisionAccount($emailApplication); // Pass the updated application model

      // 5. Log Success and Redirect
      Log::info('Email application processing successful.', [
        'application_id' => $emailApplication->id,
        'processed_by_user_id' => Auth::id(),
        'final_status' => $emailApplication->fresh()->status, // Get the final status after service
      ]);

      return redirect()->route('email-applications.show', $emailApplication)
        ->with('success', 'Permohonan e-mel berjaya diproses oleh IT Admin.'); // Malay success message

    } catch (Exception $e) {
      // 6. Log Error and Redirect Back
      Log::error('Error processing email application.', [
        'application_id' => $emailApplication->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $validatedData, // Log validated data on error for debugging
      ]);
      return back()->withInput()->with('error', 'Gagal memproses permohonan e-mel disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  // --- Information regarding Email Service (Mailtrap vs. Production) ---
  // This information is NOT implemented within the controller code itself.
  // It is handled by Laravel's mail configuration and the service layer.

  /*
    | How Email Sending Works (Mailtrap vs. Production):
    |
    | 1. Configuration: The settings for sending emails (SMTP server, port, username, password,
    |    encryption) are defined in the project's `.env` file and configured in `config/mail.php`.
    |    - In your local development `.env`, you will set these values to point to Mailtrap's SMTP credentials.
    |      (e.g., MAIL_MAILER=smtp, MAIL_HOST=sandbox.smtp.mailtrap.io, MAIL_PORT=2525, etc. - as configured previously).
    |    - In your staging and production `.env` files, you will set these values to point to
    |      MOTAC's actual mail server or a transactional email service provider (e.g., SendGrid, Postmark, AWS SES).
    |      (e.g., MAIL_MAILER=smtp, MAIL_HOST=smtp.office365.com, MAIL_PORT=587, etc.).
    |
    | 2. Triggering Emails: When an email needs to be sent (e.g., notification that provisioning is complete),
    |    the application uses Laravel's built-in mail functionality, typically via Notifications
    |    (Section 9.5 of design) or by sending Mailable classes.
    |    - Example: In the EmailProvisioningService's 'provisionAccount' method, after successfully updating the application and
    |      potentially interacting with the external system, the service calls `$this->sendWelcomeEmail(...)` or
    |      triggers a Notification like `$emailApplication->user->notify(new \App\Notifications\EmailProvisioningComplete($emailApplication));`.
    |    - The controller *calls* the service method ('provisionAccount'), which *internally* triggers the notification/email sending via Laravel's Mailer.
    |
    | 3. Laravel's Role: Laravel reads the email configuration from the `.env` (via `config/mail.php`)
    |    and uses the specified mailer (smtp, sendmail, log, etc.) to send the email.
    |    - In development, the 'smtp' mailer with Mailtrap credentials sends the email to your Mailtrap inbox.
    |    - In production, the 'smtp' mailer with production credentials sends the email to the actual recipient.
    |
    | The controller's responsibility is to handle the request, authorize the user, validate the input, update the application model
    | with the IT admin's decisions, and then initiate the core provisioning process by calling the relevant service method.
    | It does not contain the email configuration logic itself.
    */


  // --- Optional: Add a method to list provisioned accounts for IT Admin ---
  /**
   * Display a list of provisioned email accounts (users with MOTAC email assigned).
   * This would be an IT Administrator view/report.
   *
   * Based on Section 6.2 (Admin Dashboard) and Section 9.5 (Reporting).
   */
  // public function indexProvisionedAccounts()
  // {
  // Authorization check for IT Admin to view provisioned accounts
  // $this->authorize('viewProvisionedAccounts'); // Assuming a policy method

  // Fetch users who have a MOTAC email assigned
  // $provisionedUsers = \App\Models\User::whereNotNull('motac_email')
  //                                    ->with(['department', 'position', 'grade']) // Eager load relationships
  //                                    ->orderBy('full_name')
  //                                    ->paginate(20);

  // return view('email-accounts.provisioned.index', compact('provisionedUsers'));
  // }

  // --- Optional: Add methods for other IT Admin tasks (deactivation, etc.) ---
  // public function deactivate(\App\Models\User $user) { ... }
=======
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
      'status' => 'required|in:' . EmailApplication::STATUS_PROCESSING . ',' . EmailApplication::STATUS_COMPLETED,
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
      $updatedApplication = $this->emailApplicationService->handleAdminProcessingUpdate(
        $emailApplication,
        $validatedData,
        Auth::user() // Pass the IT Admin user
      );

      // 5. Redirect based on the final status of the application after the service call
      if ($updatedApplication->isCompleted()) {
        Log::info('Email application processing and provisioning completed via service.', [
          'application_id' => $updatedApplication->id,
          'final_status' => $updatedApplication->status,
        ]);
        return redirect()->route('email-applications.show', $updatedApplication)
          ->with('success', 'Permohonan e-mel berjaya diproses dan akaun ditetapkan.');
      } elseif ($updatedApplication->isRejected()) { // Assuming provisioning_failed leads to rejected status
        Log::warning('Email application processing failed via service.', [
          'application_id' => $updatedApplication->id,
          'final_status' => $updatedApplication->status,
          'rejection_reason' => $updatedApplication->rejection_reason,
        ]);
        return redirect()->route('email-applications.show', $updatedApplication)
          ->with('error', 'Gagal memproses permohonan e-mel: ' . ($updatedApplication->rejection_reason ?? 'Ralat tidak diketahui.'));
      } else {
        // Should ideally not happen if service sets final status, but as a fallback:
        Log::error("handleAdminProcessingUpdate did not result in a final status for application ID: " . ($emailApplication->id ?? 'N/A') . ". Final status was: " . ($updatedApplication->status ?? 'N/A'));
        return redirect()->back()->with('error', 'Terdapat masalah dalam memproses permohonan. Sila cuba lagi.');
      }
    } catch (Exception $e) {
      // Catch any exceptions thrown by the service layer (e.g., transaction failed, API call error).
      Log::error('Exception caught in EmailAccountController@process for application ID ' . ($emailApplication->id ?? 'N/A') . ': ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
      return back()->withInput()->with('error', 'Gagal memproses permohonan e-mel disebabkan ralat: ' . $e->getMessage());
    }
  }

  // Optional methods for listing provisioned accounts or other admin tasks...
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
}

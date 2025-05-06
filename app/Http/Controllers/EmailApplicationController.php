<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if applications management is an admin function.

use App\Models\EmailApplication; // Import the EmailApplication model
// Assuming an EmailApplicationService handles application lifecycle logic (create, update, maybe status changes)
use App\Services\EmailApplicationService; // Use EmailApplicationService for application logic
// Assuming a StoreEmailApplicationRequest Form Request exists for validation
use App\Http\Requests\StoreEmailApplicationRequest; // Import Form Request for creation validation
// Assuming an UpdateEmailApplicationRequest Form Request exists for update validation
use App\Http\Requests\UpdateEmailApplicationRequest; // Import Form Request for update validation (create this if it doesn't exist)
use Illuminate\Http\Request; // Standard Request object (less needed with Form Requests)
use Illuminate\Support\Facades\Auth; // Import Auth facade for accessing authenticated user
use Illuminate\Support\Facades\Gate; // Import Gate (less needed with Policies)
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors


// Class name should match the route definition: EmailApplicationsController
class EmailApplicationController extends Controller // Renamed to plural as per resource controller convention
{
  protected $emailApplicationService; // Use the EmailApplicationService

  /**
   * Inject the EmailApplicationService and apply authentication/authorization middleware.
   *
   * @param \App\Services\EmailApplicationService $emailApplicationService The application service instance.
   */
  public function __construct(EmailApplicationService $emailApplicationService) // Inject EmailApplicationService
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for resource methods
    // Assumes an EmailApplicationPolicy exists and is registered.
    // Policy methods: viewAny, view, create, update, delete
    $this->authorizeResource(EmailApplication::class, 'email_application'); // Use 'email_application' as parameter name

    $this->emailApplicationService = $emailApplicationService; // Assign the injected service
  }

  /**
   * Display a listing of the email applications.
   * Fetches applications the current user is authorized to view.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny').
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions (using scopes).

    // Fetch applications with their related user, ordered by latest.
    $applications = EmailApplication::query()
      ->with('user') // Eager load the related user (the applicant)
      ->latest(); // Order by latest creation date

    // --- Alternative/Additional Filtering (If not fully handled by Policy Scopes) ---
    // If using manual filtering in addition to/instead of policy scopes:
    // Example: Allow admins/approvers to view all, users only their own.
    // You might check permissions/roles here.
    // if (!Auth::user()->can('viewAllEmailApplications')) { // Assuming a permission check
    //     $applications->where('user_id', Auth::id()); // Filter by the current user
    // }
    // --- End Filtering ---

    $applications = $applications->paginate(10); // Paginate the results

    // Return the view with the list of applications
    // Ensure your view file name matches: resources/views/email-applications/index.blade.php
    return view('email-applications.index', compact('applications'));
  }

  /**
   * Show the form for creating a new email application.
   * This method is typically for displaying a standard Blade form.
   * If using Livewire/Inertia for the form, this method might be simpler or just return JSON config.
   *
   * @return \Illuminate\View\View
   */
  public function create(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create')

    // Load any necessary data for the form (e.g., user's department, position, grade to pre-fill)
    $user = Auth::user();
    // You might need to pass user details or lists of departments/positions if not handled in the view/Livewire
    // $departments = \App\Models\Department::all();
    // $positions = \App\Models\Position::all();

    // Return the view for creating an application
    // Ensure your view file name matches: resources/views/email-applications/create.blade.php
    return view('email-applications.create', compact('user')); // Passing user might be helpful
  }

  /**
   * Store a newly created email application in storage.
   * Uses the StoreEmailApplicationRequest Form Request for validation.
   * Delegates the creation logic to the EmailApplicationService.
   *
   * @param  \App\Http\Requests\StoreEmailApplicationRequest  $request  The validated incoming registration request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreEmailApplicationRequest $request): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create')
    // Validation handled automatically by StoreEmailApplicationRequest

    // Log the creation attempt
    Log::info('Attempting to create new email application.', [
      'user_id' => Auth::id(),
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values for sensitive data
    ]);

    try {
      // Delegate the creation logic to the EmailApplicationService.
      // The service should handle:
      // - Associating the application with the authenticated user (applicant).
      // - Setting the initial status ('draft' or 'pending_support').
      // - Saving the application to the database.
      // - Potentially triggering initial workflow steps (e.g., routing to supporting officer if status is pending_support).
      // *** FIX 1: Correct the order of arguments ***
      $application = $this->emailApplicationService->createApplication($request->validated(), Auth::user());

      // Log successful creation
      Log::info('Email application created successfully.', [
        'application_id' => $application->id,
        'user_id' => $application->user_id,
        'status' => $application->status,
      ]);


      // Redirect to the 'show' route for the newly created application with a success message
      // Changed message to Malay
      return redirect()->route('email-applications.show', $application)
        ->with('success', 'Permohonan e-mel berjaya dihantar.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions during creation
      Log::error('Error creating email application.', [
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error for debugging
      ]);

      // Redirect back with an error message
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal menghantar permohonan e-mel disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Display the specified email application.
   *
   * @param  \App\Models\EmailApplication  $emailApplication  The application instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(EmailApplication $emailApplication): \Illuminate\View\View // Use 'emailApplication' as parameter name, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $emailApplication).
    // The policy's 'view' method should verify if the user is the applicant, an assigned approver, or an admin.

    // Log viewing attempt
    Log::info('Viewing email application.', [
      'application_id' => $emailApplication->id,
      'user_id' => Auth::id(), // Log the user viewing the application
    ]);


    // Eager load related data needed for the show view:
    // - The applicant user
    // - Related approval records and the officer who made the decision
    $emailApplication->load(['user', 'approvals.officer']); // Assuming relationships exist


    // Return the view to show application details
    // Ensure your view file name matches: resources/views/email-applications/show.blade.php
    return view('email-applications.show', compact('emailApplication')); // Pass as 'emailApplication'
  }

  /**
   * Show the form for editing the specified email application.
   * This method is typically only accessible if the application is in a specific status (e.g., 'draft').
   * Uses route model binding.
   *
   * @param  \App\Models\EmailApplication  $emailApplication  The application instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(EmailApplication $emailApplication): \Illuminate\View\View // Use 'emailApplication' as parameter name, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $emailApplication).
    // The policy's 'update' method should verify:
    // 1. The authenticated user is the applicant.
    // 2. The application status allows editing (e.g., status == 'draft').

    // Load any necessary data for the edit form (e.g., lists for dropdowns if form isn't Livewire)
    $user = Auth::user(); // Applicant user

    // Return the view for editing, passing the application data
    // Ensure your view file name matches: resources/views/email-applications/edit.blade.php
    return view('email-applications.edit', compact('emailApplication', 'user')); // Pass as 'emailApplication'
  }

  /**
   * Update the specified email application in storage.
   * This method is typically only accessible if the application is in a specific status (e.g., 'draft').
   * Uses the UpdateEmailApplicationRequest Form Request for validation.
   * Delegates the update logic to the EmailApplicationService.
   *
   * @param  \App\Http\Requests\UpdateEmailApplicationRequest  $request  The validated incoming request.
   * @param  \App\Models\EmailApplication  $emailApplication  The application instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateEmailApplicationRequest $request, EmailApplication $emailApplication): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint, use 'emailApplication'
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $emailApplication).
    // Validation handled automatically by UpdateEmailApplicationRequest.
    // Update policy should verify applicant and status ('draft').

    // Log update attempt
    Log::info('Attempting to update email application.', [
      'application_id' => $emailApplication->id,
      'user_id' => Auth::id(),
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values
    ]);


    try {
      // Delegate the update logic to the EmailApplicationService.
      // The service should handle:
      // - Updating the application with the validated data.
      // - Ensuring updates are only allowed in the correct status ('draft').
      // - Saving changes.
      // - Potentially resetting status if significant changes occur (though usually not needed for draft edits).
      // *** FIX 2: Pass the authenticated user as the third argument ***
      $updated = $this->emailApplicationService->updateApplication($emailApplication, $request->validated(), Auth::user());

      if ($updated) {
        // Log successful update
        Log::info('Email application updated successfully.', [
          'application_id' => $emailApplication->id,
          'user_id' => Auth::id(),
        ]);

        // Changed message to Malay
        return redirect()->route('email-applications.show', $emailApplication)
          ->with('success', 'Permohonan e-mel berjaya dikemaskini.'); // Malay success message
      } else {
        // Log failure (might indicate a service-level rule prevented update)
        Log::warning('Email application update failed via service.', [
          'application_id' => $emailApplication->id,
          'user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini permohonan e-mel.'); // Malay error message
      }
    } catch (Exception $e) {
      // Log any exceptions during update
      Log::error('Error updating email application.', [
        'application_id' => $emailApplication->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini permohonan e-mel disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Remove the specified email application from storage.
   * Typically only allowed if the application is in a specific status (e.g., 'draft').
   * Delegates deletion logic to the EmailApplicationService or handles directly after check.
   *
   * @param  \App\Models\EmailApplication  $emailApplication  The application instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(EmailApplication $emailApplication): \Illuminate\Http\RedirectResponse // Use 'emailApplication', add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('delete' on the specific $emailApplication).
    // The policy's 'delete' method should verify:
    // 1. The authenticated user is the applicant.
    // 2. The application status allows deletion (e.g., status == 'draft').

    // Log deletion attempt
    Log::info('Attempting to delete email application.', [
      'application_id' => $emailApplication->id,
      'user_id' => Auth::id(),
      'current_status' => $emailApplication->status,
      'ip_address' => request()->ip(),
    ]);


    // Prevent deletion if the application is not in 'draft' status.
    // This is a critical business rule from the workflow.
    if ($emailApplication->status !== EmailApplication::STATUS_DRAFT) { // Use constant for status check
      Log::warning('Attempted to delete email application not in draft status.', [
        'application_id' => $emailApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $emailApplication->status,
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Permohonan e-mel tidak dapat dibuang kerana statusnya bukan "draf".'); // Malay error message
    }

    // Consider using Soft Deletes for EmailApplication model if retaining historical data is needed.
    // If Soft Deletes are used, $emailApplication->delete() will perform a soft delete.

    try {
      // Delegate deletion logic to the service if cleanup/related actions are needed
      // $deleted = $this->emailApplicationService->deleteApplication($emailApplication); // Assumes deleteApplication method exists

      // Or delete directly after the status check:
      $emailApplicationId = $emailApplication->id; // Store ID before deletion
      $emailApplication->delete(); // Performs soft delete if SoftDeletes trait is used

      // Log successful deletion (soft or permanent)
      Log::info('Email application deleted successfully.', [
        'application_id' => $emailApplicationId, // Use stored ID
        'user_id' => Auth::id(),
      ]);

      // Redirect to the index page with a success message
      // Changed message to Malay
      return redirect()->route('email-applications.index')
        ->with('success', 'Permohonan e-mel berjaya dibuang.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions during deletion
      Log::error('Error deleting email application.', [
        'application_id' => $emailApplication->id ?? 'unknown', // Use ID if available
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Gagal membuang permohonan e-mel disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  // Removed commented-out placeholder methods (process, approve, reject)
  // as they are handled by the ApprovalController, EmailProvisioningController API, etc.

}

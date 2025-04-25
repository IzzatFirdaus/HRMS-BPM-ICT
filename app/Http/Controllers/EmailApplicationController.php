<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Models\EmailApplication; // Import the EmailApplication model
use App\Services\EmailProvisioningService; // Import Service for business logic
use App\Http\Requests\StoreEmailApplicationRequest; // Import Form Request for validation
use Illuminate\Http\Request; // Standard Request object (needed for update placeholder)
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)
use Illuminate\Support\Facades\Auth; // Import Auth facade for accessing authenticated user

// Class name should match the route definition: EmailApplicationsController
class EmailApplicationController extends Controller
{
  /**
   * Display a listing of the email applications.
   */
  public function index()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', EmailApplication::class); // Assuming a EmailApplicationPolicy exists

    // Fetch applications the current user is authorized to view.
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions.
    $applications = EmailApplication::query()
      ->with('user') // Eager load the related user
      ->latest(); // Order by latest

    // Manual filtering example if not using policy scopes or you need additional filtering
    if (!Auth::user()->can('viewAllEmailApplications')) { // Assuming 'viewAllEmailApplications' permission for admin/approvers
      $applications->where('user_id', Auth::id()); // Users only see their own applications
    }


    $applications = $applications->paginate(10); // Paginate the results

    // Return the view with the list of applications
    // Ensure your view file name matches: resources/views/email-applications/index.blade.php
    return view('email-applications.index', compact('applications'));
  }

  /**
   * Show the form for creating a new email application.
   * This method is likely redundant if using the EmailApplicationForm Livewire component for creation.
   * It's kept here as a standard resource controller method placeholder.
   */
  public function create()
  {
    // Optional: Add authorization check
    // $this->authorize('create', EmailApplication::class); // Assuming a EmailApplicationPolicy exists

    // Return the view for creating an application (if you have a standard Blade form)
    return view('email-applications.create'); // Assuming a 'create.blade.php' view
  }

  /**
   * Store a newly created email application in storage.
   * This method is primarily here for standard MVC form submission,
   * but the logic aligns with what a Livewire component might call internally.
   * Uses dependency injection for the Form Request and the Service.
   */
  public function store(StoreEmailApplicationRequest $request, EmailProvisioningService $service)
  {
    // Optional: Add authorization check
    // $this->authorize('create', EmailApplication::class); // Assuming a EmailApplicationPolicy exists

    // Call the service to handle the creation logic.
    // Pass the authenticated user and the validated data.
    $application = $service->createApplication(Auth::user(), $request->validated());

    // Redirect to the 'show' route for the newly created application
    return redirect()->route('email-applications.show', $application)
      ->with('success', 'Email application submitted successfully!');
  }

  /**
   * Display the specified email application.
   */
  public function show(EmailApplication $application) // Route model binding
  {
    // Authorize if the user can view this specific application (using a Policy)
    $this->authorize('view', $application); // Assumes the policy handles ownership/permissions

    // Eager load related data needed for the show view (e.g., user, approvals, officer on approval)
    $application->load(['user', 'approvals.officer']); // Assuming relationships exist

    // Return the view to show application details
    // Ensure your view file name matches: resources/views/email-applications/show.blade.php
    return view('email-applications.show', compact('application'));
  }

  /**
   * Show the form for editing the specified email application.
   * This method is likely redundant if editing is handled via a Livewire component.
   * (Placeholder - implement if needed)
   */
  public function edit(EmailApplication $application) // Route model binding
  {
    // Authorize if the user can update this application
    $this->authorize('update', $application); // Assumes the policy handles permissions

    // Eager load related data needed for the edit view
    $application->load(['user']);

    // Return the view for editing, passing the application data
    return view('email-applications.edit', compact('application')); // Assuming an 'edit.blade.php' view
  }

  /**
   * Update the specified email application in storage.
   * This method is likely redundant if updating is handled via a Livewire component.
   * (Placeholder - implement if needed)
   * Uses dependency injection for the Request and the Service.
   */
  public function update(Request $request, EmailApplication $application, EmailProvisioningService $service) // Use standard Request or create UpdateEmailApplicationRequest
  {
    // Authorize if the user can update this application
    $this->authorize('update', $application); // Assumes the policy handles permissions

    // Example validation (replace with UpdateEmailApplicationRequest if created)
    $validatedData = $request->validate([
      // Define validation rules for update here
      // e.g., 'purpose' => 'required|string|max:255',
      // 'proposed_email' => 'nullable|email|max:255',
      // Note: Many fields might not be editable after submission based on workflow
    ]);

    // Call the service to handle the update logic
    // Ensure the updateApplication method exists and accepts the application and data
    $updated = $service->updateApplication($application, $validatedData); // Calling the newly added service method


    // Redirect back or to the 'show' page
    // Check if the update was successful before redirecting
    if ($updated) {
      return redirect()->route('email-applications.show', $application)
        ->with('success', 'Email application updated successfully!');
    } else {
      return redirect()->back()
        ->with('error', 'Failed to update email application.');
    }
  }

  /**
   * Remove the specified email application from storage.
   * (Placeholder - implement if needed)
   * Uses dependency injection for the Service.
   */
  public function destroy(EmailApplication $application, EmailProvisioningService $service) // Route model binding
  {
    // Authorize if the user can delete this application
    $this->authorize('delete', $application); // Assumes the policy handles permissions

    // Optionally use the service to handle deletion logic (e.g., cleanup)
    // $service->deleteApplication($application); // If you add a delete method to the service

    // Or delete directly:
    $application->delete();

    // Redirect to the index page
    return redirect()->route('email-applications.index')
      ->with('success', 'Email application deleted successfully!');

    // Add methods for approval/rejection if not handled purely by Livewire (e.g., from show page)
    // public function approve(EmailApplication $emailApplication) { ... }
    // public function reject(EmailApplication $emailApplication) { ... }
    // Add method for IT admin processing
    // public function process(EmailApplication $emailApplication) { ... }
  }

  /**
   * Example method for an admin to process an application (e.g., assign email/user ID)
   * This would be called after approval.
   * Uses dependency injection for Request and Service.
   */
  // public function process(EmailApplication $application, Request $request, EmailProvisioningService $service)
  // {
  //     // Authorize if the user (e.g., IT Admin) can process this application
  //     $this->authorize('process', $application); // Assuming a policy action

  //     $validatedData = $request->validate([
  //         'final_assigned_email' => 'nullable|email|max:255|unique:email_applications,final_assigned_email,' . $application->id,
  //         'final_assigned_user_id' => 'nullable|string|max:255|unique:email_applications,final_assigned_user_id,' . $application->id,
  //         // Add other processing fields if any
  //     ]);

  //     // Call the service to handle the processing logic, including external provisioning
  //     $processed = $service->processApplication($application, $validatedData); // Assuming processApplication method exists in service

  //     if ($processed) {
  //         return redirect()->route('email-applications.show', $application)->with('success', 'Application processed successfully!');
  //     } else {
  //          return redirect()->back()->with('error', 'Failed to process application.');
  //     }
  // }

  /**
   * Example method for approving an application (if not done via Livewire dashboard)
   * Uses dependency injection for ApprovalService.
   */
  // public function approve(EmailApplication $application, ApprovalService $approvalService)
  // {
  //     // Authorize if the user can approve this application
  //     $this->authorize('approve', $application); // Assumes policy and checks grade/role

  //     // Use the ApprovalService to record the approval
  //     // Ensure recordApproval method exists in ApprovalService and updates application status
  //     $approval = $approvalService->recordApproval($application, Auth::user());


  //     // Trigger notification, next step in workflow (e.g., processing)
  //     // ...

  //     return redirect()->route('email-applications.show', $application)->with('success', 'Application approved.');
  // }

  /**
   * Example method for rejecting an application (if not done via Livewire dashboard)
   * Uses dependency injection for Request and ApprovalService.
   */
  // public function reject(EmailApplication $application, Request $request, ApprovalService $approvalService)
  // {
  //     // Authorize if the user can reject this application
  //     $this->authorize('reject', $application); // Assumes policy and checks grade/role

  //     $validatedData = $request->validate(['rejection_reason' => 'required|string|max:500']);

  //     // Use the ApprovalService to record the rejection
  //     // Ensure recordRejection method exists in ApprovalService and updates application status
  //     $approval = $approvalService->recordRejection($application, Auth::user(), $validatedData['rejection_reason']);

  //     // Trigger notification
  //     // ...

  //     return redirect()->route('email-applications.show', $application)->with('success', 'Application rejected.');
  // }
}

<?php

namespace App\Http\Controllers;

// Imports from the more complete version
use App\Models\EmailApplication;
use App\Services\EmailProvisioningService; // Service for business logic
use App\Http\Requests\StoreEmailApplicationRequest; // Form Request for validation
use Illuminate\Http\Request; // Standard Request object (needed for update)

class EmailApplicationController extends Controller
{
  /**
   * Display a listing of the email applications.
   */
  public function index()
  {
    // Retrieve paginated applications, eager-loading the related user
    return view('email-applications.index', [
      'applications' => EmailApplication::with('user')->latest()->paginate(10) // Added latest() for sorting
    ]);
  }

  /**
   * Show the form for creating a new email application.
   */
  public function create()
  {
    // Simply return the view for the creation form
    return view('email-applications.create');
  }

  /**
   * Store a newly created email application in storage.
   * Uses dependency injection for the Request and the Service.
   */
  public function store(StoreEmailApplicationRequest $request, EmailProvisioningService $service)
  {
    // Validate the request using StoreEmailApplicationRequest
    // Call the service to handle the creation logic
    $application = $service->createApplication($request->validated());

    // Redirect to the 'show' route for the newly created application
    // Added a success flash message (optional but good practice)
    return redirect()->route('email-applications.show', $application)
      ->with('success', 'Email application submitted successfully!');
  }

  /**
   * Display the specified email application.
   */
  public function show(EmailApplication $application) // Route model binding
  {
    // Authorize if the user can view this specific application (using a Policy)
    $this->authorize('view', $application);

    // Pass the specific application model to the view
    return view('email-applications.show', compact('application'));
    // Equivalent: return view('email-applications.show', ['application' => $application]);
  }

  /**
   * Show the form for editing the specified email application.
   * (Placeholder - implement if needed)
   */
  public function edit(EmailApplication $application) // Route model binding
  {
    // Authorize if the user can update this application
    $this->authorize('update', $application);

    // Return the view for editing, passing the application data
    return view('email-applications.edit', compact('application')); // Assuming an 'edit.blade.php' view
  }

  /**
   * Update the specified email application in storage.
   * (Placeholder - implement if needed)
   */
  public function update(Request $request, EmailApplication $application, EmailProvisioningService $service) // Use standard Request or create UpdateEmailApplicationRequest
  {
    // Authorize if the user can update this application
    $this->authorize('update', $application);

    // Validate the incoming request data
    // $validatedData = $request->validate([
    //     // Define validation rules for update
    // ]);

    // Optionally use the service to handle the update logic
    // $updated = $service->updateApplication($application, $validatedData);

    // Or update directly:
    // $application->update($validatedData);

    // Redirect back or to the 'show' page
    return redirect()->route('email-applications.show', $application)
      ->with('success', 'Email application updated successfully!');
  }

  /**
   * Remove the specified email application from storage.
   * (Placeholder - implement if needed)
   */
  public function destroy(EmailApplication $application, EmailProvisioningService $service) // Route model binding
  {
    // Authorize if the user can delete this application
    $this->authorize('delete', $application);

    // Optionally use the service to handle deletion logic (e.g., cleanup)
    // $service->deleteApplication($application);

    // Or delete directly:
    // $application->delete();

    // Redirect to the index page
    return redirect()->route('email-applications.index')
      ->with('success', 'Email application deleted successfully!');
  }
}

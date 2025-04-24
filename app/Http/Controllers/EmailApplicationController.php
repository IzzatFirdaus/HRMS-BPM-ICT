<?php

namespace App\Http\Controllers;

// Imports from the more complete version
use App\Models\EmailApplication;
use App\Services\EmailProvisioningService; // Service for business logic
use App\Http\Requests\StoreEmailApplicationRequest; // Form Request for validation
use Illuminate\Http\Request; // Standard Request object (needed for update)
use Illuminate\Support\Facades\Gate; // For using policies manually if needed


class EmailApplicationController extends Controller
{
  /**
   * Display a listing of the email applications.
   */
  public function index()
  {
    // Example: Fetch applications the user can view (either their own or all if they have permission)
    // This would ideally be handled in a Livewire component or a dedicated view controller
    // $applications = EmailApplication::where('user_id', Auth::id())->get();
    // If user has 'view_any_email_applications' permission, fetch all
    // if (Auth::user()->can('viewAny', EmailApplication::class)) {
    //     $applications = EmailApplication::all();
    // }
    // return view('email-applications.index', compact('applications'));

    // As per the route definition, Livewire components might handle viewing lists.
    // This controller could be used for non-Livewire pages or API endpoints if needed later.

    // Retrieve paginated applications, eager-loading the related user
    return view('email-applications.index', [
      'applications' => EmailApplication::with('user')->latest()->paginate(10) // Added latest() for sorting
    ]);
    // return "Email Applications Index Page (Under Development)";
  }

  /**
   * Show the form for creating a new email application.
   */
  public function create()
  {
    // The route is handled by a Livewire component, so this method might not be needed
    // unless you have a separate non-Livewire create page.
    return view('email-applications.create'); // Example if you have a standard Blade view
  }

  /**
   * Store a newly created email application in storage.
   * Uses dependency injection for the Request and the Service.
   */
  public function store(StoreEmailApplicationRequest $request, EmailProvisioningService $service)
  {
    // Form submission is likely handled by the Livewire component EmailApplicationForm
    // If you have a non-Livewire form, implement storing logic here, potentially using the EmailApplicationService
    // $this->authorize('create', EmailApplication::class); // Check policy
    // $validatedData = $request->validate([...]);
    // $application = (new \App\Services\EmailApplicationService())->createApplication(Auth::user(), $validatedData);
    // return redirect()->route('email-applications.show', $application)->with('success', 'Application created successfully!');

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

    // $this->authorize('view', $emailApplication); // Check policy
    // return view('email-applications.show', compact('emailApplication'));
    //return "Viewing Email Application #" . $emailApplication->id . " (Under Development)";
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

    // $this->authorize('update', $emailApplication); // Check policy
    // return view('email-applications.edit', compact('emailApplication'));
    //return "Editing Email Application #" . $emailApplication->id . " (Under Development)";
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

    // $this->authorize('update', $emailApplication); // Check policy
    // $validatedData = $request->validate([...]);
    // $emailApplication->update($validatedData); // Or use the service
    // return redirect()->route('email-applications.show', $emailApplication)->with('success', 'Application updated successfully!');

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

    // $this->authorize('delete', $emailApplication); // Check policy
    // $emailApplication->delete();
    // return redirect()->route('email-applications.index')->with('success', 'Application deleted successfully!');

    // Redirect to the index page
    return redirect()->route('email-applications.index')
      ->with('success', 'Email application deleted successfully!');

    // Add methods for approval/rejection if not handled purely by Livewire
    // public function approve(EmailApplication $emailApplication) { ... }
    // public function reject(EmailApplication $emailApplication) { ... }
    // Add method for IT admin processing
    // public function process(EmailApplication $emailApplication) { ... }
  }
}

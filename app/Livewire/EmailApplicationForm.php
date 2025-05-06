<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Grade;
use App\Models\Department;
use App\Models\User;
use App\Models\EmailApplication;
use App\Services\EmailApplicationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Exception; // Import the base Exception class without leading backslash

class EmailApplicationForm extends Component
{
  use AuthorizesRequests;

  public string $service_status = '';
  public string $purpose = '';
  public string $proposed_email = '';
  public string $group_email = '';
  public string $group_admin_name = '';
  public string $group_admin_email = '';
  public bool $certification = false;
  public string $supporting_officer_id = '';

  public ?Collection $supportingOfficers = null;

  public ?int $applicationId = null;
  protected ?EmailApplication $emailApplication = null;

  public ?string $applicantName = null;
  public ?string $applicantIC = null;
  public ?string $applicantGradeName = null;
  public ?string $applicantPositionName = null;
  public ?string $applicantDepartmentName = null;
  public ?string $applicantMobileNumber = null;
  public ?string $applicantPersonalEmail = null;

  public function updated(string $propertyName): void
  {
    $rules = [
      'service_status' => ['required', Rule::in(EmailApplication::SERVICE_STATUSES)],
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'boolean',
    ];

    try {
      $this->validateOnly($propertyName, $rules);
    } catch (ValidationException $e) {
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      Log::debug('Real-time validation failed', [
        'property' => $propertyName,
        'errors' => $e->errors(),
      ]);
    }
  }

  public function mount(?EmailApplication $emailApplication = null)
  {
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm mounted for unauthenticated user.');
      return redirect()->route('login');
    }

    $user = Auth::user();
    $this->emailApplication = $emailApplication;

    // Fetch supporting officers
    try {
      $minGrade = config('motac.approval.min_supporting_officer_grade_level', 9);
      $this->supportingOfficers = User::with('grade')
        ->whereHas('grade', fn($q) => $q->where('level', '>=', $minGrade))
        ->where('status', 'active')
        ->orderBy('name')
        ->get();
    } catch (Exception $e) { // FIX: Simplified \Exception
      Log::error('Error fetching supporting officers.', [
        'exception' => $e,
      ]);
      $this->supportingOfficers = collect();
      session()->flash('error', __('Could not load supporting officers.'));
    }

    // Populate applicant info
    $this->applicantName = $user->full_name ?? $user->name ?? __('N/A');
    $this->applicantIC = $user->ic_number ?? $user->identification_number ?? __('N/A');
    $this->applicantGradeName = $user->grade?->name ?? __('N/A');
    $this->applicantPositionName = $user->position?->name ?? __('N/A');
    $this->applicantDepartmentName = $user->department?->name ?? __('N/A');
    $this->applicantMobileNumber = $user->mobile_number ?? __('N/A');
    $this->applicantPersonalEmail = $user->personal_email ?? $user->email ?? __('N/A');

    if ($emailApplication) {
      if ($emailApplication->user_id !== $user->id) {
        session()->flash('error', __('Not authorized to edit this application.'));
        return redirect()->route('dashboard');
      }
      if ($emailApplication->status !== EmailApplication::STATUS_DRAFT) {
        session()->flash('error', __('Cannot edit application not in draft.'));
        return redirect()->route('email-applications.show', $emailApplication);
      }

      $this->applicationId = $emailApplication->id;
      $this->service_status = $emailApplication->service_status ?? '';
      $this->purpose = $emailApplication->purpose ?? '';
      $this->proposed_email = $emailApplication->proposed_email ?? '';
      $this->group_email = $emailApplication->group_email ?? '';
      $this->group_admin_name = $emailApplication->group_admin_name ?? '';
      $this->group_admin_email = $emailApplication->group_admin_email ?? '';
      $this->certification = (bool) ($emailApplication->certification_accepted ?? false);
      $this->supporting_officer_id = $emailApplication->supporting_officer_id ?? '';
    } else {
      $this->service_status = $user->service_status ?? '';

      try {
        $temp = new EmailApplication(['user_id' => $user->id]);
        // FIX: Call assignSupportingOfficer, ensure it's public in Service, or revisit logic
        $assigned = app(EmailApplicationService::class)->assignSupportingOfficer($temp); // Line 134 error PHP1416 here
        $this->supporting_officer_id = $assigned->id ?? '';
      } catch (Exception $e) { // FIX: Simplified \Exception
        Log::warning('Auto-assign supporting officer failed.', ['exception' => $e]);
        session()->flash('warning', __('Please select a supporting officer.'));
        $this->supporting_officer_id = '';
      }
    }
  }

  public function render(): View
  {
    return view('livewire.email-application-form', [
      'supportingOfficers' => $this->supportingOfficers,
      'emailApplication' => $this->emailApplication,
      'user' => Auth::user(),
      'serviceStatuses' => EmailApplication::SERVICE_STATUSES_SELECT,
    ]);
  }

  public function saveAsDraft(EmailApplicationService $service): void
  {
    if (!Auth::check()) {
      session()->flash('error', __('You must be logged in to save a draft.'));
      return;
    }
    $user = Auth::user();

    $rules = [
      'service_status' => ['required', Rule::in(EmailApplication::SERVICE_STATUSES)],
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'nullable|boolean',
    ];

    try {
      $data = $this->validate($rules);
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', __('Fix the errors before saving.'));
      return;
    }

    $payload = [
      'service_status' => $data['service_status'],
      'purpose' => $data['purpose'],
      'proposed_email' => $data['proposed_email'] ?: null,
      'group_email' => $data['group_email'] ?: null,
      'group_admin_name' => $data['group_admin_name'] ?: null,
      'group_admin_email' => $data['group_admin_email'] ?: null,
      'supporting_officer_id' => $this->supporting_officer_id ?: null,
      'certification_accepted' => $this->certification,
    ];

    DB::beginTransaction();

    try {
      if ($this->applicationId) {
        $app = EmailApplication::findOrFail($this->applicationId);
        $this->authorize('update', $app);
        if ($app->status !== EmailApplication::STATUS_DRAFT) {
          throw new Exception('Not a draft.'); // FIX: Simplified \Exception
        }
        // FIX: Pass authenticated user to service update method
        $service->updateApplication($app, $payload, $user); // Line 201 error PHP0423 here
        session()->flash('success', __('Draft updated.'));
      } else {
        $this->authorize('create', EmailApplication::class);
        // Call was already correct here based on previous review ($payload, $user)
        $app = $service->createApplication($payload, $user); // Line 210 call PHP0406 (at 414) is correct here
        $this->applicationId = $app->id;
        $this->emailApplication = $app;
        session()->flash('success', __('Draft saved.'));
      }

      DB::commit();
    } catch (Exception $e) { // FIX: Simplified \Exception
      DB::rollBack();
      Log::error('Failed to save draft.', ['exception' => $e]);
      session()->flash('error', __('Error saving draft.'));
    }
  }

  public function submitApplication(EmailApplicationService $service)
  {
    if (!Auth::check()) {
      session()->flash('error', __('You must be logged in to submit.'));
      return null;
    }
    $user = Auth::user();

    $rules = [
      'service_status' => ['required', Rule::in(EmailApplication::SERVICE_STATUSES)],
      'purpose' => 'required|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => ['required', 'exists:users,id'],
      'certification' => 'accepted',
    ];

    try {
      $valid = $this->validate($rules);

      $individual = !empty($valid['proposed_email']);
      $groupReq = !empty($valid['group_email']);
      $completeGrp = $groupReq
        && !empty($valid['group_admin_name'])
        && !empty($valid['group_admin_email']);

      if (!$individual && !$completeGrp) {
        throw ValidationException::withMessages([
          'proposed_email' => __('Provide either an individual email or full group details.'),
          'group_email' => __('Provide either an individual email or full group details.'),
          'group_admin_name' => __('Provide either an individual email or full group details.'),
          'group_admin_email' => __('Provide either an individual email or full group details.'),
        ]);
      }
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', __('Fix the errors before submitting.'));
      return null;
    }

    if (!$this->applicationId) {
      session()->flash('error', __('Save as draft first.'));
      return null;
    }

    try {
      $app = EmailApplication::findOrFail($this->applicationId);
      $this->authorize('update', $app);
      if ($app->status !== EmailApplication::STATUS_DRAFT) {
        session()->flash('error', __('Not in draft status.'));
        return redirect()->route('email-applications.show', $app);
      }

      DB::beginTransaction();
      // FIX: Pass authenticated user to service update method
      $service->updateApplication($app, $valid, $user); // Line 275 error PHP0423 here
      // FIX: Change method name and pass arguments
      // Method in service is submitApplication, expects application, data, user
      $service->submitApplication($app, $valid, $user); // Line 284 error PHP0418 here

      DB::commit();

      session()->flash('success', __('Submitted successfully.'));
      return redirect()->route('email-applications.show', $app);
    } catch (Exception $e) { // FIX: Simplified \Exception
      DB::rollBack();
      Log::error('Submission failed.', ['exception' => $e]);
      session()->flash('error', __('Error during submission.'));
      return null;
    }
  }

  public function deleteDraft(EmailApplicationService $service): RedirectResponse|\Illuminate\Routing\Redirector|null
  {
    if (!Auth::check()) {
      session()->flash('error', __('You must be logged in.'));
      return null;
    }
    $user = Auth::user();

    if (!$this->applicationId) {
      session()->flash('error', __('No draft selected.'));
      return null;
    }

    try {
      $app = EmailApplication::findOrFail($this->applicationId);
      $this->authorize('delete', $app);
      if ($app->status !== EmailApplication::STATUS_DRAFT) {
        session()->flash('error', __('Cannot delete non-draft.'));
        return redirect()->route('email-applications.show', $app);
      }

      DB::beginTransaction();
      // FIX: Pass authenticated user to service delete method (line 319 shows user passed, assuming error at 651 is elsewhere)
      $service->deleteApplication($app, $user); // Line 319 call (error at 651)

      DB::commit();

      session()->flash('success', __('Draft deleted.'));
      return redirect()->route('dashboard');
    } catch (Exception $e) { // FIX: Simplified \Exception
      DB::rollBack();
      Log::error('Delete draft failed.', ['exception' => $e]);
      session()->flash('error', __('Error deleting draft.'));
      return null;
    }
  }
}

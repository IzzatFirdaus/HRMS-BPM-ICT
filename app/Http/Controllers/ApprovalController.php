<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
  protected $approvalService;

  public function __construct(ApprovalService $approvalService)
  {
    $this->middleware('auth');
    $this->approvalService = $approvalService;
  }

  public function index()
  {
    $pendingApprovals = Approval::with('approvable')
      ->where('officer_id', auth()->id())
      ->where('status', 'pending')
      ->get();

    return view('approvals.index', compact('pendingApprovals'));
  }

  public function show(Approval $approval)
  {
    return view('approvals.show', compact('approval'));
  }

  public function update(Request $request, Approval $approval)
  {
    $request->validate([
      'action' => 'required|in:approve,reject',
      'comments' => 'nullable|string',
    ]);

    $approvable = $approval->approvable;

    if ($request->action === 'approve') {
      $this->approvalService->approve($approvable, $request->comments);
    } else {
      $this->approvalService->reject($approvable, $request->comments);
    }

    return redirect()->route('approvals.index')->with('success', 'Decision recorded.');
  }
}

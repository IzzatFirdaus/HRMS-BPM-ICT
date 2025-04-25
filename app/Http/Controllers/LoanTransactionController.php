<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Models\LoanTransaction; // Import the LoanTransaction model
use Illuminate\Http\Request; // Import Request if needed (not strictly needed for show)
use Illuminate\Support\Facades\Auth; // Import Auth facade if needed
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)

class LoanTransactionController extends Controller
{
  /**
   * Apply authentication middleware to all methods in this controller.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display the specified loan transaction.
   *
   * @param  \App\Models\LoanTransaction  $transaction  The loan transaction instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(LoanTransaction $transaction) // Use route model binding
  {
    // Authorize if the user can view this specific transaction (using a Policy)
    // Assuming a LoanTransactionPolicy exists and handles ownership/permissions.
    // For example, a user might view their own transaction, or a BPM staff/Admin can view any.
    $this->authorize('view', $transaction);

    // Eager load relationships needed for the transaction details view
    // This ensures related data is loaded efficiently.
    $transaction->load([
      'loanApplication.user', // Load the related loan application and its user
      'equipment', // Load the equipment involved in the transaction
      'issuingOfficer', // Load the user who issued the equipment
      'receivingOfficer', // Load the user who received the equipment (on issuance)
      'returningOfficer', // Load the user who returned the equipment (on return)
      'returnAcceptingOfficer', // Load the user who accepted the return
    ]);

    // Return the view to show transaction details
    // Ensure your view file name matches: resources/views/loan-transactions/show.blade.php
    return view('loan-transactions.show', compact('transaction'));
  }

  /**
   * Show the form for creating a new resource.
   * (Not typically used for transactions, as they are created via LoanApplication issuance)
   */
  // public function create() { /* ... */ }

  /**
   * Store a newly created resource in storage.
   * (Not typically used for transactions, as they are created via LoanApplication issuance)
   */
  // public function store(Request $request) { /* ... */ }

  /**
   * Show the form for editing the specified resource.
   * (Not typically used for transactions, as return is processed via LoanApplicationController)
   */
  // public function edit(LoanTransaction $loanTransaction) { /* ... */ }

  /**
   * Update the specified resource in storage.
   * (Not typically used for transactions, as return is processed via LoanApplicationController)
   */
  // public function update(Request $request, LoanTransaction $loanTransaction) { /* ... */ }

  /**
   * Remove the specified resource from storage.
   * (Deletion of transactions might be restricted or handled differently)
   */
  // public function destroy(LoanTransaction $loanTransaction) { /* ... */ }
}

<?php

namespace App\Http\Controllers;

use App\Models\EmailApplication;
use App\Models\LoanApplication;

class ReportController extends Controller
{
  public function equipment()
  {
    $applications = LoanApplication::with('items')->latest()->get();
    return view('reports.equipment', compact('applications'));
  }

  public function emailAccounts()
  {
    $applications = EmailApplication::latest()->get();
    return view('reports.email-accounts', compact('applications'));
  }
}

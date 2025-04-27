<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import the new MOTAC API Controller
use App\Http\Controllers\Api\EmailProvisioningController; // Ensure correct namespace

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Existing route for authenticated user details
// This route is typically used to fetch the currently authenticated user's details
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
})->name('api.user'); // Added a name for clarity

// 👇 New API routes for MOTAC Resource Management 👇

// Group new API routes under a version prefix and apply authentication
// Using 'auth:sanctum' middleware to protect these endpoints
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

  // Endpoint for triggering email provisioning
  // This route will be called by the system (e.g., from a Service or Controller
  // after an email application is approved) to initiate the actual email account creation
  // It expects a POST request containing the necessary data for provisioning
  Route::post('/provision-email', [EmailProvisioningController::class, 'provision'])->name('api.v1.provision-email');

  // Add other API endpoints if needed for integration with external systems
  // For example, endpoints for status updates from the email provisioning system
  // Route::post('/provision-status-update', [EmailProvisioningController::class, 'handleStatusUpdate'])->name('api.v1.provision-status-update');

  // Example: API endpoint to get equipment availability status
  // Route::get('/equipment/{equipment}/availability', [EquipmentController::class, 'getAvailability'])->name('api.v1.equipment.availability');

});

// ☝️ End New API routes for MOTAC Resource Management ☝️

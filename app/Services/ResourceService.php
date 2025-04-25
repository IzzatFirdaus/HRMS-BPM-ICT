<?php

namespace App\Services;

use App\Models\Equipment; // Import the Equipment model
use App\Models\User; // Import User model if actions are linked to users
use Illuminate\Support\Facades\Log; // Import Log facade
use Exception; // Import base Exception class
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException

/**
 * Optional service to encapsulate business logic for general resource/equipment management.
 * This can be used for inventory updates, maintenance tracking, reporting, etc.
 */
class ResourceService
{
  // Inject any dependencies needed for resource management (e.g., repositories, other services)

  /**
   * Updates the status of a specific equipment asset.
   * This might be used for actions like marking equipment as 'under_maintenance' or 'disposed'.
   *
   * @param Equipment $equipment The equipment asset to update.
   * @param string $newStatus The new status for the equipment.
   * @param array $extraDetails Optional extra details (e.g., maintenance notes).
   * @return Equipment The updated equipment model instance.
   * @throws \Exception If the update fails.
   */
  public function updateEquipmentStatus(Equipment $equipment, string $newStatus, array $extraDetails = []): Equipment
  {
    try {
      // Validate the new status if necessary (e.g., check against a list of allowed statuses)
      // if (!in_array($newStatus, Equipment::ALLOWED_STATUSES)) { // Assuming ALLOWED_STATUSES constant on model
      //     throw new Exception("Invalid equipment status: " . $newStatus);
      // }

      // Update the status and potentially other details
      $equipment->status = $newStatus;
      // Merge extra details into notes or dedicated fields
      if (!empty($extraDetails['notes'])) {
        $equipment->notes = ($equipment->notes ? $equipment->notes . "\n" : "") . "Status updated to '{$newStatus}' on " . now()->toDateTimeString() . ". " . $extraDetails['notes'];
      }

      $equipment->save();

      Log::info("Equipment ID: " . $equipment->id . " status updated to '" . $newStatus . "'.");

      return $equipment; // Return the updated model

    } catch (Exception $e) {
      Log::error("Failed to update status for Equipment ID: " . $equipment->id . " to '" . $newStatus . "'. Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Logs a maintenance record for a specific equipment asset.
   *
   * @param Equipment $equipment The equipment asset.
   * @param string $type The type of maintenance (e.g., 'repair', 'calibration').
   * @param string $details Details about the maintenance performed.
   * @param User|null $performedBy The user who performed the maintenance (optional).
   * @return bool True on success (e.g., maintenance record created/logged).
   */
  public function logMaintenance(Equipment $equipment, string $type, string $details, ?User $performedBy = null): bool
  {
    try {
      // This assumes you have a separate 'maintenance_records' table and model,
      // or you are logging this information in the equipment's notes or a JSON column.
      // Example: $equipment->maintenanceRecords()->create([...]); // Assuming relationship

      // Or, simply update notes with maintenance log
      $logEntry = "\n[" . now()->toDateTimeString() . " - Maintenance - " . $type . "] " . $details;
      if ($performedBy) {
        $logEntry .= " Performed by: " . $performedBy->full_name ?? $performedBy->name;
      }
      $equipment->notes .= $logEntry;
      $equipment->save();

      Log::info("Maintenance logged for Equipment ID: " . $equipment->id . ". Type: " . $type);

      return true; // Indicate successful logging

    } catch (Exception $e) {
      Log::error("Failed to log maintenance for Equipment ID: " . $equipment->id . ". Type: " . $type . ". Error: " . $e->getMessage());
      return false; // Indicate failure
    }
  }

  /**
   * Retrieves equipment assets based on various criteria (e.g., status, type, location).
   * This could be used for inventory reports or lists.
   *
   * @param array $filters An array of filters (e.g., ['status' => 'available', 'asset_type' => 'Laptop']).
   * @return \Illuminate\Database\Eloquent\Collection The collection of matching equipment assets.
   */
  public function filterEquipment(array $filters): \Illuminate\Database\Eloquent\Collection
  {
    $query = Equipment::query(); // Start with the base query

    // Apply filters dynamically
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }
    if (!empty($filters['asset_type'])) {
      $query->where('asset_type', $filters['asset_type']);
    }
    if (!empty($filters['current_location'])) {
      $query->where('current_location', $filters['current_location']);
    }
    // Add more filters as needed (e.g., by brand, purchase date range)

    return $query->get(); // Return the collection of results
    // Consider adding pagination here if dealing with large inventories
    // return $query->paginate(15);
  }


  // Add other methods related to equipment management here, e.g.:
  // public function getEquipmentUtilizationReport(): array;
  // public function assignEquipmentToUser(Equipment $equipment, User $user): bool; // If resources can be assigned outside loans
}

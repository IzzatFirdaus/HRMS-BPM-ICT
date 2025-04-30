<?php

namespace App\Services; // Ensure the namespace is correct for your project

use App\Models\Equipment; // Import the Equipment model
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\QueryException; // Import if your service interacts with the DB directly and might throw DB exceptions
use Illuminate\Support\Facades\DB; // Import DB facade if using transactions or manual queries


// You might need to import other models or classes that your service interacts with
// use App\Models\LoanTransaction; // If checking loan history within the service
// use App\Models\LoanApplication; // If interacting with loan applications


class EquipmentService
{
  /**
   * Create a new equipment record in the inventory.
   *
   * @param array $data Validated data for equipment creation, typically from a StoreEquipmentRequest.
   * Expected keys should match fillable attributes on the Equipment model
   * (e.g., 'tag_id', 'asset_type', 'brand', 'model', 'serial_number',
   * 'purchase_date', 'warranty_expiry_date', 'notes', potentially 'department_id', 'position_id').
   * @return \App\Models\Equipment The newly created equipment instance.
   * @throws \Exception
   */
  public function createEquipment(array $data): Equipment
  {
    Log::info('Attempting to create equipment via service.', ['data_keys' => array_keys($data)]);

    try {
      // Begin a database transaction if multiple operations are involved
      // DB::beginTransaction();

      // Logic to create the equipment
      // Ensure fillable properties are set on the Equipment model for mass assignment, or use forceFill
      $equipment = Equipment::create($data);

      // Set initial status if not provided in data (usually 'available')
      // if (!isset($data['status'])) {
      $equipment->status = 'available'; // Set default initial status
      $equipment->save();
      // }


      // Optional: Trigger events or other actions after creation
      // event(new EquipmentCreated($equipment));

      // Commit the transaction if using
      // DB::commit();

      Log::info('Equipment created successfully via service.', ['equipment_id' => $equipment->id]);

      return $equipment;
    } catch (QueryException $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log specific database error
      Log::error('Database error creating equipment via service: ' . $e->getMessage(), ['data' => $data]);
      throw new Exception("Database error occurred while creating equipment.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log general exception
      Log::error('Failed to create equipment via service: ' . $e->getMessage(), ['data' => $data]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update an existing equipment record in the inventory.
   *
   * @param \App\Models\Equipment $equipment The equipment instance to update.
   * @param array $data Validated data for equipment update, typically from an UpdateEquipmentRequest.
   * Expected keys should match fillable attributes on the Equipment model.
   * @return bool True if updated, false otherwise.
   * @throws \Exception
   */
  public function updateEquipment(Equipment $equipment, array $data): bool
  {
    Log::info('Attempting to update equipment via service.', ['equipment_id' => $equipment->id, 'data_keys' => array_keys($data)]);

    try {
      // Begin a database transaction if multiple operations are involved
      // DB::beginTransaction();

      // Logic to update the equipment
      // Ensure fillable properties are set or use forceFill
      $updated = $equipment->update($data);

      // Optional: Handle specific status transitions if allowed via update
      // For example, if only IT admin can set status to 'under_maintenance' or 'disposed'
      // This logic might be better handled in a dedicated method or checked in a Form Request/Policy
      // if (isset($data['status']) && $equipment->status !== $data['status']) {
      // Perform checks or trigger actions based on status change
      // Log::info("Equipment status changed from {$equipment->status} to {$data['status']}");
      // }


      // Optional: Trigger events or other actions after update
      // event(new EquipmentUpdated($equipment));

      // Commit the transaction if using
      // DB::commit();

      if ($updated) {
        Log::info('Equipment updated successfully via service.', ['equipment_id' => $equipment->id]);
      } else {
        // Note: update() returns false if no attributes were changed
        Log::warning('Equipment update via service did not result in changes.', ['equipment_id' => $equipment->id, 'data' => $data]);
      }

      return $updated;
    } catch (QueryException $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log specific database error
      Log::error('Database error updating equipment via service: ' . $e->getMessage(), ['equipment_id' => $equipment->id, 'data' => $data]);
      throw new Exception("Database error occurred while updating equipment.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log general exception
      Log::error('Failed to update equipment via service: ' . $e->getMessage(), ['equipment_id' => $equipment->id, 'data' => $data]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Delete an equipment record from the inventory.
   * Consider if soft deletion is more appropriate for historical data.
   * NOTE: The controller already checks for loan history before calling this method.
   * This service method primarily handles the database deletion.
   *
   * @param \App\Models\Equipment $equipment The equipment instance to delete.
   * @return bool True if deleted, false otherwise.
   * @throws \Exception
   */
  public function deleteEquipment(Equipment $equipment): bool
  {
    // The controller should have already checked for related loan transactions.
    // If this method were called directly without the check, you'd add it here:
    // if ($equipment->loanTransactions()->exists()) {
    //      Log::warning('Attempted service delete on equipment with loan history.', ['equipment_id' => $equipment->id]);
    //      // Throw a custom exception or return false indicating business rule violation
    //      throw new \Exception("Cannot delete equipment with loan history.");
    // }

    Log::info('Attempting to delete equipment via service.', ['equipment_id' => $equipment->id]);

    try {
      // Begin a database transaction if multiple operations are involved
      // DB::beginTransaction();

      // Logic to delete the equipment record
      // This will perform a soft delete if the Equipment model uses the SoftDeletes trait
      $deleted = $equipment->delete();

      // Optional: Trigger events or other cleanup actions after deletion
      // event(new EquipmentDeleted($equipment)); // Fire event after deletion
      // e.g., remove from cache, unlink related files

      // Commit the transaction if using
      // DB::commit();

      if ($deleted) {
        // Note: For soft deletes, the record still exists but deleted_at is set.
        Log::info('Equipment deleted successfully via service.', ['equipment_id' => $equipment->id]);
      } else {
        // This might happen if the record was already soft deleted
        Log::warning('Equipment deletion via service did not result in deletion.', ['equipment_id' => $equipment->id]);
      }

      return $deleted;
    } catch (QueryException $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log specific database error (unlikely if controller checks history, but as safeguard)
      Log::error('Database error deleting equipment via service: ' . $e->getMessage(), ['equipment_id' => $equipment->id]);
      throw new Exception("Database error occurred while deleting equipment.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // Rollback transaction if using
      // DB::rollBack();
      // Log general exception
      Log::error('Failed to delete equipment via service: ' . $e->getMessage(), ['equipment_id' => $equipment->id]);
      throw $e; // Re-throw the exception
    }
  }

  // --- Add other service methods related to equipment inventory management here ---

  /**
   * Get a list of equipment available for loan.
   *
   * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Equipment[]
   */
  public function getAvailableEquipment()
  {
    // Example implementation:
    return Equipment::where('status', 'available')->get();
  }

  /**
   * Update the status of an equipment item.
   * Used for status changes NOT related to the loan transaction process (e.g., maintenance, disposal).
   * Loan transaction status changes should be handled by LoanTransactionService.
   *
   * @param \App\Models\Equipment $equipment The equipment instance.
   * @param string $newStatus The new status ('available', 'under_maintenance', 'disposed').
   * @return bool True if updated, false otherwise.
   * @throws \Exception
   */
  public function updateEquipmentStatus(Equipment $equipment, string $newStatus): bool
  {
    // Validate status transition if needed (e.g., cannot set to 'available' if still on loan)
    // if ($newStatus === 'available' && $equipment->status === 'on_loan') {
    //     throw new Exception("Cannot set status to 'available' while equipment is on loan.");
    // }

    // Validate new status is one of the allowed inventory statuses (excluding 'on_loan')
    $allowedStatuses = ['available', 'under_maintenance', 'disposed']; // Define allowed statuses
    if (!in_array($newStatus, $allowedStatuses)) {
      throw new Exception("Invalid status provided for inventory update.");
    }

    Log::info('Attempting to update equipment status via service.', ['equipment_id' => $equipment->id, 'old_status' => $equipment->status, 'new_status' => $newStatus]);

    try {
      $updated = $equipment->update(['status' => $newStatus]);

      if ($updated) {
        Log::info('Equipment status updated successfully via service.', ['equipment_id' => $equipment->id, 'status' => $newStatus]);
      } else {
        Log::warning('Equipment status update via service did not result in change.', ['equipment_id' => $equipment->id, 'status' => $newStatus]);
      }

      return $updated;
    } catch (Exception $e) {
      Log::error('Failed to update equipment status via service: ' . $e->getMessage(), ['equipment_id' => $equipment->id, 'new_status' => $newStatus]);
      throw $e;
    }
  }

  // Add methods for managing equipment location if needed
  // public function assignEquipmentToLocation(Equipment $equipment, Department $department = null, Position $position = null): bool;

}

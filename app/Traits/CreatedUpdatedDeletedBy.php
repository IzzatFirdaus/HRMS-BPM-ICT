<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth; // Import Auth facade for clarity and consistency
use Illuminate\Database\Eloquent\Model; // Import Model for type hinting
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes for checking if model uses it

trait CreatedUpdatedDeletedBy
{
  // This method is automatically called by Eloquent when the trait is booted on a model.
  public static function bootCreatedUpdatedDeletedBy()
  {
    // Listen for the 'creating' event on the model
    static::creating(function (Model $model) {
      // If created_by is not already set (e.g., manually assigned before saving)
      // set it to the authenticated user's ID. Auth::id() returns the user's ID or null.
      if (!$model->isDirty('created_by')) {
        $model->created_by = Auth::id();
      }
      // Also set updated_by on creation, as it's the initial update
      if (!$model->isDirty('updated_by')) {
        $model->updated_by = Auth::id();
      }
    });

    // Listen for the 'updating' event on the model
    static::updating(function (Model $model) {
      // If updated_by is not already set, set it to the authenticated user's ID.
      if (!$model->isDirty('updated_by')) {
        $model->updated_by = Auth::id();
      }
      // Note: Eloquent automatically sets updated_at on update.
      // This part ensures updated_by is linked to that update,
      // unless you explicitly set updated_by manually before updating.
      $model->updated_by = Auth::id(); // Always set updated_by on update
    });

    // Listen for the 'deleting' event on the model (for soft deletes)
    static::deleting(function (Model $model) {
      // Check if the model uses the SoftDeletes trait.
      // We only want to set deleted_by if the model is being soft deleted.
      if (in_array(SoftDeletes::class, class_uses($model))) {
        // If deleted_by is not already set
        if (!$model->isDirty('deleted_by')) {
          $model->deleted_by = Auth::id(); // Set to the authenticated user's ID or null
        }
        // Need to manually save the model to persist the deleted_by value
        // before the soft delete timestamp is applied.
        // Temporarily disable events to prevent infinite loops during save.
        $model->withoutEvents(function () use ($model) {
          $model->save();
        });
      } else {
        // If the model is being permanently deleted (doesn't use SoftDeletes)
        // you might log this action elsewhere if needed, but don't set deleted_by
        // as the record is about to be removed entirely.
      }
    });

    // Optional: Listen for the 'restoring' event (when a soft-deleted model is restored)
    static::restoring(function (Model $model) {
      // Clear the deleted_by value when the model is restored
      $model->deleted_by = null;
      // Optionally set updated_by to the user who restored it
      $model->updated_by = Auth::id();
    });
  }

  // No need for the usesSoftDeletes helper method if checking SoftDeletes::class directly
  // within the deleting event listener.
}

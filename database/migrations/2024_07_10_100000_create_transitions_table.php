<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('transitions', function (Blueprint $table) {
      $table->id();
      // 👇 UPDATED: Change FK column name to equipment_id and reference the 'equipment' table
      $table->foreignId('equipment_id')->constrained('equipment')->onDelete('restrict'); // Constrain to 'equipment' table, restrict deleting equipment if transitions exist
      // ☝️ END UPDATED
      $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Cascade delete if employee is removed

      $table->date('handed_date')->nullable();
      $table->date('return_date')->nullable();
      $table
        ->string('center_document_number')
        ->unique()
        ->nullable();
      $table->string('reason')->nullable();
      $table->longText('note')->nullable();

      // Audit columns as nullable unsignedBigInteger with foreign keys (Already updated in previous step)
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns (Already updated in previous step)
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      // Soft deletes for consistency (Already updated in previous step)
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('transitions', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      // 👇 UPDATED: This will now drop the FK to 'equipment'
      if (Schema::hasColumn('transitions', 'equipment_id')) { // Check if column exists with new name
        $table->dropForeign(['equipment_id']);
      }
      // ☝️ END UPDATED
      $table->dropForeign(['employee_id']);
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);

      // If you ever need to roll back past this point and the 'asset_id' column existed,
      // you might need logic here to re-add the 'asset_id' column and its FK
      // based on the schema *before* this migration ran.
      // For simplicity, we assume clean install or rollback only to a state before these conflicting migrations.
    });
    Schema::dropIfExists('transitions');
  }
};

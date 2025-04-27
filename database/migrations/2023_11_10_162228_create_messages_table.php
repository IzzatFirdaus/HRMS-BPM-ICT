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
    Schema::create('messages', function (Blueprint $table) {
      $table->id();
      $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null'); // Set null if employee is removed

      $table->longText('text');
      $table->string('recipient'); // Consider if this should be user_id or another FK
      $table->boolean('is_sent');
      $table->string('error')->nullable();

      // 👇 UPDATED: Audit columns as nullable unsignedBigInteger with foreign keys
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END UPDATED

      $table->timestamps();
      // 👇 UPDATED: Added soft deletes for consistency
      $table->softDeletes();
      // ☝️ END UPDATED
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('messages', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      if (Schema::hasColumn('messages', 'employee_id')) { // Check if the column was added
        $table->dropForeign(['employee_id']);
      }
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('messages');
  }
};

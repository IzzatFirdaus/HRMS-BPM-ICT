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
    Schema::create('center_holiday', function (Blueprint $table) {
      // Define foreign keys to centers and holidays
      // Assumes 'centers' and 'holidays' tables exist and have standard 'id' primary keys (unsignedBigInteger).
      $table->foreignId('center_id')->constrained();
      $table->foreignId('holiday_id')->constrained();

      // Define the composite primary key using the foreign keys
      $table->primary(['center_id', 'holiday_id']);

      // 👇 UPDATED: Audit columns as nullable unsignedBigInteger foreign keys referencing the 'users' table
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
      // ☝️ END UPDATED

      // Add standard timestamps and soft delete timestamp
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('center_holiday', function (Blueprint $table) {
      // Drop foreign key constraints before dropping the table
      // The constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['center_id']);
      $table->dropForeign(['holiday_id']);
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('center_holiday');
  }
};

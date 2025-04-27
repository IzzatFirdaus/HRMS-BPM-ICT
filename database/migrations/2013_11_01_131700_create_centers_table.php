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
    Schema::create('centers', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->time('start_work_hour');
      $table->time('end_work_hour');
      $table->json('weekends'); // Corrected type from string to JSON as per model review
      $table->boolean('is_active')->default(true); // Ensure default is true

      // 👇 UPDATED: Audit columns using foreignId()->constrained() syntax
      // These require the 'users' table to exist when this migration runs (handled by file naming)
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
      // ☝️ END UPDATED

      $table->timestamps();
      $table->softDeletes(); // Added soft deletes as per our standardization
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('centers', function (Blueprint $table) {
      // Drop foreign key constraints before dropping the table
      // The constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('centers');
  }
};

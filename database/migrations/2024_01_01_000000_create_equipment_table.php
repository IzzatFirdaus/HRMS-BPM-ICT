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
    Schema::create('equipment', function (Blueprint $table) {
      $table->id();
      // Keep specific equipment types if needed, or integrate with 'class' from assets
      // 👇 Merging relevant columns from the 'assets' table
      $table->string('old_id')->nullable(); // Previous ID system if any (from assets)
      $table->enum('class', ['Electronic', 'Furniture', 'Gear', 'Other'])->nullable(); // General class (from assets), made nullable

      // Columns originally in equipment
      $table->enum('asset_type', ['laptop', 'projector', 'printer', 'monitor', 'keyboard', 'mouse', 'webcam', 'other'])->nullable(); // Specific equipment type

      $table->string('brand')->nullable();
      $table->string('model')->nullable();
      $table->string('serial_number')->unique()->nullable();
      $table->string('tag_id')->unique()->nullable(); // MOTAC asset tag

      // 👇 Merging acquisition/price/status fields from 'assets'
      $table->enum('condition_status', ['Good', 'Fine', 'Bad', 'Damaged'])->nullable(); // Physical condition status (from assets)
      $table->enum('availability_status', ['available', 'on_loan', 'under_maintenance', 'disposed', 'lost', 'damaged'])->default('available'); // Availability status (from equipment), default available

      $table->text('description')->nullable(); // Description (from assets, changed to text)
      $table->boolean('in_service')->default(true); // Whether the asset is currently usable (from assets)
      $table->boolean('is_gpr')->default(true); // Is it a GPR asset? (from assets)
      $table->integer('real_price')->nullable(); // Actual purchase price (from assets)
      $table->integer('expected_price')->nullable(); // Estimated current value or disposal price (from assets)

      // Add the 'purchase_date' column here
      $table->date('purchase_date')->nullable(); // Date the asset was purchased

      $table->date('acquisition_date')->nullable(); // Date asset was acquired (from assets) - this one was already there
      $table->enum('acquisition_type', ['Directed', 'Founded', 'Transferred', 'Purchased'])->nullable(); // Source of acquisition (from assets, added Purchased), made nullable
      $table->string('funded_by')->nullable(); // Source of funding (from assets)
      $table->string('current_location')->nullable(); // Current physical location (from equipment)
      $table->text('notes')->nullable(); // General notes (from equipment, already text)
      $table->date('warranty_expiry_date')->nullable(); // Warranty expiry date - this one was already there
      // ☝️ END Merging

      $table->timestamps();

      // Audit columns and foreign keys (as you already have them)
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      // Soft deletes (as you already have it)
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('equipment', function (Blueprint $table) {
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('equipment');
  }
};

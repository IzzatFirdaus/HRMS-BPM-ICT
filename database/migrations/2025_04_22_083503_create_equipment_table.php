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
      $table->enum('asset_type', ['laptop', 'projector', 'printer', 'monitor', 'keyboard', 'mouse', 'webcam', 'other']); // Expand as needed
      $table->string('brand')->nullable();
      $table->string('model')->nullable();
      $table->string('serial_number')->unique()->nullable();
      $table->string('tag_id')->unique()->nullable(); // MOTAC asset tag
      $table->date('purchase_date')->nullable();
      $table->date('warranty_expiry_date')->nullable();
      $table->enum('status', ['available', 'on_loan', 'under_maintenance', 'disposed', 'lost', 'damaged'])->default('available');
      $table->string('current_location')->nullable(); // e.g., "Stor BPM", "Level 5, Bilik Mesyuarat"
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('equipment');
  }
};

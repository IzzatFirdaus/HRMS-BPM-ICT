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
    Schema::create('loan_application_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();
      $table->string('equipment_type'); // e.g., "Laptop", "Projector" - refers to desired type, not specific asset
      $table->integer('quantity_requested');
      $table->text('notes')->nullable(); // Any specific requirements
      $table->integer('quantity_approved')->nullable(); // Quantity approved by officer
      $table->integer('quantity_issued')->default(0); // Quantity actually issued by BPM
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('loan_application_items');
  }
};

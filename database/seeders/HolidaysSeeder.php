<?php

namespace Database\Seeders;

// Assuming a Holiday model exists and has 'name', 'from_date', 'to_date', audit columns, timestamps, soft deletes fillable/cast
use App\Models\Holiday; // Import the Holiday model (Adjust if your model name is different)
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Carbon; // Import Carbon for date manipulation
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\Log; // Import Log for logging


class HolidaysSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding holidays...'); // Log start

    // Optional: Delete existing records before seeding to avoid duplicates
    // Ensure foreign key checks are off if using truncate and other tables reference holidays.
    // DB::table('holidays')->truncate(); // Use with caution if you need a clean slate
    // \Log::info('Truncated holidays table.');


    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point due to migration order.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define standard holidays for a specific year or range
    // Adjust the year and dates as needed for your seeding
    $year = 2025; // Example year

    // Define holidays using 'from_date' and 'to_date' as per the holidays table migration
    $holidays = [
      // Single-day holidays (from_date and to_date are the same)
      ['name' => 'New Year\'s Day', 'from_date' => Carbon::createFromDate($year, 1, 1)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 1, 1)->format('Y-m-d'), 'note' => null],
      ['name' => 'Thaipusam', 'from_date' => Carbon::createFromDate($year, 2, 11)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 2, 11)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Nuzul Al-Quran', 'from_date' => Carbon::createFromDate($year, 4, 7)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 4, 7)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Labour Day', 'from_date' => Carbon::createFromDate($year, 5, 1)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 5, 1)->format('Y-m-d'), 'note' => null],
      ['name' => 'Wesak Day', 'from_date' => Carbon::createFromDate($year, 5, 12)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 5, 12)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Agong\'s Birthday', 'from_date' => Carbon::createFromDate($year, 6, 7)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 6, 7)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Awal Muharam (Maal Hijrah)', 'from_date' => Carbon::createFromDate($year, 7, 27)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 7, 27)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Merdeka Day', 'from_date' => Carbon::createFromDate($year, 8, 31)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 8, 31)->format('Y-m-d'), 'note' => null],
      ['name' => 'Malaysia Day', 'from_date' => Carbon::createFromDate($year, 9, 16)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 9, 16)->format('Y-m-d'), 'note' => null],
      ['name' => 'Prophet Muhammad\'s Birthday', 'from_date' => Carbon::createFromDate($year, 10, 5)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 10, 5)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Deepavali', 'from_date' => Carbon::createFromDate($year, 10, 20)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 10, 20)->format('Y-m-d'), 'note' => null], // Example date, verify actual year's date
      ['name' => 'Christmas Day', 'from_date' => Carbon::createFromDate($year, 12, 25)->format('Y-m-d'), 'to_date' => Carbon::createFromDate($year, 12, 25)->format('Y-m-d'), 'note' => null],
      // Add dates for Raya Aidilfitri, Raya Aidiladha, Chinese New Year, State Holidays, etc.
      // Note: Dates for variable holidays like Eids and CNY change yearly.
      // Multi-day holiday example:
      // ['name' => 'Hari Raya Aidilfitri (Day 1)', 'from_date' => '2025-03-31', 'to_date' => '2025-03-31', 'note' => 'Hari Raya Aidilfitri'],
      // ['name' => 'Hari Raya Aidilfitri (Day 2)', 'from_date' => '2025-04-01', 'to_date' => '2025-04-01', 'note' => 'Hari Raya Aidilfitri'],
    ];

    foreach ($holidays as $holidayData) {
      // Create the holiday if it doesn't already exist based on from_date and name
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Holiday model, which has audit columns and timestamps.
      // This should be fine as the Holiday model does not have SoftDeletes.
      Holiday::firstOrCreate(
        ['from_date' => $holidayData['from_date'], 'name' => $holidayData['name']], // Find by from_date and name
        array_merge(
          $holidayData, // Add other attributes (includes to_date and note)
          [
            'created_by' => $auditUserId, // Set audit columns using the fetched admin user ID
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
    }

    \Log::info('Holidays seeded successfully.'); // Log success

    // Optional: Create additional random holidays using a factory if you have one
    // If you created a HolidayFactory, ensure it populates audit columns correctly
    // and handles from_date/to_date.
    // \App\Models\Holiday::factory()->count(10)->create(); // Create 10 more random holidays
  }
}

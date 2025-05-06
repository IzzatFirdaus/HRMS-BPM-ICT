<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * <<<<<<< HEAD
 * Class Approval
 * 
 * Represents a single approval record for a polymorphic 'approvable' model
 * (e.g., EmailApplication, LoanApplication). Stores information about the
 * officer who made the decision, the status, the stage in the workflow,
 * comments, and the timestamp of the decision.
 * =======
 *
 * @property int $id
 * @property string $approvable_type
 * @property int $approvable_id
 * @property int $officer_id
 * @property string|null $stage
 * @property string $status
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $approval_timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|\Eloquent $approvable
 * @property-read string $stage_translated
 * @property-read string $status_translated
 * @property-read User $officer
 * @method static \Database\Factories\ApprovalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovalTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval withoutTrashed()
 * @mixin \Eloquent
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 * @property-read string|null $officer_name
 */
	class Approval extends \Eloquent {}
}

namespace App\Models{
/**
 * <<<<<<< HEAD
 * Class Category
 * 
 * Represents a top-level category for items within the system (e.g., ICT Equipment Categories).
 * Has a one-to-many relationship with SubCategory models.
 * =======
 *
 * @property int $id
 * @property string $name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SubCategory> $subCategories
 * @property-read int|null $sub_categories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Center
 * 
 * Represents an administrative or physical center within the organization.
 * Manages relationships with Timelines (employee assignments), Holidays,
 * and provides methods for checking work hours, weekends, and associated employees.
 *
 * @property int $id
 * @property string $name
 * @property string|null $start_work_hour
 * @property string|null $end_work_hour
 * @property array<array-key, mixed> $weekends // Cast to array
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Holiday> $holidays
 * @property-read int|null $holidays_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Timeline> $timelines
 * @property-read int|null $timelines_count
 * @property-read string $weekends_formatted // Custom accessor
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Employee> $activeEmployees // Custom method
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereEndWorkHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereStartWorkHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereWeekends($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center withoutTrashed()
 * @mixin \Eloquent
 */
	class Center extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Changelog
 * 
 * Represents a single entry in the application's changelog, detailing changes for a specific version.
 * Includes version, title, description, and audit trails.
 *
 * @property int $id
 * @property string $version The application version this changelog entry belongs to (e.g., '1.0.0').
 * @property string $title The title of the changelog entry (e.g., 'New Feature: Loan Requests').
 * @property string $description The detailed description of the changes (can be markdown/HTML).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait if applied here).
 * <<<<<<< HEAD
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * 
 * =======
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog query()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog withoutTrashed()
 * @mixin \Eloquent
 */
	class Changelog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Contract
 * 
 * Represents a type of employment contract or service appointment (e.g., 'Permanent', 'Contract', 'MySTEP').
 * Includes details like a work rate and notes.
 * Has a one-to-many relationship with Employee models.
 *
 * @property int $id
 * @property string $name The name of the contract type (e.g., 'Permanent', 'Contract', 'MySTEP').
 * @property float $work_rate The work rate associated with this contract type (e.g., an hourly or daily rate).
 * @property string|null $notes Any additional notes or description for the contract type.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait if applied here).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees with this contract type.
 * @property-read int|null $employees_count Count of employees with this contract type.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereWorkRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract withoutTrashed()
 * @mixin \Eloquent
 */
	class Contract extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 * 
 * Represents an organizational department or unit within MOTAC.
 * Linked to Timelines, Users, and Employees via one-to-many relationships.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name The name of the department (e.g., 'Bahagian Pengurusan Maklumat').
 * @property string|null $description A description of the department or its functions.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees belonging to this department.
 * @property-read int|null $employees_count Count of employees in this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines The timelines associated with this department.
 * @property-read int|null $timelines_count Count of timelines for this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users The users belonging to this department.
 * @property-read int|null $users_count Count of users in this department.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department withoutTrashed()
 * @mixin \Eloquent
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $serial_number
 * @property string|null $model
 * @property string|null $manufacturer
 * @property string|null $location
 * @property int $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withoutTrashed()
 */
	class Device extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Discount
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee the discount applies to.
 * @property float $rate The discount rate or amount.
 * @property \Illuminate\Support\Carbon $date The date the discount applies to.
 * @property string|null $reason The reason for the discount.
 * @property bool $is_auto Indicates if the discount was applied automatically.
 * @property bool $is_sent Indicates if the discount notification/record has been sent/processed.
 * @property string|null $batch A batch identifier for processing discounts.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee $employee The employee the discount belongs to.
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withoutTrashed()
 * @mixin \Eloquent
 */
	class Discount extends \Eloquent {}
}

namespace App\Models{
/**
 * Add PHPDoc annotations to help static analysis tools (like Intelephense)
 * recognize the dynamic properties provided by Eloquent's magic methods.
 * 
 * This helps resolve "Undefined method 'id'" and similar linter warnings.
 * (ADDED based on discussion)
 *
 * @property int $id
 * @property int $user_id The applicant who submitted the application.
 * @property string|null $service_status Matches enum in email_applications migration (e.g., 'Kakitangan Tetap').
 * @property string|null $purpose Tujuan Permohonan.
 * @property string|null $proposed_email Cadangan E-mel/ID.
 * @property string|null $group_email Nama Group Email.
 * @property string|null $group_admin_name Nama Admin/EO/CC.
 * @property string|null $group_admin_email E-mel Admin/EO/CC.
 * @property int|null $supporting_officer_id FK to users table (for the support reviewer).
 * @property string $status Matches enum in email_applications migration (e.g., 'draft').
 * @property bool $certification_accepted Whether the applicant accepted the certification terms.
 * @property Carbon|null $certification_timestamp Timestamp when the certification was accepted.
 * @property string|null $rejection_reason Reason for rejection if status is rejected.
 * @property string|null $final_assigned_email The email/ID finally assigned by IT Admin.
 * // @property int|null $final_assigned_user_id This column is NOT on this table per migration.
 * // @property Carbon|null $provisioned_at This column is NOT on this table per migration.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by FK to users table (audit trail).
 * @property int|null $updated_by FK to users table (audit trail).
 * @property int|null $deleted_by FK to users table (audit trail).
 * 
 * // Relationships
 * @property-read User $user The applicant.
 * @property-read User|null $supportingOfficer The assigned supporting officer.
 * // @property-read User|null $finalAssignedUser Relationship removed as column is not on this table.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Approval> $approvals The approvals related to this application.
 * @property-read User|null $creator The user who created this record (via CreatedUpdatedDeletedBy trait).
 * @property-read User|null $updator The user who last updated this record (via CreatedUpdatedDeletedBy trait).
 * @property-read User|null $deletor The user who deleted this record (via CreatedUpdatedDeletedBy trait).
 * 
 * // Accessors
 * @property-read string $service_status_translated Translated service status.
 * @property-read string $status_translated Translated workflow status.
 * @mixin \Illuminate\Database\Eloquent\Builder // Include mixin for model scopes and query builder methods
 * @property string|null $final_assigned_user_id
 * @property string|null $provisioned_at
 * @property-read int|null $approvals_count
 * @method static \Database\Factories\EmailApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication status(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereCertificationAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereCertificationTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereFinalAssignedEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereFinalAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereGroupAdminEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereGroupAdminName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereGroupEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereProposedEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereProvisionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereServiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereSupportingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication withoutTrashed()
 */
	class EmailApplication extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Employee
 * 
 * Represents an employee within the HRMS system. Stores personal, work, and status details,
 * and manages relationships with various other system entities like contracts, departments,
 * positions, grades, timelines, leaves, fingerprints, discounts, messages, transitions,
 * assigned equipment, and a related user account. Includes audit trails, soft deletion,
 * accessors for derived attributes, and a local scope for checking leave applications.
 *
 * @property int $id
 * @property int|null $contract_id Foreign key to the contracts table.
 * @property int|null $department_id Foreign key to the departments table.
 * @property int|null $position_id Foreign key to the positions table.
 * @property int|null $grade_id Foreign key to the grades table.
 * @property string $first_name Employee's first name.
 * @property string|null $father_name Employee's father's name.
 * @property string|null $last_name Employee's last name.
 * @property string|null $mother_name Employee's mother's name.
 * @property string|null $birth_and_place Employee's birth date and place (stored as string).
 * @property string|null $national_number Employee's national identification number (NRIC).
 * @property string|null $mobile_number Employee's mobile phone number.
 * @property string|null $degree Employee's highest degree or qualification.
 * @property string|null $gender Employee's gender.
 * @property string|null $address Employee's address.
 * @property string|null $notes Additional notes or remarks about the employee.
 * @property int $balance_leave_allowed Employee's remaining annual leave balance.
 * @property int $max_leave_allowed Employee's maximum allowed annual leave.
 * @property string|null $delay_counter Counter for tracking delays (stored as time format string e.g., 'HH:MM:SS').
 * @property string|null $hourly_counter Counter for tracking hourly work (stored as time format string e.g., 'HH:MM:SS').
 * @property bool $is_active Indicates if the employee is currently active.
 * @property string|null $profile_photo_path Path to the employee's profile photo file.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment> $assignedEquipment Equipment currently assigned to the employee.
 * @property-read int|null $assigned_equipment_count
 * @property-read \App\Models\Contract|null $contract The employee's contract type.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\Department|null $department The employee's department.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discount> $discounts Discounts associated with the employee.
 * @property-read int|null $discounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeave> $employeeLeaveApplications Individual employee leave application records.
 * @property-read int|null $employee_leave_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fingerprint> $fingerprints Fingerprint records for the employee.
 * @property-read int|null $fingerprints_count
 * @property-read \App\Models\Grade|null $grade The employee's grade.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Leave> $leaves Leave types associated via pivot table (employee_leave).
 * @property-read int|null $leaves_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages Messages sent by the employee.
 * @property-read int|null $messages_count
 * @property-read \App\Models\Position|null $position The employee's position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timeline entries for assignments and status changes.
 * @property-read int|null $timelines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Equipment transitions involving the employee.
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @property-read \App\Models\User|null $user The user account associated with the employee (if a one-to-one user account exists).
 * @property-read string $fullName Employee's concatenated full name.
 * @property-read string $shortName Employee's concatenated short name (first + last).
 * @property-read int $workedYears Number of years worked based on timelines.
 * @property-read string $currentPosition Employee's current position name based on active timeline.
 * @property-read string $currentDepartment Employee's current department name based on active timeline.
 * @property-read string $currentCenter Employee's current center name based on active timeline.
 * @property-read string $joinAtShortForm Employee's join date in short human-readable format.
 * @property-read string $joinAt Employee's join date in formatted string.
 * @method static \Illuminate\Database\Eloquent\Builder|Employee checkLeave(int $leave_id, string $from_date, string $to_date, ?string $start_at = null, ?string $end_at = null) Scope to find employees with a matching leave application.
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBalanceLeaveAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBirthAndPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDelayCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereHourlyCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMaxLeaveAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNationalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withoutTrashed()
 * <<<<<<< HEAD
 * =======
 * @method static \Illuminate\Database\Eloquent\Builder|Employee checkLeave($leave_id, $from_date, $to_date, $start_at, $end_at)
 * @property-read string $current_center
 * @property-read string $current_department
 * @property-read string $current_position
 * @property-read string $full_name
 * @property-read string $join_at
 * @property-read string $join_at_short_form
 * @property-read string $short_name
 * @property-read int $worked_years
 * @method static \Database\Factories\EmployeeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Employee whereUpdatedAt($value)
 * @method static Builder<static>|Employee whereUpdatedBy($value)
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 * @mixin \Eloquent
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeLeave
 * 
 * Represents a specific instance of an employee taking a type of leave.
 * This model typically maps to a pivot table ('employee_leave') with additional columns.
 *
 * @property int $id
 * @property int $employee_id Foreign key to employees table.
 * @property int $leave_id Foreign key to leaves table.
 * @property \Illuminate\Support\Carbon $from_date The start date of the leave.
 * @property \Illuminate\Support\Carbon $to_date The end date of the leave.
 * @property string|null $start_at The start time of the leave (if applicable).
 * @property string|null $end_at The end time of the leave (if applicable).
 * @property string|null $note Additional notes for the leave application.
 * @property bool $is_authorized Indicates if the leave application has been authorized.
 * @property bool $is_checked Indicates if the leave application has been checked/processed.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee $employee The employee associated with this record.
 * @property-read \App\Models\Leave $leave The leave type associated with this record.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereIsAuthorized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereIsChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereLeaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave withoutTrashed()
 * @method static \Database\Factories\EmployeeLeaveFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
	class EmployeeLeave extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Equipment
 * 
 * Represents an equipment item within the ICT equipment management system.
 * Stores details about the equipment, its status (availability and condition),
 * location, acquisition details, and relationships to employees, departments,
 * centers, and various loan/transition records. Includes audit trails and soft deletion.
 * Note: This model is designed to hold all relevant data, potentially merging
 * information that might have originated from separate 'equipment' and 'assets' tables.
 *
 * @property int $id
 * @property string|null $class Equipment classification (e.g., IT, Furniture, Vehicle).
 * @property string|null $asset_type More specific equipment type (e.g., Laptop, Projector, Chair, Sedan).
 * @property string|null $serial_number Equipment's unique serial number.
 * @property string|null $tag_id Equipment's unique asset tag ID assigned by the organization.
 * @property string|null $model Equipment model name or number.
 * @property string|null $manufacturer Equipment manufacturer's name.
 * @property float $value Equipment's current value or purchase price (stored as decimal).
 * @property \Illuminate\Support\Carbon|null $purchase_date Date the equipment was purchased.
 * @property \Illuminate\Support\Carbon|null $warranty_expiry_date Date the equipment's warranty expires.
 * @property string|null $notes Additional notes or remarks about the equipment.
 * @property \Illuminate\Support\Carbon|null $acquisition_date Date the equipment was acquired.
 * @property string|null $acquisition_type Type of acquisition (e.g., Purchase, Donation, Transfer).
 * @property string|null $funded_by Source of funding for the acquisition (e.g., Grant, Operating Budget).
 * @property string $availability_status Equipment's current availability status (e.g., available, on_loan, in_maintenance, retired).
 * @property string $condition_status Equipment's current condition status (e.g., good, fair, poor, damaged, in_repair).
 * @property string|null $location_details Detailed location information (e.g., "Room 101", "Storage Warehouse").
 * @property int|null $assigned_to_employee_id Foreign key to the employee the equipment is currently assigned to (nullable).
 * @property int|null $department_id Foreign key to the department the equipment belongs to or is permanently assigned to (nullable).
 * @property int|null $center_id Foreign key to the center the equipment belongs to or is permanently located at (nullable).
 * @property int|null $position_id Foreign key to the position associated with the equipment (nullable). // Added position_id to docblock
 * @property bool $is_active Indicates if the equipment record is currently active in the system (assuming this column exists).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \App\Models\LoanTransaction|null $activeLoanTransaction The currently active loan transaction for the equipment (needed for eager loading in controller).
 * @property-read \App\Models\Employee|null $assignedToEmployee The employee the equipment is currently assigned to.
 * @property-read \App\Models\Center|null $center The center the equipment belongs to or is located in.
 * @property-read \App\Models\LoanTransaction|null $currentTransaction The current active loan transaction for the equipment (if on loan - helper method).
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\Department|null $department The department the equipment belongs to or is located in.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $loanApplicationItems Loan application items associated with this equipment (many-to-one).
 * @property-read int|null $loan_application_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $loanTransactions Loan transactions associated with this equipment (many-to-one).
 * @property-read int|null $loan_transactions_count
 * @property-read \App\Models\Position|null $position The position associated with the equipment. // Added to docblock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Transitions associated with this equipment (many-to-one).
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @property-read string $nameAndTag The combined name and tag ID for the equipment.
 * @property-read string $availabilityStatusTranslated The human-readable, translated availability status.
 * @property-read string $conditionStatusTranslated The human-readable, translated condition status.
 * @property-read string $name_and_tag
 * @property-read string $availability_status_translated
 * @property-read string $condition_status_translated
 * @property string|null $old_id
 * @property string|null $brand
 * @property string|null $description
 * @property int $in_service
 * @property int $is_gpr
 * @property int|null $real_price
 * @property int|null $expected_price
 * @property string|null $current_location
 * @method static \Database\Factories\EquipmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereCurrentLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereExpectedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereInService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereIsGpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereOldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereRealPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment wherePositionId($value) // Added wherePositionId to docblock
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereAcquisitionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereAcquisitionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereAvailabilityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereConditionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereFundedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereWarrantyExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment withoutTrashed()
 */
	class Equipment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Fingerprint
 * 
 * Represents a daily attendance record for an employee, typically captured via a fingerprint or time clock system.
 * Includes check-in/out times, logs, and related information.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee associated with this record.
 * @property \Illuminate\Support\Carbon $date The date of the attendance record.
 * @property string|null $log Raw log data from the fingerprint device (nullable).
 * @property \Illuminate\Support\Carbon|null $check_in The check-in time for the day (nullable timestamp).
 * @property \Illuminate\Support\Carbon|null $check_out The check-out time for the day (nullable timestamp).
 * @property bool $is_checked Flag indicating if the record has been reviewed or processed.
 * @property string|null $excuse Notes or reasons for absence/anomalies (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the record was soft deleted.
 * @property-read string $checkIn The formatted check-in time (H:i string).
 * @property-read string $checkOut The formatted check-out time (H:i string).
 * @property-read \App\Models\Employee $employee The employee associated with this record.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereExcuse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereIsChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint filteredFingerprints(Builder $query, $selectedEmployeeId, $fromDate, $toDate, $isAbsence, $isOneFingerprint)
 * @property int|null $device_id
 * @method static Builder<static>|Fingerprint whereDeviceId($value)
 * @mixin \Eloquent
 */
	class Fingerprint extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Grade
 * 
 * Represents an employee grade level (e.g., '41', '44', 'N19').
 * Stores grade name, numerical level, and indicates if the grade is designated as an approver grade.
 * Linked to User, Employee, and Position models via one-to-many relationships.
 * Includes a self-referencing relationship to track minimum approval grades.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name The name of the grade (e.g., 'Grade 41', 'PTD 48').
 * @property int $level The numerical level of the grade (e.g., 41, 48, 19) for sorting or comparison.
 * @property bool $is_approver_grade Indicates if this grade is designated as an approver grade (e.g., Grade 41+).
 * @property int|null $min_approval_grade_id Foreign key to the Grade model representing the minimum grade required to approve items for this grade level (self-referencing, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees belonging to this grade.
 * @property-read int|null $employees_count Count of employees in this grade.
 * @property-read \App\Models\Grade|null $minApprovalGrade The minimum approval grade required for this grade level (self-referencing).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $positions Positions belonging to this grade.
 * @property-read int|null $positions_count Count of positions in this grade.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users belonging to this grade.
 * @property-read int|null $users_count Count of users in this grade.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereIsApproverGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereMinApprovalGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withoutTrashed()
 * @method static \Database\Factories\GradeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @mixin \Eloquent
 */
	class Grade extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Holiday
 * 
 * Represents a holiday that can be associated with multiple centers.
 * Tracks holiday name, dates, notes, and links to centers via a pivot table.
 *
 * @property int $id
 * @property string $name The name of the holiday.
 * @property \Illuminate\Support\Carbon $from_date The start date of the holiday.
 * @property \Illuminate\Support\Carbon $to_date The end date of the holiday.
 * @property string|null $note Additional notes about the holiday (nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the holiday was soft deleted.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Center> $centers The centers associated with this holiday.
 * @property-read int|null $centers_count
 * @property-read int $durationInDays The duration of the holiday in days.
 * @property-read bool $isMultiDay Check if this holiday spans multiple days.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday withoutTrashed()
 * @property-read int $duration_in_days
 * @mixin \Eloquent
 */
	class Holiday extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Import
 * 
 * Represents a record of a file import process.
 * Tracks file details, import status, progress, and details (e.g., errors).
 *
 * @property int $id
 * @property string $file_name The original name of the imported file.
 * @property int $file_size The size of the imported file in bytes.
 * @property string $file_ext The file extension (e.g., 'xlsx', 'csv').
 * @property string $file_type The type of data being imported (e.g., 'employees', 'equipment').
 * @property string $status The current status of the import process (e.g., 'pending', 'processing', 'completed', 'failed').
 * @property array|null $details Details about the import process, often including errors (JSON or text, here cast to JSON).
 * @property int $current The number of rows/items processed so far.
 * @property int $total The total number of rows/items to process.
 * @property int|null $created_by Foreign key to the user who initiated the import.
 * @property int|null $updated_by Foreign key to the user who last updated the import record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the import record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the import record was soft deleted.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileExt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Import withoutTrashed()
 * @property-read float $completion_percentage
 * @property-read string $status_translated
 * @mixin \Eloquent
 */
	class Import extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Leave
 * 
 * Represents a type of leave (e.g., Annual Leave, Sick Leave).
 * Linked to individual employee leave applications through the `EmployeeLeave` model,
 * and indirectly to employees via `EmployeeLeave` which links to the `Employee` model.
 *
 * @property int $id
 * @property string $name The name of the leave type (e.g., 'Annual Leave').
 * @property bool $is_instantly Indicates if this leave can be taken instantly without prior authorization.
 * @property bool $is_accumulative Indicates if remaining leave days/minutes accumulate to the next period.
 * @property int $discount_rate The rate at which this leave is discounted (e.g., 100 for full pay).
 * @property int $days_limit The maximum number of days allowed for this leave type.
 * @property int $minutes_limit The maximum number of minutes allowed for this leave type (if applicable).
 * @property string|null $notes Additional notes about the leave type.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees who have taken this leave type (via pivot).
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeave> $employeeLeaveApplications Individual employee leave applications for this leave type (one-to-many).
 * @property-read int|null $employee_leave_applications_count
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record.
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record.
 * @property-read string $name_with_discount_rate The name formatted with the discount rate (accessor).
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDaysLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDiscountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereIsAccumulative($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereIsInstantly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereMinutesLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave withoutTrashed()
 * @mixin \Eloquent
 */
	class Leave extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $responsible_officer_id
 * @property string $purpose
 * @property string $location
 * @property \Illuminate\Support\Carbon $loan_start_date
 * @property \Illuminate\Support\Carbon $loan_end_date
 * @property string $status
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $applicant_confirmation_timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals
 * @property-read int|null $approvals_count
 * @property-read string $status_translated
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $responsibleOfficer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\LoanApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereApplicantConfirmationTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereLoanEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereLoanStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereResponsibleOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplication withoutTrashed()
 */
	class LoanApplication extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LoanApplicationItem
 * <<<<<<< HEAD
 * 
 * Represents a single equipment item requested within a loan application.
 * Linked to a specific parent LoanApplication and a specific Equipment asset.
 * Stores the quantity requested, quantity approved, and any specific notes for this item.
 * Includes audit trails and soft deletion.
 * =======
 * 
 * Represents a single item requested within a loan application.
 * Linked to a specific LoanApplication and a specific Equipment asset.
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property int $loan_application_id The loan application this item belongs to (Foreign key to 'loan_applications' table).
 * @property int $equipment_id Foreign key to the specific Equipment asset requested ('equipment' table).
 * @property int $quantity_requested Kuantiti requested by the applicant (Integer).
 * @property int|null $quantity_approved Quantity approved by the approver(s) (Integer), can be null if not yet approved.
 * @property string|null $notes Catatan or additional notes for this specific item (Text or String, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \App\Models\Equipment $equipment The specific equipment asset model requested for this item.
 * @property-read \App\Models\LoanApplication $loanApplication The parent loan application model that the item belongs to.
 * @property-read int $issuedQuantity The total quantity of this specific equipment item issued for the parent application.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereLoanApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withoutTrashed()
 * @property string $equipment_type
 * @property-read int $issued_quantity
 * @method static \Database\Factories\LoanApplicationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplicationItem whereEquipmentType($value)
 * @mixin \Eloquent
 */
	class LoanApplicationItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LoanTransaction
 * 
 * Represents a single transaction record within a loan application.
 * Tracks the issue or return of a specific equipment item, including
 * involved officers, checklists, timestamps, notes, and the transaction status.
 * Linked to a parent LoanApplication and a specific Equipment asset.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $loan_application_id Foreign key to the parent loan application ('loan_applications' table).
 * @property int $equipment_id Foreign key to the specific equipment asset involved ('equipment' table).
 * @property int|null $issuing_officer_id Foreign key to the user who issued the equipment ('users' table, nullable).
 * @property int|null $receiving_officer_id Foreign key to the user who received the equipment at issue ('users' table, typically the applicant, nullable).
 * @property array|null $accessories_checklist_on_issue JSON checklist of accessories noted at the time of issue (nullable).
 * @property \Illuminate\Support\Carbon|null $issue_timestamp Timestamp when the equipment was issued (nullable).
 * @property int|null $returning_officer_id Foreign key to the user who returned the equipment ('users' table, typically the applicant, nullable).
 * @property int|null $return_accepting_officer_id Foreign key to the user who accepted the returned equipment ('users' table, typically BPM staff, nullable).
 * @property array|null $accessories_checklist_on_return JSON checklist of accessories noted at the time of return (nullable).
 * @property \Illuminate\Support\Carbon|null $return_timestamp Timestamp when the equipment was returned (nullable).
 * @property string|null $return_notes Notes recorded upon return of the equipment (Text or String, nullable).
 * @property string $status Workflow status of the transaction (e.g., issued, returned, overdue, lost, damaged).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \App\Models\Equipment $equipment The specific equipment asset model involved in the transaction.
 * @property-read \App\Models\User|null $issuingOfficer The user model for the officer who issued the equipment.
 * @property-read \App\Models\LoanApplication $loanApplication The parent loan application model associated with the transaction.
 * @property-read \App\Models\User|null $receivingOfficer The user model for the officer who received the equipment at issue.
 * @property-read \App\Models\User|null $returnAcceptingOfficer The user model for the officer who accepted the returned equipment.
 * @property-read \App\Models\User|null $returningOfficer The user model for the officer who returned the equipment.
 * @property-read \App\Models\User|null $user The generic user associated with the transaction (e.g., the recipient). // Added for clarity
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @property-read string $statusTranslated The human-readable, translated workflow status for the transaction.
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereAccessoriesChecklistOnIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereAccessoriesChecklistOnReturn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereIssueTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereIssuingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereLoanApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReceivingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnAcceptingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturningOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withoutTrashed()
 * @property-read string $status_translated
 * @method static \Database\Factories\LoanTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanTransaction whereId($value)
 * @mixin \Eloquent
 */
	class LoanTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Message
 * 
 * Represents a message record, potentially for notifications sent to employees (e.g., via SMS).
 * Linked to an Employee and tracks message details, recipient, status, and sender photo based on the updater.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee who is the recipient or subject of the message.
 * @property string $text The content of the message.
 * @property string|null $recipient The recipient identifier (e.g., phone number, email address, user ID).
 * @property bool $is_sent Flag indicating if the message was successfully sent.
 * @property string|null $error Any error message if sending failed (nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record (often the sender in simple setups).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the message record was soft deleted.
 * @property-read \App\Models\Employee $employee The employee associated with this message record.
 * @property-read string $messageSenderPhoto The URL or path to the profile photo of the message sender (based on updated_by).
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @mixin \Eloquent
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Notification
 * 
 * Represents a custom notification record for a user.
 * Stores notification data and read status. Can potentially support polymorphic notifications.
 *
 * @property string $id The primary key for the notification (often a UUID in Laravel's default).
 * @property string $type The notification class name.
 * @property array $data The notification data as a JSON encoded array.
 * @property \Illuminate\Support\Carbon|null $read_at The timestamp when the notification was marked as read.
 * @property int $user_id The ID of the user who should receive the notification (if not using polymorphic).
 * @property string|null $notifiable_type If polymorphic, the model type of the notifiable entity.
 * @property int|string|null $notifiable_id If polymorphic, the ID of the notifiable entity.
 * @property int|null $created_by Foreign key to the user who created the record (if applicable).
 * @property int|null $updated_by Foreign key to the user who last updated the record (if applicable).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (if applicable).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the notification was soft deleted.
 * @property-read \App\Models\User $user The user that the notification belongs to (if not using polymorphic 'notifiable').
 * @property-read Model|\Eloquent $notifiable The notifiable entity that the notification belongs to (if using polymorphic).
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification withoutTrashed()
 * @mixin \Eloquent
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Position
 * <<<<<<< HEAD
 * 
 * Represents an employee position or job title within the organizational structure.
 * Stores the position name, vacancy count, description, and links to an associated grade.
 * Also tracks which Timelines, Users, and Employees are linked to this position.
 * Includes audit trails and soft deletion.
 * =======
 * 
 * Represents an employee position or job title.
 * Linked to Grade, Timelines, Users, and Employees.
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property string $name The name of the position (e.g., 'Manager', 'Assistant Director', 'Clerk').
 * @property int|null $vacancies_count The number of vacant positions currently open (nullable integer).
 * @property string|null $description A detailed description of the position's roles and responsibilities (nullable).
 * @property int|null $grade_id Foreign key to the associated grade level ('grades' table, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees currently holding this position.
 * @property-read int|null $employees_count Count of employees in this position.
 * @property-read \App\Models\Grade|null $grade The grade level associated with this position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timeline entries associated with this position (historical assignments).
 * @property-read int|null $timelines_count Count of associated timeline entries.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users currently holding this position (user accounts linked to this position).
 * @property-read int|null $users_count Count of users in this position.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereVacanciesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position withoutTrashed()
 * @method static \Database\Factories\PositionFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
	class Position extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Setting
 * <<<<<<< HEAD
 * 
 * Represents application-wide configuration settings stored in a single database row.
 * Includes various system settings, such as SMS API credentials.
 * This model follows a singleton pattern for easy access to the global settings.
 * Includes audit trails and soft deletion (though soft deleting a singleton might be less common).
 * =======
 * 
 * Represents application-wide configuration settings, typically stored in a single database row.
 * Includes settings like SMS API credentials.
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property string|null $sms_api_sender The sender ID or name for the SMS API (nullable).
 * @property string|null $sms_api_username The username for authentication with the SMS API (nullable).
 * @property string|null $sms_api_password The password for authentication with the SMS API (nullable).
 * // Add other setting properties here based on your database schema
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the setting was soft deleted.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withoutTrashed()
 * @mixin \Eloquent
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SubCategory
 * 
 * Represents a subcategory within a hierarchical category structure.
 * Stores the subcategory name and links to a parent Category.
 * May include a description if that column exists in the database table.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $category_id Foreign key to the parent category ('categories' table).
 * @property string $name The name of the subcategory.
 * @property string|null $description A description of the subcategory (if column exists, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the subcategory was soft deleted.
 * @property-read \App\Models\Category $category The parent category model that the subcategory belongs to.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereName($value)
 * // Added whereDescription if column exists and is queryable
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withoutTrashed()
 * @mixin \Eloquent
 */
	class SubCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Timeline
 * <<<<<<< HEAD
 * 
 * Represents a historical entry for an employee's assignment and location details over a specific time period.
 * Tracks the employee's associated center, department, and position, along with the start and end dates of the assignment.
 * Can indicate if the period is a sequential/continuous part of their employment history.
 * Includes audit trails and soft deletion.
 * =======
 * 
 * Represents a historical entry for an employee's assignment, tracking their position,
 * department, and center over a specific time period.
 * >>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee associated with this timeline entry ('employees' table).
 * @property int|null $center_id Foreign key to the center associated with this timeline entry ('centers' table, nullable).
 * @property int|null $department_id Foreign key to the department associated with this timeline entry ('departments' table, nullable).
 * @property int|null $position_id Foreign key to the position associated with this timeline entry ('positions' table, nullable).
 * @property \Illuminate\Support\Carbon $start_date The start date of the assignment period.
 * @property \Illuminate\Support\Carbon|null $end_date The end date of the assignment period (null if ongoing/current).
 * @property bool $is_sequent Indicates if this is a sequential/continuous period of employment/assignment (boolean).
 * @property string|null $notes Additional notes about this timeline entry (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the timeline entry was soft deleted.
 * @property-read \App\Models\Center|null $center The center model associated with this timeline entry.
 * @property-read \App\Models\Department|null $department The department model associated with this timeline entry.
 * @property-read \App\Models\Employee $employee The employee model associated with this timeline entry.
 * @property-read \App\Models\Position|null $position The position model associated with this timeline entry.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline query()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereIsSequent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline withoutTrashed()
 * @mixin \Eloquent
 */
	class Timeline extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Transition
 * 
 * Represents a record of equipment being handed out to or returned by an employee.
 * Tracks the equipment item involved, the employee associated with the transition,
 * the dates of handing out and return, document numbers, reason, and notes.
 * This model is designed to replace the functionality related to older asset transitions,
 * linking directly to the modern Equipment model.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $equipment_id Foreign key to the equipment asset involved in the transition ('equipment' table).
 * @property int $employee_id Foreign key to the employee associated with the transition ('employees' table).
 * @property \Illuminate\Support\Carbon $handed_date Date when the equipment was handed out to the employee.
 * @property \Illuminate\Support\Carbon|null $return_date Date when the equipment was returned by the employee (null if not yet returned).
 * @property string|null $center_document_number Document number from the center/department related to the transition (nullable).
 * @property string|null $reason The reason for the transition (nullable string).
 * @property string|null $note Additional notes about the transition (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the transition record was soft deleted.
 * @property-read \App\Models\Employee $employee The employee model associated with this transition record.
 * @property-read \App\Models\Equipment $equipment The equipment asset model involved in the transition record.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Transition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCenterDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereHandedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withoutTrashed()
 * @method static \Database\Factories\TransitionFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
	class Transition extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property array<array-key, mixed>|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $employee_id
 * @property int|null $department_id
 * @property int|null $position_id
 * @property int|null $grade_id
 * @property string|null $full_name
 * @property string|null $personal_email
 * @property string|null $motac_email
 * @property string|null $nric
 * @property string|null $mobile_number
 * @property string|null $user_id_assigned
 * @property string|null $service_status
 * @property string|null $appointment_type
 * @property string $status
 * @property bool $is_admin
 * @property bool $is_bpm_staff
 * @property string|null $profile_photo_path
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $acceptedReturnTransactions
 * @property-read int|null $accepted_return_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailApplication> $emailApplications
 * @property-read int|null $email_applications_count
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Grade|null $grade
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $issuedTransactions
 * @property-read int|null $issued_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplication> $loanApplications
 * @property-read int|null $loan_applications_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Position|null $position
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $receivedTransactions
 * @property-read int|null $received_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplication> $responsibleLoanApplications
 * @property-read int|null $responsible_loan_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $returningTransactions
 * @property-read int|null $returning_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailApplication> $supportedEmailApplications
 * @property-read int|null $supported_email_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAppointmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsBpmStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMotacEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNric($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePersonalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereServiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserIdAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}


@extends('layouts.app')

@section('title', __('Edit User') . ': ' . ($user->name ?? ($user->full_name ?? 'N/A')))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ __('Edit User') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }}
                </h1>
                <p class="text-sm text-gray-600 mt-2">
                    {{ __('Last updated') }}: {{ $user->updated_at->format('d M Y H:i') }}
                </p>
            </div>
            <a href="{{ route('resource-management.admin.users.index') }}" class="btn-primary">
                {{ __('Back to Users List') }}
            </a>
        </div>

        <form action="{{ route('resource-management.admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-lg rounded-xl p-8 mb-8">
                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Basic Information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.users.partials.input-text', [
                            'name' => 'name',
                            'label' => __('Name'),
                            'required' => true,
                        ])

                        @include('admin.users.partials.input-text', [
                            'name' => 'full_name',
                            'label' => __('Full Name'),
                        ])

                        @include('admin.users.partials.input-email', [
                            'name' => 'email',
                            'label' => __('Official Email'),
                            'required' => true,
                        ])

                        @include('admin.users.partials.input-email', [
                            'name' => 'personal_email',
                            'label' => __('Personal Email'),
                        ])

                        @include('admin.users.partials.input-email', [
                            'name' => 'motac_email',
                            'label' => __('MOTAC Email'),
                        ])
                    </div>
                </section>

                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Employment Details') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.users.partials.input-text', [
                            'name' => 'employee_id',
                            'label' => __('Employee ID'),
                        ])

                        @include('admin.users.partials.input-text', [
                            'name' => 'user_id_assigned',
                            'label' => __('Assigned User ID'),
                        ])

                        @include('admin.users.partials.input-text', [
                            'name' => 'phone_number',
                            'label' => __('Phone Number'),
                            'value' => $user->phone_number ?? $user->mobile,
                        ])

                        @include('admin.users.partials.input-text', [
                            'name' => 'identification_number',
                            'label' => __('NRIC/Passport Number'),
                        ])
                    </div>
                </section>

                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Organizational Details') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.users.partials.select', [
                            'name' => 'department_id',
                            'label' => __('Department'),
                            'options' => $departments,
                            'optionValue' => 'id',
                            'optionLabel' => 'name',
                        ])

                        @include('admin.users.partials.select', [
                            'name' => 'position_id',
                            'label' => __('Position'),
                            'options' => $positions,
                            'optionValue' => 'id',
                            'optionLabel' => 'name',
                        ])

                        @include('admin.users.partials.select', [
                            'name' => 'grade_id',
                            'label' => __('Grade'),
                            'options' => $grades,
                            'optionValue' => 'id',
                            'optionLabel' => 'name',
                        ])
                    </div>
                </section>

                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Employment Status') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.users.partials.select', [
                            'name' => 'service_status',
                            'label' => __('Service Status'),
                            'options' => config('user.service_statuses'),
                            'optionValue' => 'value',
                            'optionLabel' => 'label',
                        ])

                        @include('admin.users.partials.select', [
                            'name' => 'appointment_type',
                            'label' => __('Appointment Type'),
                            'options' => config('user.appointment_types'),
                            'optionValue' => 'value',
                            'optionLabel' => 'label',
                        ])

                        @include('admin.users.partials.select', [
                            'name' => 'status',
                            'label' => __('Account Status'),
                            'options' => config('user.statuses'),
                            'optionValue' => 'value',
                            'optionLabel' => 'label',
                        ])
                    </div>
                </section>

                <div class="border-t pt-8 mt-8">
                    <div class="flex justify-end gap-4">
                        <button type="button" class="btn-secondary" onclick="window.location.reload()">
                            {{ __('Reset Changes') }}
                        </button>
                        <button type="submit" class="btn-primary">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Dynamic select2 initialization if needed
        document.addEventListener('DOMContentLoaded', function() {
            $('[data-select2]').select2({
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush

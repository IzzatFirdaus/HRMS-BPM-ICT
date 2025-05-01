@extends('layouts.app')

@section('title', __('Department/Unit List'))

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ __('Department/Unit List') }}</h2>
            <a href="{{ route('admin.departments.create') }}" class="btn-primary">
                <x-heroicon-o-plus-circle class="w-5 h-5 mr-2" />
                {{ __('Add New') }}
            </a>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        @if (session('error'))
            <x-alert type="error" :message="session('error')" class="mb-4" />
        @endif

        @if ($departments->isEmpty())
            <x-empty-state :message="__('No departments found.')" />
        @else
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table-header>{{ __('Name') }}</x-table-header>
                                <x-table-header>{{ __('Code') }}</x-table-header>
                                <x-table-header>{{ __('Branch Type') }}</x-table-header>
                                <x-table-header>{{ __('Description') }}</x-table-header>
                                <x-table-header class="text-center">{{ __('Members') }}</x-table-header>
                                <x-table-header class="text-right">{{ __('Actions') }}</x-table-header>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($departments as $department)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $department->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $department->code ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ __(Str::headline($department->branch_type)) ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                                        {{ $department->description ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-900">
                                        <span class="inline-block px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                            {{ $department->users_count }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <x-link-button :href="route('admin.departments.show', $department)" color="gray">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                            </x-link-button>

                                            <x-link-button :href="route('admin.departments.edit', $department)">
                                                <x-heroicon-o-pencil class="w-4 h-4" />
                                            </x-link-button>

                                            <form method="POST"
                                                action="{{ route('admin.departments.destroy', $department) }}"
                                                x-data="{ confirmDelete() { if (confirm('{{ __('Are you sure?') }}')) $el.submit() } }">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" @click="confirmDelete"
                                                    class="btn-icon text-red-600 hover:bg-red-50">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($departments->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $departments->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

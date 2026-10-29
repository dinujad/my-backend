@extends('layouts.admin')

@section('title', 'Staff')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-person-badge text-brand-red"></i> Staff
    </h1>
    <a href="{{ route('admin.staff.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add staff
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Role</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($staff as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $member->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $member->email }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ \App\Models\User::assignableRoles()[$member->role] ?? ucfirst(str_replace('_', ' ', $member->role)) }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($member->role !== 'super_admin' || auth()->user()->role === 'super_admin')
                            <a href="{{ route('admin.staff.edit', $member) }}" class="text-brand-red hover:underline text-xs">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">No staff accounts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($staff->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $staff->links() }}</div>
    @endif
</div>
@endsection

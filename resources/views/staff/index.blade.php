@extends('dashboard.includes.partial.base')

@section('title', 'Staff')

@section('content')
    <x-page-header title="Staff" subtitle="People employed at your venue" icon="badge"
        :breadcrumbs="['Staff' => null]">
        <x-slot:actions>
            @can('create-staff')
                <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">
                    <i class="material-icons-outlined fs-6 align-middle">person_add</i> Add Staff
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Active staff" :value="number_format($staff->where('is_active', true)->count())"
                icon="badge" tone="primary" :hint="number_format($staff->count()).' on record'" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Monthly payroll" :value="'Rs. '.number_format($monthlyPayroll)" icon="payments"
                tone="warning" hint="Salaried staff only" />
        </div>
        <div class="col-xl-4 col-sm-6">
            <x-stat-card label="Daily wage" :value="number_format($staff->where('employment_type', 'daily_wage')->count())"
                icon="engineering" tone="secondary" hint="Paid per event" />
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="p-3 pb-0">
                <form method="GET" class="row g-2 align-items-end" data-no-guard>
                    <div class="col-sm-3">
                        <label class="form-label">Employment</label>
                        <select name="employment_type" class="form-select form-select-sm">
                            <option value="">All types</option>
                            @foreach (\App\Models\Staff::EMPLOYMENT_TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(request('employment_type') === $key)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-sm-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary">Filter</button>
                        @if (request()->hasAny(['employment_type', 'status']))
                            <a href="{{ route('staff.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <hr class="my-3">

            <x-data-table :order="[[0, 'asc']]">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        @if (auth()->user()->isSuperAdmin())
                            <th>Hall</th>
                        @endif
                        <th>Contact</th>
                        <th>Employment</th>
                        <th class="text-end">Salary</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th class="no-sort text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-initial">{{ mb_substr($member->name, 0, 1) }}</span>
                                    <a href="{{ route('staff.show', $member) }}"
                                        class="fw-semibold text-decoration-none">{{ $member->name }}</a>
                                </div>
                            </td>
                            <td>{{ $member->designation ?: '—' }}</td>
                            @if (auth()->user()->isSuperAdmin())
                                <td class="text-secondary">{{ $member->hall->name ?? '—' }}</td>
                            @endif
                            <td>
                                @if ($member->phone)
                                    <a href="tel:{{ $member->phone }}"
                                        class="text-decoration-none">{{ $member->phone }}</a>
                                @else — @endif
                                @if ($member->cnic)
                                    <small class="text-secondary d-block">{{ $member->cnic }}</small>
                                @endif
                            </td>
                            <td><span class="chip">{{ $member->employment_type_label }}</span></td>
                            <td class="text-end">
                                <x-money :amount="$member->monthly_salary" zero="—" />
                            </td>
                            <td>{{ $member->joined_on?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <x-status-badge :label="$member->is_active ? 'Active' : 'Inactive'"
                                    :tone="$member->is_active ? 'success' : 'secondary'" />
                            </td>
                            <td class="text-end">
                                @can('edit-staff')
                                    <a href="{{ route('staff.edit', $member) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="material-icons-outlined fs-6">edit</i>
                                    </a>
                                @endcan
                                @can('delete-staff')
                                    <form action="{{ route('staff.destroy', $member) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Remove"
                                            data-confirm="Remove {{ $member->name }} from staff?">
                                            <i class="material-icons-outlined fs-6">delete</i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :colspan="auth()->user()->isSuperAdmin() ? 9 : 8" icon="badge"
                            title="No staff on record"
                            message="Add your team so you can roster them onto events and track payroll.">
                            @can('create-staff')
                                <x-slot:action>
                                    <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">
                                        <i class="material-icons-outlined fs-6 align-middle">person_add</i> Add Staff
                                    </a>
                                </x-slot:action>
                            @endcan
                        </x-empty-state>
                    @endforelse
                </tbody>
            </x-data-table>
        </div>
    </div>
@endsection

@extends('dashboard.includes.partial.base')

@section('title', 'Halls')

@section('content')
    <h6 class="mb-0 text-uppercase">Halls</h6>
    <hr>

    @can('create-halls')
        <div class="mb-3 text-end">
            <button class="btn btn-grd btn-grd-primary" id="addHallBtn">
                <i class="material-icons-outlined">add</i> Add Hall
            </button>
        </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Name</th>
                            <th>Owner</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Modified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($halls as $hall)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('halls.show', $hall->id) }}">
                                        {{ $hall->name }}
                                    </a>
                                </td>
                                <td>{{ $hall->owner_name ?? '-' }}</td>
                                <td>{{ $hall->users()->count() }}</td>
                                <td>{!! $hall->status_label ?? '-' !!}</td>
                                <td>{{ $hall->creator_name ?? '-' }}</td>
                                <td>{{ $hall->editor_name ?? '-' }}</td>
                                <td>
                                    @can('edit-halls')
                                        <button class="btn btn-sm btn-info editHallBtn" data-id="{{ $hall->id }}"
                                            data-name="{{ $hall->name }}" data-owner="{{ $hall->owner_name }}"
                                            data-phone="{{ $hall->phone }}" data-email="{{ $hall->email }}"
                                            data-address="{{ $hall->address }}" data-city="{{ $hall->city }}"
                                            data-state="{{ $hall->state }}" data-country="{{ $hall->country }}"
                                            data-zipcode="{{ $hall->zipcode }}" data-area="{{ $hall->area }}"
                                            data-description="{{ $hall->description }}" data-status="{{ $hall->status }}">
                                            <i class="material-icons-outlined">edit</i>
                                        </button>
                                    @endcan
                                    @can('delete-halls')
                                        <form action="{{ route('halls.destroy', $hall->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="material-icons-outlined">delete</i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    {{-- Optional JS for modal or datatable --}}
@endsection

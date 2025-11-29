@extends('dashboard.includes.partial.base')

@section('title', 'Subcategories')

@section('content')
    <h6 class="mb-0 text-uppercase">Subcategories</h6>
    <hr>

    @can('create-subcategories')
        <div class="mb-3 text-end">
            <button class="btn btn-grd btn-grd-primary" id="addSubcategoryBtn">
                <i class="material-icons-outlined">add</i> Add Subcategory
            </button>
        </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle datatable" id="subcategoryTable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Modified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subcategories as $subcategory)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $subcategory->name }}</td>
                                <td>{{ $subcategory->category_name ?? '-' }}</td>
                                <td>{!! $subcategory->status_label !!}</td>
                                <td>{{ $subcategory->creator_name ?? '-' }}</td>
                                <td>{{ $subcategory->editor_name ?? '-' }}</td>
                                <td>
                                    @can('edit-subcategories')
                                        <button class="btn btn-sm btn-info editSubcategoryBtn" data-id="{{ $subcategory->id }}"
                                            data-category_id="{{ $subcategory->category_id }}"
                                            data-name="{{ $subcategory->name }}"
                                            data-description="{{ $subcategory->description }}"
                                            data-status="{{ $subcategory->status }}">
                                            <i class="material-icons-outlined">edit</i>
                                        </button>
                                    @endcan
                                    @can('delete-subcategories')
                                        <form action="{{ route('subcategories.destroy', $subcategory->id) }}" method="POST"
                                            class="d-inline">
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

    <!-- Add/Edit Subcategory Modal -->
    <div class="modal fade" id="subcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="subcategoryForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subcategoryModalLabel">Add Subcategory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subcategoryCategory" class="form-label">Category</label>
                            <select name="category_id" id="subcategoryCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subcategoryName" class="form-label">Name</label>
                            <input type="text" name="name" id="subcategoryName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="subcategoryDescription" class="form-label">Description</label>
                            <textarea name="description" id="subcategoryDescription" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="subcategoryStatus" class="form-label">Status</label>
                            <select name="status" id="subcategoryStatus" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-grd btn-grd-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            // Add Subcategory button
            $('#addSubcategoryBtn').click(function() {
                $('#subcategoryForm').attr('action', "{{ route('subcategories.store') }}");
                $('#formMethod').val('POST');
                $('#subcategoryModalLabel').text('Add Subcategory');
                $('#subcategoryForm')[0].reset();
                new bootstrap.Modal(document.getElementById('subcategoryModal')).show();
            });

            // Edit Subcategory buttons
            $('.editSubcategoryBtn').click(function() {
                const id = $(this).data('id');
                $('#subcategoryForm').attr('action', '/subcategories/' + id);
                $('#formMethod').val('PUT');
                $('#subcategoryModalLabel').text('Edit Subcategory');
                $('#subcategoryCategory').val($(this).data('category_id'));
                $('#subcategoryName').val($(this).data('name'));
                $('#subcategoryDescription').val($(this).data('description'));
                $('#subcategoryStatus').val($(this).data('status'));
                new bootstrap.Modal(document.getElementById('subcategoryModal')).show();
            });
        });
    </script>
@endsection

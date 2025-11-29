@extends('dashboard.includes.partial.base')

@section('title', 'Categories')

@section('content')
    <h6 class="mb-0 text-uppercase">Categories</h6>
    <hr>

    @can('create-categories')
        <div class="mb-3 text-end">
            <button class="btn btn-grd btn-grd-primary" id="addCategoryBtn">
                <i class="material-icons-outlined">add</i> Add Category
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
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Modified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{!! $category->status_label !!}</td>
                                <td>{{ $category->creator_name ?? '-' }}</td>
                                <td>{{ $category->editor_name ?? '-' }}</td>
                                <td>
                                    @can('edit-categories')
                                        <button class="btn btn-sm btn-info editCategoryBtn" data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}" data-description="{{ $category->description }}"
                                            data-status="{{ $category->status }}">
                                            <i class="material-icons-outlined">edit</i>
                                        </button>
                                    @endcan
                                    @can('delete-categories')
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
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

    <!-- Add/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="categoryForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Name</label>
                            <input type="text" name="name" id="categoryName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea name="description" id="categoryDescription" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="categoryStatus" class="form-label">Status</label>
                            <select name="status" id="categoryStatus" class="form-select">
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
            // Add Category button
            $('#addCategoryBtn').click(function() {
                $('#categoryForm').attr('action', "{{ route('categories.store') }}");
                $('#formMethod').val('POST');
                $('#categoryModalLabel').text('Add Category');
                $('#categoryForm')[0].reset();
                let modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                modal.show();
            });

            // Edit Category buttons
            $('.editCategoryBtn').click(function() {
                const id = $(this).data('id');
                $('#categoryForm').attr('action', '/categories/' + id);
                $('#formMethod').val('PUT');
                $('#categoryModalLabel').text('Edit Category');
                $('#categoryName').val($(this).data('name'));
                $('#categoryDescription').val($(this).data('description'));
                $('#categoryStatus').val($(this).data('status'));
                let modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                modal.show();
            });
        });
    </script>
@endsection

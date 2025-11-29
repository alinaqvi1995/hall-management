@extends('dashboard.includes.partial.base')

@section('title', 'Blogs')

@section('content')
    <h6 class="mb-0 text-uppercase">All Blogs</h6>
    <hr>
    <div class="card">
        <div class="card-body">

            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <h5 class="mb-0">Recent Blogs</h5>
                </div>
                <a href="{{ route('blogs.create') }}" class="btn btn-grd btn-grd-primary">
                    <i class="material-icons-outlined">add</i> Add New Blog
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="blogsTable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Tags</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $blog->heading_one }}</td>
                                <td>{{ $blog->author }}</td>
                                <td>{{ $blog->tags }}</td>
                                <td>{{ $blog->created_at->format('d M, Y') }}</td>
                                <td>
                                    <a href="{{ route('blogs.edit', $blog->id) }}" class="btn btn-sm btn-info">
                                        <i class="material-icons-outlined">edit</i>
                                    </a>
                                    <form action="{{ route('blogs.destroy', $blog->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="material-icons-outlined">delete</i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No blogs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function () {
            $('#blogsTable').DataTable({
                pageLength: 10,
                autoWidth: false,
                order: [[3, 'desc']], // Sort by Created At
                columnDefs: [
                    { orderable: false, targets: [4] } // Disable sorting on Actions column
                ]
            });
        });
    </script>
@endsection

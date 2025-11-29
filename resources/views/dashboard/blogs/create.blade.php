@extends('dashboard.includes.partial.base')
@section('title', 'Create Blog')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Blog</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('blogs.store') }}" method="POST" enctype="multipart/form-data" id="blogForm">
        @csrf

        <div class="row">
            <!-- Blog Main Information -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Blog Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                                    placeholder="Enter blog title" required>
                            </div>

                            {{-- <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug') }}" class="form-control"
                                    placeholder="Leave empty to auto-generate">
                            </div> --}}

                            <div class="col-md-12">
                                <label class="form-label">Excerpt</label>
                                <textarea name="excerpt" class="form-control summernote" rows="3" maxlength="500" placeholder="Short summary">{{ old('excerpt') }}</textarea>
                                <small class="text-muted">Max 500 characters</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Sections -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Content Sections</h5>
                        <div class="row g-3">
                            <!-- Section One -->
                            <div class="col-md-6">
                                <label class="form-label">Heading One</label>
                                <input type="text" name="heading_one" class="form-control"
                                    value="{{ old('heading_one') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description One</label>
                                <textarea name="description_one" class="form-control summernote" rows="4">{{ old('description_one') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image One</label>
                                <input type="file" name="image_one" class="form-control">
                            </div>

                            {{-- <hr class="mt-3">

                            <!-- Section Two -->
                            <div class="col-md-6">
                                <label class="form-label">Heading Two</label>
                                <input type="text" name="heading_two" class="form-control"
                                    value="{{ old('heading_two') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description Two</label>
                                <textarea name="description_two" class="form-control summernote" rows="4">{{ old('description_two') }}</textarea>
                            </div>

                            <!-- Additional descriptions for Section Two -->
                            @for ($i = 1; $i <= 7; $i++)
                                <div class="col-md-12">
                                    <label class="form-label">Extra Description {{ $i }}</label>
                                    <textarea name="description_two_{{ $i }}" class="form-control summernote" rows="2">{{ old('description_two_' . $i) }}</textarea>
                                </div>
                            @endfor

                            <div class="col-md-6">
                                <label class="form-label">Image Two</label>
                                <input type="file" name="image_two" class="form-control">
                            </div>

                            <hr class="mt-3">

                            <!-- Section Three -->
                            <div class="col-md-6">
                                <label class="form-label">Heading Three</label>
                                <input type="text" name="heading_three" class="form-control"
                                    value="{{ old('heading_three') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description Three</label>
                                <textarea name="description_three" class="form-control summernote" rows="4">{{ old('description_three') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image Three</label>
                                <input type="file" name="image_three" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Image Four</label>
                                <input type="file" name="image_four" class="form-control">
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories & Meta Info -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Categories & Meta Info</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-select">
                                    <option value="">-- Select Subcategory --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" value="{{ old('tags') }}" class="form-control"
                                    placeholder="e.g. SEO, Laravel, PHP">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Header Image Button Text</label>
                                <input type="text" name="header_image_btn" value="{{ old('header_image_btn') }}"
                                    class="form-control" placeholder="Button text or link">
                            </div>

                            {{-- <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Published</option>
                                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Author Info -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Author Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Author Name</label>
                                <input type="text" name="author" value="{{ old('author') }}" class="form-control"
                                    placeholder="Author name">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Author Note</label>
                                <textarea name="author_note" class="form-control summernote" rows="3">{{ old('author_note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-end">
                        <button type="submit" class="btn btn-primary px-4">Create Blog</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            $('#category_id').on('change', function() {
                var categoryId = $(this).val();
                $('#subcategory_id').html('<option value="">Loading...</option>');

                if (categoryId) {
                    var url = '{{ route('subcategories.by.category', ':id') }}';
                    url = url.replace(':id', categoryId);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            $('#subcategory_id').empty().append(
                                '<option value="">-- Select Subcategory --</option>');
                            $.each(data, function(key, subcategory) {
                                $('#subcategory_id').append('<option value="' +
                                    subcategory.id + '">' + subcategory.name +
                                    '</option>');
                            });
                        }
                    });
                } else {
                    $('#subcategory_id').html('<option value="">-- Select Subcategory --</option>');
                }
            });
        });
    </script>
@endsection

@extends('dashboard.includes.partial.base')
@section('title', 'Edit Blog')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Blog</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data" id="blogForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Blog Main Information -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Blog Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $blog->title) }}"
                                    class="form-control" placeholder="Enter blog title" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Excerpt</label>
                                <textarea name="excerpt" class="form-control summernote" rows="3" maxlength="500" placeholder="Short summary">{{ old('excerpt', $blog->excerpt) }}</textarea>
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
                                    value="{{ old('heading_one', $blog->heading_one) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description One</label>
                                <textarea name="description_one" class="form-control summernote" rows="4">{{ old('description_one', $blog->description_one) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image One</label>
                                <input type="file" name="image_one" class="form-control">
                                @if ($blog->image_one)
                                    <img src="{{ asset($blog->image_one) }}" alt="Image One" class="mt-2" width="100">
                                @endif
                            </div>

                            {{-- <hr class="mt-3">

                            <!-- Section Two -->
                            <div class="col-md-6">
                                <label class="form-label">Heading Two</label>
                                <input type="text" name="heading_two" class="form-control"
                                    value="{{ old('heading_two', $blog->heading_two) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description Two</label>
                                <textarea name="description_two" class="form-control summernote" rows="4">{{ old('description_two', $blog->description_two) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Extra Description 1</label>
                                <textarea name="description_two_one" class="form-control summernote" rows="2">{{ old('description_two_one', $blog->description_two_one) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 2</label>
                                <textarea name="description_two_two" class="form-control summernote" rows="2">{{ old('description_two_two', $blog->description_two_two) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 3</label>
                                <textarea name="description_two_three" class="form-control summernote" rows="2">{{ old('description_two_three', $blog->description_two_three) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 4</label>
                                <textarea name="description_two_four" class="form-control summernote" rows="2">{{ old('description_two_four', $blog->description_two_four) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 5</label>
                                <textarea name="description_two_five" class="form-control summernote" rows="2">{{ old('description_two_five', $blog->description_two_five) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 6</label>
                                <textarea name="description_two_six" class="form-control summernote" rows="2">{{ old('description_two_six', $blog->description_two_six) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Extra Description 7</label>
                                <textarea name="description_two_seven" class="form-control summernote" rows="2">{{ old('description_two_seven', $blog->description_two_seven) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Image Two</label>
                                <input type="file" name="image_two" class="form-control">
                                @if ($blog->image_two)
                                    <img src="{{ asset($blog->image_two) }}" alt="Image Two" class="mt-2"
                                        width="100">
                                @endif
                            </div>

                            <hr class="mt-3">

                            <!-- Section Three -->
                            <div class="col-md-6">
                                <label class="form-label">Heading Three</label>
                                <input type="text" name="heading_three" class="form-control"
                                    value="{{ old('heading_three', $blog->heading_three) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description Three</label>
                                <textarea name="description_three" class="form-control summernote" rows="4">{{ old('description_three', $blog->description_three) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image Three</label>
                                <input type="file" name="image_three" class="form-control">
                                @if ($blog->image_three)
                                    <img src="{{ asset($blog->image_three) }}" alt="Image Three" class="mt-2"
                                        width="100">
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Image Four</label>
                                <input type="file" name="image_four" class="form-control">
                                @if ($blog->image_four)
                                    <img src="{{ asset($blog->image_four) }}" alt="Image Four" class="mt-2"
                                        width="100">
                                @endif
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
                                            {{ old('category_id', $blog->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-select">
                                    <option value="">-- Select Subcategory --</option>
                                    @foreach ($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}"
                                            {{ old('subcategory_id', $blog->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" value="{{ old('tags', $blog->tags) }}"
                                    class="form-control" placeholder="e.g. SEO, Laravel, PHP">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Header Image Button Text</label>
                                <input type="text" name="header_image_btn"
                                    value="{{ old('header_image_btn', $blog->header_image_btn) }}" class="form-control"
                                    placeholder="Button text or link">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ old('status', $blog->status) == 1 ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="0" {{ old('status', $blog->status) == 0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>
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
                                <input type="text" name="author" value="{{ old('author', $blog->author) }}"
                                    class="form-control" placeholder="Author name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Author Note</label>
                                <textarea name="author_note" class="form-control summernote" rows="3">{{ old('author_note', $blog->author_note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-end">
                        <button type="submit" class="btn btn-primary px-4">Update Blog</button>
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

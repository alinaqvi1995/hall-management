{{--
    Session feedback and validation errors.
    Dismissible, icon-led and auto-hiding for the success case, replacing the
    plain undismissable alert blocks that used to sit above every page.
--}}

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2 app-alert"
        role="alert" data-auto-dismiss="6000">
        <i class="material-icons-outlined">check_circle</i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 app-alert" role="alert">
        <i class="material-icons-outlined">error_outline</i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2 app-alert" role="alert">
        <i class="material-icons-outlined">warning_amber</i>
        <div class="flex-grow-1">{{ session('warning') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2 app-alert"
        role="alert" data-auto-dismiss="6000">
        <i class="material-icons-outlined">check_circle</i>
        <div class="flex-grow-1">Your profile has been updated.</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show app-alert" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="material-icons-outlined">report_problem</i>
            <div class="flex-grow-1">
                <p class="fw-semibold mb-1">
                    {{ $errors->count() === 1 ? 'There is a problem with your submission' : 'There are '.$errors->count().' problems with your submission' }}
                </p>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

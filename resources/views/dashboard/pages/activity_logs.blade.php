@extends('dashboard.includes.partial.base')

@section('title', 'Activity Logs')

@section('content')
    <h6 class="mb-0 text-uppercase">Activity Logs</h6>
    <hr>

    <div class="mb-3 text-end">
        <button class="btn btn-grd btn-grd-primary" id="refreshLogsBtn">
            <i class="material-icons-outlined">refresh</i> Refresh
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('view.activity_logs') }}">
                <div class="order-search position-relative my-3">
                    <input class="form-control rounded-5 px-5" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search Logs">
                    <span
                        class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle" id="logsTable">
                    <thead>
                        <tr>
                            <th>Sr#.</th>
                            <th>Log Name</th>
                            <th>Description</th>
                            <th>Causer</th>
                            <th>Subject</th>
                            <th>Old Values</th>
                            <th>New Values</th>
                            <th>IP</th>
                            <th>User Agent</th>
                            {{-- <th>Location</th> --}}
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $log->log_name }}</td>
                                <td>{{ $log->description }}</td>
                                <td>
                                    {{ optional($log->causer)->name ?? $log->causer_type . ' #' . $log->causer_id }}
                                </td>
                                <td>
                                    {{ optional($log->subject)->id ?? $log->subject_type . ' #' . $log->subject_id }}
                                </td>
                                <td>
                                    <pre>{{ json_encode($log->properties['old_values'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td>
                                    <pre>{{ json_encode($log->properties['new_values'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td>{{ $log->properties['ip_address'] ?? '-' }}</td>
                                <td>{{ $log->properties['user_agent'] ?? '-' }}</td>
                                {{-- <td>
                                    {{ $log->properties['location']['city'] ?? '-' }},
                                    {{ $log->properties['location']['region'] ?? '-' }},
                                    {{ $log->properties['location']['country'] ?? '-' }}
                                </td> --}}
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="small text-muted">
                </div>
                <div>
                    {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        $(document).ready(function() {
            // Simple search filter
            $('#searchLogs').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#logsTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Refresh button
            $('#refreshLogsBtn').click(function() {
                location.reload();
            });
        });
    </script>
@endsection

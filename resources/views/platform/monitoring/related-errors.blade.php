@if(count($relatedErrors) > 0)
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($relatedErrors as $relatedError)
                    @php
                        $context = json_decode($relatedError->context ?? '{}');
                        $isResolved = isset($context->resolved) && $context->resolved;
                    @endphp
                    <tr>
                        <td>{{ $relatedError->id }}</td>
                        <td>{{ date('Y-m-d H:i:s', strtotime($relatedError->created_at)) }}</td>
                        <td>
                            <span class="badge {{ $relatedError->level === 'error' ? 'bg-warning' : 'bg-danger' }}">
                                {{ ucfirst($relatedError->level) }}
                            </span>
                        </td>
                        <td>
                            @if($isResolved)
                                <span class="badge bg-success">Resolved</span>
                            @else
                                <span class="badge bg-secondary">Unresolved</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('platform.monitoring.error.view', $relatedError->id) }}" class="btn btn-sm btn-link">
                                <i class="icon-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <p class="text-muted">No related errors found.</p>
    </div>
@endif

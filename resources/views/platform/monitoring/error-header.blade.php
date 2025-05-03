<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4>{{ Str::limit($error->message, 100) }}</h4>
        <div class="text-muted">
            <span class="badge {{ $error->level === 'error' ? 'bg-warning' : 'bg-danger' }} me-2">{{ ucfirst($error->level) }}</span>
            First seen: {{ date('Y-m-d H:i:s', strtotime($error->created_at)) }}
            @if(isset($context->resolved) && $context->resolved)
                <span class="badge bg-success ms-2">Resolved</span>
            @endif
        </div>
    </div>
    <div>
        @if(isset($context->error_id) && $context->error_id)
            <span class="badge bg-light text-dark p-2">ID: {{ $context->error_id }}</span>
        @endif
    </div>
</div>

@if(isset($context->exception) && isset($context->exception->message))
    <div class="alert alert-light border">
        <h6 class="mb-1">Exception Message:</h6>
        <code>{{ $context->exception->message }}</code>
    </div>
@endif

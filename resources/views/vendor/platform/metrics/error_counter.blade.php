<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
            <h3 class="text-center mb-0">{{ $errors->total() }}</h3>
            <span class="text-muted text-center">Total Errors</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
            <h3 class="text-center mb-0">{{ $errors->where('created_at', '>=', now()->startOfDay())->count() }}</h3>
            <span class="text-muted text-center">Today's Errors</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
            <h3 class="text-center mb-0">{{ count($errorsByType) }}</h3>
            <span class="text-muted text-center">Error Types</span>
        </div>
    </div>
</div>

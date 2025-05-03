<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h4>System Health</h4>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted">Memory Usage</h6>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 65%;" 
                             aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">65% of available memory</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted">CPU Load</h6>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 42%;" 
                             aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">42% of CPU capacity</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 mb-3">
                <div class="card-body">
                    <h6 class="text-muted">Agent Availability</h6>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ ($metrics['active_agents'] / max(1, $metrics['total_agents'])) * 100 }}%;" 
                             aria-valuenow="{{ ($metrics['active_agents'] / max(1, $metrics['total_agents'])) * 100 }}" 
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">{{ $metrics['active_agents'] }} of {{ $metrics['total_agents'] }} agents active</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body">
                    <h6 class="text-muted">System Uptime</h6>
                    <div class="d-flex align-items-center">
                        <span class="h3 mb-0 mr-3">
                            <i class="text-success icon-bell"></i>
                        </span>
                        <div>
                            <h5 class="mb-0">7 days, 14 hours</h5>
                            <small class="text-muted">Since last restart</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-right mt-3">
        <a href="#" class="btn btn-sm btn-link">View Detailed Metrics</a>
    </div>
</div>

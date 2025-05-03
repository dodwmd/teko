<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h4 class="mb-3">Recently Active Agents</h4>
    
    @if(count($recently_active) > 0)
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Type</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recently_active as $agent)
                    <tr>
                        <td class="font-weight-bold">{{ $agent->name }}</td>
                        <td>
                            {{ ucfirst(str_replace('_', ' ', $agent->type)) }}
                            @if($agent->language)
                                <span class="badge bg-light text-dark">{{ strtoupper($agent->language) }}</span>
                            @endif
                        </td>
                        <td>{{ $agent->last_active_at->diffForHumans() }}</td>
                        <td>
                            @if($agent->enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('platform.agent.edit', $agent->id) }}" class="btn btn-sm btn-link">
                                <i class="icon-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="text-right">
            <a href="{{ route('platform.agent.list') }}" class="btn btn-sm btn-link">View All Agents</a>
        </div>
    @else
        <div class="empty-state text-center py-5">
            <i class="icon-energy h1 text-muted mb-3"></i>
            <p class="mb-4">No agent activity detected in the last 24 hours</p>
            <a href="{{ route('platform.agent.list') }}" class="btn btn-sm btn-default">Manage Agents</a>
        </div>
    @endif
</div>

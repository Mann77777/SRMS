@forelse($requests as $request)
    <tr>
        <td><input type="checkbox" name="selected_requests[]" value="{{ $request['id'] }}"></td>
        <td>
            <span class="clickable-request-id" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                {{ $request['id'] }}
            </span>
        </td>
        <td>{!! $request['request_data'] !!}</td>
        <td>{{ $request['role'] }}</td>
        <td>
            <span>{{ \Carbon\Carbon::parse($request['date'])->format('M d, Y') }}</span><br>
            <span>{{ \Carbon\Carbon::parse($request['date'])->format('h:i A') }}</span>
        </td>
        <td>
            @if($request['status'] == 'Completed')
                @if(isset($request['updated_at']))
                    <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('M d, Y') }}</span><br>
                    <span>{{ \Carbon\Carbon::parse($request['updated_at'])->format('h:i A') }}</span>
                @else
                    <!-- If no updated_at field, display the date field or a placeholder -->
                    <span>{{ \Carbon\Carbon::parse($request['date'])->format('M d, Y') }}</span><br>
                    <span>{{ \Carbon\Carbon::parse($request['date'])->format('h:i A') }}</span>
                    <!-- Add a note or styling to indicate this is estimated -->
                @endif
            @else
                –
            @endif
        </td>
        <td>
            @if($request['status'] == 'Pending')
                <span class="custom-badge custom-badge-warning">{{ $request['status'] }}</span>
            @elseif($request['status'] == 'In Progress')
                <span class="custom-badge custom-badge-info">{{ $request['status'] }}</span>
            @elseif($request['status'] == 'Completed')
                <span class="custom-badge custom-badge-success">{{ $request['status'] }}</span>
            @elseif($request['status'] == 'Cancelled')
                <span class="custom-badge custom-badge-danger">{{ $request['status'] }}</span>
            @else
                <span class="custom-badge custom-badge-secondary">{{ $request['status'] }}</span>
            @endif
        </td>
                                    
        <td class="btns">
            @if($request['status'] == 'Pending')
                <button type="button" class="btn-approve" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                    Approve
                </button>
                <button type="button" class="btn-reject" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}" data-details="{{ $request['request_data'] }}">
                    Reject
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        {{-- Adjusted colspan to 8 to match the number of columns in the header --}}
        <td colspan="8" class="empty-state"> 
            <i class="fas fa-inbox fa-3x"></i>
            <p>No requests found matching the selected status.</p>
        </td>
    </tr>
@endforelse

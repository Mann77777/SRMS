@inject('serviceHelper', 'App\\Helpers\\ServiceHelper')
@inject('dateChecker', 'App\\Utilities\\DateChecker') {{-- Added DateChecker utility --}}
@forelse($requests as $request)
    <tr>
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
            @elseif($request['status'] == 'Overdue')
                <span class="custom-badge custom-badge-overdue">{{ $request['status'] }}</span>
            @elseif($request['status'] == 'Rejected')
                <span class="custom-badge custom-badge-danger">{{ $request['status'] }}</span>
            @elseif($request['status'] == 'Unresolvable')
                <span class="custom-badge custom-badge-gray">{{ $request['status'] }}</span>
            @else
                <span class="custom-badge custom-badge-secondary">{{ $request['status'] }}</span>
            @endif
        </td>
        <td>
            @if($request['status'] == 'In Progress' && isset($request['transaction_type']) && isset($request['updated_at']))
                @php
                    $transactionLimits = [
                        'Simple Transaction' => 3,
                        'Complex Transaction' => 7,
                        'Highly Technical Transaction' => 20,
                    ];
                    // Assuming 'updated_at' is when the request became 'In Progress' or was assigned.
                    // If 'created_at' or another field is more appropriate for the start of the SLA, adjust here.
                    $assignedDate = \Carbon\Carbon::parse($request['updated_at'])->startOfDay();
                    $businessDaysLimit = $transactionLimits[$request['transaction_type']] ?? 0;
                    
                    $deadlineDate = $dateChecker::calculateDeadline($assignedDate, $businessDaysLimit);
                    
                    // Calculate remaining working days from today until the deadline.
                    // countWorkingDaysBetween counts days from start up to, but not including, end.
                    // So, if today is the deadline, it will show 0 days left.
                    // If deadline is tomorrow, it will show 1 day left.
                    $remainingDays = $dateChecker::countWorkingDaysBetween(\Carbon\Carbon::today(), $deadlineDate);

                    // For display, "Overdue on" is the day *after* the deadline.
                    // We need to ensure this "overdue on" date is also a business day.
                    $displayOverdueDate = $deadlineDate->copy();
                     // If deadline itself is a non-working day (should not happen with calculateDeadline), or to be safe:
                    // Loop to find the next business day if deadlineDate was the last allowed day.
                    // For "Overdue on", we typically mean the start of the next business day *after* the deadline.
                    // So, we add one day and then find the next business day.
                    $displayOverdueDate->addDay(); // Move to the day after the deadline
                    while($dateChecker::isNonWorkingDay($displayOverdueDate)['isNonWorkingDay']) {
                        $displayOverdueDate->addDay();
                    }

                @endphp
                @if($remainingDays >= 0) {{-- Changed to >= 0 to correctly show "0 days left" on the due date --}}
                    <span class="remaining-days {{ $remainingDays <= 1 ? 'negative' : 'positive' }}"> {{-- Style as negative if 1 or 0 days left --}}
                        {{ $remainingDays }} day{{ $remainingDays != 1 ? 's' : '' }} left
                        <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays. Due by end of day: {{ $deadlineDate->format('M d, Y') }}" style="cursor: pointer; color: #888; margin-left: 4px;">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <br>
                        <small style="color:#888;">Due: {{ $deadlineDate->format('M d, Y') }}</small>
                    </span>
                @else
                    <span class="remaining-days negative">
                        Overdue by {{ abs($remainingDays) }} day{{ abs($remainingDays) != 1 ? 's' : '' }}
                        <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays. Was due: {{ $deadlineDate->format('M d, Y') }}" style="cursor: pointer; color: #888; margin-left: 4px;">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <br>
                        <small style="color:#888;">Was due: {{ $deadlineDate->format('M d, Y') }}</small>
                    </span>
                @endif
            @else
                -
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
        {{-- Adjusted colspan to 7 to match the number of columns in the header --}}
        <td colspan="7" class="empty-state"> 
            <i class="fas fa-inbox fa-3x"></i>
            <p>No requests found matching the selected status.</p>
        </td>
    </tr>
@endforelse

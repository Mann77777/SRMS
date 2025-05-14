@inject('serviceHelper', 'App\\Helpers\\ServiceHelper')
@forelse($requests as $request)
    <tr>
        <td><input type="checkbox" name="selected_requests[]" value="{{ $request['id'] }}"></td>
        <td>
            <span class="clickable-request-id" style="cursor: pointer; color: #007bff; text-decoration: underline;">
                {{ $request['id'] }}
            </span>
        </td>
        <td>{!! $request['request_data'] !!}</td>
        <td>
            @php
                $validityDays = $serviceHelper::getServiceValidityDays($request['service']);
                $validityText = match($validityDays) {
                    3 => 'Simple (3 days)',
                    7 => 'Complex (7 days)',
                    20 => 'Highly Technical (20 days)',
                    default => $validityDays . ' days',
                };
            @endphp
            {{ $validityText }}
        </td>
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
        <td>
            @if($request['status'] == 'In Progress' && isset($request['transaction_type']))
                @php
                    $transactionLimits = [
                        'Simple Transaction' => 3,
                        'Complex Transaction' => 7,
                        'Highly Technical Transaction' => 20,
                    ];
                    $assignedDate = \Carbon\Carbon::parse($request['updated_at'])->startOfDay();
                    $today = \Carbon\Carbon::today();

                    // 1. Find the first business day after assignment
                    $firstBusinessDay = $assignedDate->copy();
                    while (true) {
                        $dayOfWeek = $firstBusinessDay->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($firstBusinessDay);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($firstBusinessDay, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            break;
                        }
                        $firstBusinessDay->addDay();
                    }

                    // 2. Calculate last allowed business day
                    $limit = $transactionLimits[$request['transaction_type']] ?? 0;
                    $lastAllowedDay = $firstBusinessDay->copy();
                    $businessDaysCounted = 0;
                    while ($businessDaysCounted < $limit) {
                        $dayOfWeek = $lastAllowedDay->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($lastAllowedDay);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($lastAllowedDay, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            $businessDaysCounted++;
                        }
                        if ($businessDaysCounted < $limit) {
                            $lastAllowedDay->addDay();
                        }
                    }

                    // 3. Find the next business day after the last allowed day (for overdue)
                    $overdueDate = $lastAllowedDay->copy()->addDay();
                    while (true) {
                        $dayOfWeek = $overdueDate->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($overdueDate);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($overdueDate, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            break;
                        }
                        $overdueDate->addDay();
                    }

                    // 4. Calculate business days elapsed (from first business day)
                    $businessDaysElapsed = 0;
                    $currentDate = $firstBusinessDay->copy();
                    while ($currentDate->lte($today)) {
                        $dayOfWeek = $currentDate->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($currentDate);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($currentDate, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            $businessDaysElapsed++;
                        }
                        $currentDate->addDay();
                    }

                    $remainingDays = $limit - $businessDaysElapsed;
                @endphp
                @if($remainingDays > 0)
                    <span class="remaining-days positive">
                        {{ $remainingDays }} days left
                        <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <br>
                        <small style="color:#888;">Overdue on: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
                    </span>
                @else
                    <span class="remaining-days negative">
                        Overdue by {{ abs($remainingDays) }} days
                        <span class="info-tooltip" data-toggle="tooltip" title="Excludes weekends and holidays" style="cursor: pointer; color: #888; margin-left: 4px;">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <br>
                        <small style="color:#888;">Was due: {{ $overdueDate->format('M d, Y') }} 8:00 AM</small>
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
        {{-- Adjusted colspan to 8 to match the number of columns in the header --}}
        <td colspan="8" class="empty-state"> 
            <i class="fas fa-inbox fa-3x"></i>
            <p>No requests found matching the selected status.</p>
        </td>
    </tr>
@endforelse

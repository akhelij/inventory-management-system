@props(['currentStatus'])

@php
    $statuses = [
        'RECEIVED' => [
            'label' => __('Received'),
            'icon' => 'package',
            'color' => 'blue'
        ],
        'IN_PROGRESS' => [
            'label' => __('In Progress'),
            'icon' => 'tools',
            'color' => 'orange'
        ],
        'REPAIRED' => [
            'label' => __('Repaired'),
            'icon' => 'check-circle',
            'color' => 'green'
        ],
        'UNREPAIRABLE' => [
            'label' => __('Unrepairable'),
            'icon' => 'x-circle',
            'color' => 'red'
        ],
        'DELIVERED' => [
            'label' => __('Delivered'),
            'icon' => 'handshake',
            'color' => 'purple'
        ]
    ];
    
    $statusOrder = ['RECEIVED', 'IN_PROGRESS', 'REPAIRED', 'UNREPAIRABLE', 'DELIVERED'];
    $currentIndex = array_search($currentStatus, $statusOrder);
@endphp

<div class="steps steps-counter steps-blue mb-4">
    @foreach($statusOrder as $index => $status)
        
        @php
            $isCompleted = $index < $currentIndex;
            $isCurrent = $status === $currentStatus;
            $isUnrepairable = $currentStatus === 'UNREPAIRABLE' && $status === 'REPAIRED';
            
            // Skip "REPAIRED" if ticket is marked as "UNREPAIRABLE"
            if ($isUnrepairable) continue;
            
            // Set special styles for unrepairable path
            if ($currentStatus === 'UNREPAIRABLE' && $status === 'UNREPAIRABLE') {
                $isCurrent = true;
            }
            
            // Skip "UNREPAIRABLE" if ticket is marked as "REPAIRED"
            if ($currentStatus === 'REPAIRED' && $status === 'UNREPAIRABLE') continue;
        @endphp
        <a href="#" class="step-item {{ $isCurrent ? 'active' : '' }}"
           style="{{ $isCurrent ? 'font-weight: bold;' : '' }} {{ $status === 'UNREPAIRABLE' ? 'color: var(--tblr-red);' : '' }}">
            <div class="step-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-{{ $statuses[$status]['icon'] }}" 
                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2" 
                     stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    @if($statuses[$status]['icon'] === 'package')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"></path>
                        <path d="M12 12l8 -4.5"></path>
                        <path d="M12 12l0 9"></path>
                        <path d="M12 12l-8 -4.5"></path>
                    @elseif($statuses[$status]['icon'] === 'tools')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4"></path>
                        <path d="M14.5 5.5l4 4"></path>
                        <path d="M12 8l-5 -5l-4 4l5 5"></path>
                        <path d="M7 8l-1.5 1.5"></path>
                        <path d="M16 12l5 5l-4 4l-5 -5"></path>
                        <path d="M16 17l-1.5 1.5"></path>
                    @elseif($statuses[$status]['icon'] === 'check-circle')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M9 12l2 2l4 -4"></path>
                    @elseif($statuses[$status]['icon'] === 'x-circle')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M10 10l4 4m0 -4l-4 4"></path>
                    @elseif($statuses[$status]['icon'] === 'handshake')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path>
                    @endif
                </svg>
            </div>
            <div class="step-item-label">{{ $statuses[$status]['label'] }}</div>
        </a>
    @endforeach
</div> 
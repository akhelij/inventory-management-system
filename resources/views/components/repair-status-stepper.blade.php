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
        'REPAIR_RESULT' => [
            'label' => __('Repair/Unrepairable'),
            'icon' => 'check-circle', // Default icon
            'color' => 'green', // Default color
            'alt_icon' => 'x-circle', // Alternate icon for UNREPAIRABLE
            'alt_color' => 'red' // Alternate color for UNREPAIRABLE
        ],
        'DELIVERED' => [
            'label' => __('Delivered'),
            'icon' => 'box-seam-check',
            'color' => 'purple'
        ]
    ];
    
    $statusOrder = ['RECEIVED', 'IN_PROGRESS', 'REPAIR_RESULT', 'DELIVERED'];

    // Map actual status to our simplified status order
    $mappedStatus = $currentStatus;
    if ($currentStatus === 'REPAIRED' || $currentStatus === 'UNREPAIRABLE') {
        $mappedStatus = 'REPAIR_RESULT';
    }
    
    $currentIndex = array_search($mappedStatus, $statusOrder);
@endphp

<div class="steps steps-counter steps-blue mb-4">
    @foreach($statusOrder as $index => $status)
        
        @php
            $isCompleted = $index < $currentIndex;
            $isCurrent = $status === $mappedStatus;
            
            // For REPAIR_RESULT step, determine if it's REPAIRED or UNREPAIRABLE
            $useAltStyle = false;
            if ($status === 'REPAIR_RESULT' && $currentStatus === 'UNREPAIRABLE') {
                $useAltStyle = true;
            }
            
            // Determine the icon and color for REPAIR_RESULT
            $icon = $statuses[$status]['icon'];
            $iconColor = '';
            
            if ($status === 'REPAIR_RESULT') {
                if ($useAltStyle) {
                    $icon = $statuses[$status]['alt_icon'];
                    $iconColor = 'color: var(--tblr-red);';
                } else {
                    $iconColor = $currentStatus === 'REPAIRED' ? 'color: var(--tblr-green);' : '';
                }
            }
        @endphp
        <a href="#" class="step-item {{ $isCurrent ? 'active' : '' }}"
           style="{{ $isCurrent ? 'font-weight: bold;' : '' }} {{ $iconColor }}">
            <div class="step-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-{{ $icon }}" 
                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2" 
                     stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    @if($icon === 'package')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"></path>
                        <path d="M12 12l8 -4.5"></path>
                        <path d="M12 12l0 9"></path>
                        <path d="M12 12l-8 -4.5"></path>
                    @elseif($icon === 'tools')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4"></path>
                        <path d="M14.5 5.5l4 4"></path>
                        <path d="M12 8l-5 -5l-4 4l5 5"></path>
                        <path d="M7 8l-1.5 1.5"></path>
                        <path d="M16 12l5 5l-4 4l-5 -5"></path>
                        <path d="M16 17l-1.5 1.5"></path>
                    @elseif($icon === 'check-circle')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M9 12l2 2l4 -4"></path>
                    @elseif($icon === 'x-circle')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M10 10l4 4m0 -4l-4 4"></path>
                    @elseif($icon === 'box-seam-check')
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"></path>
                        <path d="M12 12l8 -4.5"></path>
                        <path d="M12 12l0 9"></path>
                        <path d="M12 12l-8 -4.5"></path>
                        <path d="M15 18.5l2 -2.5"></path>
                        <path d="M7 16.5l4 4"></path>
                    @endif
                </svg>
            </div>
            <div class="step-item-label">{{ $statuses[$status]['label'] }}</div>
        </a>
    @endforeach
</div> 
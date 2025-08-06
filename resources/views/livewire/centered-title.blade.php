@php
    // Get the value of 'nama_material' from the record
    $state = $getState();
@endphp

{{-- This div centers the text and applies font styling --}}
<div class="text-center py-4">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ $state }}
    </h1>
</div>
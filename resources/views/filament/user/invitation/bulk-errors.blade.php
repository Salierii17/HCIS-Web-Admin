<div class="space-y-2 max-h-96 overflow-y-auto p-2">
    <div class="text-sm font-medium text-gray-700 mb-2">
        <span class="fi-icon fi-icon-exclamation-circle text-danger-500 mr-1"></span>
        Processing encountered some issues:
    </div>
    
    <div class="space-y-1">
        @foreach($errors as $error)
            <div class="text-sm p-2 bg-danger-50/50 rounded border border-danger-100 text-danger-700">
                {{ $loop->iteration }}. {{ $error }}
            </div>
        @endforeach
    </div>
    
    @if(count($errors) === 0)
        <div class="text-sm p-2 bg-success-50 rounded border border-success-100 text-success-700">
            All selected candidates were processed successfully!
        </div>
    @endif
</div>
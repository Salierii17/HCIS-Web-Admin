<div class="flex flex-col h-full">
    <div class="flex-grow relative" style="min-height: 70vh;">
        <iframe 
            src="{{ $url }}#view=fitH" 
            width="100%" 
            height="100%"
            frameborder="0"
            class="absolute inset-0 border rounded-lg shadow-md"
            style="min-height: 500px;"
        >
            <p class="p-4 text-gray-500">Your browser does not support PDFs. 
                <a href="{{ $url }}" class="text-primary-600 hover:underline" download>Download the PDF instead.</a>
            </p>
        </iframe>
    </div>
    <div class="mt-4 flex justify-end py-4 px-2 bg-gray-50 rounded-b-lg">
        <button 
            x-on:click="$dispatch('close-modal')" 
            class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
        >
            Close
        </button>
    </div>
</div>
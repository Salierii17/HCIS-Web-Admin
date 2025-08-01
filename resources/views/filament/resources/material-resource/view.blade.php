@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fileUrl = Storage::url($record->file_path);
@endphp

<x-filament::page>
    <x-filament::section>
        <x-filament::section.heading>
            Preview Material: {{ $record->nama_material }}
        </x-filament::section.heading>

        <x-filament::section.content>
            @if (Str::endsWith($record->file_path, '.pdf'))
                <iframe src="{{ $fileUrl }}" class="w-full h-[80vh]" frameborder="0"></iframe>
            @else
                <p class="text-gray-700">Preview tidak tersedia untuk file ini.</p>
                <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 underline">Download file</a>
            @endif
        </x-filament::section.content>
    </x-filament::section>
</x-filament::page>

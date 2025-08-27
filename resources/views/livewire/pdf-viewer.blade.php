{{--
    This Blade component is responsible for rendering the PDF file.
    It retrieves the file path from the record and generates a public URL
    to be used in the iframe's src attribute.
--}}
@php
    // Get the file path from the record. e.g., 'materials/document.pdf'
    $filePath = $getRecord()->file_path;

    // We will use the asset() helper to generate the URL. This is often more reliable
    // than Storage::url() if the APP_URL in your .env file is not perfectly configured.
    // It creates a full URL like: http://localhost/storage/materials/yourfile.pdf
    $url = asset('storage/' . $filePath);

    // Check if the physical file exists in the storage path.
    $fileExists = Illuminate\Support\Facades\Storage::disk('public')->exists($filePath);
@endphp

{{-- The height is now set to h-screen, which makes the viewer take up the full vertical height of the browser window. --}}
<div class="w-full h-screen rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($filePath && $fileExists)
        {{-- Embed the PDF using an iframe --}}
        <iframe
            src="{{ $url }}"
            class="w-full h-full"
            frameborder="0"
        ></iframe>
    @else
        {{-- Display a detailed message if the file is not found, with debugging steps. --}}
        <div class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400 p-4">
            <p class="font-bold text-lg mb-2">File Not Found</p>
            <p>The application could not display the file.</p>

            <div class="text-left text-sm mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg w-full max-w-2xl">
                <p class="font-semibold">Debugging Checklist:</p>
                <ol class="list-decimal list-inside mt-2 space-y-1">
                    <li>
                        <strong>Verify URL:</strong> Try opening this link directly in a new tab:
                        <a href="{{ $url }}" target="_blank" class="text-blue-500 hover:underline break-all">{{ $url }}</a>
                        <p class="text-xs italic pl-4">If this link works, the issue might be with the iframe. If not, continue below.</p>
                    </li>
                    <li>
                        <strong>Check `.env` file:</strong> Ensure `APP_URL` is set to your local server address (e.g., `APP_URL=http://localhost` or `APP_URL=http://your-project.test`).
                    </li>
                    <li>
                        <strong>Re-link Storage:</strong> Since the link exists, try unlinking and relinking.
                        <ol class="list-alpha list-inside pl-4">
                            <li>Manually delete the `storage` shortcut/folder inside your project's `/public` directory.</li>
                            <li>Run `php artisan storage:link` again.</li>
                        </ol>
                    </li>
                     <li>
                        <strong>Web Server Config:</strong> For Apache, ensure your document root is pointing to your Laravel project's `/public` directory, not the project root.
                    </li>
                </ol>
            </div>
        </div>
    @endif
</div>

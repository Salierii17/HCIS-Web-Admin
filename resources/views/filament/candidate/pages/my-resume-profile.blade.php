<x-filament-panels::page>
    <form wire:submit="updateRecord" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="mt-3">
            <x-filament::button
                color="warning"
                icon="far-paper-plane"
                icon-position="before"
                iconSize="sm"
                type="submit"
                wire:target="updateRecord"
            >
                Update Profile
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

<x-filament-panels::page>
    <form wire:submit="saveSettings">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit" color="primary">
                Save Settings
            </x-filament::button>

            <x-filament::button type="button" color="info" wire:click="testPrinter">
                Test Printer
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

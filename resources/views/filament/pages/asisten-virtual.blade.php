<x-filament-panels::page>
    <div>
        <form wire:submit="askAi">
            {{ $this->form }}
            <x-filament::button type="submit" class="mt-4" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="askAi">
                    Tanya Asisten
                </span>
                <span wire:loading wire:target="askAi">
                    Memproses...
                </span>
            </x-filament::button>
        </form>

        @if ($result)
            <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-2">Jawaban Asisten:</h3>

                <div class="prose dark:prose-invert max-w-none">
                    {!! $result !!}
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

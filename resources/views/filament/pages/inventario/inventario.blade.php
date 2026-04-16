<x-filament-panels::page>
    <x-filament::tabs>
        @foreach ($this->getCachedTabs() as $tabKey => $tab)
            <x-filament::tabs.item
                :active="$tabKey === $this->activeTab"
                wire:click="$set('activeTab', '{{ $tabKey }}')"
            >
                {{ $tab->getLabel() }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>

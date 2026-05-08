<x-filament-panels::page>
    {{--
        wire:ignore le dice a Livewire: "nunca toques este div ni su contenido".
        Sin esto, cada vez que Livewire re-renderiza la página destruiría
        el árbol de componentes React que está adentro.
    --}}
    <div id="facturacion-react-root" wire:ignore></div>

    @viteReactRefresh
    @vite('resources/js/facturacion.jsx')
</x-filament-panels::page>

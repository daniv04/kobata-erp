<?php

namespace App\Filament\Pages\Inventario;

use App\Enums\NavigationGroup;
use App\Enums\StockMovementType;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class Inventario extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected string $view = 'filament.pages.inventario.inventario';

    protected static ?string $title = 'Inventario';

    protected static ?string $navigationLabel = 'Inventario';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BodegasInventario;

    protected static ?int $navigationSort = 2;

    public function mount(): void
    {
        $this->loadDefaultActiveTab();
    }

    public function getTabs(): array
    {
        $tabs = [
            'todas' => Tab::make('Todas'),
        ];

        foreach (Warehouse::where('is_active', true)->get() as $warehouse) {
            $tabs["warehouse_{$warehouse->id}"] = Tab::make($warehouse->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('warehouse_id', $warehouse->id));
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->modifyQueryWithActiveTab(
                WarehouseStock::query()->with(['product.category', 'warehouse', 'variant'])
            ))
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant.name')
                    ->label('Variante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable(),

                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('reserved_quantity')
                    ->label('Reservado')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('available')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 0)
                    ->getStateUsing(fn (WarehouseStock $record): float => $record->quantity - $record->reserved_quantity)
                    ->color(fn (float $state): string => $state <= 0 ? 'danger' : 'success'),
            ])
            ->filters([
                SelectFilter::make('product.category_id')
                    ->label('Categoría')
                    ->relationship('product.category', 'name'),
            ])
            ->recordActions([
                Action::make('ajustar')
                    ->label('Ajustar Stock')
                    ->icon(Heroicon::AdjustmentsHorizontal)
                    ->modalHeading(fn (WarehouseStock $record) => 'Ajustar stock: '.$record->product->name)
                    ->schema([
                        Select::make('direccion')
                            ->label('Tipo de ajuste')
                            ->options([
                                'entrada' => 'Entrada (+)',
                                'salida' => 'Salida (-)',
                            ])
                            ->required(),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),

                        Textarea::make('notas')
                            ->label('Notas')
                            ->rows(2)
                            ->required(),

                    ])
                    ->action(function (WarehouseStock $record, array $data, StockService $stockService): void {
                        $cantidad = (float) $data['cantidad'];

                        if ($data['direccion'] === 'salida') {
                            $cantidad = -$cantidad;
                        }

                        try {
                            $stockService->adjust(
                                productId: $record->product_id,
                                warehouseId: $record->warehouse_id,
                                quantity: $cantidad,
                                type: StockMovementType::Adjustment,
                                referenceType: 'adjustment',
                                referenceId: null,
                                unitCost: 0.0,
                                notes: $data['notas'] ?? null,
                                userId: auth()->id(),
                                variantId: $record->variant_id,
                            );

                            Notification::make()
                                ->title('Stock ajustado correctamente')
                                ->success()
                                ->send();

                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Error al ajustar stock')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

            ])
            ->defaultSort('product.name');

    }
}

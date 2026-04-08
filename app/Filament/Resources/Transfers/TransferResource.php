<?php

namespace App\Filament\Resources\Transfers;

use App\Filament\Resources\Transfers\Pages\CreateTransfer;
use App\Filament\Resources\Transfers\Pages\ListTransfers;
use App\Filament\Resources\Transfers\Pages\ViewTransfer;
use App\Filament\Resources\Transfers\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Transfers\Schemas\TransferForm;
use App\Filament\Resources\Transfers\Tables\TransfersTable;
use App\Models\Transfer;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $label = 'Traslado';

    protected static ?string $pluralLabel = 'Traslados';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return TransferForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del traslado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label('Referencia'),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'Pendiente',
                                        'dispatched' => 'Despachado',
                                        'received' => 'Recibido',
                                        'cancelled' => 'Cancelado',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'gray',
                                        'dispatched' => 'warning',
                                        'received' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('fromWarehouse.name')
                                    ->label('Bodega origen'),
                                TextEntry::make('toWarehouse.name')
                                    ->label('Bodega destino'),
                            ]),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ]),

                Section::make('Trazabilidad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('requestedByUser.name')
                                    ->label('Solicitado por'),
                                TextEntry::make('requested_at')
                                    ->label('Fecha solicitud')
                                    ->dateTime(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('dispatchedByUser.name')
                                    ->label('Despachado por'),
                                TextEntry::make('dispatched_at')
                                    ->label('Fecha despacho')
                                    ->dateTime(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('receivedByUser.name')
                                    ->label('Recibido por'),
                                TextEntry::make('received_at')
                                    ->label('Fecha recepción')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransfers::route('/'),
            'create' => CreateTransfer::route('/create'),
            'view' => ViewTransfer::route('/{record}'),
        ];
    }
}

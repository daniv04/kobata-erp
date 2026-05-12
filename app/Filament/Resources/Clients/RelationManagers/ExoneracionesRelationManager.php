<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Enums\NombreInstitucionExoneracion;
use App\Enums\TipoDocumentoExoneracion;
use App\Models\ClientExoneracion;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExoneracionesRelationManager extends RelationManager
{
    protected static string $relationship = 'exoneraciones';

    protected static ?string $title = 'Exoneraciones';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('tipo_documento')
                    ->label('Tipo de documento')
                    ->options(TipoDocumentoExoneracion::class)
                    ->required()
                    ->live(),
                TextInput::make('tipo_documento_otro')
                    ->label('Descripción del tipo (Otros)')
                    ->minLength(5)
                    ->maxLength(100)
                    ->required(fn (Get $get) => $get('tipo_documento') === TipoDocumentoExoneracion::Otros->value)
                    ->visible(fn (Get $get) => $get('tipo_documento') === TipoDocumentoExoneracion::Otros->value),
            ]),

            Grid::make(2)->schema([
                TextInput::make('numero_documento')
                    ->label('Número de documento')
                    ->required()
                    ->minLength(3)
                    ->maxLength(40),
                DateTimePicker::make('fecha_emision')
                    ->label('Fecha de emisión')
                    ->required()
                    ->seconds(false),
            ]),

            Grid::make(2)->schema([
                TextInput::make('articulo')
                    ->label('Artículo de ley')
                    ->numeric()
                    ->required(fn (Get $get) => in_array($get('tipo_documento'), [
                        TipoDocumentoExoneracion::Diplomaticos->value,
                        TipoDocumentoExoneracion::LeyEspecial->value,
                        TipoDocumentoExoneracion::ZonaFranca->value,
                    ]))
                    ->visible(fn (Get $get) => in_array($get('tipo_documento'), [
                        TipoDocumentoExoneracion::Diplomaticos->value,
                        TipoDocumentoExoneracion::LeyEspecial->value,
                        TipoDocumentoExoneracion::ZonaFranca->value,
                    ])),
                TextInput::make('inciso')
                    ->label('Inciso')
                    ->numeric()
                    ->visible(fn (Get $get) => in_array($get('tipo_documento'), [
                        TipoDocumentoExoneracion::Diplomaticos->value,
                        TipoDocumentoExoneracion::LeyEspecial->value,
                        TipoDocumentoExoneracion::ZonaFranca->value,
                    ])),
            ]),

            Grid::make(2)->schema([
                Select::make('nombre_institucion')
                    ->label('Institución emisora')
                    ->options(NombreInstitucionExoneracion::class)
                    ->required()
                    ->live(),
                TextInput::make('nombre_institucion_otros')
                    ->label('Nombre de la institución (Otros)')
                    ->minLength(5)
                    ->maxLength(160)
                    ->required(fn (Get $get) => $get('nombre_institucion') === NombreInstitucionExoneracion::Otros->value)
                    ->visible(fn (Get $get) => $get('nombre_institucion') === NombreInstitucionExoneracion::Otros->value),
            ]),

            TextInput::make('tarifa_exonerada')
                ->label('Tarifa exonerada (%)')
                ->helperText('Porcentaje del IVA que se exonera. Ej: 13 para exoneración total, 6.5 para 50%.')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.5)
                ->suffix('%')
                ->required(),

            Toggle::make('is_active')
                ->label('Exoneración activa')
                ->helperText('Solo puede haber una exoneración activa por cliente.')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_documento')
            ->columns([
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->formatStateUsing(fn (TipoDocumentoExoneracion $state) => $state->value.' - '.$state->getLabel()),
                TextColumn::make('numero_documento')
                    ->label('N° Documento')
                    ->searchable(),
                TextColumn::make('nombre_institucion')
                    ->label('Institución')
                    ->formatStateUsing(fn (NombreInstitucionExoneracion $state) => $state->getLabel()),
                TextColumn::make('fecha_emision')
                    ->label('Fecha emisión')
                    ->date('d/m/Y'),
                TextColumn::make('tarifa_exonerada')
                    ->label('Tarifa')
                    ->suffix('%'),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                   -> label('Agregar exoneración')
                    ->after(function (ClientExoneracion $record) {
                        if ($record->is_active) {
                            $record->client->exoneraciones()
                                ->where('id', '!=', $record->id)
                                ->update(['is_active' => false]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (ClientExoneracion $record) {
                        if ($record->is_active) {
                            $record->client->exoneraciones()
                                ->where('id', '!=', $record->id)
                                ->update(['is_active' => false]);
                        }
                    }),
                DeleteAction::make(),
            ]);
    }
}

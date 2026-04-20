<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $idNumber = $this->data['id_number'] ?? null;

        if ($idNumber && Client::where('id_number', $idNumber)->where('id', '!=', $this->record->id)->exists()) {
            Notification::make()
                ->danger()
                ->title('Número de identificación duplicado')
                ->body("Ya existe un cliente registrado con la identificación {$idNumber}.")
                ->persistent()
                ->send();

            $this->halt();
        }
    }
}

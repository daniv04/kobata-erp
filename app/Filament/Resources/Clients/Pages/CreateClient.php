<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function beforeCreate(): void
    {
        $idNumber = $this->data['id_number'] ?? null;

        if ($idNumber && Client::where('id_number', $idNumber)->exists()) {
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

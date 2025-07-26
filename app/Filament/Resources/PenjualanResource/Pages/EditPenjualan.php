<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenjualan extends EditRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set kode_faktur based on faktur_id
        if (isset($data['faktur_id'])) {
            $faktur = \App\Models\FakturModel::find($data['faktur_id']);
            if ($faktur) {
                $data['kode_faktur'] = $faktur->kode_faktur;
            }
        }

        return $data;
    }
}

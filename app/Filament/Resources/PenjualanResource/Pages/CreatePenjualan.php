<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a unique 'kode' value, e.g., using current timestamp and random string
        $data['kode'] = 'KODE-' . time() . '-' . substr(md5(uniqid('', true)), 0, 6);

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

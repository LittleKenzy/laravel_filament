<?php

namespace App\Filament\Resources\FakturResource\Pages;

use App\Filament\Resources\FakturResource;
use App\Models\PenjualanModel;
use App\Models\CustomerModel;
use Filament\Resources\Pages\CreateRecord;

class CreateFaktur extends CreateRecord
{
    protected static string $resource = FakturResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['customer_id'])) {
            $customer = CustomerModel::find($data['customer_id']);
            if ($customer) {
                $data['kode_customer'] = $customer->kode_customer;
            }
        }
        return $data;
    }

    protected function afterCreate()
    {
        PenjualanModel::create([
            'kode' => $this->record->kode_faktur,
            'tanggal' => $this->record->tanggal_faktur,
            'jumlah' => $this->record->total,
            'customer_id' => $this->record->customer_id,
            'faktur_id' => $this->record->id,
            'keterangan' => $this->record->ket_faktur,
            'status' => 0,
        ]);
    }
}

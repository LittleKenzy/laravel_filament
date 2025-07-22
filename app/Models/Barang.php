<?php

namespace App\Models;

use App\Filament\Resources\FakturResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function detail()
    {
        return $this->hasMany(DetailFakturModel::class);
    }

    public function harga_barang()
    {
        return $this->hasMany(FakturResource::class);
    }

    public function nama_barang()
    {
        return $this->hasMany(FakturResource::class);
    }
}

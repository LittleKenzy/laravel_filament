<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailFakturModel extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'detail';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function faktur()
    {
        return $this->belongsTo(FakturModel::class, 'faktur_id');
    }
}

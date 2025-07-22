<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FakturModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'faktur';

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class);
    }

    public function detail()
    {
        return $this->hasMany(DetailFakturModel::class, 'faktur_id');
    }
}

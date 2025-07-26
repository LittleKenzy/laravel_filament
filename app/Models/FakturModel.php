<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FakturModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_faktur',
        'tanggal_faktur',
        'total',
        'customer_id',
        'kode_customer',
        'charge',
        'id',
        'nominal_charge',
        'ket_faktur',
        'status',
        'total_final' // Tambahkan ini!
    ];

    protected $guarded = [];

    protected $table = 'faktur';

    // Tambahkan default values untuk mencegah NULL
    protected $attributes = [
        'nominal_charge' => 0,
        'charge' => 0,
        'total_final' => 0
    ];

    // Auto calculate total_final saat saving
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // Auto calculate total_final before saving
            if ($model->total && $model->nominal_charge !== null) {
                $charge = $model->total * ($model->nominal_charge / 100);
                $model->charge = (int) $charge;
                $model->total_final = (int) ($model->total + $charge);
            } else {
                $model->charge = $model->charge ?? 0;
                $model->total_final = $model->total_final ?? $model->total ?? 0;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class);
    }
    public function detail()
    {
        return $this->hasMany(DetailFakturModel::class, 'faktur_id');
    }

    public function penjualan()
    {
        return $this->hasMany(PenjualanModel::class, 'faktur_id');
    }
}
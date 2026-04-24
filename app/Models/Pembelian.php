<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// tambahan
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory;
    protected $table = 'pembelians';

    protected $fillable = [
        'supplier_id',
        'no_faktur',
        'status',
        'tgl',
        'total_tagihan',
    ];

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    // Relasi ke Pembelian Barang
    public function pembelianBarang()
    {
        return $this->hasMany(PembelianBarang::class, 'pembelian_id', 'id');
    }

    // Relasi ke Pembayaran Pembelian
    public function pembayaranPembelian()
    {
        return $this->hasOne(PembayaranPembelian::class, 'pembelian_id', 'id');
    }

    public static function getKodeFaktur()
    {
    $last = self::latest()->first();

    if (!$last) {
        return 'FKT001';
    }

    $number = (int) substr($last->no_faktur, 3);
    return 'FKT' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }
}

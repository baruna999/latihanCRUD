<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBarang extends Model
{
    use HasFactory;
    protected $table = 'pembelian_barang';

    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'harga_beli',
        'harga_jual',
        'jml',
        'subtotal',
        'tgl',
    ];

    // Relasi ke Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id', 'id');
    }

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    // Relasi ke Pembayaran Pembelian (melalui pembelian)
    public function pembayaranPembelian()
    {
        return $this->hasOneThrough(
            PembayaranPembelian::class,
            Pembelian::class,
            'id',           // FK di pembelian
            'pembelian_id', // FK di pembayaran_pembelian
            'pembelian_id', // LK di pembelian_barang
            'id'            // LK di pembelian
        );
    }

    // Auto hitung subtotal sebelum disimpan
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->subtotal = $model->harga_beli * $model->jml;
        });
    }
}

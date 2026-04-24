<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPembelian extends Model
{
    use HasFactory;
    protected $table = 'pembayaran_pembelian';

    protected $fillable = [
        'pembelian_id',
        'tgl_bayar',
        'jenis_pembayaran',
        'gross_amount',
        'order_id',
        'payment_type',
        'status_code',
        'transaction_id',
        'transaction_time',
        'settlement_time',
        'status_message',
        'merchant_id',
    ];

    protected $casts = [
        'tgl_bayar'        => 'date',
        'transaction_time' => 'datetime',
        'settlement_time'  => 'datetime',
        'gross_amount'     => 'decimal:2',
    ];

    // Relasi ke Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id', 'id');
    }

    // Relasi ke Pembelian Barang (melalui pembelian)
    public function pembelianBarang()
    {
        return $this->hasManyThrough(
            PembelianBarang::class,
            Pembelian::class,
            'id',           // FK di pembelian
            'pembelian_id', // FK di pembelian_barang
            'pembelian_id', // LK di pembayaran_pembelian
            'id'            // LK di pembelian
        );
    }
}

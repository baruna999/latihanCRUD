<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// tambahan
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'supplier';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'email',
        'no_telp',
        'alamat',
    ];

    // Relasi ke tabel pembelian (jika ada)
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'supplier_id', 'id');
    }

    public static function getKodeSupplier()
    {
    $last = self::latest()->first();

    if (!$last) {
        return 'SUP001';
    }

    $number = (int) substr($last->kode_supplier, 3);
    return 'SUP' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }
}

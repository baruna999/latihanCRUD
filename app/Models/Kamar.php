<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// tambahan
use Illuminate\Support\Facades\DB;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar'; // Nama tabel eksplisit

    protected $guarded = [];

     public static function getNomorKamar()
    {
        // query kode perusahaan
        $sql = "SELECT IFNULL(MAX(no_kamar), 'KMR-000') as no_kamar 
                FROM kamar ";
        $nokamar = DB::select($sql);

        // cacah hasilnya
        foreach ($nokamar as $nokmr) {
            $no = $nokmr->no_kamar;
        }
        // Mengambil substring tiga digit akhir dari string PR-000
        $noawal = substr($no,-3);
        $noakhir = $noawal+1; //menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'KMR'.str_pad($noakhir,3,"0",STR_PAD_LEFT); //menyambung dengan string PR-001
        return $noakhir;

    }

     public function setHargaKamarAttribute($value)
    {
        // Hapus koma (,) dari nilai sebelum menyimpannya ke database
        $this->attributes['harga_kamar'] = str_replace('.', '', $value);
    }
}

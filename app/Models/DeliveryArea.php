<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'provinsi_id',
        'provinsi_name',
        'kabupaten_id',
        'kabupaten_name',
        'kecamatan_id',
        'kecamatan_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope untuk wilayah aktif
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    // Method untuk mendapatkan provinsi yang tersedia
    public static function getAvailableProvinsi()
    {
        return self::active()
            ->select('provinsi_id', 'provinsi_name')
            ->distinct()
            ->get()
            ->pluck('provinsi_name', 'provinsi_id');
    }

    // Method untuk mendapatkan kabupaten berdasarkan provinsi
    public static function getAvailableKabupaten($provinsiId)
    {
        return self::active()
            ->where('provinsi_id', $provinsiId)
            ->select('kabupaten_id', 'kabupaten_name')
            ->distinct()
            ->get()
            ->pluck('kabupaten_name', 'kabupaten_id');
    }

    // Method untuk mendapatkan kecamatan berdasarkan kabupaten
    // public static function getAvailableKecamatan($kabupatenId)
    // {
    //     return self::active()
    //         ->where('kabupaten_id', $kabupatenId)
    //         ->whereNotNull('kecamatan_id')
    //         ->select('kecamatan_id', 'kecamatan_name')
    //         ->get()
    //         ->pluck('kecamatan_name', 'kecamatan_id');
    // }

    public static function getKecamatanFromApi($kabupatenId)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.binderbyte.com/wilayah/kecamatan', [
                'query' => [
                    'api_key' => 'a83a97cb58d93379b17e61de25fd839ce33445f6db05572672bf99344e697c97',
                    'id_kabupaten' => $kabupatenId
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $kecamatans = $data['value'] ?? $data['data'] ?? [];
            
            return collect($kecamatans)->pluck('name', 'id');
        } catch (\Exception $e) {
            return collect([]);
        }
    }

      public static function isAreaAvailable($provinsiId, $kabupatenId)
    {
        return self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId)
            ->exists();
    }

    // // Method untuk mengecek apakah wilayah tersedia
    // public static function isAreaAvailable($provinsiId, $kabupatenId, $kecamatanId = null)
    // {
    //     $query = self::active()
    //         ->where('provinsi_id', $provinsiId)
    //         ->where('kabupaten_id', $kabupatenId);

    //     if ($kecamatanId) {
    //         $query->where(function($q) use ($kecamatanId) {
    //             $q->where('kecamatan_id', $kecamatanId)
    //               ->orWhereNull('kecamatan_id');
    //         });
    //     }

    //     return $query->exists();
    // }
}
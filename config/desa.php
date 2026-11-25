<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Desa
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh cetakan laporan (misalnya Laporan Realisasi APBDes).
    | Nilai dapat ditimpa melalui environment variable.
    |
    */

    'nama' => env('DESA_NAMA', 'WAINDAWULA'),

    'kepala_desa' => env('DESA_KEPALA_NAMA'),

];

<?php 
// Mismo nombre que la tabla que voy a manejar

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auxiliar{
    public static function generarCodigo() {
        $strToShuffle = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr( str_shuffle( $strToShuffle ), 0, 5 );
    }
}

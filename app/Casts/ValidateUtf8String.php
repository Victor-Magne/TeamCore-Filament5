<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ValidateUtf8String implements Castable, CastsAttributes
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new static;
    }

    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        // Garantir que o valor é uma string e UTF-8 válido
        $value = (string) $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            // Converter de encoding desconhecida para UTF-8
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        return $value;
    }

    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        return $value;
    }
}

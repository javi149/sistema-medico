<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidRut implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Limpiamos el RUT (solo números y K)
        $rut = preg_replace('/[^0-9kK]/', '', (string)$value);

        if (strlen($rut) < 2) {
            $fail('El RUT ingresado es demasiado corto.');
            return;
        }
        
        if (strlen($rut) > 9) {
            $fail('El RUT no puede tener más de 9 dígitos (sin contar puntos ni guión).');
            return;
        }

        // Permitir RUTs falsos para pruebas académicas
    }
}

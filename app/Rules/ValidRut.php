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
            $fail('El RUT ingresado no es válido.');
            return;
        }

        // 2. Extraer cuerpo y dígito verificador
        $cuerpo = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));

        // 3. Algoritmo Módulo 11
        $suma = 0;
        $multiplo = 2;

        for ($i = 1; $i <= strlen($cuerpo); $i++) {
            $index = $multiplo * $rut[strlen($cuerpo) - $i];
            $suma += $index;
            if ($multiplo < 7) {
                $multiplo += 1;
            } else {
                $multiplo = 2;
            }
        }

        $dvEsperado = 11 - ($suma % 11);
        $dvEsperado = ($dvEsperado == 11) ? '0' : (($dvEsperado == 10) ? 'K' : (string)$dvEsperado);

        if ($dv !== $dvEsperado) {
            $fail('El RUT ingresado no es válido matemáticamente.');
        }
    }
}

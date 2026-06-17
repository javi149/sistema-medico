<?php

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    /**
     * Lista real de especialidades médicas chilenas.
     */
    protected static array $specialties = [
        ['name' => 'Medicina General', 'description' => 'Atención primaria y diagnóstico inicial de patologías comunes en adultos.'],
        ['name' => 'Pediatría', 'description' => 'Atención médica integral para lactantes, niños y adolescentes hasta los 18 años.'],
        ['name' => 'Cardiología', 'description' => 'Diagnóstico y tratamiento de enfermedades del corazón y sistema cardiovascular.'],
        ['name' => 'Dermatología', 'description' => 'Tratamiento de enfermedades de la piel, cabello y uñas.'],
        ['name' => 'Ginecología y Obstetricia', 'description' => 'Salud reproductiva femenina, embarazo, parto y posparto.'],
        ['name' => 'Traumatología y Ortopedia', 'description' => 'Tratamiento de lesiones y enfermedades del sistema músculo-esquelético.'],
        ['name' => 'Oftalmología', 'description' => 'Diagnóstico y tratamiento de enfermedades de los ojos y la visión.'],
        ['name' => 'Otorrinolaringología', 'description' => 'Tratamiento de enfermedades del oído, nariz y garganta.'],
        ['name' => 'Neurología', 'description' => 'Diagnóstico y tratamiento de enfermedades del sistema nervioso central y periférico.'],
        ['name' => 'Psiquiatría', 'description' => 'Diagnóstico y tratamiento farmacológico de trastornos mentales y del comportamiento.'],
        ['name' => 'Urología', 'description' => 'Tratamiento de enfermedades del sistema urinario y aparato reproductor masculino.'],
        ['name' => 'Gastroenterología', 'description' => 'Diagnóstico y tratamiento de enfermedades del aparato digestivo.'],
        ['name' => 'Endocrinología', 'description' => 'Tratamiento de enfermedades hormonales: diabetes, tiroides y metabolismo.'],
        ['name' => 'Neumología', 'description' => 'Diagnóstico y tratamiento de enfermedades del aparato respiratorio y pulmones.'],
        ['name' => 'Nefrología', 'description' => 'Tratamiento de enfermedades del riñón y vías urinarias altas.'],
        ['name' => 'Reumatología', 'description' => 'Tratamiento de enfermedades autoinmunes y del tejido conectivo.'],
        ['name' => 'Oncología', 'description' => 'Diagnóstico y tratamiento integral del cáncer y tumores malignos.'],
        ['name' => 'Cirugía General', 'description' => 'Intervenciones quirúrgicas del aparato digestivo, hernias y tejidos blandos.'],
        ['name' => 'Medicina Interna', 'description' => 'Diagnóstico y manejo no quirúrgico de enfermedades complejas del adulto.'],
        ['name' => 'Kinesiología', 'description' => 'Rehabilitación física y terapia de movimiento para recuperación funcional.'],
    ];

    protected static int $index = 0;

    public function definition(): array
    {
        // Usamos el índice para asegurar que cada especialidad sea única
        $specialty = self::$specialties[self::$index % count(self::$specialties)];
        self::$index++;

        return [
            'name' => $specialty['name'],
            'description' => $specialty['description'],
        ];
    }
}

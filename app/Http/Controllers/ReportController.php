<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        /*
         * [LÓGICA DE NEGOCIO PROFUNDA - INGENIERÍA DE SOFTWARE]
         * Este controlador genera los reportes gerenciales críticos para la toma de decisiones.
         * Se optó por usar DB::select() con SQL puro (Raw SQL) en lugar de Eloquent ORM porque 
         * las funciones de agregación complejas (COUNT DISTINCT, condicionales CASE WHEN, 
         * cálculos matemáticos en la proyección) son mucho más eficientes procesadas directamente 
         * por el motor de base de datos (PostgreSQL) que cargando colecciones masivas en la memoria de PHP.
         */

        // Módulo 8: Ocupación y Disponibilidad Médica
        // Lógica de Negocio: Mide la eficiencia de uso del tiempo de cada médico. 
        // Formula: (Citas agendadas / Bloques disponibles) * 100
        // Prevención de errores: Se usa CASE WHEN para evitar división por cero si un médico no tiene bloques.
        $modulo8 = DB::select("
            SELECT 
                u.name AS medico_nombre,
                u.email AS medico_email,
                COUNT(DISTINCT av.id) AS total_bloques_disponibles,
                COUNT(DISTINCT ap.id) AS total_citas_agendadas,
                CASE 
                    WHEN COUNT(DISTINCT av.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT ap.id)::numeric / COUNT(DISTINCT av.id)::numeric) * 100, 1)
                END AS porcentaje_ocupacion
            FROM users u
            INNER JOIN professional_profiles pp ON u.id = pp.user_id
            LEFT JOIN availabilities av ON pp.id = av.professional_profile_id
            LEFT JOIN appointments ap ON pp.id = ap.professional_profile_id
            GROUP BY u.id, u.name, u.email
            ORDER BY porcentaje_ocupacion DESC
        ");

        // Módulo 9: Tasa de Cancelación y Deserción
        // Lógica de Negocio: Identifica médicos con altos índices de pérdida de pacientes.
        // Se cuenta el total de citas y se suman aquellas cuyo estado contiene la palabra 'cancelada'.
        $modulo9 = DB::select("
            SELECT 
                u.name AS medico_nombre,
                COUNT(ap.id) AS total_citas,
                SUM(CASE WHEN ap.status ILIKE '%cancelada%' THEN 1 ELSE 0 END) AS total_canceladas,
                SUM(CASE WHEN ap.status ILIKE '%ausente%' THEN 1 ELSE 0 END) AS total_deserciones,
                CASE 
                    WHEN COUNT(ap.id) = 0 THEN 0
                    ELSE ROUND((SUM(CASE WHEN ap.status ILIKE '%cancelada%' THEN 1 ELSE 0 END)::numeric / COUNT(ap.id)::numeric) * 100, 1)
                END AS porcentaje_cancelacion,
                CASE 
                    WHEN COUNT(ap.id) = 0 THEN 0
                    ELSE ROUND((SUM(CASE WHEN ap.status ILIKE '%ausente%' THEN 1 ELSE 0 END)::numeric / COUNT(ap.id)::numeric) * 100, 1)
                END AS porcentaje_desercion
            FROM users u
            INNER JOIN professional_profiles pp ON u.id = pp.user_id
            LEFT JOIN appointments ap ON pp.id = ap.professional_profile_id
            GROUP BY u.id, u.name
            ORDER BY porcentaje_cancelacion DESC
        ");

        // Módulo 10: Nivel de Demanda en Listas de Espera
        // Lógica de Negocio: Ayuda a la gerencia a decidir si necesitan contratar más médicos de cierta especialidad.
        // Cuantifica cuántos pacientes están en lista de espera por cada especialidad.
        $modulo10 = DB::select("
            SELECT 
                s.name AS especialidad,
                COUNT(w.id) AS total_en_espera,
                SUM(CASE WHEN w.status = 'Pendiente' THEN 1 ELSE 0 END) AS total_pendientes
            FROM specialties s
            LEFT JOIN waitlists w ON s.id = w.specialty_id
            GROUP BY s.id, s.name
            ORDER BY total_en_espera DESC
        ");

        return view('reportes.index', compact('modulo8', 'modulo9', 'modulo10'));
    }
}
@extends('layouts.app')

@section('content')
<div class="top-bar">
    <div>
        <h1 class="page-title">Reportes de Gestión Médica</h1>
        <p class="page-subtitle">Visualización de métricas, rendimiento clínico y listas de espera</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Modulo 8 Resumen -->
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Promedio de Ocupación</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">
            {{ count($modulo8) > 0 ? round(collect($modulo8)->avg('porcentaje_ocupacion'), 1) : 0 }}%
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--status-success);">Basado en {{ count($modulo8) }} médicos</p>
    </div>

    <!-- Modulo 9 Resumen -->
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Tasa de Cancelación Promedio</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--status-warning);">
            {{ count($modulo9) > 0 ? round(collect($modulo9)->avg('porcentaje_cancelacion'), 1) : 0 }}%
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--text-muted);">Promedio por profesional</p>
    </div>

    <!-- Modulo 10 Resumen -->
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Pacientes en Espera</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--status-danger);">
            {{ collect($modulo10)->sum('total_en_espera') }}
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--text-muted);">Total de registros en lista</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Grafico Ocupacion -->
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-main);">Ocupación por Médico</h3>
        <div id="chart-ocupacion" style="min-height: 300px;"></div>
    </div>

    <!-- Grafico Lista de espera -->
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-main);">Demanda por Especialidad</h3>
        <div id="chart-espera" style="min-height: 300px;"></div>
    </div>
</div>

<!-- Tabla Módulo 9: Cancelaciones -->
<div class="card">
    <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--text-main);">Tasa de Cancelación y Deserción por Médico</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Médico</th>
                    <th>Total Citas</th>
                    <th>Canceladas</th>
                    <th>Tasa de Cancelación</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modulo9 as $m)
                <tr>
                    <td style="font-weight: 600;">{{ $m->medico_nombre }}</td>
                    <td>{{ $m->total_citas }}</td>
                    <td>{{ $m->total_canceladas }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $m->porcentaje_cancelacion }}%; background-color: {{ $m->porcentaje_cancelacion > 20 ? 'var(--status-danger)' : 'var(--status-warning)' }};"></div>
                            </div>
                            <span style="font-weight: 600; width: 45px; text-align: right;">{{ $m->porcentaje_cancelacion }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($m->porcentaje_cancelacion > 20)
                            <span class="badge badge-danger">Alerta</span>
                        @elseif($m->porcentaje_cancelacion > 10)
                            <span class="badge badge-warning">Atención</span>
                        @else
                            <span class="badge badge-success">Saludable</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datos Módulo 8
        const nombresOcupacion = {!! json_encode(collect($modulo8)->pluck('medico_nombre')) !!};
        const datosOcupacion = {!! json_encode(collect($modulo8)->pluck('porcentaje_ocupacion')) !!};

        const optionsOcupacion = {
            series: [{
                name: 'Ocupación (%)',
                data: datosOcupacion
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    colors: {
                        ranges: [{
                            from: 0,
                            to: 100,
                            color: '#0f766e'
                        }]
                    }
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: nombresOcupacion,
                labels: { style: { colors: '#64748b' } }
            },
            yaxis: {
                labels: { style: { colors: '#0f172a', fontWeight: 500 } }
            }
        };
        new ApexCharts(document.querySelector("#chart-ocupacion"), optionsOcupacion).render();

        // Datos Módulo 10
        const nombresEspera = {!! json_encode(collect($modulo10)->pluck('especialidad')) !!};
        const datosEspera = {!! json_encode(collect($modulo10)->pluck('total_en_espera')) !!};

        const optionsEspera = {
            series: datosEspera.map(Number), // Asegurar que sean números
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit'
            },
            labels: nombresEspera,
            colors: ['#14b8a6', '#0f766e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: { show: true, fontWeight: 700, fontSize: '24px' },
                            total: { show: true, showAlways: true, label: 'Total' }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', labels: { colors: '#64748b' } },
            stroke: { show: false }
        };
        
        if(datosEspera.length > 0) {
            new ApexCharts(document.querySelector("#chart-espera"), optionsEspera).render();
        } else {
            document.querySelector("#chart-espera").innerHTML = '<div style="display:flex; height:100%; align-items:center; justify-content:center; color:#94a3b8;">No hay datos de listas de espera</div>';
        }
    });
</script>
@endsection
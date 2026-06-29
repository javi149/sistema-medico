@extends('layouts.app')

@section('content')
@php
    $promedioOcupacion = count($modulo8) > 0 ? round(collect($modulo8)->avg('porcentaje_ocupacion'), 1) : 0;
    $promedioCancelacion = count($modulo9) > 0 ? round(collect($modulo9)->avg('porcentaje_cancelacion'), 1) : 0;
    $promedioDesercion = count($modulo9) > 0 ? round(collect($modulo9)->avg('porcentaje_desercion'), 1) : 0;
    $totalEnEspera = collect($modulo10)->sum('total_en_espera');
    $totalPendientes = collect($modulo10)->sum('total_pendientes');
@endphp

<div class="top-bar">
    <div>
        <h1 class="page-title">Reportes de Gestión</h1>
        <p class="page-subtitle">Métricas de ocupación, cancelación y demanda clínica.</p>
    </div>
    <div style="text-align: right; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">
        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
    </div>
</div>

{{-- KPIs resumen --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="card" style="border-top: 4px solid var(--primary-light);">
        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Ocupación</span>
        <h2 style="margin: 10px 0 0; font-size: 2.2rem; font-weight: 800; color: var(--primary);">{{ $promedioOcupacion }}%</h2>
        <p style="margin: 8px 0 0; font-size: 0.85rem; color: var(--text-muted);">Promedio entre {{ count($modulo8) }} médicos</p>
    </div>
    <div class="card" style="border-top: 4px solid var(--status-warning);">
        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Cancelación</span>
        <h2 style="margin: 10px 0 0; font-size: 2.2rem; font-weight: 800; color: var(--status-warning);">{{ $promedioCancelacion }}%</h2>
        <p style="margin: 8px 0 0; font-size: 0.85rem; color: var(--text-muted);">Tasa media por profesional</p>
    </div>
    <div class="card" style="border-top: 4px solid var(--status-danger);">
        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Deserción</span>
        <h2 style="margin: 10px 0 0; font-size: 2.2rem; font-weight: 800; color: var(--status-danger);">{{ $promedioDesercion }}%</h2>
        <p style="margin: 8px 0 0; font-size: 0.85rem; color: var(--text-muted);">Pacientes ausentes (no-show)</p>
    </div>
    <div class="card" style="border-top: 4px solid var(--status-info);">
        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Demanda</span>
        <h2 style="margin: 10px 0 0; font-size: 2.2rem; font-weight: 800; color: var(--status-info);">{{ $totalPendientes }}</h2>
        <p style="margin: 8px 0 0; font-size: 0.85rem; color: var(--text-muted);">{{ $totalEnEspera }} registros totales en listas</p>
    </div>
</div>

{{-- Módulo 8 --}}
<section style="margin-bottom: 40px;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <span class="badge badge-info">OCUPACIÓN GENERAL</span>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Ocupación y Disponibilidad Médica</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <div class="card">
            <h4 style="margin: 0 0 20px; color: var(--text-main); font-size: 1rem;">Ocupación por Médico</h4>
            <div id="chart-ocupacion" style="min-height: 320px;"></div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 20px 0;">
                <h4 style="margin: 0; color: var(--text-main); font-size: 1rem;">Detalle de Disponibilidad</h4>
            </div>
            <div class="table-container" style="margin-top: 15px; border: none; box-shadow: none; border-radius: 0;">
                @if(count($modulo8) === 0)
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">Sin datos de ocupación.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Médico</th>
                                <th>Bloques</th>
                                <th>Citas</th>
                                <th>Ocupación</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-ocupacion">
                            @foreach($modulo8 as $m)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $m->medico_nombre }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $m->medico_email }}</div>
                                </td>
                                <td>{{ $m->total_bloques_disponibles }}</td>
                                <td>{{ $m->total_citas_agendadas }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                            <div style="height: 100%; width: {{ min($m->porcentaje_ocupacion, 100) }}%; background: var(--primary-light);"></div>
                                        </div>
                                        <span style="font-weight: 700; min-width: 45px; text-align: right;">{{ $m->porcentaje_ocupacion }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Módulo 9 --}}
<section style="margin-bottom: 40px;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <span class="badge badge-warning">TASA DE CANCELACIÓN</span>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Tasa de Cancelación y Deserción</h3>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Grafico Ocupacion -->
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-main);">Ocupación por Médico</h3>
        <div style="max-height: 350px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
            <div id="chart-ocupacion" style="min-height: 300px;"></div>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container" style="margin: 0; border: none; box-shadow: none;">
            @if(count($modulo9) === 0)
                <div style="padding: 40px; text-align: center; color: var(--text-muted);">Sin datos de cancelación.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Total Citas</th>
                            <th>Canceladas</th>
                            <th>% Cancelación</th>
                            <th>Deserciones</th>
                            <th>% Deserción</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cancelacion">
                        @foreach($modulo9 as $m)
                        <tr>
                            <td style="font-weight: 600;">{{ $m->medico_nombre }}</td>
                            <td>{{ $m->total_citas }}</td>
                            <td>{{ $m->total_canceladas }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; min-width: 60px;">
                                        <div style="height: 100%; width: {{ min($m->porcentaje_cancelacion, 100) }}%; background: var(--status-warning);"></div>
                                    </div>
                                    <span style="font-weight: 600;">{{ $m->porcentaje_cancelacion }}%</span>
                                </div>
                            </td>
                            <td>{{ $m->total_deserciones }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; min-width: 60px;">
                                        <div style="height: 100%; width: {{ min($m->porcentaje_desercion, 100) }}%; background: var(--status-danger);"></div>
                                    </div>
                                    <span style="font-weight: 600;">{{ $m->porcentaje_desercion }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($m->porcentaje_cancelacion > 20 || $m->porcentaje_desercion > 15)
                                    <span class="badge badge-danger">Alerta</span>
                                @elseif($m->porcentaje_cancelacion > 10 || $m->porcentaje_desercion > 8)
                                    <span class="badge badge-warning">Atención</span>
                                @else
                                    <span class="badge badge-success">Saludable</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</section>

{{-- Módulo 10 --}}
<section>
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <span class="badge badge-success">LISTAS DE ESPERA</span>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Nivel de Demanda en Listas de Espera</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <div class="card">
            <h4 style="margin: 0 0 20px; color: var(--text-main); font-size: 1rem;">Distribución por Especialidad</h4>
            <div id="chart-espera" style="min-height: 320px;"></div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 20px 0;">
                <h4 style="margin: 0; color: var(--text-main); font-size: 1rem;">Detalle de Demanda</h4>
            </div>
            <div class="table-container" style="margin-top: 15px; border: none; box-shadow: none; border-radius: 0;">
                @if(collect($modulo10)->where('total_en_espera', '>', 0)->isEmpty())
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">No hay pacientes en listas de espera.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Especialidad</th>
                                <th>Total en Lista</th>
                                <th>Pendientes</th>
                                <th>Nivel</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-espera">
                            @foreach($modulo10 as $m)
                                @if($m->total_en_espera > 0)
                                <tr>
                                    <td style="font-weight: 600;">{{ $m->especialidad }}</td>
                                    <td>{{ $m->total_en_espera }}</td>
                                    <td>{{ $m->total_pendientes }}</td>
                                    <td>
                                        @if($m->total_pendientes >= 5)
                                            <span class="badge badge-danger">Alta</span>
                                        @elseif($m->total_pendientes >= 2)
                                            <span class="badge badge-warning">Media</span>
                                        @else
                                            <span class="badge badge-success">Baja</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartTheme = {
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        foreColor: '#64748b',
    };

    const nombresOcupacion = {!! json_encode(collect($modulo8)->pluck('medico_nombre')) !!};
    const datosOcupacion = {!! json_encode(collect($modulo8)->pluck('porcentaje_ocupacion')) !!};

    if (nombresOcupacion.length > 0) {
        new ApexCharts(document.querySelector('#chart-ocupacion'), {
            series: [{ name: 'Ocupación (%)', data: datosOcupacion.map(Number) }],
            chart: { type: 'bar', height: 320, toolbar: { show: false }, ...chartTheme },
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
                height: Math.max(300, nombresOcupacion.length * 25), // Altura dinámica: 25px por cada médico, mínimo 300px
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: { borderRadius: 6, horizontal: true, barHeight: '60%' }
            },
            colors: ['#14b8a6'],
            dataLabels: {
                enabled: true,
                formatter: val => val + '%',
                style: { fontSize: '11px', fontWeight: 600 }
            },
            xaxis: { max: 100, labels: { formatter: val => val + '%' } },
            yaxis: { labels: { style: { colors: '#0f172a', fontWeight: 600 } } },
            grid: { borderColor: '#e2e8f0' },
            tooltip: { y: { formatter: val => val + '%' } }
        }).render();
    } else {
        document.querySelector('#chart-ocupacion').innerHTML =
            '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#94a3b8;">Sin datos de ocupación</div>';
    }

    const nombresEspera = {!! json_encode(collect($modulo10)->where('total_en_espera', '>', 0)->pluck('especialidad')) !!};
    const datosEspera = {!! json_encode(collect($modulo10)->where('total_en_espera', '>', 0)->pluck('total_en_espera')) !!};

    if (datosEspera.length > 0) {
        new ApexCharts(document.querySelector('#chart-espera'), {
            series: datosEspera.map(Number),
            chart: { type: 'donut', height: 320, ...chartTheme },
            labels: nombresEspera,
            colors: ['#14b8a6', '#0f766e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: { show: true, label: 'Total', fontWeight: 700 }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            stroke: { show: false }
        }).render();
    } else {
        document.querySelector('#chart-espera').innerHTML =
            '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#94a3b8;">No hay datos de listas de espera</div>';
    }

    // ── Expandir / Colapsar tablas largas ────────────────────────────────────
    function setupExpandableTable(tbodyId, visibleRows = 4) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        if (rows.length <= visibleRows) return; // no hace falta el botón

        // Ocultar filas extra al inicio
        rows.slice(visibleRows).forEach(r => {
            r.style.display = 'none';
            r.dataset.hidden = '1';
        });

        const totalExtra = rows.length - visibleRows;

        // Crear fila con botón
        const btnRow = document.createElement('tr');
        btnRow.id = 'btnrow-' + tbodyId;
        btnRow.innerHTML = `
            <td colspan="99" style="
                text-align: center;
                padding: 12px 16px;
                border-top: 1px solid #e2e8f0;
                background: transparent;
            ">
                <button
                    id="btn-${tbodyId}"
                    onclick="toggleTable('${tbodyId}', ${visibleRows})"
                    style="
                        background: none;
                        border: 1.5px solid var(--primary-light, #14b8a6);
                        color: var(--primary-light, #14b8a6);
                        border-radius: 20px;
                        padding: 6px 22px;
                        cursor: pointer;
                        font-size: 0.82rem;
                        font-weight: 600;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        transition: background .2s, color .2s;
                    "
                    onmouseover="this.style.background='var(--primary-light,#14b8a6)';this.style.color='#fff';"
                    onmouseout="this.style.background='none';this.style.color='var(--primary-light,#14b8a6)';"
                >
                    <span id="arrow-${tbodyId}" style="font-size: 0.75rem; transition: transform .3s; display:inline-block;">▼</span>
                    <span id="label-${tbodyId}">Ver ${totalExtra} más</span>
                </button>
            </td>`;
        tbody.appendChild(btnRow);
    }

    window.toggleTable = function(tbodyId, visibleRows) {
        const tbody = document.getElementById(tbodyId);
        const hiddenRows = Array.from(tbody.querySelectorAll('tr[data-hidden]'));
        const isExpanded = hiddenRows[0]?.style.display !== 'none';

        hiddenRows.forEach(r => r.style.display = isExpanded ? 'none' : '');

        const arrow = document.getElementById('arrow-' + tbodyId);
        const label = document.getElementById('label-' + tbodyId);
        arrow.style.transform = isExpanded ? '' : 'rotate(180deg)';
        label.textContent = isExpanded ? `Ver ${hiddenRows.length} más` : 'Ocultar';
    };

    // Aplicar a las tres tablas (4 filas visibles por defecto)
    setupExpandableTable('tbody-ocupacion', 4);
    setupExpandableTable('tbody-cancelacion', 4);
    setupExpandableTable('tbody-espera', 4);
});
</script>
@endsection
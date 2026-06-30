@extends('layouts.app')

@section('content')
<div class="top-bar">
    <div>
        <h1 class="page-title">Reportes de Gestión Médica</h1>
        <p class="page-subtitle">Visualización de métricas, rendimiento clínico y listas de espera</p>
    </div>
</div>

{{-- ── TARJETAS RESUMEN ── --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Promedio de Ocupación</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">
            {{ count($modulo8) > 0 ? round(collect($modulo8)->avg('porcentaje_ocupacion'), 1) : 0 }}%
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--status-success);">Basado en {{ count($modulo8) }} médicos</p>
    </div>
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Tasa de Cancelación Promedio</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--status-warning);">
            {{ count($modulo9) > 0 ? round(collect($modulo9)->avg('porcentaje_cancelacion'), 1) : 0 }}%
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--text-muted);">Promedio por profesional</p>
    </div>
    <div class="card">
        <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Pacientes en Espera</h3>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--status-danger);">
            {{ collect($modulo10)->sum('total_en_espera') }}
        </div>
        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--text-muted);">Total de registros en lista</p>
    </div>
</div>

{{-- ── GRÁFICOS ── --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-main);">Ocupación por Médico</h3>
        <div style="max-height: 350px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
            <div id="chart-ocupacion" style="min-height: 300px;"></div>
        </div>
    </div>
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-main);">Demanda por Especialidad</h3>
        <div id="chart-espera" style="min-height: 300px;"></div>
    </div>
</div>

{{-- ── TABLA MÓDULO 9: CANCELACIONES ── --}}
<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <div>
            <h3 style="margin: 0; color: var(--text-main);">Tasa de Cancelación y Deserción por Médico</h3>
            <p style="margin: 4px 0 0; font-size: 0.82rem; color: var(--text-muted);">{{ count($modulo9) }} profesionales</p>
        </div>
        <button class="toggle-btn" onclick="toggleTable('tabla-cancelaciones', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            Ver lista completa
        </button>
    </div>
    <div id="tabla-cancelaciones" class="collapsible-table">
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
                                    <div style="height: 100%; width: {{ min($m->porcentaje_cancelacion, 100) }}%; background-color: {{ $m->porcentaje_cancelacion > 20 ? 'var(--status-danger)' : 'var(--status-warning)' }};"></div>
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
</div>

{{-- ── TABLA MÓDULO 8: OCUPACIÓN ── --}}
<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <div>
            <h3 style="margin: 0; color: var(--text-main);">Ocupación de Agenda por Médico</h3>
            <p style="margin: 4px 0 0; font-size: 0.82rem; color: var(--text-muted);">{{ count($modulo8) }} profesionales</p>
        </div>
        <button class="toggle-btn" onclick="toggleTable('tabla-ocupacion', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            Ver lista completa
        </button>
    </div>
    <div id="tabla-ocupacion" class="collapsible-table">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Slots Disponibles</th>
                        <th>Slots Ocupados</th>
                        <th>Ocupación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulo8 as $m)
                    <tr>
                        <td style="font-weight: 600;">{{ $m->medico_nombre }}</td>
                        <td>{{ $m->especialidad ?? '—' }}</td>
                        <td>{{ $m->total_slots ?? '—' }}</td>
                        <td>{{ $m->slots_ocupados ?? '—' }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ min($m->porcentaje_ocupacion, 100) }}%; background-color: var(--primary);"></div>
                                </div>
                                <span style="font-weight: 600; width: 45px; text-align: right;">{{ $m->porcentaje_ocupacion }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TABLA MÓDULO 10: LISTA DE ESPERA ── --}}
@if(count($modulo10) > 0)
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <div>
            <h3 style="margin: 0; color: var(--text-main);">Lista de Espera por Especialidad</h3>
            <p style="margin: 4px 0 0; font-size: 0.82rem; color: var(--text-muted);">{{ count($modulo10) }} especialidades con demanda</p>
        </div>
        <button class="toggle-btn" onclick="toggleTable('tabla-espera', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            Ver lista completa
        </button>
    </div>
    <div id="tabla-espera" class="collapsible-table">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Especialidad</th>
                        <th>Pacientes en Espera</th>
                        <th>Demanda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulo10 as $m)
                    <tr>
                        <td style="font-weight: 600;">{{ $m->especialidad }}</td>
                        <td>{{ $m->total_en_espera }}</td>
                        <td>
                            @php $max = collect($modulo10)->max('total_en_espera'); @endphp
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $max > 0 ? round(($m->total_en_espera / $max) * 100) : 0 }}%; background-color: var(--status-danger);"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<style>
    /* ── Botón toggle ── */
    .toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #f1f5f9;
        border: 1.5px solid #e2e8f0;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .toggle-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .toggle-btn svg {
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }
    .toggle-btn.open svg {
        transform: rotate(180deg);
    }
    .toggle-btn.open {
        background: #f0fdfa;
        border-color: #0f766e;
        color: #0f766e;
    }

    /* ── Tabla colapsable ── */
    .collapsible-table {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.3s ease;
        opacity: 0;
    }
    .collapsible-table.open {
        max-height: 2000px;
        opacity: 1;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function toggleTable(id, btn) {
        const el = document.getElementById(id);
        const isOpen = el.classList.toggle('open');
        btn.classList.toggle('open', isOpen);

        const textNode = btn.childNodes[btn.childNodes.length - 1];
        textNode.textContent = isOpen ? ' Ocultar lista' : ' Ver lista completa';
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Gráfico Módulo 8 - Ocupación
        const nombresOcupacion = {!! json_encode(collect($modulo8)->pluck('medico_nombre')) !!};
        const datosOcupacion   = {!! json_encode(collect($modulo8)->pluck('porcentaje_ocupacion')) !!};

        new ApexCharts(document.querySelector("#chart-ocupacion"), {
            series: [{ name: 'Ocupación (%)', data: datosOcupacion }],
            chart: {
                type: 'bar',
                height: Math.max(300, nombresOcupacion.length * 25),
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    colors: { ranges: [{ from: 0, to: 100, color: '#0f766e' }] }
                }
            },
            dataLabels: { enabled: false },
            xaxis: { categories: nombresOcupacion, labels: { style: { colors: '#64748b' } } },
            yaxis: { labels: { style: { colors: '#0f172a', fontWeight: 500 } } }
        }).render();

        // Gráfico Módulo 10 - Lista de Espera
        const nombresEspera = {!! json_encode(collect($modulo10)->pluck('especialidad')) !!};
        const datosEspera   = {!! json_encode(collect($modulo10)->pluck('total_en_espera')) !!};

        if (datosEspera.length > 0) {
            new ApexCharts(document.querySelector("#chart-espera"), {
                series: datosEspera.map(Number),
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                labels: nombresEspera,
                colors: ['#14b8a6', '#0f766e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                value: { show: true, fontWeight: 700, fontSize: '24px' },
                                total: { show: true, showAlways: true, label: 'Total' }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', labels: { colors: '#64748b' } },
                stroke: { show: false }
            }).render();
        } else {
            document.querySelector("#chart-espera").innerHTML =
                '<div style="display:flex;height:300px;align-items:center;justify-content:center;color:#94a3b8;">No hay datos de listas de espera</div>';
        }
    });
</script>
@endsection
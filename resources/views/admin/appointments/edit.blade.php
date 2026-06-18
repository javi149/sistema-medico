@extends('layouts.app')

@section('content')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .admin-edit-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .form-header h2 { margin: 0; font-size: 1.35rem; font-weight: 800; }
        .form-header p { margin: 5px 0 0 0; opacity: 0.85; font-size: 0.85rem; }

        .form-body {
            padding: 30px;
        }

        .current-details {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .date-carousel {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin: 15px 0;
        }

        .date-card {
            min-width: 90px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .date-card.active {
            border-color: var(--primary);
            border-bottom: 4px solid var(--primary);
            background: #f0fdfa;
        }

        .date-month { font-size: 0.75rem; color: var(--text-muted); }
        .date-day { font-size: 1.35rem; font-weight: 700; color: var(--primary); }
        .date-name { font-size: 0.75rem; color: var(--text-muted); }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .slot-btn {
            padding: 10px 0;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .slot-btn:hover { background: #ccfbf1; }
        .slot-btn.selected { background: var(--primary); color: white; }
        .slot-btn:disabled { border-color: #cbd5e1; color: #cbd5e1; cursor: not-allowed; background: transparent; }

        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid var(--primary);
            width: 28px;
            height: 28px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

    <div class="top-bar">
        <div>
            <h1 class="page-title">Reprogramar Consulta</h1>
            <p class="page-subtitle">Modificación administrativa del horario de consulta.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
            &lt; Volver al Panel
        </a>
    </div>

    <div class="admin-edit-container" x-data="adminRescheduleWizard()" x-init="init()">
        <div class="form-header">
            <h2>Cambio de Bloque Horario</h2>
            <p>Seleccione el nuevo bloque para la consulta del especialista asignado.</p>
        </div>

        <div class="form-body">
            
            <div class="current-details">
                <h3 style="margin: 0 0 10px 0; font-size: 1rem; font-weight: 700; color: var(--text-main);">Detalles de la Cita Seleccionada</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 15px; font-size: 0.9rem;">
                    <div>
                        <strong style="color: var(--text-muted);">Paciente:</strong><br>
                        {{ $appointment->patient->name }}<br>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">RUT: {{ $appointment->patient->rut }}</span>
                    </div>
                    <div>
                        <strong style="color: var(--text-muted);">Especialista:</strong><br>
                        Dr(a). {{ $appointment->professionalProfile->user->name }}
                    </div>
                    <div>
                        <strong style="color: var(--text-muted);">Especialidad:</strong><br>
                        {{ $appointment->specialty->name }}
                    </div>
                    <div>
                        <strong style="color: var(--text-muted);">Horario Actual:</strong><br>
                        {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('d/m/Y - H:i A') }}
                    </div>
                </div>
            </div>

            <form id="adminRescheduleForm" action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="start_datetime" x-model="selectedDatetime">
            </form>

            <h4 style="margin: 0 0 15px 0; font-weight: 700; font-size: 1rem; color: var(--text-main);">1. Nueva Fecha de Atención:</h4>
            
            <!-- Date Carousel -->
            <div class="date-carousel">
                <template x-for="(dateObj, index) in dates" :key="index">
                    <div class="date-card" :class="{'active': selectedDate === dateObj.fullDate}" @click="selectDate(dateObj.fullDate)">
                        <div class="date-month" x-text="dateObj.monthStr"></div>
                        <div class="date-day" x-text="dateObj.day"></div>
                        <div class="date-name" x-text="dateObj.dayName"></div>
                    </div>
                </template>
            </div>

            <h4 style="margin: 25px 0 10px 0; font-weight: 700; font-size: 1rem; color: var(--text-main);">2. Horarios Disponibles para el Médico:</h4>

            <div x-show="loadingAvailability" style="text-align: center; padding: 30px;">
                <div class="loader"></div>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 10px;">Buscando horas disponibles...</p>
            </div>

            <div x-show="!loadingAvailability">
                <div x-show="slots.length === 0" style="text-align: center; padding: 30px; color: var(--text-muted); background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color);">
                    No hay horas de atención disponibles para este especialista en la fecha seleccionada.
                </div>

                <div x-show="slots.length > 0" class="slots-grid">
                    <template x-for="(slot, index) in slots" :key="index">
                        <button 
                            type="button"
                            class="slot-btn" 
                            :class="{'selected': selectedDatetime === slot.datetime}"
                            :disabled="!slot.available"
                            @click="selectedDatetime = slot.datetime"
                            x-text="slot.available ? slot.time : 'Reservado'"
                        ></button>
                    </template>
                </div>
            </div>

            <div x-show="selectedDatetime" style="margin-top: 35px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <button 
                    type="button" 
                    class="btn btn-primary" 
                    style="padding: 12px 35px; font-size: 1rem;" 
                    onclick="if(confirm('¿Confirmar la reprogramación administrativa de la consulta?')) document.getElementById('adminRescheduleForm').submit()"
                >
                    Confirmar Cambio de Horario
                </button>
            </div>

        </div>
    </div>

    <script>
        function adminRescheduleWizard() {
            return {
                specialtyId: {{ $appointment->specialty_id }},
                doctorId: {{ $appointment->professional_profile_id }},
                dates: [],
                selectedDate: null,
                loadingAvailability: false,
                slots: [],
                selectedDatetime: null,

                init() {
                    this.initDates();
                    if (this.dates.length > 0) {
                        this.selectDate(this.dates[0].fullDate);
                    }
                },

                initDates() {
                    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    
                    let today = new Date();
                    for (let i = 0; i < 14; i++) {
                        let d = new Date(today);
                        d.setDate(today.getDate() + i);
                        
                        let fullDate = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                        
                        this.dates.push({
                            fullDate: fullDate,
                            monthStr: months[d.getMonth()] + '.',
                            day: d.getDate(),
                            dayName: days[d.getDay()]
                        });
                    }
                },

                selectDate(fullDate) {
                    this.selectedDate = fullDate;
                    this.selectedDatetime = null;
                    this.fetchAvailability();
                },

                fetchAvailability() {
                    this.loadingAvailability = true;
                    this.slots = [];
                    
                    fetch(`{{ route("wizard.availability") }}?specialty_id=${this.specialtyId}&date=${this.selectedDate}`)
                    .then(res => res.json())
                    .then(data => {
                        const doctorData = data.find(d => d.doctor_id === this.doctorId);
                        if (doctorData && doctorData.slots) {
                            this.slots = doctorData.slots;
                        }
                        this.loadingAvailability = false;
                    })
                    .catch(err => {
                        this.loadingAvailability = false;
                        console.error('Error fetching availability:', err);
                    });
                }
            }
        }
    </script>

@endsection

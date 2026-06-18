@extends('layouts.app')

@section('content')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .admin-form-container {
            max-width: 800px;
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

        .doctor-row {
            display: flex;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            background: #f8fafc;
        }

        .doctor-info {
            width: 220px;
            padding: 20px;
            background: #f1f5f9;
            border-right: 1px solid var(--border-color);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .doctor-slots {
            flex-grow: 1;
            padding: 20px;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 8px;
        }

        .slot-btn {
            padding: 8px 0;
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
            <h1 class="page-title">Agendar Nueva Cita</h1>
            <p class="page-subtitle">Formulario administrativo de reserva horaria.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
            &lt; Volver al Panel
        </a>
    </div>

    <div class="admin-form-container" x-data="adminBookingWizard()" x-init="initDates()">
        <div class="form-header">
            <h2>Ingreso de Cita Médica</h2>
            <p>Complete los datos del paciente y seleccione al profesional y horario correspondiente.</p>
        </div>

        <div class="form-body">
            
            <form id="adminBookingForm" action="{{ route('admin.appointments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="patient_id" x-model="patientId">
                <input type="hidden" name="professional_profile_id" x-model="selectedDoctorId">
                <input type="hidden" name="start_datetime" x-model="selectedDatetime">

                <!-- 1. PACIENTE -->
                <div class="form-group">
                    <label class="form-label" for="patient-select">1. Seleccione al Paciente:</label>
                    <select id="patient-select" class="form-control" x-model="patientId" style="font-weight: 600;">
                        <option value="">-- Buscar / Seleccionar Paciente --</option>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente->id }}">{{ $paciente->name }} (RUT: {{ $paciente->rut }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. ESPECIALIDAD -->
                <div class="form-group" style="margin-top: 25px;">
                    <label class="form-label" for="specialty-select">2. Seleccione la Especialidad:</label>
                    <select id="specialty-select" class="form-control" x-model="specialtyId" @change="fetchAvailability()" :disabled="!patientId">
                        <option value="">-- Seleccione Especialidad --</option>
                        @foreach($specialties as $spec)
                            <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. FECHA -->
                <div x-show="specialtyId" style="margin-top: 25px; display: none;">
                    <label class="form-label">3. Seleccione la Fecha:</label>
                    
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
                </div>

                <!-- 4. MÉDICO Y BLOQUE HORARIO -->
                <div x-show="selectedDate && specialtyId" style="margin-top: 25px; display: none;">
                    <label class="form-label">4. Disponibilidad de Médicos y Horarios:</label>

                    <div x-show="loadingAvailability" style="text-align: center; padding: 20px;">
                        <div class="loader"></div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 10px;">Buscando horas disponibles...</p>
                    </div>

                    <div x-show="!loadingAvailability">
                        <div x-show="availableDoctors.length === 0" style="text-align: center; padding: 25px; color: var(--text-muted); background: #f8fafc; border-radius: 8px; border: 1px solid var(--border-color);">
                            No hay profesionales o bloques de horas disponibles para la fecha seleccionada.
                        </div>

                        <template x-for="(doc, index) in availableDoctors" :key="index">
                            <div class="doctor-row">
                                <div class="doctor-info">
                                    <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;" x-text="doc.doctor_name"></div>
                                </div>
                                <div class="doctor-slots">
                                    <div class="slots-grid">
                                        <template x-for="(slot, sIndex) in doc.slots" :key="sIndex">
                                            <button 
                                                type="button"
                                                class="slot-btn" 
                                                :class="{'selected': selectedDatetime === slot.datetime && selectedDoctorId === doc.doctor_id}"
                                                :disabled="!slot.available"
                                                @click="confirmSlot(doc.doctor_id, slot.datetime)"
                                                x-text="slot.available ? slot.time : 'Reservada'"
                                            ></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div x-show="selectedDatetime" style="margin-top: 35px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px; display: none;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 1rem;">
                        Agendar Consulta Médica
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
        function adminBookingWizard() {
            return {
                patientId: '',
                specialtyId: '',
                dates: [],
                selectedDate: null,
                loadingAvailability: false,
                availableDoctors: [],
                selectedDoctorId: null,
                selectedDatetime: null,

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
                    this.selectedDoctorId = null;
                    this.selectedDatetime = null;
                    this.fetchAvailability();
                },

                fetchAvailability() {
                    if (!this.specialtyId) return;
                    if (!this.selectedDate && this.dates.length > 0) {
                        this.selectedDate = this.dates[0].fullDate;
                    }

                    this.loadingAvailability = true;
                    this.availableDoctors = [];
                    
                    fetch(`{{ route("wizard.availability") }}?specialty_id=${this.specialtyId}&date=${this.selectedDate}`)
                    .then(res => res.json())
                    .then(data => {
                        this.availableDoctors = data;
                        this.loadingAvailability = false;
                    })
                    .catch(err => {
                        this.loadingAvailability = false;
                        console.error('Error fetching availability:', err);
                    });
                },

                confirmSlot(doctorId, datetime) {
                    this.selectedDoctorId = doctorId;
                    this.selectedDatetime = datetime;
                }
            }
        }
    </script>

@endsection

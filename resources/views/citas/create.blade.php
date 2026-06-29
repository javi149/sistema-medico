@extends('layouts.public')

@section('content')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .wizard-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .wizard-header {
            background: #0f766e;
            color: white;
            padding: 20px 30px;
        }

        .wizard-header h2 { margin: 0; font-size: 1.5rem; }
        .wizard-header p { margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9rem; }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 40px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            font-weight: 600;
        }

        .step-item.active { color: #0f766e; }
        .step-item.completed { color: #10b981; }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .step-item.active .step-circle { background: #0f766e; color: white; }
        .step-item.completed .step-circle { background: #10b981; color: white; }

        .step-line {
            flex-grow: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 15px;
        }

        .wizard-body {
            padding: 40px;
        }

        /* Step 1 */
        .rut-input-group {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            margin-top: 20px;
        }

        .rut-input-group.error { border-color: #ef4444; }

        .rut-label {
            position: absolute;
            top: -12px;
            left: 15px;
            background: white;
            padding: 0 5px;
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
        }

        .rut-input-group input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 1.2rem;
            color: #0f172a;
        }

        /* Step 2 */
        .specialty-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .specialty-btn {
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            text-align: left;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .specialty-btn:hover {
            border-color: #0f766e;
            color: #0f766e;
            background: #f0fdfa;
        }

        /* Step 3 */
        .date-carousel {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .date-card {
            min-width: 100px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .date-card.active {
            border-color: #0f766e;
            border-bottom: 4px solid #0f766e;
            background: #f0fdfa;
        }

        .date-month { font-size: 0.8rem; color: #64748b; }
        .date-day { font-size: 1.5rem; font-weight: 700; color: #0f766e; }
        .date-name { font-size: 0.8rem; color: #64748b; }

        .doctor-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .doctor-row {
            display: flex;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .doctor-info {
            width: 250px;
            padding: 20px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            text-align: center;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e2e8f0;
            margin: 0 auto 10px;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .doctor-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .doctor-slots {
            flex-grow: 1;
            padding: 20px;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }

        .slot-btn {
            padding: 8px 0;
            border: 1px solid #0f766e;
            background: white;
            color: #0f766e;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .slot-btn:hover { background: #ccfbf1; }
        .slot-btn.selected { background: #0f766e; color: white; }
        .slot-btn:disabled { border-color: #cbd5e1; color: #cbd5e1; cursor: not-allowed; background: transparent; }

        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #0f766e;
            width: 24px;
            height: 24px;
            -webkit-animation: spin 1s linear infinite; /* Safari */
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

    <div x-data="bookingWizard()" x-init="initDates()">
        
        <div class="wizard-container">
            <div class="wizard-header">
                <h2>Reserva de hora</h2>
                <p x-text="'Paso ' + step + ': ' + stepTitle"></p>
            </div>

            <!-- Stepper -->
            <div class="stepper">
                <div class="step-item" :class="{'active': step === 1, 'completed': step > 1}">
                    <div class="step-circle" x-text="step > 1 ? '✓' : '1'"></div>
                    <span>Identificar paciente</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" :class="{'active': step === 2, 'completed': step > 2}">
                    <div class="step-circle" x-text="step > 2 ? '✓' : '2'"></div>
                    <span>Especialidad</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" :class="{'active': step === 3}">
                    <div class="step-circle">3</div>
                    <span>Día y hora</span>
                </div>
            </div>

            <div class="wizard-body">
                
                @if($errors->any())
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulario Oculto que se envía al final -->
                <form id="bookingForm" action="{{ route('citas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" x-model="patientId">
                    <input type="hidden" name="professional_profile_id" x-model="selectedDoctorId">
                    <input type="hidden" name="start_datetime" x-model="selectedDatetime">
                </form>

                <!-- Formulario Oculto para Lista de Espera -->
                <form id="waitlistForm" action="{{ route('waitlist.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" x-model="patientId">
                    <input type="hidden" name="specialty_id" x-model="specialtyId">
                    <input type="hidden" name="professional_profile_id" x-model="selectedDoctorId">
                </form>

                <!-- STEP 1: RUT -->
                <div x-show="step === 1" x-transition>
                    <h3 style="color: #0f766e; text-align: center; margin-bottom: 5px;">¿PARA QUIÉN ES LA HORA?</h3>
                    <p style="text-align: center; color: #64748b; margin-bottom: 30px;">Complete los datos del <u>Paciente</u> que será atendido:</p>
                    
                    <div class="rut-input-group" :class="{'error': errorMsg}">
                        <div class="rut-label">RUT del Paciente</div>
                        <input type="text" x-model="rut" @input="formatRut" placeholder="Ej: 11.222.333-4" @keyup.enter="checkRut" maxlength="12">
                    </div>
                    
                    <p x-show="errorMsg" x-text="errorMsg" style="color: #ef4444; font-size: 0.9rem; margin-top: 10px;"></p>
                    
                    <div x-show="showRegisterBtn" style="margin-top: 15px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center;">
                        <p style="margin: 0 0 10px 0; font-size: 0.95rem; color: #475569;">Para agendar una cita necesitas estar registrado en nuestro sistema.</p>
                        <a href="{{ route('register') }}" class="btn-primary" style="display: inline-block; padding: 8px 20px; text-decoration: none;">Crear Cuenta Nueva</a>
                    </div>
                    <p x-show="patientName" style="color: #10b981; font-weight: 600; margin-top: 10px;">¡Hola, <span x-text="patientName"></span>!</p>

                    <div style="text-align: right; margin-top: 30px;">
                        <button class="btn btn-primary" @click="checkRut" :disabled="loading" style="padding: 12px 30px; font-size: 1.1rem;">
                            <span x-show="!loading">CONTINUAR</span>
                            <div x-show="loading" class="loader" style="margin: 0 auto;"></div>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Especialidad -->
                <div x-show="step === 2" x-transition style="display: none;">
                    <button @click="step = 1" style="background:none; border:none; color:#0f766e; font-weight:600; cursor:pointer; margin-bottom: 20px;">&lt; VOLVER</button>
                    
                    <h3 style="color: #0f766e; margin-bottom: 20px;">Búsqueda por especialidad</h3>
                    
                    <div class="specialty-grid">
                        @foreach($specialties as $specialty)
                            <button class="specialty-btn" @click="selectSpecialty({{ $specialty->id }}, '{{ $specialty->name }}')">
                                <span style="font-size: 1.2rem; color: #0f766e;">✓</span>
                                {{ $specialty->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- STEP 3: Fecha y Hora -->
                <div x-show="step === 3" x-transition style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <button @click="step = 2" style="background:none; border:none; color:#0f766e; font-weight:600; cursor:pointer;">&lt; VOLVER</button>
                        <div style="color: #64748b; font-weight: 600;">Especialidad: <span x-text="specialtyName" style="color: #0f766e;"></span></div>
                    </div>

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

                    <div x-show="loadingAvailability" style="text-align: center; padding: 40px;">
                        <div class="loader" style="margin: 0 auto; border-top-color: #0f766e; width: 40px; height: 40px;"></div>
                        <p style="color: #64748b; margin-top: 15px;">Buscando horas disponibles...</p>
                    </div>

                    <!-- Doctors and Slots -->
                    <div x-show="!loadingAvailability" class="doctor-list">
                        
                        <div x-show="availableDoctors.length === 0 || !hasAvailableSlots()" style="text-align: center; padding: 40px; color: #64748b; background: #f8fafc; border-radius: 8px;">
                            <p style="margin: 0 0 15px 0;">No hay horas disponibles para esta fecha. Intenta con otro día.</p>
                            <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                                <h4 style="color: #0f766e; margin: 0 0 10px 0; font-size: 1.1rem;">¿Deseas inscribirte en la lista de espera?</h4>
                                <p style="font-size: 0.9rem; color: #64748b; margin: 0 0 15px 0;">Te notificaremos automáticamente por correo electrónico en cuanto se libere un cupo.</p>
                                <button type="button" class="btn-primary" style="display: inline-block; padding: 10px 24px; border-radius: 50px; font-weight: 600; cursor: pointer; border: none;" @click="joinWaitlist()">
                                    Inscribirse en Lista de Espera
                                </button>
                            </div>
                        </div>

                        <template x-for="(doc, index) in availableDoctors" :key="index">
                            <div class="doctor-row">
                                <div class="doctor-info">
                                    <div class="doctor-avatar">
                                        <!-- Determinamos la imagen según género detectado en el nombre -->
                                        <img :src="'/images/doctors/doc_' + (doc.doctor_name.includes('Dra.') ? (index % 2 === 0 ? 1 : 3) : (index % 2 === 0 ? 2 : 4)) + '.png'" alt="Doctor">
                                    </div>
                                    <div style="font-weight: 700; color: #0f766e;" x-text="doc.doctor_name"></div>
                                    <div style="font-size: 0.8rem; color: #64748b;" x-text="specialtyName"></div>
                                </div>
                                <div class="doctor-slots">
                                    <div style="background: #10b981; color: white; display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; margin-bottom: 15px;">
                                        Próximas Horas
                                    </div>
                                    <div class="slots-grid">
                                        <template x-for="(slot, sIndex) in doc.slots" :key="sIndex">
                                            <button 
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

                    <div x-show="selectedDatetime" style="margin-top: 30px; text-align: right; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                        <button class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;" onclick="document.getElementById('bookingForm').submit()">
                            CONFIRMAR RESERVA
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        function bookingWizard() {
            return {
                step: 1,
                rut: '{{ auth()->user()->rut ?? '' }}', // Autocompletar si está logueado
                patientName: '',
                patientId: null,
                errorMsg: '',
                loading: false,
                showRegisterBtn: false,
                
                specialtyId: null,
                specialtyName: '',
                
                dates: [],
                selectedDate: null,
                loadingAvailability: false,
                availableDoctors: [],
                
                selectedDoctorId: null,
                selectedDatetime: null,

                get stepTitle() {
                    if (this.step === 1) return 'Identificar paciente';
                    if (this.step === 2) return 'Seleccionar especialidad';
                    if (this.step === 3) return 'Seleccionar día y hora';
                    return '';
                },

                formatRut() {
                    let value = this.rut.replace(/[^0-9kK]/g, '').toUpperCase();
                    if (value.length > 1) {
                        value = value.slice(0, -1) + '-' + value.slice(-1);
                    }
                    if (value.length > 5) {
                        let parts = value.split('-');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        value = parts.join('-');
                    }
                    this.rut = value.substring(0, 12);
                },

                checkRut() {
                    if (!this.rut) {
                        this.errorMsg = 'Por favor ingrese el RUT';
                        return;
                    }
                    this.loading = true;
                    this.errorMsg = '';
                    
                    fetch('{{ route("wizard.check-rut") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ rut: this.rut })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.patientName = data.name;
                            this.patientId = data.id;
                            this.showRegisterBtn = false;
                            setTimeout(() => { this.step = 2; }, 800);
                        } else {
                            this.errorMsg = data.message;
                            this.showRegisterBtn = true;
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        this.errorMsg = 'Error de conexión.';
                    });
                },

                selectSpecialty(id, name) {
                    this.specialtyId = id;
                    this.specialtyName = name;
                    this.step = 3;
                    
                    // Si no hay fecha seleccionada, seleccionar hoy
                    if (!this.selectedDate && this.dates.length > 0) {
                        this.selectDate(this.dates[0].fullDate);
                    } else {
                        this.fetchAvailability();
                    }
                },

                initDates() {
                    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    
                    let today = new Date();
                    for (let i = 0; i < 14; i++) {
                        let d = new Date(today);
                        d.setDate(today.getDate() + i);
                        
                        // YYYY-MM-DD
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

                hasAvailableSlots() {
                    return this.availableDoctors.some(doc => doc.slots && doc.slots.some(slot => slot.available));
                },

                joinWaitlist() {
                    if (!this.patientId || !this.specialtyId) {
                        alert('Por favor complete los pasos anteriores.');
                        return;
                    }
                    this.$nextTick(() => {
                        document.getElementById('waitlistForm').submit();
                    });
                },

                fetchAvailability() {
                    if (!this.specialtyId || !this.selectedDate) return;
                    
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
                        console.error(err);
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
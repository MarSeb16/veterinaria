<script setup>
import { ref, computed, watch } from 'vue'
import { Spanish } from 'flatpickr/dist/l10n/es.js'

const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)
const veterinarie_time_availability = ref([])
const segment_time_veterinaries = ref([])
const selected_segment_times = ref([])
const segment_time_hour_veterinaries = ref([]);
const loading = ref(false)
const search = ref()
const select_pet = ref(null)
const items = ref([])
const veterinarie_id = ref(null)
const reason = ref(null)
const vaccine_names = ref(null)
const nex_due_date = ref(null);
const outside = ref('0');

const datePickerConfig = {
    locale: Spanish,
    altInput: true,
    altFormat: 'd/m/Y',
    dateFormat: 'Y-m-d',
    allowInput: true,
    monthSelectorType: 'dropdown'
}

const form = ref({
    vaccination_date: null,
    time: null,
    amount: 0,
    method_payment: 'EFECTIVO',
    amount_add: 0
})

const method_payments = ref(['EFECTIVO', 'PAGO POR QR', 'YAPE'])

const canSubmit = computed(() => {
    return (
        form.value.vaccination_date &&
        reason.value &&
        vaccine_names.value &&
        select_pet.value &&
        form.value.amount &&
        form.value.amount_add !== null &&
        selected_segment_times.value.length > 0
    )
})

const filter = async () => {
    if (!form.value.vaccination_date) {
        warning.value = 'Es necesario ingresar una fecha'
        return
    }
    try {
        const data = {
            vaccination_date: form.value.vaccination_date,
            hour: form.value.time
        }
        const resp = await $api('/appointments/filter-availability', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })
        warning.value = null
        veterinarie_time_availability.value = resp.veterinarie_time_availability
    } catch (error) {
        console.log(error)
    }
}

const reset = () => {
    form.value.vaccination_date = null
    form.value.time = null
    veterinarie_time_availability.value = []
    segment_time_veterinaries.value = []
    selected_segment_times.value = []
}

const selectedSegmentHour = (veterinarie_time, segment_time_group) => {
    veterinarie_time.segment_times = segment_time_group.segment_times
    // selected_segment_times.value = []
    // segment_time_veterinaries.value = []
    veterinarie_id.value = veterinarie_time.id
    selected_segment_times.value = selected_segment_times.value.filter(item => item.veterinarie_id === veterinarie_time.id)
    segment_time_veterinaries.value = segment_time_veterinaries.value.filter(item => item.indexOf(`${veterinarie_time.id}-`) !== -1)
    segment_time_hour_veterinaries.value = segment_time_hour_veterinaries.value.filter(item => item.indexOf(`${veterinarie_time.id}-`) !== -1)

}

const addSelectedSegmentTime = (veterinarie_time, segment_time) => {
    const index = selected_segment_times.value.findIndex(item =>
        item.veterinarie_id === veterinarie_time.id && item.segment_time_id === segment_time.veterinarie_schedule_hour_id
    )
    if (index !== -1) {
        selected_segment_times.value.splice(index, 1)
    } else {
        selected_segment_times.value.push({
            veterinarie_id: veterinarie_time.id,
            segment_time_id: segment_time.veterinarie_schedule_hour_id
        })
    }
    veterinarie_id.value = veterinarie_time.id
    selected_segment_times.value = selected_segment_times.value.filter(item => item.veterinarie_id === veterinarie_time.id)
    segment_time_veterinaries.value = segment_time_veterinaries.value.filter(item => item.indexOf(`${veterinarie_time.id}-`) !== -1)
}

const addSelectedSegmentTimeHour = (veterinarie_time, segment_time_group) => {
    segment_time_group.segment_times.forEach(segment_time => {
        const index = selected_segment_times.value.findIndex(item =>
            item.veterinarie_id === veterinarie_time.id && item.segment_time_id === segment_time.veterinarie_schedule_hour_id
        )
        if (index !== -1) {
            selected_segment_times.value.splice(index, 1)
            segment_time_veterinaries.value.splice(index, 1)
        } else {
            if (!segment_time.check) {
                selected_segment_times.value.push({
                    veterinarie_id: veterinarie_time.id,
                    segment_time_id: segment_time.veterinarie_schedule_hour_id
                })
                segment_time_veterinaries.value.push(veterinarie_time.id + '-' + segment_time.veterinarie_schedule_hour_id)
            }
        }
    })

    veterinarie_id.value = veterinarie_time.id
    selected_segment_times.value = selected_segment_times.value.filter(item => item.veterinarie_id === veterinarie_time.id)
    segment_time_veterinaries.value = segment_time_veterinaries.value.filter(item => item.indexOf(`${veterinarie_time.id}-`) !== -1)
    segment_time_hour_veterinaries.value = segment_time_hour_veterinaries.value.filter(item => item.indexOf(`${veterinarie_time.id}-`) !== -1)
}
const fieldsClean = () => {
    form.value.vaccination_date = null
    form.value.time = null
    form.value.amount = 0
    form.value.method_payment = 'EFECTIVO'
    form.value.amount_add = 0
    veterinarie_time_availability.value.forEach(vet => (vet.segment_times = []))
    selected_segment_times.value = []
    segment_time_veterinaries.value = []
    select_pet.value = null
    reason.value = null
    vaccine_names.value = null
    nex_due_date.value = null
    outside.value = '0'
}

const store = async () => {
    warning.value = null

    if (parseFloat(form.value.amount_add) > parseFloat(form.value.amount)) {
        warning.value = 'El costo de adelanto no puede ser mayor al costo de la cita'
        return
    }

    let STATE_PAY = 1
    const amount = parseFloat(form.value.amount)
    const amount_add = parseFloat(form.value.amount_add)

    if (!amount_add || amount_add === 0) {
        STATE_PAY = 1 // Pendiente
    } else if (amount_add < amount) {
        STATE_PAY = 2 // Parcial
    } else if (amount_add === amount) {
        STATE_PAY = 3 // Completo
    }
    try {
        const data = {
            veterinarie_id: veterinarie_id.value,
            pet_id: select_pet.value.id,
            vaccination_date: form.value.vaccination_date,
            reason: reason.value,
            amount: form.value.amount,
            state_pay: STATE_PAY,
            method_payment: form.value.method_payment,
            adelanto: form.value.amount_add,
            selected_segment_times: selected_segment_times.value,
            vaccine_names: vaccine_names.value,
            outside: outside.value,
            nex_due_date: nex_due_date.value
        }
        const resp = await $api('/vaccinations', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })

        success.value = 'La vacuna se ha programado correctamente'

        setTimeout(() => {
            success.value = null
            warning.value = null
            error_exists.value = null
            fieldsClean()
        }, 2500)
    } catch (error) {
        console.log(error)
    }
}

const querySelections = async query => {
    loading.value = true
    setTimeout(async () => {
        const resp = await $api(`/appointments/search-pets/${query}`, {
            method: 'GET',
            onResponseError({ response }) {
                error_exists.value = response._data.error
            }
        })
        items.value = resp.pets
        loading.value = false
    }, 500)
}

watch(search, query => {
    if (query && query.length > 1) querySelections(query)
    else items.value = []
})

definePage({
    meta: {
        Permission: 'register_vaccination'
    }
})
</script>
<template>
    <Transition name="fade">
        <VCard class="pa-3 pa-sm-8 rounded-lg mb-6" elevation="4">
            <VCardText>
                <div class="text-center">
                    <h4 class="section-title">Vacunaciones</h4>
                    <p class="section-subtitle">Completa los datos correspondiente para la vacuna </p>
                </div>
            </VCardText>
            <v-divider class="mb-4" />

            <v-row dense>
                <v-col cols="12" md="4">
                    <AppDateTimePicker v-model="form.vaccination_date" label="Fecha de la vacuna" dense outlined
                        :config="{
                            ...datePickerConfig,
                            minDate: 'today',
                            disable: [date => date.getDay() === 0 || date.getDay() === 6]
                        }" />
                </v-col>
                <v-col cols="12" md="4">
                    <AppDateTimePicker v-model="form.time" label="Hora de la vacuna"
                        :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }" />
                </v-col>
                <div class="d-flex align-center gap-x-2 mt-2">
                    <VBtn color="info" icon @click="filter">
                        <i class="ri-search-2-line" />
                    </VBtn>
                    <VBtn color="secondary" icon @click="reset">
                        <i class="ri-restart-line" />
                    </VBtn>
                </div>
            </v-row>
        </VCard>
    </Transition>

    <v-col cols="12">
        <VAlert v-if="error_exists" type="error" dense class="mb-2">{{ error_exists }}</VAlert>
        <VAlert v-if="warning" type="warning" dense class="mb-2">{{ warning }}</VAlert>
        <VAlert v-if="success" type="success" dense class="mb-2">{{ success }}</VAlert>
    </v-col>

    <Transition name="fade">
        <VCard class="pa-3 pa-sm-8 rounded-lg mb-6" elevation="4">
            <v-divider class="mb-4" />
            <h6 class="section-title">Disponibilidad</h6>
            <v-row dense>
                <v-col cols="12">
                    <VTable>
                        <thead>
                            <tr>
                                <th>Veterinario</th>
                                <th>Tiempos activos</th>
                                <th>Segmentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(veterinarie_time, index) in veterinarie_time_availability" :key="index">
                                <tr>
                                    <td>{{ veterinarie_time.full_name }}</td>
                                    <td>
                                        <ul>
                                            <li v-for="(segment_time_group, i) in veterinarie_time.segment_time_groups"
                                                :key="i" style="list-style: none; display: flex; align-items: center">
                                                <VCheckbox v-model="segment_time_hour_veterinaries"
                                                    @click="addSelectedSegmentTimeHour(veterinarie_time, segment_time_group)"
                                                    :value="veterinarie_time.id + '-' + segment_time_group.hour_format" />
                                                <VBtn color="success" icon
                                                    @click="selectedSegmentHour(veterinarie_time, segment_time_group)">
                                                    <i class="ri-file-add-line" />
                                                </VBtn>
                                                {{ segment_time_group.hour_format }} ({{
                                                    segment_time_group.segment_times.length }})
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            <li v-for="(segment_time, i) in veterinarie_time.segment_times" :key="i"
                                                class="list-none">
                                                <VCheckbox v-if="!segment_time.check"
                                                    v-model="segment_time_veterinaries"
                                                    :label="segment_time.schedule_hour.hour_start_format + ' - ' + segment_time.schedule_hour.hour_end_format"
                                                    :value="veterinarie_time.id + '-' + segment_time.veterinarie_schedule_hour_id"
                                                    @click="addSelectedSegmentTime(veterinarie_time, segment_time)" />
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </VTable>
                    <v-row dense>
                        <v-col cols="12" md="5">
                            <VTextarea v-model="reason" label="Razón de la vacuna"
                                placeholder="Describe el motivo de poner la vacuna" class="mt-4" />
                        </v-col>
                        <v-col cols="12" md="5">
                            <VTextarea v-model="vaccine_names" label="Vacunas"
                                placeholder="Describe el motivo de la vacuna" class="mt-4" />
                        </v-col>
                        <v-col cols="12" md="5">
                            <AppDateTimePicker v-model="nex_due_date" label="Fecha de la proxima vacuna" dense outlined
                                :config="{
                                    ...datePickerConfig,
                                    minDate: 'today',
                                    disable: [date => date.getDay() === 0 || date.getDay() === 6]
                                }" />
                        </v-col>
                        <v-col cols="12" md="5">
                            <VRadioGroup v-model="outside" inline>
                                <VRadio label="Dentro de la veterinaria" value="0" />
                                <VRadio label="Fuera de la veterinaria" value="1" />
                            </VRadioGroup>
                        </v-col>
                    </v-row>

                </v-col>
            </v-row>
        </VCard>
    </Transition>

    <Transition name="fade">
        <VCard class="pa-3 pa-sm-8 rounded-lg mb-6" elevation="4">
            <v-divider class="mb-4" />
            <h6 class="section-title">Paciente</h6>
            <v-row dense>
                <v-col cols="12" md="4">
                    <VAutocomplete v-model="select_pet" item-title="name" item-value="id" return-object
                        v-model:search="search" :loading="loading" :items="items" placeholder="Buscar mascota"
                        label="¿Quién es la mascotita?" variant="underlined" :menu-props="{ maxHeight: '200px' }" />
                </v-col>
                <v-col cols="12" md="4" v-if="select_pet">
                    <p>Especie: {{ select_pet.specie }}</p>
                    <p>Raza: {{ select_pet.breed }}</p>
                </v-col>
                <v-col cols="12" md="4" v-if="select_pet">
                    <p>Dueño: {{ select_pet.owner.first_name }} {{ select_pet.owner.last_name }}</p>
                    <p>Teléfono: {{ select_pet.owner.phone }}</p>
                    <p>CI: {{ select_pet.owner.n_document }}</p>
                </v-col>
            </v-row>
        </VCard>
    </Transition>

    <Transition name="fade">
        <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
            <v-divider class="mb-4" />
            <h6 class="section-title">Pagos</h6>
            <v-row dense>
                <v-col cols="12" md="4">
                    <VTextField label="Costo de la vacuna" type="number" placeholder="Ej.: 100 Bs."
                        v-model="form.amount" />
                </v-col>
                <v-col cols="12" md="4">
                    <VSelect :items="method_payments" label="Método de pago" placeholder="Selecciona método"
                        v-model="form.method_payment" outlined dense />
                </v-col>
                <v-col cols="12" md="4">
                    <VTextField label="Adelanto" type="number" placeholder="Ej.: 50 Bs." v-model="form.amount_add" />
                </v-col>
            </v-row>
        </VCard>
    </Transition>

    <v-col cols="12" class="text-center mt-4">
        <VBtn color="primary" size="large" class="px-8 text-subtitle-1" elevation="2" :disabled="!canSubmit"
            @click="store">
            Registrar cita médica
        </VBtn>
    </v-col>
</template>
<style scoped>
.v-btn__prepend {
    margin-inline: 0 !important;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.section-subtitle {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.v-card {
    border-radius: 12px;
}

.v-text-field input[type='number']::-webkit-inner-spin-button,
.v-text-field input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.v-text-field input[type='number'] {
    -moz-appearance: textfield;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

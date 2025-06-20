<script setup>
// import VueCal from 'vue-cal'
// import 'vue-cal/dist/vuecal.css'
import { ref } from 'vue'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)
const veterinarie_time_availability = ref([]);
const segment_time_veterinaries = ref([]);
const selected_segment_times = ref([]);
const loading = ref(false)
const search = ref()
const select_pet = ref(null)
const items = ref([])
const datePickerConfig = {
    locale: Spanish,
    altInput: true,
    altFormat: 'd/m/Y',
    dateFormat: 'Y-m-d',
    allowInput: true,
    monthSelectorType: 'dropdown',
    monthSelectorType: 'static', // muestra el dropdown del mes
    // ⚠️ flatpickr usa dropdown por defecto para el año, no hay 'yearSelectorType'
    // para años antiguos tendrás que permitir desplazamiento rápido
}
const form = ref({
    date_appointment: null,
    time: null,
    amount: 0,
    method_payment: 'EFECTIVO',
    amount_add: 0
})
const method_payments = ref([
    "EFECTIVO",
    "PAGO POR QR",
    "YAPE",
])

const filter = async () => {
    try {
        if (!form.value.date_appointment) {
            warning.value = "Es necesario ingresar una fecha";
            return;
        }
        let data = {
            date_appointment: form.value.date_appointment,
            hour: form.value.time,
        }
        const resp = await $api('/appointments/filter-availability', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })
        console.log(resp);
        warning.value = null;
        veterinarie_time_availability.value = resp.veterinarie_time_availability;
    } catch (error) {
        console.log(error);
    }
}

const selectedSegmentHour = (veterinarie_time, segment_time_group) => {
    veterinarie_time.segment_times = segment_time_group.segment_times;
    selected_segment_times.value = [];
    segment_time_veterinaries.value = [];
    console.log(selected_segment_times.value);
}

const reset = () => {
    form.value.date_appointment = null,
        form.value.time = null
}
const addSelectedSegmentTime = (veterinarie_time, segment_time) => {
    let INDEX = selected_segment_times.value.findIndex(item => item.veterinarie_id == veterinarie_time.id && item.segment_time_id == segment_time.id)
    if (INDEX != 1) {
        selected_segment_times.value.splice(INDEX, 1);
    } else {
        selected_segment_times.value.push({
            veterinarie_id: veterinarie_time.id,
            segment_time_id: segment_time.veterinarie_schedule_hour_id,
        })
    }
    selected_segment_times.value = selected_segment_times.value.filter((item) => {
        return item.veterinarie_id == veterinarie_time.id;
    })
    segment_time_veterinaries.value = segment_time_veterinaries.value.filter((item) => {
        return item.indexOf(veterinarie_time.id + "-") != -1;
    })
    console.log(selected_segment_times.value);
}
const fieldsClean = () => {
    form.value.date_appointment = null;
    form.value.time = null,
        form.value.amount = 0,
        form.value.method_payment = 'EFECTIVO',
        form.value.amount_add = 0
    eterinarie_time.segment_times.value = [];
    selected_segment_times.value = [];
    segment_time_veterinaries.value = [];
    select_pet.value = null;
}
const store = async () => {
    try {
        warning.value = null;
        if (!form.value.date_appointment) {
            warning.value = "El campo de fecha es requerido";
            return;
        }
        if (selected_segment_times.value.length == 0) {
            warning.value = "Es necesario asignarle un horario a la cita medica";
            return;
        }
        if (!select_pet.value) {
            warning.value = "Es requerido seleccionar una mascota";
            return;
        }
        if (!form.value.amount) {
            warning.value = "Es requerido ingresar el costo de la cita medica";
            return;
        }
        if (!form.value.amount_add) {
            warning.value = "Es requerido ingresar un adelanto del costo de la cita medica";
            return;
        }
        if (parseInt(form.value.amount_add) > parseInt(form.value.amount)) {
            warning.value = "El costo de adelanto no puede ser mayor al costo de la cita";
            return;
        }
        // let data {

        // }
        // const resp = await $api('/appointments', {
        //     method: 'POST',
        //     body: data,
        //     onResponseError({ response }) {
        //         error_exists.value = response._data?.error || 'Error desconocido'
        //     }
        // })

        // console.log(resp)
        // success.value = 'La cita medica se ha programado correctamente'

        // setTimeout(() => {
        //     success.value = null
        //     warning.value = null
        //     error_exists.value = null
        //     fieldsClean()
        // }, 200)
    } catch (error) {
        console.log(error)
    }
}
const querySelections = async (query) => {
    loading.value = true

    // Simulated ajax query
    setTimeout(async () => {
        // items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
        const resp = await $api("/appointments/search-pets/" + query, {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);
        items.value = resp.pets;
        loading.value = false
    }, 500)
}

watch(search, query => {
    if (query && query.length > 1) {
        querySelections(query)
    } else {
        items.value = [];
    }
})
</script>

<template>
    <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
        <VCardText>
            <div class="mb-6 text-center">
                <h4 class="text-h4 mb-2">Agregar Cita medica</h4>
                <p class="text-subtitle-1 text-muted">Completa los datos requeridos</p>
            </div>
        </VCardText>

        <v-divider class="mb-4"></v-divider>
        <h6 class="text-h6 mb-2">Fecha y hora</h6>

        <vRow dense>
            <vCol cols="12" md="4">
                <AppDateTimePicker v-model="form.date_appointment" label="Fecha de la cita" dense outlined :config="{
                    minDate: 'today', disable: [
                        (date) => {
                            // Deshabilita sábados (6) y domingos (0)
                            return date.getDay() === 0 || date.getDay() === 6;
                        },
                    ]
                }" />
            </vCol>

            <vCol cols="12" md="4">
                <AppDateTimePicker v-model="form.time" label="Hora de la cita"
                    :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }" />
            </vCol>
            <div class="d-flex gap-x-1 align-center">
                <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="filter()" />
                <VBtn color="secondary" class="mx-1" prepend-icon="ri-restart-line" @click="reset()" />
            </div>
        </vRow>

    </VCard>
    <v-col cols="12">
        <VAlert v-if="error_exists" type="error" class="mb-2" dense>{{ error_exists }}</VAlert>
        <VAlert v-if="warning" type="warning" class="mb-2" dense>{{ warning }}</VAlert>
        <VAlert v-if="success" type="success" class="mb-2" dense>{{ success }}</VAlert>
    </v-col>
    <br>
    <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
        <v-divider class="mb-4"></v-divider>
        <h6 class="text-h6 mb-2">Disponibilidad</h6>

        <vRow dense>
            <vCol cols="12">
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                Veterinario
                            </th>
                            <th class="text-uppercase">
                                Tiempos activos
                            </th>
                            <th class="text-uppercase">
                                Segmentos de tiempos
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        {{ segment_time_veterinaries }}
                        <template v-for="(veterinarie_time, index) in veterinarie_time_availability" :key="index">
                            <tr>
                                <td>
                                    {{ veterinarie_time.full_name }}
                                </td>
                                <td>
                                    <ul>
                                        <li v-for="(segment_time_group, index) in veterinarie_time.segment_time_groups"
                                            :key="index" style="list-style: none;">
                                            <VBtn color="success" class="mx-1" prepend-icon="ri-file-add-line"
                                                variant="text"
                                                @click="selectedSegmentHour(veterinarie_time, segment_time_group)">
                                            </VBtn>
                                            {{ segment_time_group.hour_format }}({{
                                                segment_time_group.segment_times.length }})
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    <ul>
                                        <li v-for="(segment_time, index) in veterinarie_time.segment_times" :key="index"
                                            style="list-style: none;">
                                            <VCheckbox v-model="segment_time_veterinaries"
                                                :label="segment_time.schedule_hour.hour_start_format + ' ' + segment_time.schedule_hour.hour_end_format"
                                                :value="veterinarie_time.id + '-' + segment_time.veterinarie_schedule_hour_id"
                                                @click="addSelectedSegmentTime(veterinarie_time, segment_time)" />
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </VTable>
            </vCol>
        </vRow>
    </VCard>
    <br>
    <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
        <v-divider class="mb-4"></v-divider>
        <h6 class="text-h6 mb-2">Paciente</h6>
        <vRow dense>
            <vCol cols="4" md="4">
                <VAutocomplete v-model="select_pet" item-title="name" item-value="id" return-object
                    v-model:search="search" :loading="loading" :items="items"
                    placeholder="Ingresa informacion de la mascota" label="Quien es la mascotita?" variant="underlined"
                    :menu-props="{ maxHeight: '200px' }" />
            </vCol>
            <vCol cols="3" md="3" v-if="select_pet">
                <label for="">Especie: {{ select_pet.specie }}</label>
                <br>
                <label for="">Raza: Pastor {{ select_pet.breed }}</label>

            </vCol>
            <vCol cols="2" md="2" v-if="select_pet">
                <label for="">nombre y Apellido: {{ select_pet.owner.first_name + ' ' + select_pet.owner.last_name
                }}</label>
                <br>
                <label for="">Telefono: {{ select_pet.owner.phone }}</label>
                <br>
                <label for="">Carnet de identidad: {{ select_pet.owner.n_document }}</label>
            </vCol>
        </vRow>
    </VCard>
    <br>
    <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
        <v-divider class="mb-4"></v-divider>
        <h6 class="text-h6 mb-2">Pagos</h6>
        <vRow dense>
            <vCol cols="4" md="4">
                <VTextField label="Costo de la cita" type="number" placeholder="Ej.: 100 Bs." v-model="form.amount" />
            </vCol>
            <vCol cols="4" md="4">
                <VSelect :items="method_payments" label="Metodos de pago" placeholder="Selecciona el metodo de pago"
                    v-model="form.method_payment" outlined dense />
            </vCol>
            <vCol cols="4" md="4">
                <VTextField label="Adelante" type="number" placeholder="Ej.: 100 Bs." v-model="form.amount_add" />
            </vCol>
        </vRow>
    </VCard>
    <v-col cols="12" class="text-center mt-2">
        <VBtn color="primary" size="large" class="px-8 text-subtitle-1" elevation="2" @click="store">
            Registrar cita medica
        </VBtn>
    </v-col>
</template>
<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>
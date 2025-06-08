<script setup>
import { $api } from '@/utils/api'
import { onMounted, ref } from 'vue'

const type_documents = ['CI', 'PASAPORTE', 'CARNET DE EXTRANJERIA']

const form = ref({
    name: null,
    surname: null,
    email: null,
    phone: null,
    type_document: 'CI',
    n_document: null,
    birthday: null,
    designation: null,
    gender: null,
    role_id: null,
    password: null
})
const FILE_AVATAR = ref(null)
const IMAGEN_PREVISUALIZADA = ref(null)
const isPasswordVisible = ref(false)
const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)
const roles = ref([])
const hour_days = ref([]);
const selected_segment_time = ref([]);
const schedule_hours_veterinarie = ref([]);
const load_request = ref(null);
const route = useRoute('veterinarie-edit-id');
const veterinarie_selected = ref(null);
const loadFile = event => {
    const file = event.target.files[0]
    if (!file) {
        warning.value = 'No se seleccionó ningún archivo.'
        return
    }

    if (!file.type.includes('image')) {
        FILE_AVATAR.value = null
        IMAGEN_PREVISUALIZADA.value = null
        warning.value = 'Solamente pueden ser archivos de tipo imagen.'
        return
    }

    FILE_AVATAR.value = file
    warning.value = ''

    const reader = new FileReader()
    reader.onloadend = () => {
        IMAGEN_PREVISUALIZADA.value = reader.result
    }
    reader.readAsDataURL(file)
}

const config = async () => {
    try {
        const resp = await $api("/veterinaries/config", {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);
        roles.value = resp.roles;
        hour_days.value = resp.schedule_hours_groups;
    } catch (error) {
        console.log(error);
    }
}

const days = ref(['lunes', 'martes', 'miércoles', 'jueves', 'viernes'])

const selectSegmentTimeAll = ($event, segment_times, day) => {
    if ($event.target.checked) {
        segment_times.forEach(segment_time => {
            SelectedSegmentTime($event, segment_time, day)
            let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
            if (INDEX == -1) {
                selected_segment_time.value.push(segment_time.id + '-' + day);
            }
        });
    } else {
        segment_times.forEach(segment_time => {
            SelectedSegmentTime($event, segment_time, day)
            let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
            if (INDEX != -1) {
                selected_segment_time.value.splice(INDEX, 1);
            }
        })
    }
}

const selectSegmentTimeAllGroups = ($event, segment_times) => {
    if ($event.target.checked) {
        days.value.forEach((day) => {
            segment_times.forEach(segment_time => {
                SelectedSegmentTime($event, segment_time, day)
                let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
                if (INDEX == -1) {
                    selected_segment_time.value.push(segment_time.id + '-' + day);
                }
            });
        })
    } else {
        days.value.forEach((day) => {
            segment_times.forEach(segment_time => {
                SelectedSegmentTime($event, segment_time, day)
                let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
                if (INDEX != -1) {
                    selected_segment_time.value.splice(INDEX, 1);
                }
            })
        })

    }
}

const SelectedSegmentTime = ($event, segment_time, day) => {
    if ($event.target.checked) {
        let INDEX = schedule_hours_veterinarie.value.findIndex(seg_time => seg_time.id_seg == segment_time.id + '-' + day);
        if (INDEX == -1) {
            schedule_hours_veterinarie.value.push({
                id_seg: segment_time.id + '-' + day,
                segment_time_id: segment_time.id,
                day: day,
            });
        }

    } else {
        let INDEX = schedule_hours_veterinarie.value.findIndex(seg_time => seg_time.id_seg == segment_time.id + '-' + day);
        if (INDEX != -1) {
            schedule_hours_veterinarie.value.splice(INDEX, 1);
        }
    }
}
const validations = [
    { field: 'name', message: 'Se debe llenar el nombre del veterinario' },
    { field: 'surname', message: 'Se debe llenar el apellido del veterinario' },
    { field: 'phone', message: 'Se debe llenar el teléfono del veterinario' },
    { field: 'gender', message: 'Se debe llenar el género del veterinario' },
    { field: 'role_id', message: 'Se debe seleccionar un rol para el veterinario' },
    { field: 'email', message: 'Se debe llenar el email del veterinario' }
]

const store = async () => {
    if (schedule_hours_veterinarie.value.length == 0) {
        warning.value = 'Debes programar la disponibilidad laboral del veterinario'
        return
    }
    for (const rule of validations) {
        if (!form.value[rule.field]) {
            warning.value = rule.message
            return
        }
    }
    if (!FILE_AVATAR.value) {
        warning.value = 'Se debe seleccionar un avatar para el veterinario'
        return
    }

    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
        if (form.value[key]) formData.append(key, form.value[key])
    })
    if (form.value.password) {
        formData, append("password", form.value.password)
    }
    if (form.value.password) {
        formData.append('imagen', FILE_AVATAR.value)
    }
    formData.append('schedule_hours_veterinarie', JSON.stringify(schedule_hours_veterinarie.value))

    try {
        load_request.value = true;
        const resp = await $api('/veterinaries/' + route.params.id, {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })
        console.log(resp)
        load_request.value = true;
        if (resp?.data?.message === 403) {
            warning.value = resp.message_text
        } else {
            success.value = 'El veterinario se ha editado correctamente'
            setTimeout(() => {
                success.value = null
                warning.value = null
                error_exists.value = null
            }, 1000)
        }
    } catch (error) {
        console.error(error)
        error_exists.value = 'Error al conectar con el servidor'
    } finally {
        load_request.value = false;
    }
}

const show = async () => {
    try {
        load_request.value = true;
        const resp = await $api('/veterinaries/' + route.params.id, {
            method: 'GET',
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })
        console.log(resp);
        veterinarie_selected.value = resp.veterinarie;
        console.log(form)
        form.value.name = veterinarie_selected.value.name;
        form.value.surname = veterinarie_selected.value.surname;
        form.value.email = veterinarie_selected.value.email;
        form.value.phone = veterinarie_selected.value.phone;
        form.value.type_document = veterinarie_selected.value.type_document;
        form.value.n_document = veterinarie_selected.value.n_document;
        form.value.gender = veterinarie_selected.value.gender;
        form.value.birthday = veterinarie_selected.value.birthday;
        form.value.designation = veterinarie_selected.value.designation;
        form.value.role_id = veterinarie_selected.value.role_id;
        IMAGEN_PREVISUALIZADA.value = veterinarie_selected.value.avatar;
        selected_segment_time.value = veterinarie_selected.value.selected_segment_time;
        schedule_hours_veterinarie.value = veterinarie_selected.value.schedule_hours_veterinarie
    } catch (error) {
        console.log(error);
    } finally {
        load_request.value = false;
    }
}

onMounted(() => {
    config();
    show();
})

definePage({
    meta: {
        Permission: 'edit_veterinary'
    },
})
</script>

<template>
    <div>
        <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
            <VCardText>
                <div class="mb-6 text-center">
                    <h4 class="text-h4 mb-2">Editar Veterinario</h4>
                    <p class="text-subtitle-1 text-muted">Completa los datos requeridos</p>
                </div>

                <!-- Datos personales -->
                <v-divider class="mb-4"></v-divider>
                <h6 class="text-h6 mb-2">Datos personales</h6>
                <vRow dense>
                    <vCol cols="12" md="6">
                        <VTextField dense outlined label="Nombre" v-model="form.name" placeholder="Ejemplo: Marcos" />
                    </vCol>
                    <vCol cols="12" md="6">
                        <VTextField dense outlined label="Apellido" v-model="form.surname"
                            placeholder="Ejemplo: Vargas" />
                    </vCol>
                    <vCol cols="12" md="4">
                        <VTextField dense outlined label="Teléfono" type="number" v-model="form.phone"
                            placeholder="Ejemplo: 60983646" />
                    </vCol>
                    <vCol cols="12" md="4">
                        <VSelect dense outlined :items="type_documents" v-model="form.type_document"
                            label="Tipo de documento" placeholder="Selecciona" />
                    </vCol>
                    <vCol cols="12" md="4">
                        <VTextField dense outlined label="N° de documento" type="number" v-model="form.n_document"
                            placeholder="Ejemplo: 9729185" />
                    </vCol>
                </vRow>

                <!-- Más información -->
                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Más información</h6>
                <vRow dense>
                    <vCol cols="12" md="6">
                        <AppDateTimePicker dense outlined v-model="form.birthday" label="Fecha de nacimiento"
                            placeholder="Selecciona fecha" />
                    </vCol>
                    <vCol cols="12" md="6" class="d-flex align-center">
                        <VRadioGroup v-model="form.gender" inline>
                            <VRadio label="Masculino" value="M" />
                            <VRadio label="Femenino" value="F" />
                        </VRadioGroup>
                    </vCol>
                    <vCol cols="12">
                        <VTextarea dense outlined label="Designación" placeholder="Texto" v-model="form.designation" />
                    </vCol>
                </vRow>

                <!-- Acceso -->
                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Datos de acceso</h6>
                <vRow dense>
                    <vCol cols="12" md="6">
                        <VTextField dense outlined v-model="form.email" label="Email" type="email"
                            placeholder="johndoe@email.com" />
                    </vCol>
                    <vCol cols="12" md="6">
                        <VTextField dense outlined v-model="form.password" label="Password" placeholder="············"
                            :type="isPasswordVisible ? 'text' : 'password'"
                            :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                            @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                    </vCol>
                </vRow>

                <!-- Archivo -->
                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Archivo</h6>
                <vRow dense>
                    <vCol cols="12">
                        <vRow>
                            <vCol cols="6">
                                <VFileInput dense outlined label="Selecciona archivo" @change="loadFile($event)" />
                            </vCol>
                            <vCol cols="6">
                                <VSelect :items="roles" v-model="form.role_id" label="Rol" item-title="name"
                                    item-value="id" placeholder="Selecciopna el rol" eager />
                            </vCol>
                        </vRow>
                    </vCol>
                    <vCol cols="12" v-if="IMAGEN_PREVISUALIZADA">
                        <VImg width="137" height="176" :src="IMAGEN_PREVISUALIZADA"
                            class="mt-4 mx-auto rounded elevation-3" />
                    </vCol>
                </vRow>
                <!-- Botón -->
                <div class="text-center mt-6">
                    <VBtn color="primary" class="px-8 text-subtitle-1" elevation="2" @click="store">
                        Editar veterinario
                    </VBtn>
                </div>

                <!-- Alertas -->
                <VAlert type="warning" class="mt-3" v-if="warning">
                    <strong>{{ warning }}</strong>
                </VAlert>
                <VAlert type="error" class="mt-3" v-if="error_exists">
                    <strong>{{ error_exists }}</strong>
                </VAlert>
                <VAlert type="success" class="mt-3" v-if="success">
                    <strong>{{ success }}</strong>
                </VAlert>

            </VCardText>
            <VCardText class="pa-4">
                <div class="text-h6 font-weight-medium mb-4">
                    {{ schedule_hours_veterinarie }}
                    {{ selected_segment_time }}
                </div>

                <VTable class="elevation-1 rounded-lg">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase font-weight-bold py-3" style="min-width: 100px">
                                Días / Horas
                            </th>
                            <th v-for="(day, index) in days" :key="'head-' + index"
                                class="text-center text-uppercase font-weight-bold py-3">
                                {{ day }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(item, rowIndex) in hour_days" :key="'row-' + rowIndex">
                            <!-- Columna de la hora -->
                            <td class="text-center align-top px-3 py-2">
                                <div class="font-weight-medium mb-2">{{ item.hour_format }}</div>
                                <VCheckbox class="mt-2 ml-7" density="compact" hide-details label="Todos"
                                    @click="selectSegmentTimeAllGroups($event, item.segments_time)"
                                    v-if="!load_request" />
                            </td>

                            <!-- Columnas por día -->
                            <td v-for="(day, colIndex) in days" :key="'col-' + colIndex" class="align-top px-2 py-3">
                                <div class="d-flex flex-column align-center">
                                    <VCheckbox class="mb-2" density="compact" hide-details label="Todos"
                                        @click="selectSegmentTimeAll($event, item.segments_time, day)"
                                        v-if="!load_request" />
                                    <template v-for="(segment_time, index) in item.segments_time" :key="index">
                                        <VCheckbox @click="SelectedSegmentTime($event, segment_time, day)"
                                            v-model="selected_segment_time"
                                            :label="segment_time.hour_start_format + ' ' + segment_time.hour_end_format"
                                            :value="segment_time.id + '-' + day" />
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>

        </VCard>
    </div>
</template>
<style>
.v-selection-control .v-label {
    font-size: small;
}

.v-checkbox.v-input {
    margin: 0;
}
</style>

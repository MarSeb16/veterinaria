<script setup>
import { ref, watch } from 'vue'

const showPetForm = ref(true)
const showResponsibleForm = ref(false)

const toggleCard = (card) => {
    if (card === 'pet') {
        showPetForm.value = !showPetForm.value
    } else if (card === 'responsible') {
        showResponsibleForm.value = !showResponsibleForm.value
    }
}

const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)

const specie = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro'])

const FILE_AVATAR = ref(null)
const IMAGEN_PREVISUALIZADA = ref(null)

const form = ref({
    name: null,
    specie: null,
    breed: null,
    dirth_date: null,
    gender: null,
    color: null,
    weight: 0,
    medical_notes: null,
    first_name: null,
    last_name: null,
    email: null,
    phone: null,
    address: null,
    city: null,
    emergency_contact: null,
    type_document: 'CI',
    n_document: null
})

const requiredPetFields = [
    'name',
    'specie',
    'breed',
    'dirth_date',
    'gender',
    'color',
    'weight'
]

// Función para limpiar campos del formulario
const fieldsClean = () => {
    Object.keys(form.value).forEach(key => form.value[key] = null)
    form.value.type_document = 'CI'
    FILE_AVATAR.value = null
    IMAGEN_PREVISUALIZADA.value = null
}

// Función para cargar imagen y validarla
const loadFile = (event) => {
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
    reader.onloadend = () => IMAGEN_PREVISUALIZADA.value = reader.result
    reader.readAsDataURL(file)
}

// Verifica si todos los campos requeridos del formulario de mascota están llenos
const checkPetFormCompletion = () => {
    const allFilled = requiredPetFields.every(key => {
        return form.value[key] !== null && form.value[key] !== ''
    }) && FILE_AVATAR.value !== null && form.value.medical_notes

    if (allFilled) {
        // ✅ Solo se muestra la parte de responsable, pero no se oculta mascota
        showResponsibleForm.value = true
    }
}

// Escuchamos los campos relevantes para verificar si se deben desplegar los datos del responsable
watch(
    () => [
        form.value.name,
        form.value.specie,
        form.value.breed,
        form.value.dirth_date,
        form.value.gender,
        form.value.color,
        form.value.weight,
        FILE_AVATAR.value,
        form.value.medical_notes
    ],
    checkPetFormCompletion,
    { deep: true }
)

const validateField = (field, message) => {
    if (!field) {
        warning.value = message
        return false
    }
    return true
}

const validateForm = () => {
    return (
        validateField(form.value.name, "El nombre de la mascota es obligatorio") &&
        validateField(form.value.specie, "La especie de la mascota es obligatoria") &&
        validateField(form.value.breed, "La raza de la mascota es obligatoria") &&
        validateField(form.value.dirth_date, "La fecha de nacimiento es obligatoria") &&
        validateField(form.value.gender, "El género de la mascota es obligatorio") &&
        validateField(form.value.color, "El color de la mascota es obligatorio") &&
        validateField(form.value.weight, "El peso de la mascota es obligatorio") &&
        validateField(FILE_AVATAR.value, "La imagen de la mascota es obligatoria") &&
        validateField(form.value.first_name, "El nombre del responsable es obligatorio") &&
        validateField(form.value.last_name, "El apellido del responsable es obligatorio") &&
        validateField(form.value.phone, "El teléfono del responsable es obligatorio") &&
        validateField(form.value.address, "La dirección del responsable es obligatoria") &&
        validateField(form.value.emergency_contact, "El contacto de emergencia es obligatorio") &&
        validateField(form.value.n_document, "El número de documento del responsable es obligatorio")
    )
}

const buildFormData = () => {
    const formData = new FormData()
    for (const [key, value] of Object.entries(form.value)) {
        if (value !== null && value !== '') {
            formData.append(key, value)
        }
    }
    formData.append("imagen", FILE_AVATAR.value)
    return formData
}

const store = async () => {
    warning.value = null
    success.value = null
    error_exists.value = null

    if (!validateForm()) return

    try {
        const formData = buildFormData()

        const resp = await $api('/pets', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })

        console.log(resp)
        success.value = 'La mascota se ha creado correctamente'

        setTimeout(() => {
            success.value = null
            warning.value = null
            error_exists.value = null
            fieldsClean()
        }, 200)

    } catch (error) {
        console.error(error)
    }
}
definePage({
    meta: {
        Permission: 'register_pet'
    },
})
</script>

<template>
    <!-- Mascota -->
    <VCard class="mb-6 rounded-xl" elevation="4">
        <VCardTitle class="text-h5 font-weight-bold text-center cursor-pointer py-4" @click="toggleCard('pet')">
            🐾 Información de la Mascota
        </VCardTitle>

        <VExpandTransition>
            <div v-show="showPetForm">
                <VDivider class="my-2" />
                <VCardText>
                    <v-row dense>
                        <v-col cols="12" md="6">
                            <VTextField label="Nombre de la mascota" v-model="form.name" placeholder="Ej: Boby" outlined
                                dense />
                        </v-col>
                        <v-col cols="6" md="3">
                            <VSelect :items="specie" v-model="form.specie" label="Especie"
                                placeholder="Selecciona especie" outlined dense />
                        </v-col>
                        <v-col cols="6" md="3">
                            <VTextField label="Raza" v-model="form.breed" placeholder="Ej: Labrador" outlined dense />
                        </v-col>
                        <v-col cols="12" md="4">
                            <AppDateTimePicker v-model="form.dirth_date" label="Fecha de nacimiento"
                                placeholder="Selecciona fecha" outlined dense />
                        </v-col>
                        <v-col cols="12" md="4">
                            <label class="text-subtitle-2 mb-1 d-block">Género</label>
                            <VRadioGroup v-model="form.gender" inline>
                                <VRadio label="Macho" value="M" />
                                <VRadio label="Hembra" value="H" />
                            </VRadioGroup>
                        </v-col>
                        <v-col cols="12" md="4">
                            <VTextarea label="Color del pelaje" v-model="form.color"
                                placeholder="Ej: Blanco con manchas negras" outlined dense />
                        </v-col>
                        <v-col cols="12" md="4">
                            <VTextField type="number" label="Peso (kg)" v-model="form.weight" placeholder="Ej: 8.5"
                                outlined dense />
                        </v-col>
                        <v-col cols="12" md="4">
                            <VFileInput label="Foto de la mascota" @change="loadFile($event)" accept="image/*" outlined
                                dense />
                        </v-col>
                        <v-col cols="12" v-if="IMAGEN_PREVISUALIZADA">
                            <div class="text-center">
                                <VImg :src="IMAGEN_PREVISUALIZADA" max-width="200"
                                    class="rounded-lg elevation-5 mt-4" />
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <VTextarea label="Notas médicas" placeholder="Observaciones veterinarias"
                                v-model="form.medical_notes" outlined dense />
                        </v-col>
                        <v-col cols="12">
                            <VAlert v-if="error_exists" type="error" class="mb-2" dense>{{ error_exists }}</VAlert>
                            <VAlert v-if="warning" type="warning" class="mb-2" dense>{{ warning }}</VAlert>
                            <VAlert v-if="success" type="success" class="mb-2" dense>{{ success }}</VAlert>
                        </v-col>
                    </v-row>
                </VCardText>
            </div>
        </VExpandTransition>
    </VCard>

    <!-- Responsable -->
    <VCard class="rounded-xl" elevation="4">
        <VCardTitle class="text-h5 font-weight-bold text-center cursor-pointer py-4" @click="toggleCard('responsible')">
            👤 Información del Responsable
        </VCardTitle>

        <VExpandTransition>
            <div v-show="showResponsibleForm">
                <VDivider class="my-2" />
                <VCardText>
                    <v-row dense>
                        <v-col cols="12" md="6">
                            <VTextField label="Nombre(s)" v-model="form.first_name" placeholder="Ej: Juan" outlined
                                dense />
                        </v-col>
                        <v-col cols="12" md="6">
                            <VTextField label="Apellido(s)" v-model="form.last_name" placeholder="Ej: Pérez" outlined
                                dense />
                        </v-col>
                        <v-col cols="12" md="6">
                            <VTextField label="Correo electrónico" v-model="form.email" type="email"
                                placeholder="ejemplo@email.com" outlined dense />
                        </v-col>
                        <v-col cols="12" md="6">
                            <VTextField label="Teléfono" v-model="form.phone" placeholder="Ej: 71234567" outlined
                                dense />
                        </v-col>
                        <v-col cols="12" md="8">
                            <VTextField label="Dirección" v-model="form.address" placeholder="Calle y número" outlined
                                dense />
                        </v-col>
                        <v-col cols="12" md="4">
                            <VTextField label="Ciudad" v-model="form.city" placeholder="Ej: La Paz" outlined dense />
                        </v-col>
                        <v-col cols="12" md="6">
                            <VTextField label="Contacto de emergencia" v-model="form.emergency_contact"
                                placeholder="Nombre y teléfono" outlined dense />
                        </v-col>
                        <v-col cols="6" md="3">
                            <VSelect :items="['CI', 'Pasaporte', 'DNI']" v-model="form.type_document"
                                label="Tipo de documento" outlined dense />
                        </v-col>
                        <v-col cols="6" md="3">
                            <VTextField label="N° Documento" v-model="form.n_document" outlined dense />
                        </v-col>
                    </v-row>
                </VCardText>
            </div>
        </VExpandTransition>
    </VCard>
    <v-col cols="12" class="text-center mt-2">
        <VBtn color="primary" size="large" class="px-8 text-subtitle-1" elevation="2" @click="store">
            registrar mascota
        </VBtn>
    </v-col>
</template>

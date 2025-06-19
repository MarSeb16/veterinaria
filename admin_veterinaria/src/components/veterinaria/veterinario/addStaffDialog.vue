<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
    roles: { type: Object, required: true }
})

const emit = defineEmits(['update:isDialogVisible', 'addRole', 'addUser'])

const FILE_AVATAR = ref(null)
const IMAGEN_PREVISUALIZADA = ref(null)
const isPasswordVisible = ref(false)
const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)
const roles = ref([])
const fieldsClean = () => {
    form.value = {
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
    }
}
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

const validations = [
    { field: 'name', message: 'Se debe llenar el nombre del usuario' },
    { field: 'surname', message: 'Se debe llenar el apellido del usuario' },
    { field: 'phone', message: 'Se debe llenar el teléfono del usuario' },
    { field: 'gender', message: 'Se debe llenar el género del usuario' },
    { field: 'role_id', message: 'Se debe seleccionar un rol para el usuario' },
    { field: 'password', message: 'Se debe digitar una contraseña para el usuario' },
    { field: 'email', message: 'Se debe llenar el email del usuario' }
]

// Limpiar formulario al cerrar
const dialogVisibleUpdate = val => {
    if (!val) {
        FILE_AVATAR.value = null
        IMAGEN_PREVISUALIZADA.value = null
    }
    emit('update:isDialogVisible', val)
}

// Cargar archivo e imagen
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

// Guardar usuario
const store = async () => {
    for (const rule of validations) {
        if (!form.value[rule.field]) {
            warning.value = rule.message
            return
        }
    }

    // if (!FILE_AVATAR.value) {
    //     warning.value = 'Se debe seleccionar un avatar para el usuario'
    //     return
    // }

    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
        if (form.value[key]) formData.append(key, form.value[key])
    })
    formData.append('imagen', FILE_AVATAR.value)

    try {
        const resp = await $api('/staffs', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                error_exists.value = response._data?.error || 'Error desconocido'
            }
        })

        if (resp?.data?.message === 403) {
            warning.value = resp.data.message_text
        } else {
            success.value = 'El usuario se ha creado correctamente'
            setTimeout(() => {
                success.value = null
                warning.value = null
                error_exists.value = null
                fieldsClean();
                emit('update:isDialogVisible', false)
                emit('addUser', resp.user)
            }, 200)
        }
    } catch (error) {
        console.error(error)
        error_exists.value = 'Error al conectar con el servidor'
    }
}

onMounted(() => {
    roles.value = props.roles
})
</script>


<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText>
                <div class="mb-6 text-center">
                    <h4 class="text-h4 mb-2">Agregar un nuevo usuario</h4>
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
                                <VFileInput label="Selecciona archivo" @change="loadFile($event)" />
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

                <!-- Botón -->
                <div class="text-center mt-6">
                    <VBtn color="primary" class="px-8 text-subtitle-1" elevation="2" @click="store">
                        Crear usuario
                    </VBtn>
                </div>
            </VCardText>
        </VCard>
    </VDialog>
</template>



<style lang="scss">
.refer-link-input {
    .v-field--appended {
        padding-inline-end: 0;
    }

    .v-field__append-inner {
        padding-block-start: 0.125rem;
    }
}
</style>

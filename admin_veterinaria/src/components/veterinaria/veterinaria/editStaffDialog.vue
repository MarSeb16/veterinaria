<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
    roles: { type: Array, required: true },
    userSelected: { type: Object, required: true }
})
onMounted(() => {
    console.log('Roles recibidos:', props.roles)
    props.roles.forEach(role => {
        console.log(`Rol ID: ${role.id}, Nombre: ${role.name}`)
    })
})

const emit = defineEmits(['update:isDialogVisible', 'editUser'])

const fileAvatar = ref(null)
const imagePreview = ref(null)
const isPasswordVisible = ref(false)
const warning = ref(null)
const errorMessage = ref(null)
const successMessage = ref(null)

const typeDocuments = ['CI', 'PASAPORTE', 'CARNET DE EXTRANJERIA']

const form = ref({
    name: '',
    surname: '',
    email: '',
    phone: '',
    type_document: 'CI',
    n_document: '',
    birthday: '',
    designation: '',
    gender: '',
    role_id: '',
    password: ''
})
console.log(form)
// Validaciones
const validations = [
    { field: 'name', message: 'Se debe llenar el nombre del usuario' },
    { field: 'surname', message: 'Se debe llenar el apellido del usuario' },
    { field: 'phone', message: 'Se debe llenar el teléfono del usuario' },
    { field: 'gender', message: 'Se debe llenar el género del usuario' },
    { field: 'role_id', message: 'Se debe seleccionar un rol para el usuario' },
    { field: 'email', message: 'Se debe llenar el email del usuario' }
]

// Resetear cuando se cierra el diálogo
const dialogVisibleUpdate = val => {
    if (!val) {
        fileAvatar.value = null
        imagePreview.value = null
        warning.value = null
        errorMessage.value = null
        successMessage.value = null
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
        warning.value = 'El archivo debe ser de tipo imagen.'
        fileAvatar.value = null
        imagePreview.value = null
        return
    }

    fileAvatar.value = file
    const reader = new FileReader()
    reader.onloadend = () => {
        imagePreview.value = reader.result
    }
    reader.readAsDataURL(file)
}

// Guardar usuario
const updateUser = async () => {
    // Validaciones
    for (const rule of validations) {
        if (!form.value[rule.field]) {
            warning.value = rule.message
            return
        }
    }

    if (!fileAvatar.value && !imagePreview.value) {
        warning.value = 'Se debe seleccionar un avatar para el usuario'
        return
    }


    const formData = new FormData()
    for (const key in form.value) {
        if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key])
        }
    }
    if (fileAvatar.value) {
        formData.append('imagen', fileAvatar.value)
    }

    try {
        const resp = await $api(`/staffs/${props.userSelected.id}`, {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                errorMessage.value = response._data?.error || 'Error desconocido'
            }
        })

        if (resp?.data?.message === 403) {
            warning.value = resp.data.message_text
        }
        else {
            successMessage.value = 'El usuario se ha editado correctamente'
            emit('editUser', resp.user)

            setTimeout(() => {
                successMessage.value = null
                emit('update:isDialogVisible', false)
                emit('editUser', resp.user)
            }, 500)
        }
    } catch (error) {
        console.error(error)
        errorMessage.value = 'Error al conectar con el servidor'
    }
}

// Cuando se monta o cambia el usuario seleccionado, llenar el form
watch(
    () => props.userSelected,
    (newUser) => {
        if (newUser) {
            form.value = {
                name: newUser.name || '',
                surname: newUser.surname || '',
                email: newUser.email || '',
                phone: newUser.phone || '',
                type_document: newUser.type_document || 'CI',
                n_document: newUser.n_document || '',
                birthday: newUser.birthday || '',
                designation: newUser.designation || '',
                gender: newUser.gender || '',
                role_id: newUser.role_id || '',
                password: ''
            }
            imagePreview.value = newUser.avatar || null
        }
    },
    { immediate: true }
)

</script>
<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="pa-3 pa-sm-8 rounded-lg" elevation="4">
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText>
                <div class="mb-6 text-center">
                    <h4 class="text-h4 mb-2">Editar los datos de: {{ props.userSelected.name }}</h4>
                    <p class="text-subtitle-1 text-muted">Completa los datos requeridos</p>
                </div>

                <v-divider class="mb-4"></v-divider>
                <h6 class="text-h6 mb-2">Datos personales</h6>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <VTextField dense outlined label="Nombre" v-model="form.name" placeholder="Ejemplo: Marcos" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <VTextField dense outlined label="Apellido" v-model="form.surname"
                            placeholder="Ejemplo: Vargas" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <VTextField dense outlined label="Teléfono" type="number" v-model="form.phone"
                            placeholder="Ejemplo: 60983646" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <VSelect dense outlined :items="typeDocuments" v-model="form.type_document"
                            label="Tipo de documento" placeholder="Selecciona" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <VTextField dense outlined label="N° de documento" type="number" v-model="form.n_document"
                            placeholder="Ejemplo: 9729185" />
                    </v-col>
                </v-row>

                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Más información</h6>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <AppDateTimePicker dense outlined v-model="form.birthday" label="Fecha de nacimiento"
                            placeholder="Selecciona fecha" />
                    </v-col>
                    <v-col cols="12" md="6" class="d-flex align-center">
                        <VRadioGroup v-model="form.gender" inline>
                            <VRadio label="Masculino" value="M" />
                            <VRadio label="Femenino" value="F" />
                        </VRadioGroup>
                    </v-col>
                    <v-col cols="12">
                        <VTextarea dense outlined label="Designación" placeholder="Texto" v-model="form.designation" />
                    </v-col>
                </v-row>

                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Datos de acceso</h6>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <VTextField dense outlined v-model="form.email" label="Email" type="email"
                            placeholder="johndoe@email.com" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <VTextField dense outlined v-model="form.password" label="Password" placeholder="············"
                            :type="isPasswordVisible ? 'text' : 'password'"
                            :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                            @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                    </v-col>
                </v-row>

                <v-divider class="my-4"></v-divider>
                <h6 class="text-h6 mb-2">Archivo</h6>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <VFileInput dense outlined label="Selecciona archivo" @change="loadFile($event)" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <VSelect :items="roles" v-model="form.role_id" label="Rol" item-title="name" item-value="name"
                            placeholder="Selecciopna el rol" eager />
                    </v-col>
                </v-row>

                <v-col cols="12" v-if="imagePreview">
                    <VImg width="137" height="176" :src="imagePreview" class="mt-4 mx-auto rounded elevation-3" />
                </v-col>

                <!-- Alertas -->
                <VAlert type="warning" class="mt-3" v-if="warning">
                    <strong>{{ warning }}</strong>
                </VAlert>
                <VAlert type="error" class="mt-3" v-if="errorMessage">
                    <strong>{{ errorMessage }}</strong>
                </VAlert>
                <VAlert type="success" class="mt-3" v-if="successMessage">
                    <strong>{{ successMessage }}</strong>
                </VAlert>

                <!-- Botón -->
                <div class="text-center mt-6">
                    <VBtn color="primary" class="px-8 text-subtitle-1" elevation="2" @click="updateUser">
                        Editar usuario
                    </VBtn>
                </div>
            </VCardText>
        </VCard>
    </VDialog>
</template>

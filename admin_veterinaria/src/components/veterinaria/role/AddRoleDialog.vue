<script setup>
import { ref } from 'vue'
import { PERMISOS } from '@/utils/constants'

const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
})

const emit = defineEmits(['update:isDialogVisible', 'addRole'])

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}

const LIST_PERMISSION = PERMISOS

const role = ref(null)
const error_exists = ref(null)
const success = ref(null)
const warning = ref(null)
const permissions = ref([])

const AddPermission = (permiso) => {
    const index = permissions.value.findIndex(perm => perm === permiso)
    if (index !== -1) {
        permissions.value.splice(index, 1)
    } else {
        permissions.value.push(permiso)
    }
    console.log(permissions.value)
}

const store = async () => {
    warning.value = null
    error_exists.value = null
    success.value = null

    if (!role.value) {
        warning.value = 'Se debe llenar el nombre del rol'
        return
    }

    if (permissions.value.length === 0) {
        warning.value = 'Se debe seleccionar al menos un permiso'
        return
    }

    const data = {
        name: role.value,
        permissions: permissions.value,
    }

    try {
        const resp = await $api('/role', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                console.log(response)
                error_exists.value = response._data?.error ?? 'Error desconocido'
            },
        })

        console.log(resp)
        if (resp?.data?.message === 403) {
            warning.value = resp.data.message_text
        } else {
            success.value = 'El rol se ha creado correctamente'
            setTimeout(() => {
                success.value = null;
                warning.value = null;
                role.value = null;
                permissions.value = [];
                emit('update:isDialogVisible', false)
                emit('addRole', true);
                document.location.reload();
            }, 500)
        }
    } catch (error) {
        console.log(error)
        error_exists.value = 'Error al conectar con el servidor'
    }
}
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">Agregar un nuevo rol</h4>
                </div>

                <VTextField label="Rol:" v-model="role" placeholder="Ejemplo: Administrador" />

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

            <VCardText class="pa-5">
                <VBtn color="primary" class="mb-5" @click="store">
                    Crear
                </VBtn>

                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">Módulos</th>
                            <th class="text-uppercase">Permisos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in LIST_PERMISSION" :key="index">
                            <td>{{ item.name }}</td>
                            <td>
                                <ul>
                                    <li v-for="(permiso, index2) in item.permisos" :key="index2"
                                        style="list-style: none">
                                        <VCheckbox :label="permiso.name" :value="permiso.permiso"
                                            :model-value="permissions.includes(permiso.permiso)"
                                            @click="AddPermission(permiso.permiso)" />
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
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

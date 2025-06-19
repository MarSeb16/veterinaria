<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
    petSelected: { type: Object, required: true }
})

const emit = defineEmits(['update:isDialogVisible', 'deletePet'])

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}

const success = ref(null)
const error_exists = ref(null)
const pet_selected = ref(null)

const deleted = async () => {
    try {
        const resp = await $api('/pets/' + pet_selected.value.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response)
                error_exists.value = response._data?.error ?? 'Error desconocido'
            },
        })
        console.log(resp)
        success.value = 'La mascota se ha eliminado correctamente.'
        emit('deletePet', pet_selected.value)

        setTimeout(() => {
            success.value = null
            emit('update:isDialogVisible', false)
        }, 500)
    } catch (error) {
        console.error(error)
        error_exists.value = '❌ Error al eliminar la mascota. Intente nuevamente.'
    }
}


onMounted(() => {
    pet_selected.value = props.petSelected
})
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="500" @update:model-value="dialogVisibleUpdate">
        <VCard class="pa-6 rounded-xl">

            <!-- Botón de cierre -->
            <DialogCloseBtn variant="text" size="default" class="float-right"
                @click="emit('update:isDialogVisible', false)" />

            <!-- Título -->
            <div class="text-center mb-4">
                <VIcon color="error" size="48">mdi-alert-circle-outline</VIcon>
                <h4 class="text-h5 font-weight-bold mt-2">Eliminar Mascota</h4>
                <p class="text-subtitle-1 text-grey-darken-1">¿Estás seguro de eliminar a la mascotita <strong>{{
                    pet_selected?.name }}</strong>?</p>
            </div>

            <!-- Mensajes -->
            <div class="mt-4 text-center">
                <VAlert type="error" v-if="error_exists" class="mb-3">
                    <strong>{{ error_exists }}</strong>
                </VAlert>
                <VAlert type="success" v-if="success" class="mb-3">
                    <strong>{{ success }}</strong>
                </VAlert>
            </div>

            <!-- Botón eliminar -->
            <div class="d-flex justify-center mt-1">
                <VBtn color="error" @click="deleted" class="px-6 py-2">
                    Eliminar
                </VBtn>
            </div>

        </VCard>
    </VDialog>
</template>

<style lang="scss" scoped>
.v-card {
    text-align: center;
}
</style>

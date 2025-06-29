<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const searchQuery = ref(null)
const specie = ref(null)
const router = useRouter()
const pets = ref([])
const currentPage = ref(1)
const totalPage = ref(1);
const pet_selected_deleted = ref(null);
const isDeletePetDialogVisible = ref(false);
const species = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro'])

const list = async () => {
    const resp = await $api('/pets?page=' + currentPage.value + '&search=' + (searchQuery.value || '')
        + '&species=' + (specie.value || ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response)

        },
    })
    totalPage.value = resp.total_page
    pets.value = resp.pets.data
}

const avatarText = (value) => {
    if (!value) return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}

const editItem = (item) => {
    router.push({
        name: 'pet-edit-id',
        params: {
            id: item.id
        }
    })
}
const deleteItem = (item) => {
    pet_selected_deleted.value = item;
    isDeletePetDialogVisible.value = true;
}
const deletePet = (item) => {
    let INDEX = pets.value.findIndex((pet) => pet.id == item.id);
    if (INDEX != -1) {
        pets.value.splice(INDEX, 1)
    }
}
const reset = () => {
    searchQuery.value = null;
    specie.value = null;
    currentPage.value = 1;
    list();
}
watch(currentPage, (val) => {
    list();
})
watch(isDeletePetDialogVisible, (val) => {
    if (!val) {
        pet_selected_deleted.value = null;
    }
});
onMounted(() => {
    list()
})
definePage({
    meta: {
        Permission: 'list_pet'
    },
})

</script>

<template>
    <div>
        <VCard title="Mascotas">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <VTextField v-model="searchQuery" placeholder="Buscar mascota o responsable"
                        style="inline-size: 300px" density="compact" class="me-3" @keyup.enter="list" />
                </div>
                <div>
                    <VSelect :items="species" v-model="specie" label="Especies" style="inline-size: 300px"
                        placeholder="Selecciona especie" outlined dense />
                </div>
                <VSpacer />
                <div class="d-flex gap-x-1 align-center">
                    <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="list()" />
                    <VBtn color="secondary" class="mx-1" prepend-icon="ri-restart-line" @click="reset()" />
                </div>
                <div class="d-flex gap-x-4 align-center">
                    <VBtn color="primary" prepend-icon="ri-add-line" @click="router.push({ name: 'pet-add' })">
                        Agregar Mascota
                    </VBtn>
                </div>
            </VCardText>

            <VCardText class="pa-5">
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">Nombre</th>
                            <th class="text-uppercase">Especie</th>
                            <th class="text-uppercase">Raza</th>
                            <th class="text-uppercase">Género</th>
                            <th class="text-uppercase">Peso</th>
                            <th class="text-uppercase">Color</th>
                            <th class="text-uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in pets" :key="item.id">
                            <td>
                                <div class="d-flex align-center">
                                    <VAvatar size="32" :color="item.photo ? '' : 'primary'"
                                        :class="item.photo ? '' : 'v-avatar-light-bg primary--text'"
                                        :variant="item.photo ? 'tonal' : undefined">
                                        <VImg v-if="item.photo" :src="item.photo" />
                                        <span v-else class="text-sm">{{ avatarText(item.name) }}</span>
                                    </VAvatar>
                                    <div class="d-flex flex-column ms-3">
                                        <span class="d-block font-weight-medium text-high-emphasis text-truncate">
                                            {{ item.name }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ item.specie }}</td>
                            <td>{{ item.breed }}</td>
                            <td>{{ item.gender }}</td>
                            <td>{{ item.weight }} Kg.</td>
                            <td>{{ item.color }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <VBtn icon variant="text" size="small" @click="editItem(item)">
                                        <VIcon icon="ri-pencil-line" />
                                    </VBtn>
                                    <VBtn icon variant="text" size="small" @click="deleteItem(item)">
                                        <VIcon icon="ri-delete-bin-line" />
                                    </VBtn>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
                <VPagination v-model="currentPage" :length="totalPage" />
            </VCardText>
            <DeletePetDialog v-if="pet_selected_deleted" :petSelected="pet_selected_deleted"
                v-model:is-dialog-visible="isDeletePetDialogVisible" @deletePet="deletePet" />
        </VCard>
    </div>
</template>

<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const search_pets = ref(null)
const specie = ref(null)
const router = useRouter()
const vaccinations = ref([])
const currentPage = ref(1)
const totalPage = ref(1);
const vaccination_selected_deleted = ref(null);
const isDeleteVaccinationDialogVisible = ref(false);
const species = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro'])
const dateRange = ref(null); // No como null
const type_date = ref(1);
const state_pay = ref(null)
const state_vaccination = ref(null);

const list = async () => {
    let data = {
        type_date: type_date.value,
        start_date: dateRange.value ? dateRange.value.split("to")[0] : null,
        end_date: dateRange.value ? dateRange.value.split("to")[1] : null,
        state_pay: state_pay.value,
        state: state_vaccination.value,
        specie: specie.value,
        search_pets: search_pets.value
    }
    const resp = await $api(`/vaccinations/index?page=${currentPage.value}`, {
        method: 'POST',
        body: data,
        onResponseError({ response }) {
            console.error(response);
        },
    });

    totalPage.value = resp.total_page;
    vaccinations.value = resp.vaccinations.data;
}

const downloadExcel = () => {
    let LINK = "";
    if (dateRange.value) {
        LINK += "&type_date=" + type_date.value;
        LINK += "&start_date=" + dateRange.value.split("to")[0];
        LINK += "&end_date=" + dateRange.value.split("to")[1];
    }
    if (state_pay.value) {
        LINK += "&state_pay=" + state_pay.value;
    }
    if (state_vaccination.value) {
        LINK += "&state_vaccination=" + state_vaccination.value;
    }
    if (species.value) {
        LINK += "&species=" + species.value;
    }
    if (search_pets.value) {
        LINK += "&search_pets=" + search_pets.value;
    }
    window.open(import.meta.env.VITE_API_BASE_URL + "/vaccination-excel?k=1" + LINK, "_blank");
}
const avatarText = (value) => {
    if (!value) return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}

const editItem = (item) => {
    router.push({
        name: 'vaccination-edit-id',
        params: {
            id: item.id
        }
    })
}
const deleteItem = (item) => {
    vaccination_selected_deleted.value = item;
    isDeleteVaccinationDialogVisible.value = true;
}
const deleteVaccination = (item) => {
    let INDEX = vaccinations.value.findIndex((vaccination) => vaccination.id == item.id);
    if (INDEX != -1) {
        vaccinations.value.splice(INDEX, 1)
    }
}
const reset = () => {
    search_pets.value = null;
    specie.value = null;
    dateRange.value = null;
    type_date.value = 1;
    state_pay.value = null;
    state_vaccination.value = null;
    currentPage.value = 1;
    list();
};

watch(currentPage, (val) => {
    list();
})
watch(isDeleteVaccinationDialogVisible, (val) => {
    if (!val) {
        vaccination_selected_deleted.value = null;
    }
});
onMounted(() => {
    list()
})
definePage({
    meta: {
        Permission: 'list_vaccination'
    },
})

</script>

<template>
    <div class="pa-4">
        <VCard>
            <VCardTitle class="text-h6 font-weight-bold d-flex justify-space-between align-center">
                Vacunas
                <VBtn color="primary" prepend-icon="ri-add-line" @click="router.push({ name: 'appointment-add' })">
                    Nueva Vacuna
                </VBtn>
            </VCardTitle>

            <VCardText>
                <VRow dense>
                    <VCol cols="12" md="3">
                        <VSelect v-model="type_date" :items="[
                            { name: 'Fecha de la vacuna', id: 1 },
                            { name: 'Fecha de registro', id: 2 }
                        ]" item-title="name" item-value="id" label="Tipo de Fecha" dense clearable />
                    </VCol>
                    <VCol cols="12" md="3">
                        <AppDateTimePicker v-model="dateRange" label="Rango de Fechas" :config="{ mode: 'range' }" dense
                            clearable />
                    </VCol>
                    <VCol cols="12" md="3">
                        <VSelect v-model="state_vaccination" :items="[
                            { name: 'Pendiente', id: 1 },
                            { name: 'Cancelado', id: 2 },
                            { name: 'Atendido', id: 3 }
                        ]" item-title="name" item-value="id" label="Estado de la vacuna" dense clearable />
                    </VCol>

                    <VCol cols="12" md="3">
                        <VSelect v-model="specie" :items="species" label="Especie" dense clearable />
                    </VCol>

                    <VCol cols="12" md="3">
                        <VTextField v-model="search_pets" label="Buscar por nombre" placeholder="Ej. Bobby" clearable
                            dense @keyup.enter="list" />
                    </VCol>
                    <VCol cols="12" md="3">
                        <VSelect v-model="state_pay" :items="[
                            { name: 'Pendiente', id: 1 },
                            { name: 'Parcial', id: 2 },
                            { name: 'Completo', id: 3 }
                        ]" item-title="name" item-value="id" label="Estado del pago" dense clearable />
                    </VCol>

                    <VCol cols="12" md="6" class="d-flex justify-end align-center gap-2">
                        <VBtn color="info" prepend-icon="ri-search-line" @click="list">
                            Buscar
                        </VBtn>
                        <VBtn color="secondary" prepend-icon="ri-restart-line" @click="reset">
                            Limpiar
                        </VBtn>
                        <VBtn color="success" prepend-icon="ri-file-excel-2-line" @click="downloadExcel">
                            descargar
                        </VBtn>

                    </VCol>
                </VRow>
            </VCardText>

            <VDivider />

            <VCardText>
                <VTable dense class="elevation-1">
                    <thead>
                        <tr>
                            <th>Mascota</th>
                            <th>Especie</th>
                            <th>Fecha</th>
                            <th>Veterinario</th>
                            <th>Estado de la vacuna</th>
                            <th>Estado del pago</th>
                            <th>Costo</th>
                            <th>Adelanto</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in vaccinations" :key="item.id">
                            <td>
                                <div class="d-flex align-center">
                                    <VAvatar size="32" :color="item.pet.photo ? '' : 'primary'"
                                        :variant="item.pet.photo ? 'tonal' : undefined">
                                        <VImg v-if="item.pet.photo" :src="item.pet.photo" />
                                        <span v-else class="text-sm font-weight-bold white--text">{{
                                            avatarText(item.pet.name) }}</span>
                                    </VAvatar>
                                    <div class="ms-2">
                                        <div class="font-weight-medium">{{ item.pet.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ item.pet.specie }}</td>
                            <td>{{ item.vaccination_date }}</td>
                            <td>{{ item.veterinarie.full_name }}</td>
                            <td>
                                <VChip :color="item.state === 1 ? 'warning' : item.state === 2 ? 'error' : 'success'"
                                    dark small>
                                    {{ item.state === 1 ? 'Pendiente' : item.state === 2 ? 'Cancelado' : 'Atendiendo' }}
                                </VChip>
                            </td>
                            <td>
                                <VChip
                                    :color="item.state_pay === 1 ? 'warning' : item.state_pay === 2 ? 'error' : 'success'"
                                    dark small>
                                    {{ item.state_pay === 1 ? 'Pendiente' : item.state_pay === 2 ? 'Parcial' :
                                        'Completo' }}
                                </VChip>
                            </td>
                            <td>{{ item.amount }} Bs.</td>
                            <td> Adelanto {{item.payments.reduce((sum, payment) => sum + parseFloat(payment.amount), 0)
                            }} Bs.
                            </td>
                            <td class="text-center">
                                <VBtn icon variant="text" size="small" @click="editItem(item)">
                                    <VIcon icon="ri-pencil-line" />
                                </VBtn>
                                <VBtn icon variant="text" size="small" @click="deleteItem(item)">
                                    <VIcon icon="ri-delete-bin-line" />
                                </VBtn>
                            </td>
                        </tr>
                    </tbody>
                </VTable>

                <div class="d-flex justify-end mt-4">
                    <VPagination v-model="currentPage" :length="totalPage" />
                </div>
            </VCardText>
        </VCard>

        <DeleteVaccinationDialog v-if="vaccination_selected_deleted" :appointmentSelected="vaccination_selected_deleted"
            v-model:is-dialog-visible="isDeleteVaccinationDialogVisible" @deleteVaccination="deleteVaccination" />
    </div>
</template>
<style scoped>
.v-btn__prepend {
    margin-inline: 0 !important;
}

.v-table th {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.v-table td {
    font-size: 14px;
}
</style>

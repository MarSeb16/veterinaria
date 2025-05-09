<template>
    <div>
        <VCard title="Roles y Permisos">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <VTextField v-model="searchQuery" placeholder="Buscar Rol" style="inline-size: 300px;"
                        density="compact" class="me-3" @keyup.enter="list" />
                </div>

                <VSpacer />

                <div class="d-flex gap-x-4 align-center">
                    <!-- 👉 Export button -->
                    <!-- <VBtn variant="outlined" color="secondary" prepend-icon="ri-upload-2-line">
                        Export
                    </VBtn> -->
                    <VBtn color="primary" prepend-icon="ri-add-line"
                        @click="isAddRoleDialogVisible = !isAddRoleDialogVisible">
                        Agregar Rol
                    </VBtn>
                </div>
            </VCardText>
            <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">
                <template #item.id="{ item }">
                    <span class="text-h6">{{ item.id }}</span>
                </template>
                <template #item.fullName="{ item }">
                    <div class="d-flex align-center">
                        <VAvatar size="32" :color="item.avatar ? '' : 'primary'"
                            :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
                            :variant="!item.avatar ? 'tonal' : undefined">
                            <VImg v-if="item.avatar" :src="item.avatar" />
                            <!-- <span v-else class="text-sm">{{ avatarText(item.fullName) }}</span> -->
                        </VAvatar>
                        <!-- <div class="d-flex flex-column ms-3">
                            <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName
                                }}</span>
                            <small>{{ item.post }}</small>
                        </div> -->
                    </div>
                </template>
                <template #item.action="{ item }">
                    <div class="d-flex gap-1">
                        <IconBtn size="small" @click="editItem(item)">
                            <VIcon icon="ri-pencil-line" />
                        </IconBtn>
                        <IconBtn size="small" @click="deleteItem(item)">
                            <VIcon icon="ri-delete-bin-line" />
                        </IconBtn>
                    </div>
                </template>
            </VDataTable>
            <AddRoleDialog v-model:is-dialog-visible="isAddRoleDialogVisible" @addRole="list()" />
            <EditRoleDialog v-if="role_selected" :rolSelected="role_selected" @editRole=" list()"
                v-model:is-dialog-visible="isEditRoleDialogVisible" />
            <DeleteRoleDialog v-if="role_selected_deleted" :rolSelected="role_selected_deleted"
                v-model:is-dialog-visible="isDeleteRoleDialogVisible" @deleteRole="list()" />
        </VCard>
    </div>
</template>

<script setup>
// import data from '@/views/js/datatable'
import { onMounted, ref, watch } from 'vue';
const data = ref([]);

const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Avatar', key: 'image' },
    { title: 'Nombre y apellido', key: 'full_name' },
    { title: 'Telefono', key: 'phone' },
    { title: 'tipo de documento', key: 'type_document' },
    { title: 'N° de documento', key: 'n_document' },
    { title: 'Fecha de nacimiento', key: 'birthday' },
    { title: 'Acciones', key: 'action' },


]
const searchQuery = ref(null);
const role_selected = ref(null);
const role_selected_deleted = ref(null);
const isAddRoleDialogVisible = ref(false);
const isEditRoleDialogVisible = ref(false);
const isDeleteRoleDialogVisible = ref(false);

const list = async () => {
    const resp = await $api('/role?search=' + (searchQuery.value ? searchQuery.value : ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response)
        },
    })
    console.log(resp);
    data.value = resp.roles;
}

const editItem = (item) => {
    isEditRoleDialogVisible.value = true
    role_selected.value = item

}

const deleteItem = (item) => {
    isDeleteRoleDialogVisible.value = true
    role_selected_deleted.value = item
}

onMounted(() => {
    list();
})
watch(isEditRoleDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        role_selected.value = null;
    }
})

watch(isDeleteRoleDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        role_selected_deleted.value = null;
    }
})
</script>
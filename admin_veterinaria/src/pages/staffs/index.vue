<template>
    <div>
        <VCard title="Staffs">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <VTextField v-model="searchQuery" placeholder="Buscar Staff" style="inline-size: 300px;"
                        density="compact" class="me-3" @keyup.enter="list" />
                </div>

                <VSpacer />

                <div class="d-flex gap-x-4 align-center">
                    <!-- 👉 Export button -->
                    <!-- <VBtn variant="outlined" color="secondary" prepend-icon="ri-upload-2-line">
                        Export
                    </VBtn> -->
                    <VBtn color="primary" prepend-icon="ri-add-line"
                        @click="isAddStaffDialogVisible = !isAddStaffDialogVisible">
                        Agregar Staff
                    </VBtn>
                </div>
            </VCardText>
            <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">
                <template #item.id="{ item }">
                    <span class="text-h6">{{ item.id }}</span>
                </template>
                <template #item.imagen="{ item }">
                    <div class="d-flex align-center">
                        <VAvatar size="32" :color="item.avatar ? '' : 'primary'"
                            :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
                            :variant="item.avatar ? 'tonal' : undefined">
                            <VImg v-if="item.avatar" :src="item.avatar" />
                            <span v-else class="text-sm">{{ avatarText(item.full_name) }}</span>
                        </VAvatar>
                        <!-- <div class="d-flex flex-column ms-3">
              <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.full_Name }}</span>
              <small>{{ item.post }}</small>
            </div> -->
                    </div>
                </template>
                <template #item.document_full="{ item }">
                    <div class="d-flex align-center">
                        <div class="d-flex flex-column ms-3">
                            <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.n_document
                                }}</span>
                            <small>{{ item.type_document }}</small>
                        </div>
                    </div>
                </template>
                <template #item.action="{ item }">
                    <div class="d-flex gap-1">
                        <VBtn icon variant="text" size="small" @click="editItem(item)">
                            <VIcon icon="ri-pencil-line" />
                        </VBtn>
                        <VBtn icon variant="text" size="small" @click="deleteItem(item)">
                            <VIcon icon="ri-delete-bin-line" />
                        </VBtn>
                    </div>
                </template>
            </VDataTable>
            <AddStaffDialog v-if="roles.length > 0" v-model:is-dialog-visible="isAddStaffDialogVisible" :roles="roles"
                @addUser="addUser" />
            <EditStaffDialog v-if="user_selected" :userSelected="user_selected" :roles="roles" @editUser="editUser"
                v-model:is-dialog-visible="isEditStaffDialogVisible" />
            <DeleteStaffDialog v-if="user_selected_deleted" :userSelected="user_selected_deleted"
                v-model:is-dialog-visible="isDeleteStaffDialogVisible" @deleteUser="deleteUser" />
        </VCard>
    </div>
</template>

<script setup>
// import data from '@/views/js/datatable'
import { onMounted, ref, watch } from 'vue';
const data = ref([]);
const roles = ref([]);
const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Avatar', key: 'imagen' },
    { title: 'Nombre y apellido', key: 'full_name' },
    { title: 'Rol', key: 'role_name' },
    { title: 'Correo', key: 'email' },
    { title: 'Telefono', key: 'phone' },
    { title: 'Documento', key: 'document_full' },
    // { title: 'Fecha de nacimiento', key: 'birthday' },
    { title: 'Acciones', key: 'action' },
]
const avatarText = value => {
    if (!value) return '';

    const nameArray = value.trim().split(/\s+/); // Elimina espacios extra

    if (nameArray.length < 2) return nameArray[0].charAt(0).toUpperCase(); // Si solo hay un nombre

    const firstNameInitial = nameArray[0].charAt(0).toUpperCase();
    const lastNameInitial = nameArray[2] ? nameArray[2].charAt(0).toUpperCase() : nameArray[1].charAt(0).toUpperCase();

    return firstNameInitial + lastNameInitial;
    // if (!value)
    //     return ''
    // const nameArray = value.split(' ')
    // return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}


const searchQuery = ref(null);
const user_selected = ref(null);
const user_selected_deleted = ref(null);
const isAddStaffDialogVisible = ref(false);
const isEditStaffDialogVisible = ref(false);
const isDeleteStaffDialogVisible = ref(false);

const list = async () => {
    const resp = await $api('/staffs?search=' + (searchQuery.value ? searchQuery.value : ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response)
        },
    })
    data.value = resp.users.data;
    roles.value = resp.roles;
}

const addUser = (newUser) => {
    data.value.unshift(newUser);
}

const deleteUser = async (User) => {
    let INDEX = data.value.findIndex((user) => user.id == User.id);
    if (INDEX != -1) {
        data.value.splice(INDEX, 1);
    }
}

const editUser = async (editUser) => {
    let INDEX = data.value.findIndex((user) => user.id == editUser.id);
    if (INDEX != -1) {
        data.value[INDEX] = editUser;
    }
}
const editItem = (item) => {
    isEditStaffDialogVisible.value = true
    user_selected.value = item

}

const deleteItem = (item) => {
    isDeleteStaffDialogVisible.value = true
    user_selected_deleted.value = item
}

onMounted(() => {
    list();
})
watch(isEditStaffDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        user_selected.value = null;
    }
})

watch(isDeleteStaffDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        user_selected_deleted.value = null;
    }
})
definePage({
    meta: {
        Permission: 'list_staff'
    },
})
</script>
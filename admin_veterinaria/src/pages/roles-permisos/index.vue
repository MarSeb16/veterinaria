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
            <VDataTable :headers="headers" :items="data" :items-per-page="5" :loading="isLoading" class="text-no-wrap">
                <template #item.id="{ item }">
                    <span class="text-h6">{{ item.id }}</span>
                </template>
                <template #item.permissions_pluck="{ item }">
                    <ul>
                        <li v-for="(permiso, index) in item.permissions_pluck" :key="index">{{ permiso }}</li>
                    </ul>
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
            <!-- Fíjate aquí: le pasamos addRol al evento -->
            <AddRoleDialog v-model:is-dialog-visible="isAddRoleDialogVisible" @addRole="addRol" />
            <EditRoleDialog v-if="role_selected" :rolSelected="role_selected" @editRole="list"
                v-model:is-dialog-visible="isEditRoleDialogVisible" />
            <DeleteRoleDialog v-if="role_selected_deleted" :rolSelected="role_selected_deleted"
                v-model:is-dialog-visible="isDeleteRoleDialogVisible" @deleteRole="list" />
        </VCard>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';

// refs y estados
const data = ref([]);
const isLoading = ref(false);

const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Role', key: 'name' },
    { title: 'Permisos', key: 'permissions_pluck' },
    { title: 'Fecha', key: 'created_at' },
    { title: 'Acciones', key: 'action' },
];

const searchQuery = ref(null);
const role_selected = ref(null);
const role_selected_deleted = ref(null);
const isAddRoleDialogVisible = ref(false);
const isEditRoleDialogVisible = ref(false);
const isDeleteRoleDialogVisible = ref(false);

// función para cargar lista de roles
const list = async () => {
    isLoading.value = true;
    try {
        const resp = await $api('/role?search=' + (searchQuery.value || ''), {
            method: 'GET',
            onResponseError({ response }) {
                console.error('Error en respuesta del servidor:', response);
            },
        });
        console.log('Roles cargados:', resp);
        data.value = resp.roles;
    } catch (error) {
        console.error('Error al obtener roles:', error);
    } finally {
        isLoading.value = false;
    }
};

// función para agregar rol
const addRol = async (form) => {
    isLoading.value = true;
    try {
        const resp = await $api('/role', {
            method: 'POST',
            body: form,
            onResponseError({ response }) {
                console.error('Error en respuesta del servidor:', response);
            },
        });
        if (resp.message === 200) {
            isAddRoleDialogVisible.value = false;
            $toast.success('Rol registrado correctamente');
            list(); // recarga la lista
        } else {
            $toast.error(resp.message_text || 'Error al registrar rol');
        }
    } catch (error) {
        $toast.error('Error al registrar rol');
    } finally {
        isLoading.value = false;
    }
};

const editItem = (item) => {
    isEditRoleDialogVisible.value = true;
    role_selected.value = item;
};

const deleteItem = (item) => {
    isDeleteRoleDialogVisible.value = true;
    role_selected_deleted.value = item;
};

onMounted(() => {
    list();
});

watch(isEditRoleDialogVisible, (visible) => {
    if (!visible) {
        role_selected.value = null;
    }
});

watch(isDeleteRoleDialogVisible, (visible) => {
    if (!visible) {
        role_selected_deleted.value = null;
    }
});
definePage({
    meta: {
        Permission: 'list_rol'
    },
})
</script>

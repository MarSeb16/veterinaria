<template>
    <div>
        <StaffToolbar v-model:search="searchQuery" @add="isAddDialogVisible = true" />
        <StaffTable :staffs="data" :avatarText="avatarText"
            @edit="(item) => { selectedStaff = item; isEditDialogVisible = true }"
            @delete="(item) => { selectedStaffToDelete = item; isDeleteDialogVisible = true }" />
        <AddStaffDialog v-model:is-dialog-visible="isAddDialogVisible" @added="fetchStaffs" />
        <!-- Edit/Delete dialogs -->
    </div>
</template>

<script setup>
import { useStaffs } from '@/composables/useStaffs'
import StaffToolbar from '@/components/staff/StaffToolbar.vue'
import StaffTable from '@/components/staff/StaffTable.vue'
import AddStaffDialog from '@/components/staff/dialogs/AddStaffDialog.vue'

const {
    data, searchQuery, isAddDialogVisible, isEditDialogVisible,
    isDeleteDialogVisible, selectedStaff, selectedStaffToDelete,
    fetchStaffs, avatarText
} = useStaffs()
</script>

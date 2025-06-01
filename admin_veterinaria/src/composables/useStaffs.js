import { ref, onMounted, watch } from 'vue'
import { getStaffs } from '@/services/staffService'

export function useStaffs() {
    const data = ref([])
    const searchQuery = ref('')
    const isAddDialogVisible = ref(false)
    const isEditDialogVisible = ref(false)
    const isDeleteDialogVisible = ref(false)
    const selectedStaff = ref(null)
    const selectedStaffToDelete = ref(null)

    const fetchStaffs = async () => {
        const result = await getStaffs(searchQuery.value)
        data.value = result
    }

    const avatarText = value => {
        if (!value) return ''
        const names = value.trim().split(/\s+/)
        const first = names[0]?.[0]?.toUpperCase() || ''
        const last = names[2]?.[0]?.toUpperCase() || names[1]?.[0]?.toUpperCase() || ''
        return first + last
    }

    onMounted(fetchStaffs)

    watch(isEditDialogVisible, v => !v && (selectedStaff.value = null))
    watch(isDeleteDialogVisible, v => !v && (selectedStaffToDelete.value = null))

    return {
        data, searchQuery, isAddDialogVisible, isEditDialogVisible,
        isDeleteDialogVisible, selectedStaff, selectedStaffToDelete,
        fetchStaffs, avatarText
    }
}

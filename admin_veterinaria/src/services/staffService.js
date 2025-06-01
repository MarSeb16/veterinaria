export async function getStaffs(search = '') {
    try {
        const response = await $api(`/staffs?search=${search}`)
        return response.users.data
    } catch (err) {
        console.error('Error fetching staffs:', err)
        return []
    }
}

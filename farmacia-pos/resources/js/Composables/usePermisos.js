import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermisos() {
    const page = usePage()
    const user = computed(() => page.props.auth?.user || {})
    const rol = computed(() => user.value.rol || user.value.role || '')
    const permisos = computed(() => user.value.permisos || [])

    function tieneRol(...roles) {
        return roles.includes(rol.value)
    }

    function tienePermiso(permiso) {
        if (rol.value === 'admin') return true
        return permisos.value.includes(permiso)
    }

    function puede(accion, recurso) {
        return tienePermiso(`${accion}_${recurso}`)
    }

    return {
        user,
        rol,
        permisos,
        tieneRol,
        tienePermiso,
        puede,
    }
}

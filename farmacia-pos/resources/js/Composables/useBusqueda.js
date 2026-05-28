import { ref, watch, computed } from 'vue'

export function useBusqueda(items = [], options = {}) {
    const {
        searchFields = ['nombre', 'codigo'],
        debounceMs = 300,
    } = options

    const query = ref('')
    const filtro = ref('')
    const debouncedQuery = ref('')

    let timer = null
    watch(query, (v) => {
        clearTimeout(timer)
        timer = setTimeout(() => {
            debouncedQuery.value = v
        }, debounceMs)
    })

    const resultados = computed(() => {
        const q = debouncedQuery.value.toLowerCase().trim()
        if (!q && !filtro.value) return items

        return items.filter((item) => {
            const matchFiltro = !filtro.value || item.seccion === filtro.value || item.categoria === filtro.value
            if (!q) return matchFiltro

            const matchBusqueda = searchFields.some((field) => {
                const val = item[field]
                return val && String(val).toLowerCase().includes(q)
            })
            return matchBusqueda && matchFiltro
        })
    })

    function setFiltro(val) {
        filtro.value = val
    }

    function limpiar() {
        query.value = ''
        filtro.value = ''
        debouncedQuery.value = ''
    }

    return {
        query,
        filtro,
        debouncedQuery,
        resultados,
        setFiltro,
        limpiar,
    }
}

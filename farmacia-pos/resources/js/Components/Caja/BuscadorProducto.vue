<script setup>
import { ref, computed, watch } from 'vue'
import InputSearch from '@/Components/UI/InputSearch.vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    secciones: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'update:filtroSeccion'])

const localQuery = ref(props.modelValue)
const filtroSeccion = ref('')

let debounceTimer = null
watch(localQuery, (v) => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        emit('update:modelValue', v)
    }, 300)
})

watch(() => props.modelValue, (v) => {
    if (v !== localQuery.value) localQuery.value = v
})

const seccionActiva = computed(() => filtroSeccion.value)

function toggleSeccion(seccion) {
    if (filtroSeccion.value === seccion) {
        filtroSeccion.value = ''
    } else {
        filtroSeccion.value = seccion
    }
    emit('update:filtroSeccion', filtroSeccion.value)
}

const recentSearches = ref(
    JSON.parse(localStorage.getItem('recentSearches') || '[]')
)

watch(localQuery, (v) => {
    if (v && v.length >= 2) {
        const list = [v, ...recentSearches.value.filter(s => s !== v)].slice(0, 5)
        recentSearches.value = list
        localStorage.setItem('recentSearches', JSON.stringify(list))
    }
})
</script>

<template>
    <div class="space-y-3">
        <InputSearch v-model="localQuery" placeholder="Buscar producto por nombre o código..." />

        <div v-if="secciones.length" class="flex flex-wrap gap-1.5">
            <button
                v-for="sec in secciones"
                :key="sec"
                :class="seccionActiva === sec ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="rounded-full px-3 py-1 text-xs font-medium transition"
                @click="toggleSeccion(sec)"
            >
                {{ sec }}
            </button>
        </div>

        <div v-if="!localQuery && recentSearches.length" class="text-xs text-gray-500">
            <p class="mb-1 font-medium">Búsquedas recientes:</p>
            <div class="flex flex-wrap gap-1.5">
                <button
                    v-for="s in recentSearches"
                    :key="s"
                    class="rounded-full bg-gray-50 px-2.5 py-0.5 text-gray-600 hover:bg-gray-100"
                    @click="localQuery = s"
                >
                    {{ s }}
                </button>
            </div>
        </div>
    </div>
</template>

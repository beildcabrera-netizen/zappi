<script setup>
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    talonario: { type: Object, required: true },
})
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-900">{{ talonario.nombre || `Talonario #${talonario.id}` }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Autorización: {{ talonario.nro_autorizacion || '—' }}</p>
            </div>
            <Badge :color="talonario.activo ? 'green' : 'red'" :active="talonario.activo">
                {{ talonario.activo ? 'Activo' : 'Inactivo' }}
            </Badge>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-500">Desde:</span>
                <span class="ml-1 font-medium text-gray-900">{{ talonario.desde || '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Hasta:</span>
                <span class="ml-1 font-medium text-gray-900">{{ talonario.hasta || '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Usados:</span>
                <span class="ml-1 font-medium text-gray-900">{{ talonario.usados || 0 }}</span>
            </div>
            <div>
                <span class="text-gray-500">Disponibles:</span>
                <span class="ml-1 font-medium text-gray-900">{{ (talonario.disponibles ?? (talonario.hasta - talonario.desde + 1 - (talonario.usados || 0))) || 0 }}</span>
            </div>
        </div>
        <div v-if="talonario.fecha_vencimiento" class="mt-3 text-xs text-gray-400">
            Vence: {{ talonario.fecha_vencimiento }}
        </div>
    </div>
</template>

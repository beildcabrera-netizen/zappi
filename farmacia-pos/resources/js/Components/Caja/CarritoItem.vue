<script setup>
defineProps({
    item: { type: Object, required: true },
})

const emit = defineEmits(['quitar', 'editar'])

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3">
        <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-medium text-gray-900">{{ item.nombre }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ item.presentacion }} × {{ item.cantidad }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-semibold text-gray-900">{{ formatear(item.total) }}</p>
            <p class="text-xs text-gray-400">{{ formatear(item.precio_unitario) }} c/u</p>
        </div>
        <div class="flex items-center gap-1">
            <button
                class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-blue-600"
                @click="emit('editar', item)"
                title="Editar"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button
                class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                @click="emit('quitar', item)"
                title="Quitar"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</template>

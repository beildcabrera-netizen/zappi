<script setup>
import StockBadge from '@/Components/Producto/StockBadge.vue'

const props = defineProps({
    producto: { type: Object, required: true },
})

const emit = defineEmits(['agregar'])

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <button
        class="group relative flex flex-col rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-blue-300 hover:shadow-md"
        @click="emit('agregar', producto)"
    >
        <div class="mb-2 flex items-start justify-between">
            <h3 class="text-sm font-semibold text-gray-900 line-clamp-2">{{ producto.nombre_comercial }}</h3>
            <StockBadge :stock="producto.stock" :minimo="producto.stock_minimo" />
        </div>
        <p v-if="producto.nombre_generico" class="mb-2 text-xs text-gray-500 line-clamp-1">{{ producto.nombre_generico }}</p>
        <p class="mt-auto text-lg font-bold text-blue-600">{{ formatear(producto.precio_venta_unidad) }}</p>
        <p v-if="producto.stock !== undefined" class="text-xs text-gray-400">
            Stock: {{ producto.stock }}
        </p>
        <div class="absolute inset-0 rounded-xl ring-1 ring-transparent group-hover:ring-blue-400 transition" />
    </button>
</template>

<script setup>
import Badge from '@/Components/UI/Badge.vue'

defineProps({
    venta: { type: Object, required: true },
})

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <tr class="border-b border-gray-100 hover:bg-gray-50">
        <td class="px-4 py-3 text-sm text-gray-900">#{{ venta.id }}</td>
        <td class="px-4 py-3 text-sm text-gray-600">{{ venta.created_at }}</td>
        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ formatear(venta.total) }}</td>
        <td class="px-4 py-3 text-sm capitalize">{{ venta.metodo_pago || '—' }}</td>
        <td class="px-4 py-3">
            <Badge :color="venta.anulada ? 'red' : 'green'">
                {{ venta.anulada ? 'Anulada' : 'Completada' }}
            </Badge>
        </td>
        <td class="px-4 py-3 text-sm">
            <span v-if="venta.vendedor" class="text-gray-600">{{ venta.vendedor.name || venta.vendedor }}</span>
        </td>
    </tr>
</template>

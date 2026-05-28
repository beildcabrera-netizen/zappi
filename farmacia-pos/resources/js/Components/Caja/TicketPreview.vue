<script setup>
defineProps({
    ticketData: { type: Object, required: true },
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
    <div class="mx-auto max-w-xs space-y-2 bg-white p-4 text-xs font-mono">
        <div class="text-center border-b border-dashed border-gray-300 pb-2">
            <p class="text-sm font-bold">{{ ticketData.farmacia || 'Farmacia POS' }}</p>
            <p v-if="ticketData.direccion" class="text-gray-500">{{ ticketData.direccion }}</p>
            <p v-if="ticketData.nit" class="text-gray-500">NIT: {{ ticketData.nit }}</p>
        </div>

        <div class="border-b border-dashed border-gray-300 pb-2 text-center">
            <p class="font-semibold">
                {{ ticketData.tipo_documento === 'factura' ? 'FACTURA' : 'TICKET' }}
            </p>
            <p v-if="ticketData.nro_factura">N° {{ ticketData.nro_factura }}</p>
            <p>{{ ticketData.fecha }}</p>
        </div>

        <div v-if="ticketData.cliente" class="border-b border-dashed border-gray-300 pb-2">
            <p>Cliente: {{ ticketData.cliente.nombre || ticketData.cliente.razon_social || 'Consumidor Final' }}</p>
            <p v-if="ticketData.cliente.nit">NIT: {{ ticketData.cliente.nit }}</p>
        </div>

        <div class="border-b border-dashed border-gray-300 pb-2">
            <div class="flex justify-between font-semibold mb-1">
                <span>Detalle</span>
                <span>Total</span>
            </div>
            <div v-for="(item, i) in ticketData.items" :key="i" class="flex justify-between">
                <span class="flex-1 truncate">{{ item.nombre }} × {{ item.cantidad }}</span>
                <span>{{ formatear(item.total) }}</span>
            </div>
        </div>

        <div class="space-y-0.5">
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL</span>
                <span>{{ formatear(ticketData.total) }}</span>
            </div>
            <div v-if="ticketData.metodo_pago" class="flex justify-between text-gray-500">
                <span>Pago</span>
                <span class="capitalize">{{ ticketData.metodo_pago }}</span>
            </div>
            <div v-if="ticketData.cambio" class="flex justify-between text-gray-500">
                <span>Cambio</span>
                <span>{{ formatear(ticketData.cambio) }}</span>
            </div>
        </div>

        <div v-if="ticketData.leyenda" class="text-center border-t border-dashed border-gray-300 pt-2 text-gray-500">
            {{ ticketData.leyenda }}
        </div>
        <div class="text-center pt-1 text-gray-400">
            {{ ticketData.pie || '¡Gracias por su compra!' }}
        </div>
    </div>
</template>

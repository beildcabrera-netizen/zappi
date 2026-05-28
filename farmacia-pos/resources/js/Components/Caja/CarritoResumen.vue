<script setup>
import { computed } from 'vue'
import CarritoItem from '@/Components/Caja/CarritoItem.vue'

const props = defineProps({
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
})

const emit = defineEmits(['quitar', 'editar-cantidad'])

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}

const cantidadItems = computed(() =>
    props.items.reduce((sum, item) => sum + item.cantidad, 0)
)
</script>

<template>
    <div class="flex flex-col h-full">
        <div class="flex-1 overflow-y-auto space-y-2">
            <CarritoItem
                v-for="(item, index) in items"
                :key="index"
                :item="item"
                @quitar="emit('quitar', index)"
                @editar="emit('editar-cantidad', index)"
            />
            <p v-if="!items.length" class="py-8 text-center text-sm text-gray-400">
                Carrito vacío
            </p>
        </div>

        <div class="border-t border-gray-200 pt-4 mt-4 space-y-3">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Items</span>
                <span>{{ cantidadItems }}</span>
            </div>
            <div class="flex items-center justify-between text-lg font-bold text-gray-900">
                <span>Total</span>
                <span>{{ formatear(total) }}</span>
            </div>
            <slot name="actions" />
        </div>
    </div>
</template>

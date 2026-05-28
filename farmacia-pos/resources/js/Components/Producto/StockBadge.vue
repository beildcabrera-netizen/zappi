<script setup>
import { computed } from 'vue'

const props = defineProps({
    stock: { type: Number, default: 0 },
    minimo: { type: Number, default: 0 },
})

const status = computed(() => {
    if (props.stock <= 0) return { label: 'Sin stock', class: 'bg-red-100 text-red-700' }
    if (props.stock <= (props.minimo || 5)) return { label: 'Stock bajo', class: 'bg-yellow-100 text-yellow-700' }
    return { label: 'Disponible', class: 'bg-green-100 text-green-700' }
})
</script>

<template>
    <span :class="status.class" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap">
        {{ status.label }}
    </span>
</template>

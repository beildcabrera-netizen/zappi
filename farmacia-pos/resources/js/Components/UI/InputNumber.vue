<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: { type: [Number, String], default: 0 },
    min: { type: Number, default: 0 },
    max: { type: Number, default: Infinity },
    step: { type: Number, default: 1 },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const value = computed({
    get: () => props.modelValue,
    set: (v) => {
        let n = Number(v)
        if (isNaN(n)) n = props.min
        if (n < props.min) n = props.min
        if (n > props.max) n = props.max
        emit('update:modelValue', n)
    },
})
</script>

<template>
    <input
        v-model.number="value"
        type="number"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:opacity-50"
    />
</template>

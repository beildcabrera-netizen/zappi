<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
    title: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const sizeClasses = computed(() => ({
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
}[props.size] || 'max-w-lg'))

watch(() => props.modelValue, (val) => {
    if (val) {
        document.body.classList.add('overflow-hidden')
    } else {
        document.body.classList.remove('overflow-hidden')
    }
})
</script>

<template>
    <teleport to="body">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div
                class="fixed inset-0 bg-gray-900/50 transition-opacity"
                @click="$emit('update:modelValue', false)"
            />
            <div
                :class="sizeClasses"
                class="relative z-10 mx-4 w-full rounded-xl bg-white p-6 shadow-xl"
            >
                <div v-if="title" class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    <button
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        @click="$emit('update:modelValue', false)"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <slot />
            </div>
        </div>
    </teleport>
</template>

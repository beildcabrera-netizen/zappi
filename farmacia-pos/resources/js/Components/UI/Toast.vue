<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    message: { type: String, default: '' },
    type: { type: String, default: 'success' },
    visible: { type: Boolean, default: false },
    duration: { type: Number, default: 3000 },
})

const emit = defineEmits(['update:visible'])

const typeStyles = {
    success: 'bg-green-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-blue-500 text-white',
}

let timer = null

watch(() => props.visible, (val) => {
    if (val) {
        clearTimeout(timer)
        timer = setTimeout(() => {
            emit('update:visible', false)
        }, props.duration)
    }
})
</script>

<template>
    <teleport to="body">
        <div
            v-if="visible"
            :class="typeStyles[type] || typeStyles.info"
            class="fixed right-4 top-4 z-[60] flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg transition-all"
        >
            <svg v-if="type === 'success'" class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="type === 'error'" class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium">{{ message }}</span>
            <button class="ml-2 flex-shrink-0 opacity-70 hover:opacity-100" @click="emit('update:visible', false)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </teleport>
</template>

<script setup>
defineProps({
    active: { type: Boolean, default: false },
    color: { type: String, default: 'blue' },
})

const colorClasses = {
    blue: 'bg-blue-100 text-blue-700 ring-blue-600/20',
    green: 'bg-green-100 text-green-700 ring-green-600/20',
    red: 'bg-red-100 text-red-700 ring-red-600/20',
    yellow: 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
    gray: 'bg-gray-100 text-gray-700 ring-gray-600/20',
}
</script>

<template>
    <span
        :class="[
            colorClasses[color] || colorClasses.blue,
            active ? 'ring-2 ring-offset-1' : 'ring-1 ring-inset',
        ]"
        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
    >
        <slot />
    </span>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import TalonarioCard from '@/Components/Factura/TalonarioCard.vue'

defineProps({
    talonarios: { type: Array, default: () => [] },
})
</script>

<template>
    <AppLayout>
        <Head title="Talonarios" />
        <PageHeader title="Talonarios de Facturación" description="Gestión de talonarios autorizados por SIN" />

        <div v-if="talonarios.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <TalonarioCard v-for="t in talonarios" :key="t.id" :talonario="t" />
        </div>
        <div v-else class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">
            No hay talonarios registrados
        </div>
    </AppLayout>
</template>

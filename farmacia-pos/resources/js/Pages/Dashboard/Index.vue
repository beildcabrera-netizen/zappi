<script setup>
import { usePage, Link, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import Badge from '@/Components/UI/Badge.vue'

const page = usePage()
const user = page.props.auth?.user || {}
const stats = page.props.stats || {}
const turno = page.props.turno || null
const esAdmin = user.rol === 'admin' || user.role === 'admin'

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <PageHeader title="Dashboard">
            <template #actions>
                <Link v-if="!turno" :href="route('caja.turno.apertura')">
                    <BtnPrimary>Abrir Turno</BtnPrimary>
                </Link>
                <Link :href="route('caja.punto-venta')">
                    <BtnPrimary>Ir a Punto de Venta</BtnPrimary>
                </Link>
            </template>
        </PageHeader>

        <div v-if="turno" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
            <div class="flex items-center gap-3">
                <span class="h-3 w-3 rounded-full bg-green-500"></span>
                <div>
                    <p class="font-semibold text-green-800">Turno activo #{{ turno.id }}</p>
                    <p class="text-sm text-green-600">
                        Iniciado: {{ turno.created_at }} — Caja: {{ turno.caja?.nombre || turno.caja_id }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="esAdmin" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Ventas Hoy</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ formatear(stats.ventas_hoy || 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Productos</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total_productos || 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Usuarios</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total_usuarios || 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Stock Bajo</p>
                <p class="mt-1 text-2xl font-bold text-yellow-600">{{ stats.stock_bajo || 0 }}</p>
            </div>
        </div>

        <div v-if="!esAdmin" class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <p class="text-gray-500">Bienvenido, {{ user.name }}</p>
            <p class="mt-2 text-sm text-gray-400">Use el menú lateral para navegar</p>
        </div>
    </AppLayout>
</template>

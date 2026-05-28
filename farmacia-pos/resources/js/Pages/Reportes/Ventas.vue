<script setup>
import { ref } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'

const props = defineProps({
    reporte: { type: Object, default: () => ({}) },
    ventas: { type: Array, default: () => [] },
})

const fechaDesde = ref(props.reporte.desde || '')
const fechaHasta = ref(props.reporte.hasta || '')

function filtrar() {
    router.get(route('reportes.ventas'), {
        desde: fechaDesde.value,
        hasta: fechaHasta.value,
    }, { preserveState: true })
}

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}

const resumen = props.reporte
</script>

<template>
    <AppLayout>
        <Head title="Reporte de Ventas" />
        <PageHeader title="Reporte de Ventas" />

        <div class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Desde</label>
                <input v-model="fechaDesde" type="date" class="block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Hasta</label>
                <input v-model="fechaHasta" type="date" class="block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <BtnPrimary @click="filtrar">Filtrar</BtnPrimary>
        </div>

        <div v-if="resumen.total" class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Total Ventas</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatear(resumen.total) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Cantidad</p>
                <p class="text-2xl font-bold text-gray-900">{{ resumen.cantidad || 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">Promedio</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatear(resumen.promedio || 0) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Pago</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vendedor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in ventas" :key="v.id" class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ v.id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ v.created_at }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatear(v.total) }}</td>
                        <td class="px-4 py-3 text-sm capitalize text-gray-600">{{ v.metodo_pago || '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ v.vendedor?.name || v.vendedor || '—' }}</td>
                    </tr>
                    <tr v-if="!ventas.length">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sin datos para el período</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

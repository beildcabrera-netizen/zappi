<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    diario: { type: Array, default: () => [] },
    metodosPago: { type: Array, default: () => [] },
    resumen: { type: Object, default: () => ({}) },
    productosMasVendidos: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const fechaDesde = ref(props.filters.fechaDesde || '')
const fechaHasta = ref(props.filters.fechaHasta || '')

function filtrar() {
    router.get(route('reportes.finanzas'), {
        fecha_desde: fechaDesde.value || null,
        fecha_hasta: fechaHasta.value || null,
    }, { preserveState: true, replace: true })
}

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 })
}
</script>

<template>
    <AppLayout>
        <Head title="Finanzas" />
        <PageHeader title="Finanzas" description="Reporte financiero de ventas" />

        <div class="mb-4 flex items-end gap-3">
            <div>
                <label class="mb-1 block text-xs text-gray-500">Desde</label>
                <input v-model="fechaDesde" type="date" class="block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">Hasta</label>
                <input v-model="fechaHasta" type="date" class="block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </div>
            <BtnPrimary @click="filtrar">Filtrar</BtnPrimary>
        </div>

        <div class="mb-6 grid grid-cols-4 gap-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs text-green-600">Total Ventas</p>
                <p class="text-2xl font-bold text-green-700">{{ resumen?.total_ventas || 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Monto Total</p>
                <p class="text-2xl font-bold text-blue-700">{{ formatear(resumen?.monto_total) }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs text-purple-600">Promedio</p>
                <p class="text-2xl font-bold text-purple-700">{{ formatear(resumen?.promedio_venta) }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs text-red-600">Descuentos</p>
                <p class="text-2xl font-bold text-red-700">{{ formatear(resumen?.descuentos_total) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
                <h2 class="mb-3 text-sm font-semibold text-gray-900">Ventas por Día</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Ventas</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="d in diario" :key="d.fecha" class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm text-gray-900">{{ d.fecha }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">{{ d.total_ventas }}</td>
                            <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ formatear(d.monto_total) }}</td>
                        </tr>
                        <tr v-if="!diario.length">
                            <td colspan="3" class="px-3 py-8 text-center text-sm text-gray-400">Sin datos</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold text-gray-900">Métodos de Pago</h2>
                    <div v-for="m in metodosPago" :key="m.metodo_pago" class="mb-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-700 capitalize">{{ m.metodo_pago?.replace(/_/g, ' ') }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ formatear(m.monto) }}</span>
                        </div>
                        <div class="text-xs text-gray-400">{{ m.total }} transacciones</div>
                    </div>
                    <p v-if="!metodosPago.length" class="text-sm text-gray-400">Sin datos</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold text-gray-900">Top Productos</h2>
                    <div v-for="(p, i) in productosMasVendidos" :key="p.product_id" class="mb-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-400">#{{ i + 1 }}</span>
                            <span class="text-sm text-gray-700">{{ p.product?.nombre_comercial || '—' }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ p.total_vendido }} uds</span>
                    </div>
                    <p v-if="!productosMasVendidos.length" class="text-sm text-gray-400">Sin datos</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

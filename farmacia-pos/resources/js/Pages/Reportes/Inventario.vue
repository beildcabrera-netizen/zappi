<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    productos: { type: Array, required: true },
    totales: { type: Object, required: true },
    secciones: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const filtroSeccion = ref(props.filters.seccion || '')
const filtroBajoStock = ref(props.filters.bajo_stock || false)
const filtroControlado = ref(props.filters.controlado || false)
const filtroProximoVencer = ref(props.filters.proximo_vencer || '')

function aplicarFiltros() {
    router.get(route('reportes.inventario'), {
        seccion: filtroSeccion.value || null,
        bajo_stock: filtroBajoStock.value || null,
        controlado: filtroControlado.value || null,
        proximo_vencer: filtroProximoVencer.value || null,
    }, { preserveState: true, replace: true })
}

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 })
}
</script>

<template>
    <AppLayout>
        <Head title="Inventario" />
        <PageHeader title="Inventario" description="Reporte de inventario de productos" />

        <div class="mb-6 grid grid-cols-4 gap-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs text-green-600">Total Productos</p>
                <p class="text-2xl font-bold text-green-700">{{ totales?.total_productos || 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Total Unidades</p>
                <p class="text-2xl font-bold text-blue-700">{{ totales?.total_unidades || 0 }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs text-purple-600">Valor Inventario</p>
                <p class="text-2xl font-bold text-purple-700">{{ formatear(totales?.valor_inventario) }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs text-red-600">Stock Bajo</p>
                <p class="text-2xl font-bold text-red-700">{{ totales?.productos_bajo_stock || 0 }}</p>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div v-if="secciones.length">
                <label class="mb-1 block text-xs text-gray-500">Sección</label>
                <select v-model="filtroSeccion" class="block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <option v-for="s in secciones" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>
            <label class="flex items-center gap-2 py-2">
                <input v-model="filtroBajoStock" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-700">Solo stock bajo</span>
            </label>
            <label class="flex items-center gap-2 py-2">
                <input v-model="filtroControlado" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-700">Solo controlados</span>
            </label>
            <BtnPrimary @click="aplicarFiltros">Filtrar</BtnPrimary>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Producto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Sección</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Mínimo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Precio</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Costo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="p in productos" :key="p.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ p.codigo_interno }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ p.nombre_comercial }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">{{ p.seccion || '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Badge :variant="p.stock_bajo ? 'danger' : 'success'">{{ p.stock_unidades }}</Badge>
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600">{{ p.stock_minimo_alertas || 0 }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatear(p.precio_venta_unidad) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatear(p.costo_compra_unidad) }}</td>
                    </tr>
                    <tr v-if="!productos.length">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">No hay productos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

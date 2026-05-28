<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    facturas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const fechaDesde = ref(props.filters.fecha_desde || '')
const fechaHasta = ref(props.filters.fecha_hasta || '')

function filtrar() {
    router.get(route('facturas.registro-ventas'), {
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
        <Head title="Registro de Ventas" />
        <PageHeader title="Registro de Ventas" description="Facturas emitidas con número de factura y código de control SIN" />

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

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">N° Factura</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">NIT Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Razón Social</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Emisión</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Importe</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Código Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="f in facturas.data" :key="f.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ f.numero_completo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ f.nit_cliente || '—' }}</td>
                        <td class="max-w-[200px] truncate px-4 py-3 text-sm text-gray-600">{{ f.razon_social_cliente || '—' }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">{{ f.fecha_emision }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatear(f.importe_total) }}</td>
                        <td class="px-4 py-3 text-center">
                            <Badge :variant="f.estado === 'V' ? 'success' : 'warning'">{{ f.estado === 'V' ? 'Válida' : f.estado }}</Badge>
                        </td>
                        <td class="max-w-[120px] truncate px-4 py-3 font-mono text-xs text-gray-500" :title="f.codigo_control">{{ f.codigo_control || '—' }}</td>
                    </tr>
                    <tr v-if="!facturas.data?.length">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">No hay facturas emitidas</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="facturas.links" class="mt-4 flex justify-center gap-1">
            <component
                :is="'a'"
                v-for="link in facturas.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                class="rounded-lg px-3 py-1.5 text-sm"
                :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
            />
        </div>
    </AppLayout>
</template>

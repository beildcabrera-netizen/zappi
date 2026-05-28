<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    compra: { type: Object, required: true },
})

const showRecepcion = ref(false)
const cantidades = ref({})

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 })
}

function estadoBadge(estado) {
    if (estado === 'recibida') return 'success'
    if (estado === 'pendiente') return 'warning'
    return 'info'
}

function iniciarRecepcion() {
    props.compra.items.forEach(item => {
        cantidades.value[item.id] = item.cantidad
    })
    showRecepcion.value = true
}

function recepcionar() {
    const items = Object.entries(cantidades.value).map(([id, cantidad_recibida]) => ({
        id: parseInt(id),
        cantidad_recibida: parseFloat(cantidad_recibida) || 0,
    }))

    router.post(route('compras.recepcionar', props.compra.id), { items }, {
        onSuccess: () => { showRecepcion.value = false }
    })
}
</script>

<template>
    <AppLayout>
        <Head :title="'Compra #' + compra.id" />
        <PageHeader :title="'Compra #' + compra.id">
            <template #actions>
                <BtnPrimary v-if="compra.estado === 'pendiente'" @click="iniciarRecepcion">Recepcionar</BtnPrimary>
                <BtnPrimary @click="router.get(route('compras.edit', compra.id))">Editar</BtnPrimary>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Detalle</h2>
                <dl class="mb-6 grid grid-cols-2 gap-4">
                    <div><dt class="text-xs text-gray-500">Proveedor</dt><dd class="text-sm font-medium text-gray-900">{{ compra.supplier?.nombre || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Estado</dt><dd><Badge :variant="estadoBadge(compra.estado)">{{ compra.estado }}</Badge></dd></div>
                    <div><dt class="text-xs text-gray-500">Fecha Orden</dt><dd class="text-sm font-medium text-gray-900">{{ compra.fecha_orden }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Monto Total</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(compra.monto_total) }}</dd></div>
                    <div v-if="compra.observaciones" class="col-span-2">
                        <dt class="text-xs text-gray-500">Observaciones</dt>
                        <dd class="text-sm text-gray-900">{{ compra.observaciones }}</dd>
                    </div>
                </dl>

                <h3 class="mb-3 text-sm font-semibold text-gray-900">Productos</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Producto</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">Presentación</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Costo Unit.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Subtotal</th>
                            <th v-if="compra.estado === 'recibida'" class="px-3 py-2 text-right text-xs font-medium text-gray-500">Recibido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in compra.items" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm text-gray-900">{{ item.product?.nombre_comercial || item.nombre_producto_temp || '—' }}</td>
                            <td class="px-3 py-2 text-center text-sm text-gray-500">{{ item.presentacion_comprada }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">{{ item.cantidad }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">{{ formatear(item.costo_unitario) }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">{{ formatear(item.cantidad * item.costo_unitario) }}</td>
                            <td v-if="compra.estado === 'recibida'" class="px-3 py-2 text-right text-sm text-gray-900">{{ item.cantidad_recibida }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Resumen</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-sm text-gray-500">Items</dt><dd class="text-sm font-medium text-gray-900">{{ compra.items?.length || 0 }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500">Monto</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(compra.monto_total) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <dt class="text-sm font-semibold text-gray-700">Total</dt>
                        <dd class="text-sm font-bold text-gray-900">{{ formatear(compra.monto_total) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="showRecepcion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Recepcionar Compra</h2>
                <div v-for="item in compra.items" :key="item.id" class="mb-3">
                    <label class="mb-1 block text-xs text-gray-600">{{ item.product?.nombre_comercial || 'Producto' }}</label>
                    <div class="flex items-center gap-2">
                        <input v-model.number="cantidades[item.id]" type="number" min="0" :max="item.cantidad" step="1" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <span class="text-xs text-gray-400">/ {{ item.cantidad }} {{ item.presentacion_comprada }}</span>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <BtnPrimary type="button" @click="showRecepcion = false">Cancelar</BtnPrimary>
                    <BtnPrimary type="button" @click="recepcionar">Confirmar Recepción</BtnPrimary>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

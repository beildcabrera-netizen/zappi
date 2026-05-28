<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import InputSearch from '@/Components/UI/InputSearch.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'
import Badge from '@/Components/UI/Badge.vue'
import Modal from '@/Components/UI/Modal.vue'
import StockBadge from '@/Components/Producto/StockBadge.vue'

const props = defineProps({
    productos: { type: Array, default: () => [] },
    secciones: { type: Array, default: () => [] },
})

const searchQuery = ref('')
const filtroSeccion = ref('')
const showModal = ref(false)
const editingId = ref(null)

const form = useForm({
    nombre_comercial: '',
    nombre_generico: '',
    codigo_barras: '',
    seccion: '',
    precio_venta_unidad: 0,
    precio_venta_blister: 0,
    precio_venta_caja: 0,
    unidades_por_blister: 0,
    blisters_por_caja: 0,
    stock: 0,
    stock_minimo: 5,
    requiere_receta: false,
})

const productosFiltrados = computed(() => {
    let result = props.productos
    const q = searchQuery.value.toLowerCase().trim()
    if (q) {
        result = result.filter(
            p =>
                (p.nombre_comercial && p.nombre_comercial.toLowerCase().includes(q)) ||
                (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q))
        )
    }
    if (filtroSeccion.value) {
        result = result.filter(p => p.seccion === filtroSeccion.value)
    }
    return result
})

function openCreate() {
    editingId.value = null
    form.reset()
    showModal.value = true
}

function openEdit(producto) {
    editingId.value = producto.id
    form.nombre_comercial = producto.nombre_comercial
    form.nombre_generico = producto.nombre_generico || ''
    form.codigo_barras = producto.codigo_barras || ''
    form.seccion = producto.seccion || ''
    form.precio_venta_unidad = producto.precio_venta_unidad || 0
    form.precio_venta_blister = producto.precio_venta_blister || 0
    form.precio_venta_caja = producto.precio_venta_caja || 0
    form.unidades_por_blister = producto.unidades_por_blister || 0
    form.blisters_por_caja = producto.blisters_por_caja || 0
    form.stock = producto.stock || 0
    form.stock_minimo = producto.stock_minimo || 5
    form.requiere_receta = producto.requiere_receta || false
    showModal.value = true
}

function submit() {
    if (editingId.value) {
        form.put(route('productos.update', editingId.value), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('productos.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

function eliminar(id) {
    if (confirm('¿Eliminar este producto?')) {
        router.delete(route('productos.destroy', id))
    }
}

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
        <Head title="Productos" />
        <PageHeader title="Productos">
            <template #actions>
                <BtnPrimary @click="openCreate">Nuevo Producto</BtnPrimary>
            </template>
        </PageHeader>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <InputSearch v-model="searchQuery" placeholder="Buscar producto..." />
            </div>
            <div v-if="secciones.length">
                <select
                    v-model="filtroSeccion"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">Todas las secciones</option>
                    <option v-for="s in secciones" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Sección</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Precio</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="p in productosFiltrados" :key="p.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ p.codigo_barras || '—' }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ p.nombre_comercial }}</p>
                            <p v-if="p.nombre_generico" class="text-xs text-gray-400">{{ p.nombre_generico }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ p.seccion || '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatear(p.precio_venta_unidad) }}</td>
                        <td class="px-4 py-3 text-center">
                            <StockBadge :stock="p.stock" :minimo="p.stock_minimo" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-blue-600" @click="openEdit(p)" title="Editar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" @click="eliminar(p.id)" title="Eliminar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!productosFiltrados.length">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">No hay productos</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal v-model="showModal" :title="editingId ? 'Editar Producto' : 'Nuevo Producto'" size="lg">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nombre Comercial *</label>
                        <input v-model="form.nombre_comercial" type="text" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.nombre_comercial" class="mt-1 text-xs text-red-600">{{ form.errors.nombre_comercial }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nombre Genérico</label>
                        <input v-model="form.nombre_generico" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Código de Barras</label>
                        <input v-model="form.codigo_barras" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Sección</label>
                        <select v-model="form.seccion" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Sin sección</option>
                            <option v-for="s in secciones" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Unidad</label>
                        <input v-model.number="form.precio_venta_unidad" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Blister</label>
                        <input v-model.number="form.precio_venta_blister" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Caja</label>
                        <input v-model.number="form.precio_venta_caja" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Unidades por Blister</label>
                        <input v-model.number="form.unidades_por_blister" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Blisters por Caja</label>
                        <input v-model.number="form.blisters_por_caja" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Stock</label>
                        <input v-model.number="form.stock" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Stock Mínimo</label>
                        <input v-model.number="form.stock_minimo" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input id="receta" v-model="form.requiere_receta" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <label for="receta" class="text-sm text-gray-700">Requiere receta médica</label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <BtnDanger type="button" @click="showModal = false">Cancelar</BtnDanger>
                    <BtnPrimary type="submit" :disabled="form.processing">{{ editingId ? 'Actualizar' : 'Crear' }}</BtnPrimary>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>

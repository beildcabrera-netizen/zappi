<script setup>
import { ref, computed } from 'vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BuscadorProducto from '@/Components/Caja/BuscadorProducto.vue'
import ProductoCard from '@/Components/Producto/ProductoCard.vue'
import CarritoResumen from '@/Components/Caja/CarritoResumen.vue'
import SelectorPresentacion from '@/Components/Caja/SelectorPresentacion.vue'
import CobroModal from '@/Components/Caja/CobroModal.vue'
import Modal from '@/Components/UI/Modal.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'
import { useCajaStore } from '@/Stores/cajaStore'

const page = usePage()
const store = useCajaStore()

const props = defineProps({
    productos: { type: Array, default: () => [] },
    secciones: { type: Array, default: () => [] },
    talonarios: { type: Array, default: () => [] },
    turno: { type: Object, default: null },
    puede_cobrar: { type: Boolean, default: true },
})

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}

const searchQuery = ref('')
const filtroSeccion = ref('')
const showPresentacion = ref(false)
const showCobro = ref(false)
const selectedProducto = ref(null)
const productoParaEditar = ref(null)

const productosFiltrados = computed(() => {
    let result = props.productos
    const q = searchQuery.value.toLowerCase().trim()
    if (q) {
        result = result.filter(
            p =>
                (p.nombre_comercial && p.nombre_comercial.toLowerCase().includes(q)) ||
                (p.nombre_generico && p.nombre_generico.toLowerCase().includes(q)) ||
                (p.codigo_barras && p.codigo_barras.includes(q))
        )
    }
    if (filtroSeccion.value) {
        result = result.filter(p => p.seccion === filtroSeccion.value)
    }
    return result.slice(0, 50)
})

function onAgregar(producto) {
    selectedProducto.value = producto
    showPresentacion.value = true
}

function onConfirmarPresentacion(data) {
    store.agregarItem(data.producto, data.presentacion, data.cantidad, data.receta)
    showPresentacion.value = false
    selectedProducto.value = null
}

function onEditarCantidad(index) {
    const item = store.items[index]
    const nueva = prompt('Nueva cantidad:', item.cantidad)
    if (nueva !== null) {
        store.actualizarCantidad(index, parseInt(nueva, 10) || 1)
    }
}

function onQuitarItem(index) {
    store.quitarItem(index)
}

function abrirCobro() {
    showCobro.value = true
}

function onConfirmarCobro(datosCobro) {
    router.post(route('ventas.store'), {
        items: store.items,
        total: store.total,
        ...datosCobro,
    }, {
        onSuccess: () => {
            store.limpiarCarrito()
            showCobro.value = false
        },
    })
}

function enviarACaja() {
    router.post(route('caja.enviar'), {
        items: store.items,
        total: store.total,
    }, {
        onSuccess: () => {
            store.limpiarCarrito()
        },
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Punto de Venta" />
        <div class="flex h-[calc(100vh-7rem)] gap-6">
            <div class="flex flex-1 flex-col overflow-hidden">
                <BuscadorProducto
                    v-model="searchQuery"
                    :secciones="secciones"
                    @update:filtroSeccion="filtroSeccion = $event"
                />
                <div class="mt-4 flex-1 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        <ProductoCard
                            v-for="p in productosFiltrados"
                            :key="p.id"
                            :producto="p"
                            @agregar="onAgregar"
                        />
                    </div>
                    <p v-if="!productosFiltrados.length" class="py-12 text-center text-sm text-gray-400">
                        No se encontraron productos
                    </p>
                </div>
            </div>

            <div class="flex w-96 flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-gray-900">Carrito</h2>
                <CarritoResumen
                    :items="store.items"
                    :total="store.total"
                    @quitar="onQuitarItem"
                    @editar-cantidad="onEditarCantidad"
                >
                    <template #actions>
                        <BtnPrimary
                            v-if="store.items.length && puede_cobrar"
                            class="w-full"
                            @click="abrirCobro"
                        >
                            Cobrar ({{ formatear(store.total) }})
                        </BtnPrimary>
                        <BtnPrimary
                            v-if="store.items.length && !puede_cobrar"
                            class="w-full"
                            @click="enviarACaja"
                        >
                            Enviar a Caja
                        </BtnPrimary>
                        <BtnDanger
                            v-if="store.items.length"
                            class="w-full"
                            @click="store.limpiarCarrito()"
                        >
                            Limpiar Carrito
                        </BtnDanger>
                    </template>
                </CarritoResumen>
            </div>
        </div>

        <Modal v-model="showPresentacion" title="Agregar Producto" size="sm">
            <SelectorPresentacion
                v-if="selectedProducto"
                :producto="selectedProducto"
                @confirmar="onConfirmarPresentacion"
                @cancelar="showPresentacion = false"
            />
        </Modal>

        <Modal v-model="showCobro" title="Cobro" size="md">
            <CobroModal
                :total="store.total"
                :talonarios="talonarios"
                @confirmar="onConfirmarCobro"
                @cancelar="showCobro = false"
            />
        </Modal>
    </AppLayout>
</template>

import { reactive, computed } from 'vue'

const state = reactive({
    items: [],
    cliente: null,
})

export function useCarrito() {
    const total = computed(() => state.items.reduce((sum, item) => sum + item.total, 0))
    const cantidadItems = computed(() => state.items.reduce((sum, item) => sum + item.cantidad, 0))

    function agregar(producto, presentacion, cantidad, receta = null) {
        const existente = state.items.find(
            i => i.producto_id === producto.id && i.presentacion === presentacion
        )
        if (existente) {
            existente.cantidad += cantidad
            existente.total = existente.cantidad * existente.precio_unitario
            existente.unidades_base =
                existente.cantidad *
                (presentacion === 'unidad'
                    ? 1
                    : presentacion === 'blister'
                      ? producto.unidades_por_blister
                      : producto.unidades_por_blister * producto.blisters_por_caja)
        } else {
            const precio = producto[`precio_venta_${presentacion}`]
            const unidadesBase =
                presentacion === 'unidad'
                    ? 1
                    : presentacion === 'blister'
                      ? producto.unidades_por_blister
                      : producto.unidades_por_blister * producto.blisters_por_caja
            state.items.push({
                producto_id: producto.id,
                nombre: producto.nombre_comercial,
                presentacion,
                cantidad,
                precio_unitario: precio,
                total: cantidad * precio,
                unidades_base: cantidad * unidadesBase,
                receta,
            })
        }
    }

    function quitar(index) {
        state.items.splice(index, 1)
    }

    function limpiar() {
        state.items = []
        state.cliente = null
    }

    function actualizarCantidad(index, nuevaCantidad) {
        if (nuevaCantidad <= 0) {
            quitar(index)
            return
        }
        const item = state.items[index]
        const factor = item.unidades_base / item.cantidad
        item.cantidad = nuevaCantidad
        item.total = item.cantidad * item.precio_unitario
        item.unidades_base = item.cantidad * factor
    }

    return { carrito: state, total, cantidadItems, agregar, quitar, limpiar, actualizarCantidad }
}

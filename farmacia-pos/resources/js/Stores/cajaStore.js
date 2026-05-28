import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCajaStore = defineStore('caja', () => {
    const items = ref([])
    const cliente = ref(null)
    const turnoActivo = ref(null)
    const cajaSeleccionada = ref(null)

    const total = computed(() =>
        items.value.reduce((sum, item) => sum + item.total, 0)
    )
    const cantidadItems = computed(() =>
        items.value.reduce((sum, item) => sum + item.cantidad, 0)
    )

    function agregarItem(producto, presentacion, cantidad, receta = null) {
        const existente = items.value.find(
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
            items.value.push({
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

    function quitarItem(index) {
        items.value.splice(index, 1)
    }

    function actualizarCantidad(index, nuevaCantidad) {
        if (nuevaCantidad <= 0) {
            quitarItem(index)
            return
        }
        const item = items.value[index]
        const factor = item.unidades_base / item.cantidad
        item.cantidad = nuevaCantidad
        item.total = item.cantidad * item.precio_unitario
        item.unidades_base = item.cantidad * factor
    }

    function limpiarCarrito() {
        items.value = []
        cliente.value = null
    }

    function setTurno(turno) {
        turnoActivo.value = turno
    }

    function setCaja(caja) {
        cajaSeleccionada.value = caja
    }

    return {
        items,
        cliente,
        turnoActivo,
        cajaSeleccionada,
        total,
        cantidadItems,
        agregarItem,
        quitarItem,
        actualizarCantidad,
        limpiarCarrito,
        setTurno,
        setCaja,
    }
})

<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Layout/Sidebar.vue'
import TopBar from '@/Components/Layout/TopBar.vue'

const page = usePage()
const user = page.props.auth?.user || {}
const turno = page.props.turno || null
const rol = user.rol || user.role || ''

const menu = [
    { name: 'Dashboard', href: '/dashboard', icon: 'Home', roles: ['admin', 'vendedor', 'farmaceutico'] },
    { name: 'Punto de Venta', href: '/caja/punto-venta', icon: 'ShoppingCart', roles: ['admin', 'vendedor'] },
    { name: 'Ventas', href: '/ventas', icon: 'Receipt', roles: ['admin', 'vendedor'] },
    { name: 'Productos', href: '/productos', icon: 'Package', roles: ['admin', 'farmaceutico'] },
    { name: 'Compras', href: '/compras', icon: 'Truck', roles: ['admin'] },
    { name: 'Reportes', href: '/reportes/ventas', icon: 'BarChart', roles: ['admin'] },
    { name: 'Facturación', href: '/facturas/talonarios', icon: 'FileText', roles: ['admin'] },
    { name: 'Usuarios', href: '/usuarios', icon: 'Users', roles: ['admin'] },
    { name: 'Configuración', href: '/configuracion', icon: 'Settings', roles: ['admin'] },
]

const sidebarOpen = ref(false)
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-50">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600/50 lg:hidden"
            @click="sidebarOpen = false"
        />
        <div
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 transition-transform lg:static lg:translate-x-0"
        >
            <Sidebar :menu="menu" :rol="rol" />
        </div>
        <div class="flex flex-1 flex-col overflow-hidden">
            <TopBar :user="user" :turno="turno" @toggle-sidebar="sidebarOpen = !sidebarOpen" />
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

import { BrowserRouter, Routes, Route } from "react-router-dom"
import { lazy, Suspense, useEffect } from "react"
import { useAppStore } from "@/stores/useAppStore"
import { ProtectedRoute } from "@/components/auth/ProtectedRoute"
import { AppLayout } from "@/components/layout/AppLayout"
import { ROLES } from "@/utils/constants"

const LoginPage = lazy(() => import("@/pages/Login").then((m) => ({ default: m.LoginPage })))
const DashboardPage = lazy(() => import("@/pages/Dashboard").then((m) => ({ default: m.DashboardPage })))
const PosPage = lazy(() => import("@/pages/Pos").then((m) => ({ default: m.PosPage })))
const ProductsPage = lazy(() => import("@/pages/Products").then((m) => ({ default: m.ProductsPage })))
const InventoryPage = lazy(() => import("@/pages/Inventory").then((m) => ({ default: m.InventoryPage })))
const CustomersPage = lazy(() => import("@/pages/Customers").then((m) => ({ default: m.CustomersPage })))
const SuppliersPage = lazy(() => import("@/pages/Suppliers").then((m) => ({ default: m.SuppliersPage })))
const PurchasesPage = lazy(() => import("@/pages/Purchases").then((m) => ({ default: m.PurchasesPage })))
const NewPurchasePage = lazy(() => import("@/pages/NewPurchase").then((m) => ({ default: m.NewPurchasePage })))
const FinanzasPage = lazy(() => import("@/pages/Finanzas").then((m) => ({ default: m.FinanzasPage })))
const SettingsPage = lazy(() => import("@/pages/Settings").then((m) => ({ default: m.SettingsPage })))

const Loading = () => (
  <div className="flex h-screen items-center justify-center">
    <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
  </div>
)

const routeConfig = [
  { path: "/login", element: <LoginPage /> },
  { path: "/", element: <DashboardPage />, roles: [ROLES.ADMIN, ROLES.CASHIER, ROLES.STOCK_MANAGER] },
  { path: "/pos", element: <PosPage />, roles: [ROLES.ADMIN, ROLES.CASHIER] },
  { path: "/products", element: <ProductsPage />, roles: [ROLES.ADMIN, ROLES.STOCK_MANAGER] },
  { path: "/inventory", element: <InventoryPage />, roles: [ROLES.ADMIN, ROLES.STOCK_MANAGER] },
  { path: "/purchases", element: <PurchasesPage />, roles: [ROLES.ADMIN, ROLES.STOCK_MANAGER] },
  { path: "/purchases/new", element: <NewPurchasePage />, roles: [ROLES.ADMIN, ROLES.STOCK_MANAGER] },
  { path: "/customers", element: <CustomersPage />, roles: [ROLES.ADMIN, ROLES.CASHIER] },
  { path: "/suppliers", element: <SuppliersPage />, roles: [ROLES.ADMIN, ROLES.STOCK_MANAGER] },
  { path: "/finanzas", element: <FinanzasPage />, roles: [ROLES.ADMIN] },
  { path: "/settings", element: <SettingsPage />, roles: [ROLES.ADMIN] },
]

function App() {
  const { setUser, setProfile, setLoading } = useAppStore()

  useEffect(() => {
    const stored = localStorage.getItem("farmacia-auth")
    if (stored) {
      try {
        const data = JSON.parse(stored)
        setUser(data.user)
        setProfile(data.profile)
      } catch {
        localStorage.removeItem("farmacia-auth")
      }
    }
    setLoading(false)
  }, [setUser, setProfile, setLoading])

  return (
    <BrowserRouter>
      <Suspense fallback={<Loading />}>
        <Routes>
          {routeConfig.map(({ path, element, roles }) => (
            <Route
              key={path}
              path={path}
              element={
                path === "/login" ? (
                  element
                ) : (
                  <ProtectedRoute roles={roles}>
                    <AppLayout>{element}</AppLayout>
                  </ProtectedRoute>
                )
              }
            />
          ))}
        </Routes>
      </Suspense>
    </BrowserRouter>
  )
}

export default App

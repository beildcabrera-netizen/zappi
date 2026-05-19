import { BrowserRouter, Routes, Route } from "react-router-dom"
import { useAuthStore } from "@/stores/authStore"
import { ProtectedRoute } from "@/components/auth/ProtectedRoute"
import { AppLayout } from "@/components/layout/AppLayout"
import { LoginPage } from "@/pages/Login"
import { DashboardPage } from "@/pages/Dashboard"
import { PosPage } from "@/pages/Pos"
import { ProductsPage } from "@/pages/Products"
import { InventoryPage } from "@/pages/Inventory"
import { CustomersPage } from "@/pages/Customers"
import { SuppliersPage } from "@/pages/Suppliers"
import { ReportsPage } from "@/pages/Reports"
import { SettingsPage } from "@/pages/Settings"
import { useEffect } from "react"
import { supabase } from "@/lib/supabase"

function App() {
  const { setUser, setProfile, setLoading } = useAuthStore()

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => {
      if (session?.user) {
        setUser(session.user)
        supabase
          .from('profiles')
          .select('*')
          .eq('id', session.user.id)
          .single()
          .then(({ data }) => setProfile(data))
      }
      setLoading(false)
    })
  }, [setUser, setProfile, setLoading])

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <AppLayout>
                <DashboardPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/pos"
          element={
            <ProtectedRoute roles={["admin", "cashier"]}>
              <AppLayout>
                <PosPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/products"
          element={
            <ProtectedRoute roles={["admin", "stock_manager"]}>
              <AppLayout>
                <ProductsPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/inventory"
          element={
            <ProtectedRoute roles={["admin", "stock_manager"]}>
              <AppLayout>
                <InventoryPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/customers"
          element={
            <ProtectedRoute roles={["admin", "cashier"]}>
              <AppLayout>
                <CustomersPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/suppliers"
          element={
            <ProtectedRoute roles={["admin", "stock_manager"]}>
              <AppLayout>
                <SuppliersPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/reports"
          element={
            <ProtectedRoute roles={["admin"]}>
              <AppLayout>
                <ReportsPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
        <Route
          path="/settings"
          element={
            <ProtectedRoute roles={["admin"]}>
              <AppLayout>
                <SettingsPage />
              </AppLayout>
            </ProtectedRoute>
          }
        />
      </Routes>
    </BrowserRouter>
  )
}

export default App

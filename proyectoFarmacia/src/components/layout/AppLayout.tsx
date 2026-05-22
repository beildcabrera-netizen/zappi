import { ChevronDown, LogOut, Menu, BarChart3, ShoppingCart, Package, Warehouse, Users, Truck, Settings, X, UserCircle, Receipt, DollarSign } from "lucide-react"
import { useState } from "react"
import { Link, useLocation, useNavigate } from "react-router-dom"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { useAppStore } from "@/stores/useAppStore"
import type { Role } from "@/types"

const navItems: { label: string; href: string; icon: React.ElementType; roles: Role[] }[] = [
  { label: "Dashboard", href: "/", icon: BarChart3, roles: ["admin", "cashier", "stock_manager"] },
  { label: "Punto de Venta", href: "/pos", icon: ShoppingCart, roles: ["admin", "cashier"] },
  { label: "Productos", href: "/products", icon: Package, roles: ["admin", "stock_manager"] },
  { label: "Inventario", href: "/inventory", icon: Warehouse, roles: ["admin", "stock_manager"] },
  { label: "Compras", href: "/purchases", icon: Receipt, roles: ["admin", "stock_manager"] },
  { label: "Clientes", href: "/customers", icon: Users, roles: ["admin", "cashier"] },
  { label: "Proveedores", href: "/suppliers", icon: Truck, roles: ["admin", "stock_manager"] },
  { label: "Finanzas", href: "/finanzas", icon: DollarSign, roles: ["admin"] },
  { label: "Configuración", href: "/settings", icon: Settings, roles: ["admin"] },
]

export function AppLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [profileOpen, setProfileOpen] = useState(false)
  const location = useLocation()
  const navigate = useNavigate()
  const { profile, logout, sinConfig } = useAppStore()

  const filteredNav = navItems.filter(
    (item) => profile && item.roles.includes(profile.role as Role)
  )

  const handleLogout = () => {
    logout()
    navigate("/login")
  }

  return (
    <div className="flex h-screen overflow-hidden bg-background">
      <aside
        className={cn(
          "fixed inset-y-0 left-0 z-50 w-64 transform border-r bg-card transition-transform duration-200 lg:relative lg:translate-x-0",
          sidebarOpen ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="flex h-14 items-center justify-between border-b px-4">
          <Link to="/" className="flex items-center gap-2 font-bold text-lg">
            <span className="text-primary">Farmacia</span>
            <span className="text-muted-foreground">Boliviana</span>
          </Link>
          <Button variant="ghost" size="icon" onClick={() => setSidebarOpen(false)} className="lg:hidden">
            <X className="h-5 w-5" />
          </Button>
        </div>

        <div className="flex items-center gap-2 border-b px-4 py-2">
          <div className={cn("h-2 w-2 rounded-full", sinConfig.is_online ? "bg-success" : "bg-destructive")} />
          <span className="text-xs text-muted-foreground">
            SIN: {sinConfig.is_online ? "En línea" : "Desconectado"}
          </span>
          {sinConfig.cufd_expiry && (
            <span className="text-xs text-muted-foreground ml-auto">
              CUFD: {new Date(sinConfig.cufd_expiry).toLocaleTimeString("es-BO", { hour: "2-digit", minute: "2-digit" })}
            </span>
          )}
        </div>

        <nav className="flex-1 space-y-1 p-2">
          {filteredNav.map((item) => {
            const isActive = location.pathname === item.href
            return (
              <Link
                key={item.href}
                to={item.href}
                className={cn(
                  "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                  isActive
                    ? "bg-primary text-primary-foreground"
                    : "text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                )}
                onClick={() => setSidebarOpen(false)}
              >
                <item.icon className="h-4 w-4" />
                {item.label}
              </Link>
            )
          })}
        </nav>
      </aside>

      {sidebarOpen && (
        <div className="fixed inset-0 z-40 bg-black/50 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}

      <div className="flex flex-1 flex-col overflow-hidden">
        <header className="flex h-14 items-center justify-between border-b bg-card px-4">
          <Button variant="ghost" size="icon" onClick={() => setSidebarOpen(true)} className="lg:hidden">
            <Menu className="h-5 w-5" />
          </Button>
          <div className="flex items-center gap-2 ml-auto">
            {!sinConfig.is_online && (
              <Badge variant="destructive" className="text-xs">SIN Offline</Badge>
            )}
            <div className="relative">
              <Button
                variant="ghost"
                className="flex items-center gap-2"
                onClick={() => setProfileOpen(!profileOpen)}
              >
                <UserCircle className="h-5 w-5" />
                <span className="text-sm font-medium hidden sm:inline">
                  {profile?.full_name || "Usuario"}
                </span>
                <ChevronDown className="h-4 w-4" />
              </Button>
              {profileOpen && (
                <>
                  <div className="fixed inset-0 z-10" onClick={() => setProfileOpen(false)} />
                  <div className="absolute right-0 top-full z-20 mt-1 w-56 rounded-lg border bg-card p-2 shadow-lg">
                    <div className="border-b px-2 py-1.5">
                      <p className="text-sm font-medium">{profile?.full_name}</p>
                      <p className="text-xs text-muted-foreground capitalize">{profile?.role.replace("_", " ")}</p>
                    </div>
                    <Button variant="ghost" className="w-full justify-start gap-2 mt-1" onClick={handleLogout}>
                      <LogOut className="h-4 w-4" />
                      Cerrar sesión
                    </Button>
                  </div>
                </>
              )}
            </div>
          </div>
        </header>
        <main className="flex-1 overflow-y-auto p-4 md:p-6">
          {children}
        </main>
      </div>
    </div>
  )
}

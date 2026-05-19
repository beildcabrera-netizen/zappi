import { ChevronDown, LogOut, Menu, Package, BarChart3, ShoppingCart, Users, Warehouse, Settings, X, UserCircle, Truck, Moon, Sun } from "lucide-react"
import { useState } from "react"
import { Link, useLocation, useNavigate } from "react-router-dom"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { useAuthStore } from "@/stores/authStore"
import { useTheme } from "@/hooks/useTheme"
import type { Role } from "@/types"

const navItems: { label: string; href: string; icon: React.ElementType; roles: Role[] }[] = [
  { label: "Dashboard", href: "/", icon: BarChart3, roles: ["admin", "cashier", "stock_manager"] },
  { label: "Punto de Venta", href: "/pos", icon: ShoppingCart, roles: ["admin", "cashier"] },
  { label: "Productos", href: "/products", icon: Package, roles: ["admin", "stock_manager"] },
  { label: "Inventario", href: "/inventory", icon: Warehouse, roles: ["admin", "stock_manager"] },
  { label: "Clientes", href: "/customers", icon: Users, roles: ["admin", "cashier"] },
  { label: "Proveedores", href: "/suppliers", icon: Truck, roles: ["admin", "stock_manager"] },
  { label: "Reportes", href: "/reports", icon: BarChart3, roles: ["admin"] },
  { label: "Configuración", href: "/settings", icon: Settings, roles: ["admin"] },
]

export function AppLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [profileOpen, setProfileOpen] = useState(false)
  const location = useLocation()
  const navigate = useNavigate()
  const { profile, signOut } = useAuthStore()
  const { theme, toggleTheme } = useTheme()

  const filteredNav = navItems.filter(
    (item) => profile && item.roles.includes(profile.role)
  )

  const handleSignOut = async () => {
    await signOut()
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
            <span className="text-primary">AdmiLico</span>
          </Link>
          <Button variant="ghost" size="icon" onClick={() => setSidebarOpen(false)} className="lg:hidden">
            <X className="h-5 w-5" />
          </Button>
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
          <div className="flex-1" />
          <Button variant="ghost" size="icon" onClick={toggleTheme} className="mr-1">
            {theme === "dark" ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
          </Button>
          <div className="relative">
            <Button
              variant="ghost"
              className="flex items-center gap-2"
              onClick={() => setProfileOpen(!profileOpen)}
            >
              <UserCircle className="h-5 w-5" />
              <span className="text-sm font-medium hidden sm:inline">
                {profile?.full_name || profile?.email}
              </span>
              <ChevronDown className="h-4 w-4" />
            </Button>
            {profileOpen && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setProfileOpen(false)} />
                <div className="absolute right-0 top-full z-20 mt-1 w-56 rounded-lg border bg-card p-2 shadow-lg">
                  <div className="border-b px-2 py-1.5">
                    <p className="text-sm font-medium">{profile?.full_name}</p>
                    <p className="text-xs text-muted-foreground capitalize">{profile?.role.replace('_', ' ')}</p>
                  </div>
                  <Button variant="ghost" className="w-full justify-start gap-2 mt-1" onClick={handleSignOut}>
                    <LogOut className="h-4 w-4" />
                    Cerrar sesión
                  </Button>
                </div>
              </>
            )}
          </div>
        </header>
        <main className="flex-1 overflow-y-auto p-4 md:p-6">
          {children}
        </main>
      </div>
    </div>
  )
}

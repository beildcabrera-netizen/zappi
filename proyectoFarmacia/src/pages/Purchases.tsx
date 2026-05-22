import { useEffect, useState } from "react"
import { Plus, Search, RefreshCw, Eye } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { formatCurrency, formatDate } from "@/utils/formatters"
import { supabase } from "@/lib/supabase"
import type { Purchase } from "@/types"

export function PurchasesPage() {
  const [purchases, setPurchases] = useState<Purchase[]>([])
  const [search, setSearch] = useState("")
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadPurchases() }, [])

  async function loadPurchases() {
    if (!supabase) return
    setLoading(true)
    const { data } = await supabase
      .from("purchases")
      .select("*, supplier:suppliers(name)")
      .order("created_at", { ascending: false })
    if (data) setPurchases(data as unknown as Purchase[])
    setLoading(false)
  }

  const filtered = purchases.filter((p) =>
    p.invoice_number?.toLowerCase().includes(search.toLowerCase()) ||
    (p as any).supplier?.name?.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Compras</h1>
          <p className="text-muted-foreground">Registro de compras a proveedores (Despensa)</p>
        </div>
        <Button onClick={() => window.location.href = "/purchases/new"}>
          <Plus className="h-4 w-4" />
          Nueva compra
        </Button>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input placeholder="Buscar por factura o proveedor..." className="pl-9" value={search} onChange={(e) => setSearch(e.target.value)} />
            </div>
            <Button variant="ghost" size="icon" onClick={loadPurchases}><RefreshCw className="h-4 w-4" /></Button>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b text-left text-sm text-muted-foreground">
                  <th className="px-4 py-3 font-medium">N° Factura</th>
                  <th className="px-4 py-3 font-medium">Proveedor</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">NIT</th>
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Fecha</th>
                  <th className="px-4 py-3 font-medium text-right">Total</th>
                  <th className="px-4 py-3 font-medium text-right">Crédito Fiscal</th>
                  <th className="px-4 py-3 font-medium text-right">Estado</th>
                  <th className="px-4 py-3 font-medium text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">Cargando...</td></tr>
                ) : filtered.length === 0 ? (
                  <tr><td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">No hay compras registradas</td></tr>
                ) : (
                  filtered.map((p) => (
                    <tr key={p.id} className="border-b last:border-0 hover:bg-muted/50">
                      <td className="px-4 py-3 font-medium">{p.invoice_number || "—"}</td>
                      <td className="px-4 py-3">{(p as any).supplier?.name || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden sm:table-cell">{p.nit_proveedor || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden md:table-cell">{formatDate(p.fecha_compra)}</td>
                      <td className="px-4 py-3 text-right font-medium">{formatCurrency(p.total_amount)}</td>
                      <td className="px-4 py-3 text-right text-success font-medium">{formatCurrency(p.credito_fiscal)}</td>
                      <td className="px-4 py-3 text-right">
                        <Badge variant={p.status === "completed" ? "success" : p.status === "pending" ? "warning" : "destructive"}>
                          {p.status === "completed" ? "Completada" : p.status === "pending" ? "Pendiente" : "Anulada"}
                        </Badge>
                      </td>
                      <td className="px-4 py-3 text-right">
                        <Button variant="ghost" size="icon"><Eye className="h-4 w-4" /></Button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

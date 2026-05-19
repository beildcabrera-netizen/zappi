import { useEffect, useState } from "react"
import { Plus, Search, RefreshCw, ArrowUpDown, PackageOpen } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog"
import { Select } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { Badge } from "@/components/ui/badge"
import { Label } from "@/components/ui/card"
import { supabase } from "@/lib/supabase"
import { useAuthStore } from "@/stores/authStore"
import type { Product, InventoryLog } from "@/types"

export function InventoryPage() {
  const [products, setProducts] = useState<Product[]>([])
  const [logs, setLogs] = useState<InventoryLog[]>([])
  const [search, setSearch] = useState("")
  const [stockFilter, setStockFilter] = useState<"all" | "low" | "out">("all")
  const [showEntry, setShowEntry] = useState(false)
  const [showAdjustment, setShowAdjustment] = useState(false)
  const [logView, setLogView] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadData()
  }, [])

  async function loadData() {
    setLoading(true)
    const { data: products } = await supabase
      .from("products")
      .select("*, category:categories(name)")
      .eq("is_active", true)
      .order("name")
    if (products) setProducts(products as unknown as Product[])

    const { data: logs } = await supabase
      .from("inventory_logs")
      .select("*, product:products(name)")
      .order("created_at", { ascending: false })
      .limit(50)
    if (logs) setLogs(logs as unknown as InventoryLog[])
    setLoading(false)
  }

  const filtered = products.filter((p) => {
    const matchesSearch = p.name.toLowerCase().includes(search.toLowerCase())
    if (stockFilter === "low") return matchesSearch && p.stock_quantity > 0 && p.stock_quantity <= p.min_stock_alert
    if (stockFilter === "out") return matchesSearch && p.stock_quantity === 0
    return matchesSearch
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Inventario</h1>
          <p className="text-muted-foreground">Control de existencias y movimientos</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => setLogView(true)}>
            <ArrowUpDown className="h-4 w-4" />
            Historial
          </Button>
          <Button variant="outline" onClick={() => setShowAdjustment(true)}>
            <PackageOpen className="h-4 w-4" />
            Ajustar
          </Button>
          <Button onClick={() => setShowEntry(true)}>
            <Plus className="h-4 w-4" />
            Entrada de stock
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex flex-col gap-3 sm:flex-row">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Buscar producto..."
                className="pl-9"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="flex gap-2">
              {(["all", "low", "out"] as const).map((f) => (
                <Button
                  key={f}
                  variant={stockFilter === f ? "default" : "outline"}
                  size="sm"
                  onClick={() => setStockFilter(f)}
                >
                  {f === "all" ? "Todos" : f === "low" ? "Stock bajo" : "Sin stock"}
                </Button>
              ))}
            </div>
            <Button variant="ghost" size="icon" onClick={loadData}>
              <RefreshCw className="h-4 w-4" />
            </Button>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b text-left text-sm text-muted-foreground">
                  <th className="px-4 py-3 font-medium">Producto</th>
                  <th className="px-4 py-3 font-medium">Categoría</th>
                  <th className="px-4 py-3 font-medium text-right">Stock actual</th>
                  <th className="px-4 py-3 font-medium text-right">Stock mínimo</th>
                  <th className="px-4 py-3 font-medium text-right">Estado</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-12 text-center text-muted-foreground">
                      Cargando...
                    </td>
                  </tr>
                ) : filtered.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-12 text-center text-muted-foreground">
                      No hay productos
                    </td>
                  </tr>
                ) : (
                  filtered.map((p) => (
                    <tr key={p.id} className="border-b last:border-0 hover:bg-muted/50">
                      <td className="px-4 py-3 font-medium">{p.name}</td>
                      <td className="px-4 py-3 text-sm text-muted-foreground">
                        {(p as any).category?.name || "—"}
                      </td>
                      <td className="px-4 py-3 text-right font-bold">{p.stock_quantity}</td>
                      <td className="px-4 py-3 text-right">{p.min_stock_alert}</td>
                      <td className="px-4 py-3 text-right">
                        {p.stock_quantity === 0 ? (
                          <Badge variant="destructive">Sin stock</Badge>
                        ) : p.stock_quantity <= p.min_stock_alert ? (
                          <Badge variant="warning">Stock bajo</Badge>
                        ) : (
                          <Badge variant="success">Disponible</Badge>
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <EntryDialog
        open={showEntry}
        onOpenChange={setShowEntry}
        products={products}
        onSuccess={loadData}
      />

      <AdjustmentDialog
        open={showAdjustment}
        onOpenChange={setShowAdjustment}
        products={products}
        onSuccess={loadData}
      />

      <LogDialog open={logView} onOpenChange={setLogView} logs={logs} />
    </div>
  )
}

function EntryDialog({
  open, onOpenChange, products, onSuccess,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  products: Product[]
  onSuccess: () => void
}) {
  const { profile } = useAuthStore()
  const [productId, setProductId] = useState("")
  const [quantity, setQuantity] = useState(0)
  const [notes, setNotes] = useState("")
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!productId || quantity <= 0 || !profile) return
    setSubmitting(true)
    const product = products.find((p) => p.id === productId)
    if (!product) return

    const newStock = product.stock_quantity + quantity
    await supabase.from("products").update({ stock_quantity: newStock }).eq("id", productId)
    await supabase.from("inventory_logs").insert({
      product_id: productId,
      type: "entry",
      quantity,
      previous_stock: product.stock_quantity,
      new_stock: newStock,
      notes: notes || null,
      created_by: profile.id,
    })
    setSubmitting(false)
    onOpenChange(false)
    setProductId("")
    setQuantity(0)
    setNotes("")
    onSuccess()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Entrada de stock</DialogTitle>
        <DialogDescription>Registra una entrada de mercancía</DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="space-y-2">
          <Label>Producto</Label>
          <Select
            value={productId}
            onChange={(e) => setProductId(e.target.value)}
            options={products.map((p) => ({ value: p.id, label: `${p.name} (Stock: ${p.stock_quantity})` }))}
            placeholder="Seleccionar producto"
            required
          />
        </div>
        <div className="space-y-2">
          <Label>Cantidad a agregar</Label>
          <Input type="number" min="1" value={quantity || ""} onChange={(e) => setQuantity(Number(e.target.value))} required />
        </div>
        <div className="space-y-2">
          <Label>Notas (opcional)</Label>
          <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Ej: Factura #123, Proveedor X" />
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={submitting}>{submitting ? "Guardando..." : "Registrar entrada"}</Button>
        </DialogFooter>
      </form>
    </Dialog>
  )
}

function AdjustmentDialog({
  open, onOpenChange, products, onSuccess,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  products: Product[]
  onSuccess: () => void
}) {
  const { profile } = useAuthStore()
  const [productId, setProductId] = useState("")
  const [type, setType] = useState<"adjustment" | "loss">("adjustment")
  const [quantity, setQuantity] = useState(0)
  const [notes, setNotes] = useState("")
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!productId || quantity <= 0 || !profile) return
    setSubmitting(true)
    const product = products.find((p) => p.id === productId)
    if (!product) return

    const adjustedQty = type === "loss" ? -quantity : quantity
    const newStock = Math.max(0, product.stock_quantity + adjustedQty)

    await supabase.from("products").update({ stock_quantity: newStock }).eq("id", productId)
    await supabase.from("inventory_logs").insert({
      product_id: productId,
      type,
      quantity: adjustedQty,
      previous_stock: product.stock_quantity,
      new_stock: newStock,
      notes: notes || null,
      created_by: profile.id,
    })
    setSubmitting(false)
    onOpenChange(false)
    setProductId("")
    setQuantity(0)
    setNotes("")
    onSuccess()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Ajuste de inventario</DialogTitle>
        <DialogDescription>Corregir stock por merma, rotura o ajuste</DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="space-y-2">
          <Label>Producto</Label>
          <Select
            value={productId}
            onChange={(e) => setProductId(e.target.value)}
            options={products.map((p) => ({ value: p.id, label: `${p.name} (Stock: ${p.stock_quantity})` }))}
            placeholder="Seleccionar producto"
            required
          />
        </div>
        <div className="flex gap-2">
          {(["adjustment", "loss"] as const).map((t) => (
            <Button
              key={t}
              type="button"
              variant={type === t ? "default" : "outline"}
              className="flex-1"
              onClick={() => setType(t)}
            >
              {t === "adjustment" ? "Ajuste" : "Merma/Pérdida"}
            </Button>
          ))}
        </div>
        <div className="space-y-2">
          <Label>Cantidad {type === "loss" ? "a restar" : "de ajuste"}</Label>
          <Input type="number" min="1" value={quantity || ""} onChange={(e) => setQuantity(Number(e.target.value))} required />
        </div>
        <div className="space-y-2">
          <Label>Motivo</Label>
          <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Ej: Botella rota, merma, etc." required />
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={submitting}>{submitting ? "Guardando..." : "Confirmar ajuste"}</Button>
        </DialogFooter>
      </form>
    </Dialog>
  )
}

function LogDialog({
  open, onOpenChange, logs,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  logs: InventoryLog[]
}) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Historial de movimientos</DialogTitle>
        <DialogDescription>Últimos 50 movimientos de inventario</DialogDescription>
      </DialogHeader>
      <div className="max-h-96 overflow-y-auto space-y-1">
        {logs.length === 0 ? (
          <p className="text-sm text-muted-foreground text-center py-4">Sin movimientos</p>
        ) : (
          logs.map((log) => (
            <div key={log.id} className="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
              <div>
                <p className="font-medium">{(log as any).product?.name || "Producto"}</p>
                <p className="text-xs text-muted-foreground">
                  {new Date(log.created_at).toLocaleString("es-MX")}
                  {log.notes && ` — ${log.notes}`}
                </p>
              </div>
              <div className="text-right">
                <Badge
                  variant={
                    log.type === "entry" ? "success" :
                    log.type === "sale" ? "default" :
                    "destructive"
                  }
                >
                  {log.type === "entry" ? "Entrada" :
                   log.type === "sale" ? "Venta" :
                   log.type === "loss" ? "Pérdida" : "Ajuste"}
                </Badge>
                <p className={`text-sm font-bold mt-1 ${log.quantity > 0 ? "text-green-600" : "text-red-600"}`}>
                  {log.quantity > 0 ? "+" : ""}{log.quantity}
                </p>
              </div>
            </div>
          ))
        )}
      </div>
    </Dialog>
  )
}

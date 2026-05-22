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
import { formatDate, formatDateTime } from "@/utils/formatters"
import { supabase } from "@/lib/supabase"
import { useAppStore } from "@/stores/useAppStore"
import type { Product, InventoryLog, ProductBatch } from "@/types"

export function InventoryPage() {
  const [products, setProducts] = useState<Product[]>([])
  const [batches, setBatches] = useState<ProductBatch[]>([])
  const [logs, setLogs] = useState<InventoryLog[]>([])
  const [search, setSearch] = useState("")
  const [stockFilter, setStockFilter] = useState<"all" | "low" | "out">("all")
  const [showEntry, setShowEntry] = useState(false)
  const [showAdjustment, setShowAdjustment] = useState(false)
  const [showBatches, setShowBatches] = useState(false)
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null)
  const [logView, setLogView] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadData() }, [])

  async function loadData() {
    if (!supabase) return
    setLoading(true)
    const { data: products } = await supabase
      .from("products")
      .select("*, category:categories(name)")
      .eq("is_active", true)
      .order("name")
    if (products) setProducts(products as unknown as Product[])

    const { data: batches } = await supabase
      .from("product_batches")
      .select("*, product:products(name)")
      .order("expiry_date")
    if (batches) setBatches(batches as unknown as ProductBatch[])

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
              <Input placeholder="Buscar producto..." className="pl-9" value={search} onChange={(e) => setSearch(e.target.value)} />
            </div>
            <div className="flex gap-2">
              {(["all", "low", "out"] as const).map((f) => (
                <Button key={f} variant={stockFilter === f ? "default" : "outline"} size="sm" onClick={() => setStockFilter(f)}>
                  {f === "all" ? "Todos" : f === "low" ? "Stock bajo" : "Sin stock"}
                </Button>
              ))}
            </div>
            <Button variant="ghost" size="icon" onClick={loadData}><RefreshCw className="h-4 w-4" /></Button>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b text-left text-sm text-muted-foreground">
                  <th className="px-4 py-3 font-medium">Producto</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">Categoría</th>
                  <th className="px-4 py-3 font-medium text-right">Stock total</th>
                  <th className="px-4 py-3 font-medium text-right">Stock mínimo</th>
                  <th className="px-4 py-3 font-medium text-right">Lotes</th>
                  <th className="px-4 py-3 font-medium text-right">Estado</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">Cargando...</td></tr>
                ) : filtered.length === 0 ? (
                  <tr><td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">No hay productos</td></tr>
                ) : (
                  filtered.map((p) => {
                    const productBatches = batches.filter((b) => b.product_id === p.id)
                    const expiringBatches = productBatches.filter(
                      (b) => new Date(b.expiry_date) <= new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
                    )
                    return (
                      <tr key={p.id} className="border-b last:border-0 hover:bg-muted/50">
                        <td className="px-4 py-3 font-medium">{p.name}</td>
                        <td className="px-4 py-3 text-sm text-muted-foreground hidden sm:table-cell">
                          {(p as any).category?.name || "—"}
                        </td>
                        <td className="px-4 py-3 text-right font-bold">{p.stock_quantity}</td>
                        <td className="px-4 py-3 text-right">{p.min_stock_alert}</td>
                        <td className="px-4 py-3 text-right">
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => { setSelectedProduct(p); setShowBatches(true) }}
                          >
                            {productBatches.length} lotes
                            {expiringBatches.length > 0 && (
                              <span className="text-destructive ml-1">⚠{expiringBatches.length}</span>
                            )}
                          </Button>
                        </td>
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
                    )
                  })
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <EntryDialog open={showEntry} onOpenChange={setShowEntry} products={products} onSuccess={loadData} />
      <AdjustmentDialog open={showAdjustment} onOpenChange={setShowAdjustment} products={products} onSuccess={loadData} />
      <BatchesDialog open={showBatches} onOpenChange={setShowBatches} product={selectedProduct} batches={batches} />
      <LogDialog open={logView} onOpenChange={setLogView} logs={logs} />
    </div>
  )
}

function EntryDialog({ open, onOpenChange, products, onSuccess }: {
  open: boolean; onOpenChange: (v: boolean) => void; products: Product[]; onSuccess: () => void
}) {
  const { profile } = useAppStore()
  const [productId, setProductId] = useState("")
  const [batchNumber, setBatchNumber] = useState("")
  const [expiryDate, setExpiryDate] = useState("")
  const [quantity, setQuantity] = useState(0)
  const [costPrice, setCostPrice] = useState(0)
  const [notes, setNotes] = useState("")
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!productId || quantity <= 0 || !profile || !supabase) return
    setSubmitting(true)
    const product = products.find((p) => p.id === productId)
    if (!product) return

    const newStock = product.stock_quantity + quantity

    await supabase.from("products").update({ stock_quantity: newStock }).eq("id", productId)
    await supabase.from("product_batches").insert({
      product_id: productId,
      batch_number: batchNumber || `LOTE-${Date.now()}`,
      expiry_date: expiryDate || null,
      stock_quantity: quantity,
      cost_price: costPrice || product.cost_price,
    })
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
    setProductId(""); setBatchNumber(""); setExpiryDate(""); setQuantity(0); setCostPrice(0); setNotes("")
    onSuccess()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Entrada de stock</DialogTitle>
        <DialogDescription>Registra una entrada de mercancía con lote y vencimiento</DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="space-y-2">
          <Label>Producto</Label>
          <Select value={productId} onChange={(e) => setProductId(e.target.value)}
            options={products.map((p) => ({ value: p.id, label: `${p.name} (Stock: ${p.stock_quantity})` }))}
            placeholder="Seleccionar producto" required />
        </div>
        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label>N° de Lote</Label>
            <Input value={batchNumber} onChange={(e) => setBatchNumber(e.target.value)} placeholder="LOTE-001" />
          </div>
          <div className="space-y-2">
            <Label>Fecha de vencimiento</Label>
            <Input type="date" value={expiryDate} onChange={(e) => setExpiryDate(e.target.value)} />
          </div>
        </div>
        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label>Cantidad</Label>
            <Input type="number" min="1" value={quantity || ""} onChange={(e) => setQuantity(Number(e.target.value))} required />
          </div>
          <div className="space-y-2">
            <Label>Costo unitario (Bs)</Label>
            <Input type="number" step="0.01" min="0" value={costPrice || ""} onChange={(e) => setCostPrice(Number(e.target.value))} />
          </div>
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

function AdjustmentDialog({ open, onOpenChange, products, onSuccess }: {
  open: boolean; onOpenChange: (v: boolean) => void; products: Product[]; onSuccess: () => void
}) {
  const { profile } = useAppStore()
  const [productId, setProductId] = useState("")
  const [type, setType] = useState<"adjustment" | "loss">("adjustment")
  const [quantity, setQuantity] = useState(0)
  const [notes, setNotes] = useState("")
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!productId || quantity <= 0 || !profile || !supabase) return
    setSubmitting(true)
    const product = products.find((p) => p.id === productId)
    if (!product) return

    const adjustedQty = type === "loss" ? -quantity : quantity
    const newStock = Math.max(0, product.stock_quantity + adjustedQty)

    await supabase.from("products").update({ stock_quantity: newStock }).eq("id", productId)
    await supabase.from("inventory_logs").insert({
      product_id: productId, type, quantity: adjustedQty,
      previous_stock: product.stock_quantity, new_stock: newStock,
      notes: notes || null, created_by: profile.id,
    })
    setSubmitting(false)
    onOpenChange(false)
    setProductId(""); setQuantity(0); setNotes("")
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
          <Select value={productId} onChange={(e) => setProductId(e.target.value)}
            options={products.map((p) => ({ value: p.id, label: `${p.name} (Stock: ${p.stock_quantity})` }))}
            placeholder="Seleccionar producto" required />
        </div>
        <div className="flex gap-2">
          {(["adjustment", "loss"] as const).map((t) => (
            <Button key={t} type="button" variant={type === t ? "default" : "outline"} className="flex-1" onClick={() => setType(t)}>
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
          <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Ej: Producto vencido, rotura, etc." required />
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={submitting}>{submitting ? "Guardando..." : "Confirmar ajuste"}</Button>
        </DialogFooter>
      </form>
    </Dialog>
  )
}

function BatchesDialog({ open, onOpenChange, product, batches }: {
  open: boolean; onOpenChange: (v: boolean) => void; product: Product | null; batches: ProductBatch[]
}) {
  const productBatches = batches.filter((b) => b.product_id === product?.id)

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Lotes: {product?.name}</DialogTitle>
        <DialogDescription>Detalle de lotes en inventario</DialogDescription>
      </DialogHeader>
      <div className="space-y-2">
        {productBatches.length === 0 ? (
          <p className="text-sm text-muted-foreground text-center py-4">Sin lotes registrados</p>
        ) : (
          productBatches.map((b) => {
            const isExpired = new Date(b.expiry_date) < new Date()
            const isExpiring = !isExpired && new Date(b.expiry_date) <= new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
            return (
              <div key={b.id} className="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
                <div>
                  <p className="font-medium">{b.batch_number}</p>
                  <p className="text-xs text-muted-foreground">
                    Vence: {formatDate(b.expiry_date)}
                    {isExpired && " — VENCIDO"}
                    {isExpiring && " — Próximo a vencer"}
                  </p>
                </div>
                <div className="text-right">
                  <Badge variant={isExpired ? "destructive" : isExpiring ? "warning" : "success"}>
                    {b.stock_quantity} uds.
                  </Badge>
                  <p className="text-xs text-muted-foreground mt-1">Costo: Bs {Number(b.cost_price).toFixed(2)}</p>
                </div>
              </div>
            )
          })
        )}
      </div>
    </Dialog>
  )
}

function LogDialog({ open, onOpenChange, logs }: {
  open: boolean; onOpenChange: (v: boolean) => void; logs: InventoryLog[]
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
                  {formatDateTime(log.created_at)}
                  {log.notes && ` — ${log.notes}`}
                </p>
              </div>
              <div className="text-right">
                <Badge variant={log.type === "entry" ? "success" : log.type === "sale" ? "default" : "destructive"}>
                  {log.type === "entry" ? "Entrada" : log.type === "sale" ? "Venta" : log.type === "loss" ? "Pérdida" : "Ajuste"}
                </Badge>
                <p className={`text-sm font-bold mt-1 ${log.quantity > 0 ? "text-success" : "text-destructive"}`}>
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

import { useEffect, useState, useCallback } from "react"
import { useNavigate } from "react-router-dom"
import { Trash2, Search } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select } from "@/components/ui/select"
import { Label } from "@/components/ui/card"
import { formatCurrency } from "@/utils/formatters"
import { IVA_RATE } from "@/utils/constants"
import { supabase } from "@/lib/supabase"
import { useAppStore } from "@/stores/useAppStore"
import type { Product, Supplier } from "@/types"

interface PurchaseLine {
  product_id: string
  product_name: string
  batch_number: string
  expiry_date: string
  quantity: number
  unit_cost: number
  subtotal: number
}

export function NewPurchasePage() {
  const navigate = useNavigate()
  const { profile } = useAppStore()
  const [suppliers, setSuppliers] = useState<Supplier[]>([])
  const [products, setProducts] = useState<Product[]>([])
  const [supplierId, setSupplierId] = useState("")
  const [nitProveedor, setNitProveedor] = useState("")
  const [invoiceNumber, setInvoiceNumber] = useState("")
  const [fechaCompra, setFechaCompra] = useState(new Date().toISOString().split("T")[0])
  const [lines, setLines] = useState<PurchaseLine[]>([])
  const [searchTerm, setSearchTerm] = useState("")
  const [searchResults, setSearchResults] = useState<Product[]>([])
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    loadSuppliers()
    loadProducts()
  }, [])

  useEffect(() => {
    if (supplierId) {
      const supplier = suppliers.find((s) => s.id === supplierId)
      if (supplier?.nit) setNitProveedor(supplier.nit)
    }
  }, [supplierId, suppliers])

  const searchProducts = useCallback(async (query: string) => {
    if (!supabase || !query.trim()) { setSearchResults([]); return }
    const { data } = await supabase
      .from("products")
      .select("id, name, cost_price, stock_quantity")
      .eq("is_active", true)
      .or(`name.ilike.%${query}%,barcode.ilike.%${query}%`)
      .limit(10)
    if (data) setSearchResults(data as Product[])
  }, [])

  useEffect(() => {
    const timer = setTimeout(() => searchProducts(searchTerm), 200)
    return () => clearTimeout(timer)
  }, [searchTerm, searchProducts])

  async function loadSuppliers() {
    if (!supabase) return
    const { data } = await supabase.from("suppliers").select("*").eq("is_active", true).order("name")
    if (data) setSuppliers(data)
  }

  async function loadProducts() {
    if (!supabase) return
    const { data } = await supabase.from("products").select("*").eq("is_active", true).order("name")
    if (data) setProducts(data)
  }

  function addLine(product: Product) {
    const exists = lines.find((l) => l.product_id === product.id)
    if (exists) {
      setLines(lines.map((l) =>
        l.product_id === product.id
          ? { ...l, quantity: l.quantity + 1, subtotal: (l.quantity + 1) * l.unit_cost }
          : l
      ))
    } else {
      setLines([...lines, {
        product_id: product.id,
        product_name: product.name,
        batch_number: "",
        expiry_date: "",
        quantity: 1,
        unit_cost: Number(product.cost_price),
        subtotal: Number(product.cost_price),
      }])
    }
    setSearchTerm("")
    setSearchResults([])
  }

  function updateLine(index: number, field: keyof PurchaseLine, value: string | number) {
    setLines(lines.map((line, i) => {
      if (i !== index) return line
      const updated = { ...line, [field]: value }
      if (field === "quantity" || field === "unit_cost") {
        updated.subtotal = Number(updated.quantity) * Number(updated.unit_cost)
      }
      return updated
    }))
  }

  function removeLine(index: number) {
    setLines(lines.filter((_, i) => i !== index))
  }

  const totalAmount = lines.reduce((sum, l) => sum + l.subtotal, 0)
  const creditoFiscal = totalAmount * IVA_RATE

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!supplierId || lines.length === 0 || !profile || !supabase) return
    setSubmitting(true)

    try {
      const { data: purchase, error } = await supabase
        .from("purchases")
        .insert({
          supplier_id: supplierId,
          invoice_number: invoiceNumber,
          nit_proveedor: nitProveedor,
          fecha_compra: fechaCompra,
          total_amount: totalAmount,
          tax_amount: creditoFiscal,
          credito_fiscal: creditoFiscal,
          status: "completed",
          created_by: profile.id,
        })
        .select()
        .single()

      if (error || !purchase) throw error

      for (const line of lines) {
        const product = products.find((p) => p.id === line.product_id)
        const newStock = (product?.stock_quantity || 0) + line.quantity

        await supabase.from("purchase_items").insert({
          purchase_id: purchase.id,
          product_id: line.product_id,
          batch_number: line.batch_number || `LOTE-${Date.now()}`,
          expiry_date: line.expiry_date || null,
          quantity: line.quantity,
          unit_cost: line.unit_cost,
          subtotal: line.subtotal,
        })

        await supabase.from("products").update({ stock_quantity: newStock }).eq("id", line.product_id)
        await supabase.from("product_batches").insert({
          product_id: line.product_id,
          batch_number: line.batch_number || `LOTE-${Date.now()}`,
          expiry_date: line.expiry_date || null,
          stock_quantity: line.quantity,
          cost_price: line.unit_cost,
        })
        await supabase.from("inventory_logs").insert({
          product_id: line.product_id, type: "entry", quantity: line.quantity,
          previous_stock: product?.stock_quantity || 0, new_stock: newStock,
          notes: `Compra: ${invoiceNumber}`,
          created_by: profile.id,
        })
      }

      navigate("/purchases")
    } catch (err: any) {
      alert("Error al registrar compra: " + err.message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Nueva Compra</h1>
        <p className="text-muted-foreground">Registra una compra a proveedor</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Datos del proveedor</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label>Proveedor *</Label>
                <Select value={supplierId} onChange={(e) => setSupplierId(e.target.value)}
                  options={suppliers.map((s) => ({ value: s.id, label: s.name }))}
                  placeholder="Seleccionar proveedor" required />
              </div>
              <div className="space-y-2">
                <Label>NIT Proveedor</Label>
                <Input value={nitProveedor} onChange={(e) => setNitProveedor(e.target.value)} placeholder="123456022" />
              </div>
              <div className="space-y-2">
                <Label>N° Factura Proveedor</Label>
                <Input value={invoiceNumber} onChange={(e) => setInvoiceNumber(e.target.value)} placeholder="F-2026-001" />
              </div>
              <div className="space-y-2">
                <Label>Fecha de compra</Label>
                <Input type="date" value={fechaCompra} onChange={(e) => setFechaCompra(e.target.value)} required />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="text-sm font-medium">Productos</CardTitle>
            <div className="relative w-72">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder="Buscar y agregar producto..." className="pl-9" />
              {searchResults.length > 0 && (
                <div className="absolute top-full left-0 right-0 z-10 mt-1 rounded-md border bg-card shadow-lg max-h-48 overflow-y-auto">
                  {searchResults.map((p) => (
                    <button key={p.id} type="button" className="flex w-full items-center justify-between px-3 py-2 text-sm hover:bg-accent transition-colors" onClick={() => addLine(p)}>
                      <span>{p.name}</span>
                      <span className="text-muted-foreground">{formatCurrency(Number(p.cost_price))}</span>
                    </button>
                  ))}
                </div>
              )}
            </div>
          </CardHeader>
          <CardContent className="p-0">
            {lines.length === 0 ? (
              <p className="text-sm text-muted-foreground text-center py-8">Agrega productos a la compra</p>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b text-left text-sm text-muted-foreground">
                      <th className="px-4 py-3 font-medium">Producto</th>
                      <th className="px-4 py-3 font-medium">Lote</th>
                      <th className="px-4 py-3 font-medium hidden sm:table-cell">Vencimiento</th>
                      <th className="px-4 py-3 font-medium text-right">Cantidad</th>
                      <th className="px-4 py-3 font-medium text-right">P. Unit.</th>
                      <th className="px-4 py-3 font-medium text-right">Subtotal</th>
                      <th className="px-4 py-3 font-medium text-right"></th>
                    </tr>
                  </thead>
                  <tbody>
                    {lines.map((line, i) => (
                      <tr key={i} className="border-b last:border-0">
                        <td className="px-4 py-3 font-medium text-sm">{line.product_name}</td>
                        <td className="px-4 py-3">
                          <Input value={line.batch_number} onChange={(e) => updateLine(i, "batch_number", e.target.value)} placeholder="Lote" className="h-7 text-xs w-28" />
                        </td>
                        <td className="px-4 py-3 hidden sm:table-cell">
                          <Input type="date" value={line.expiry_date} onChange={(e) => updateLine(i, "expiry_date", e.target.value)} className="h-7 text-xs w-32" />
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Input type="number" min="1" value={line.quantity || ""} onChange={(e) => updateLine(i, "quantity", Number(e.target.value))} className="h-7 text-xs w-20 text-right" />
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Input type="number" step="0.01" min="0" value={line.unit_cost || ""} onChange={(e) => updateLine(i, "unit_cost", Number(e.target.value))} className="h-7 text-xs w-24 text-right" />
                        </td>
                        <td className="px-4 py-3 text-right text-sm font-bold">{formatCurrency(line.subtotal)}</td>
                        <td className="px-4 py-3 text-right">
                          <Button type="button" variant="ghost" size="icon" className="h-7 w-7 text-destructive" onClick={() => removeLine(i)}>
                            <Trash2 className="h-3 w-3" />
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4 space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Total compra</span>
              <span className="font-bold">{formatCurrency(totalAmount)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Crédito Fiscal (13%)</span>
              <span className="font-bold text-success">{formatCurrency(creditoFiscal)}</span>
            </div>
            <div className="border-t pt-2 flex justify-between text-base">
              <span>Total + Crédito Fiscal</span>
              <span className="font-bold">{formatCurrency(totalAmount + creditoFiscal)}</span>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-2">
          <Button type="button" variant="outline" onClick={() => navigate("/purchases")}>Cancelar</Button>
          <Button type="submit" disabled={submitting || lines.length === 0 || !supplierId}>
            {submitting ? "Guardando..." : "Guardar y actualizar inventario"}
          </Button>
        </div>
      </form>
    </div>
  )
}

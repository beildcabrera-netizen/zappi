import { useEffect, useState, useRef } from "react"
import { Search, Plus, Minus, Trash2, User, X, CreditCard, Banknote, Landmark, Check, Printer } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog"
import { supabase } from "@/lib/supabase"
import { useAuthStore } from "@/stores/authStore"
import { usePosStore } from "@/stores/posStore"
import { generateTicket, printTicket } from "@/lib/ticket"
import type { Product, Customer } from "@/types"

export function PosPage() {
  const {
    cart, customer, discount, paymentMethod, amountReceived,
    addToCart, removeFromCart, updateQuantity,
    setCustomer, setDiscount, setPaymentMethod, setAmountReceived,
    clearCart, getSubtotal, getTax, getTotal, getChange,
  } = usePosStore()
  const { profile } = useAuthStore()
  const [search, setSearch] = useState("")
  const [searchResults, setSearchResults] = useState<Product[]>([])
  const [showSearch, setShowSearch] = useState(false)
  const [showPayment, setShowPayment] = useState(false)
  const [showReceipt, setShowReceipt] = useState(false)
  const [lastSale, setLastSale] = useState<any>(null)
  const [customers, setCustomers] = useState<Customer[]>([])
  const [showCustomerSearch, setShowCustomerSearch] = useState(false)
  const [customerSearch, setCustomerSearch] = useState("")
  const [submitting, setSubmitting] = useState(false)
  const searchRef = useRef<HTMLInputElement>(null)
  const total = getTotal()
  const change = getChange()

  useEffect(() => {
    loadCustomers()
  }, [])

  useEffect(() => {
    const timer = setTimeout(() => {
      if (search.trim().length > 0) {
        searchProducts(search)
      } else {
        setSearchResults([])
      }
    }, 200)
    return () => clearTimeout(timer)
  }, [search])

  useEffect(() => {
    if (showSearch && searchRef.current) {
      searchRef.current.focus()
    }
  }, [showSearch])

  async function loadCustomers() {
    const { data } = await supabase.from("customers").select("*").eq("is_active", true).order("full_name")
    if (data) setCustomers(data)
  }

  async function searchProducts(query: string) {
    const { data } = await supabase
      .from("products")
      .select("*, category:categories(name)")
      .eq("is_active", true)
      .gt("stock_quantity", 0)
      .or(`name.ilike.%${query}%,barcode.ilike.%${query}%`)
      .limit(10)
    if (data) setSearchResults(data as unknown as Product[])
  }

  function handleSelectProduct(product: Product) {
    addToCart(product)
    setSearch("")
    setSearchResults([])
    setShowSearch(false)
  }

  async function handleCheckout() {
    if (cart.length === 0 || !profile) return
    setSubmitting(true)

    try {
      const tax = getTax()
      const totalAmount = total

      // Get next invoice number
      const { data: seqData } = await supabase.rpc("nextval", { sequence: "invoice_number_seq" })
      const invNumber = `INV-${String(seqData || 1).padStart(6, "0")}`

      // Insert sale
      const { data: sale, error: saleError } = await supabase
        .from("sales")
        .insert({
          invoice_number: invNumber,
          customer_id: customer?.id || null,
          cashier_id: profile.id,
          total_amount: totalAmount,
          tax_amount: tax,
          discount,
          payment_method: paymentMethod,
          status: "completed",
        })
        .select()
        .single()

      if (saleError || !sale) throw saleError

      // Insert sale items and update inventory
      for (const item of cart) {
        await supabase.from("sale_items").insert({
          sale_id: sale.id,
          product_id: item.product.id,
          quantity: item.quantity,
          unit_price: item.unit_price,
          subtotal: item.subtotal,
        })

        // Update stock
        const newStock = item.product.stock_quantity - item.quantity
        await supabase
          .from("products")
          .update({ stock_quantity: newStock })
          .eq("id", item.product.id)

        // Log inventory movement
        await supabase.from("inventory_logs").insert({
          product_id: item.product.id,
          type: "sale",
          quantity: -item.quantity,
          previous_stock: item.product.stock_quantity,
          new_stock: newStock,
          created_by: profile.id,
        })
      }

      setLastSale(sale)
      setShowPayment(false)
      setShowReceipt(true)
    } catch (err: any) {
      alert("Error al procesar la venta: " + err.message)
    } finally {
      setSubmitting(false)
    }
  }

  function handlePrint() {
    if (!lastSale || !profile) return
    const doc = generateTicket({
      invoiceNumber: lastSale.invoice_number,
      cashierName: profile.full_name || profile.email,
      customer,
      items: cart,
      subtotal: getSubtotal(),
      tax: getTax(),
      discount,
      total,
      paymentMethod,
      amountReceived,
      change,
      date: new Date(),
    })
    printTicket(doc)
  }

  function handleNewSale() {
    clearCart()
    setShowReceipt(false)
    setLastSale(null)
  }

  const filteredCustomers = customers.filter(
    (c) =>
      c.full_name.toLowerCase().includes(customerSearch.toLowerCase()) ||
      c.dni?.includes(customerSearch)
  )

  return (
    <div className="flex h-full gap-4">
      <div className="flex flex-1 flex-col gap-4">
        <Card className="flex-shrink-0">
          <CardHeader className="py-3">
            <div className="flex items-center gap-2">
              <Button
                size="sm"
                className="gap-2"
                onClick={() => setShowSearch(!showSearch)}
              >
                <Search className="h-4 w-4" />
                Agregar producto
              </Button>
              {customer && (
                <Badge variant="secondary" className="gap-1">
                  <User className="h-3 w-3" />
                  {customer.full_name}
                  <button onClick={() => setCustomer(null)}><X className="h-3 w-3" /></button>
                </Badge>
              )}
              {!customer && (
                <Button variant="outline" size="sm" onClick={() => setShowCustomerSearch(true)}>
                  <User className="h-4 w-4" />
                  Cliente
                </Button>
              )}
            </div>
          </CardHeader>
        </Card>

        {showSearch && (
          <Card className="flex-shrink-0">
            <CardContent className="p-3">
              <Input
                ref={searchRef}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Buscar por nombre o código de barras..."
                className="mb-2"
              />
              {searchResults.length > 0 && (
                <div className="max-h-48 overflow-y-auto space-y-1">
                  {searchResults.map((p) => (
                    <button
                      key={p.id}
                      className="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-accent transition-colors"
                      onClick={() => handleSelectProduct(p)}
                    >
                      <div className="text-left">
                        <p className="font-medium">{p.name}</p>
                        <p className="text-xs text-muted-foreground">Stock: {p.stock_quantity}</p>
                      </div>
                      <span className="font-bold">${Number(p.sale_price).toFixed(2)}</span>
                    </button>
                  ))}
                </div>
              )}
              {search && searchResults.length === 0 && (
                <p className="text-sm text-muted-foreground">Sin resultados</p>
              )}
            </CardContent>
          </Card>
        )}

        <Card className="flex-1 overflow-hidden">
          <CardHeader className="py-3 border-b">
            <CardTitle className="text-sm font-medium">
              Carrito ({cart.length} {cart.length === 1 ? "producto" : "productos"})
            </CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            {cart.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
                <ShoppingCart className="h-12 w-12 mb-4" />
                <p>Carrito vacío</p>
                <p className="text-sm">Agrega productos para comenzar</p>
              </div>
            ) : (
              <div className="divide-y">
                {cart.map((item) => (
                  <div key={item.product.id} className="flex items-center gap-3 px-4 py-3">
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{item.product.name}</p>
                      <p className="text-xs text-muted-foreground">${item.unit_price.toFixed(2)} c/u</p>
                    </div>
                    <div className="flex items-center gap-1">
                      <Button
                        variant="ghost"
                        size="icon"
                        className="h-7 w-7"
                        onClick={() => updateQuantity(item.product.id, item.quantity - 1)}
                      >
                        <Minus className="h-3 w-3" />
                      </Button>
                      <span className="w-8 text-center text-sm font-medium">{item.quantity}</span>
                      {item.quantity < item.product.stock_quantity ? (
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-7 w-7"
                          onClick={() => updateQuantity(item.product.id, item.quantity + 1)}
                        >
                          <Plus className="h-3 w-3" />
                        </Button>
                      ) : (
                        <Button variant="ghost" size="icon" className="h-7 w-7" disabled>
                          <Plus className="h-3 w-3" />
                        </Button>
                      )}
                    </div>
                    <p className="w-20 text-right text-sm font-bold">${item.subtotal.toFixed(2)}</p>
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-7 w-7 text-destructive"
                      onClick={() => removeFromCart(item.product.id)}
                    >
                      <Trash2 className="h-3 w-3" />
                    </Button>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      <div className="w-72 flex flex-col gap-4 flex-shrink-0">
        <Card>
          <CardHeader className="py-3 border-b">
            <CardTitle className="text-sm font-medium">Resumen</CardTitle>
          </CardHeader>
          <CardContent className="p-4 space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Subtotal</span>
              <span>${getSubtotal().toFixed(2)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">IVA (18%)</span>
              <span>${getTax().toFixed(2)}</span>
            </div>
            <div className="flex items-center justify-between text-sm">
              <span className="text-muted-foreground">Descuento</span>
              <input
                type="number"
                min="0"
                step="0.01"
                className="w-24 text-right rounded border px-2 py-0.5 text-sm"
                value={discount}
                onChange={(e) => setDiscount(Number(e.target.value))}
                placeholder="0.00"
              />
            </div>
            <div className="border-t pt-2">
              <div className="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>${total.toFixed(2)}</span>
              </div>
            </div>
            <Button
              className="w-full mt-2"
              size="lg"
              disabled={cart.length === 0}
              onClick={() => setShowPayment(true)}
            >
              Cobrar ${total.toFixed(2)}
            </Button>
          </CardContent>
        </Card>
      </div>

      <Dialog open={showPayment} onOpenChange={setShowPayment}>
        <DialogHeader>
          <DialogTitle>Procesar pago</DialogTitle>
          <DialogDescription>Selecciona el método de pago</DialogDescription>
        </DialogHeader>
        <div className="space-y-4">
          <div className="grid grid-cols-3 gap-2">
            {[
              { value: "cash", label: "Efectivo", icon: Banknote },
              { value: "card", label: "Tarjeta", icon: CreditCard },
              { value: "transfer", label: "Transferencia", icon: Landmark },
            ].map(({ value, label, icon: Icon }) => (
              <Button
                key={value}
                variant={paymentMethod === value ? "default" : "outline"}
                className="flex-col gap-1 h-auto py-3"
                onClick={() => setPaymentMethod(value as any)}
              >
                <Icon className="h-5 w-5" />
                <span className="text-xs">{label}</span>
              </Button>
            ))}
          </div>

          {paymentMethod === "cash" && (
            <div className="space-y-2">
              <label className="text-sm font-medium">Monto recibido</label>
              <Input
                type="number"
                step="0.01"
                min="0"
                value={amountReceived || ""}
                onChange={(e) => setAmountReceived(Number(e.target.value))}
                placeholder="0.00"
              />
              {amountReceived >= total && (
                <p className="text-sm text-green-600 font-medium">
                  Cambio: ${change.toFixed(2)}
                </p>
              )}
            </div>
          )}

          <div className="border-t pt-3">
            <div className="flex justify-between text-lg font-bold mb-4">
              <span>Total a cobrar</span>
              <span>${total.toFixed(2)}</span>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setShowPayment(false)}>Cancelar</Button>
            <Button
              onClick={handleCheckout}
              disabled={
                submitting ||
                (paymentMethod === "cash" && amountReceived < total)
              }
            >
              {submitting ? "Procesando..." : "Confirmar pago"}
            </Button>
          </DialogFooter>
        </div>
      </Dialog>

      <Dialog open={showReceipt} onOpenChange={setShowReceipt}>
        <DialogHeader>
          <DialogTitle className="text-center">Venta completada</DialogTitle>
          <DialogDescription className="text-center">
            <Check className="mx-auto h-12 w-12 text-green-500 mb-2" />
            Ticket #{lastSale?.invoice_number}
          </DialogDescription>
        </DialogHeader>
        <div className="text-center space-y-2">
          <p className="text-2xl font-bold">${total.toFixed(2)}</p>
          <p className="text-sm text-muted-foreground capitalize">{paymentMethod}</p>
          {paymentMethod === "cash" && change > 0 && (
            <p className="text-sm">Cambio: ${change.toFixed(2)}</p>
          )}
        </div>
        <DialogFooter className="justify-center gap-2">
          <Button variant="outline" onClick={handlePrint}>
            <Printer className="h-4 w-4" />
            Imprimir ticket
          </Button>
          <Button onClick={handleNewSale}>
            Nueva venta
          </Button>
        </DialogFooter>
      </Dialog>

      <Dialog open={showCustomerSearch} onOpenChange={setShowCustomerSearch}>
        <DialogHeader>
          <DialogTitle>Seleccionar cliente</DialogTitle>
          <DialogDescription>Busca un cliente registrado</DialogDescription>
        </DialogHeader>
        <div className="space-y-3">
          <Input
            value={customerSearch}
            onChange={(e) => setCustomerSearch(e.target.value)}
            placeholder="Buscar por nombre o DNI..."
          />
          <div className="max-h-60 overflow-y-auto space-y-1">
            {filteredCustomers.map((c) => (
              <button
                key={c.id}
                className="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-accent transition-colors"
                onClick={() => { setCustomer(c); setShowCustomerSearch(false); setCustomerSearch("") }}
              >
                <div className="text-left">
                  <p className="font-medium">{c.full_name}</p>
                  {c.dni && <p className="text-xs text-muted-foreground">DNI: {c.dni}</p>}
                </div>
              </button>
            ))}
            {filteredCustomers.length === 0 && (
              <p className="text-sm text-muted-foreground text-center py-4">
                No se encontraron clientes
              </p>
            )}
          </div>
        </div>
      </Dialog>
    </div>
  )
}

function ShoppingCart(props: React.SVGProps<SVGSVGElement>) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <circle cx="8" cy="21" r="1" />
      <circle cx="19" cy="21" r="1" />
      <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
    </svg>
  )
}

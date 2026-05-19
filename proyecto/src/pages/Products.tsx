import { useEffect, useState } from "react"
import { Plus, Search, Pencil, Trash2, RefreshCw, Layers } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select } from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Dialog, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog"
import { ProductForm } from "@/components/products/ProductForm"
import { supabase } from "@/lib/supabase"
import type { Product, Category } from "@/types"

export function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [search, setSearch] = useState("")
  const [categoryFilter, setCategoryFilter] = useState("")
  const [showForm, setShowForm] = useState(false)
  const [editingProduct, setEditingProduct] = useState<Product | null>(null)
  const [showCategories, setShowCategories] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadCategories()
    loadProducts()
  }, [])

  async function loadCategories() {
    const { data } = await supabase.from("categories").select("*").order("name")
    if (data) setCategories(data)
  }

  async function loadProducts() {
    setLoading(true)
    const { data } = await supabase
      .from("products")
      .select("*, category:categories(name)")
      .eq("is_active", true)
      .order("name")
    if (data) setProducts(data as unknown as Product[])
    setLoading(false)
  }

  async function handleDelete(id: string) {
    if (!confirm("¿Eliminar este producto?")) return
    await supabase.from("products").update({ is_active: false }).eq("id", id)
    loadProducts()
  }

  async function handleSubmit(data: Record<string, unknown>) {
    if (editingProduct) {
      await supabase.from("products").update(data).eq("id", editingProduct.id)
    } else {
      await supabase.from("products").insert(data)
    }
    setShowForm(false)
    setEditingProduct(null)
    loadProducts()
  }

  const filtered = products.filter((p) => {
    const matchesSearch =
      !search ||
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.barcode?.toLowerCase().includes(search.toLowerCase())
    const matchesCategory = !categoryFilter || p.category_id === categoryFilter
    return matchesSearch && matchesCategory
  })

  function getStockVariant(stock: number, minAlert: number) {
    if (stock === 0) return "destructive" as const
    if (stock <= minAlert) return "warning" as const
    return "success" as const
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Productos</h1>
          <p className="text-muted-foreground">Catálogo de bebidas y productos</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => setShowCategories(true)}>
            <Layers className="h-4 w-4" />
            Categorías
          </Button>
          <Button onClick={() => { setEditingProduct(null); setShowForm(true) }}>
            <Plus className="h-4 w-4" />
            Nuevo producto
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex flex-col gap-3 sm:flex-row">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Buscar por nombre o código de barras..."
                className="pl-9"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <Select
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              options={categories.map((c) => ({ value: c.id, label: c.name }))}
              placeholder="Todas las categorías"
              className="w-full sm:w-48"
            />
            <Button variant="ghost" size="icon" onClick={loadProducts}>
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
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Código</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">Categoría</th>
                  <th className="px-4 py-3 font-medium text-right">Precio venta</th>
                  <th className="px-4 py-3 font-medium text-right">Stock</th>
                  <th className="px-4 py-3 font-medium text-right hidden lg:table-cell">Ganancia</th>
                  <th className="px-4 py-3 font-medium text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr>
                    <td colSpan={7} className="px-4 py-12 text-center text-muted-foreground">
                      Cargando productos...
                    </td>
                  </tr>
                ) : filtered.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-4 py-12 text-center text-muted-foreground">
                      No se encontraron productos
                    </td>
                  </tr>
                ) : (
                  filtered.map((product) => {
                    const margin = Number(product.sale_price) - Number(product.cost_price)
                    const marginPercent = Number(product.cost_price) > 0
                      ? ((margin / Number(product.cost_price)) * 100).toFixed(0)
                      : "∞"
                    return (
                      <tr key={product.id} className="border-b last:border-0 hover:bg-muted/50 transition-colors">
                        <td className="px-4 py-3">
                          <div>
                            <p className="font-medium">{product.name}</p>
                            {product.volume_ml && (
                              <p className="text-xs text-muted-foreground">{product.volume_ml}ml</p>
                            )}
                          </div>
                        </td>
                        <td className="px-4 py-3 text-sm hidden md:table-cell">
                          {product.barcode || "—"}
                        </td>
                        <td className="px-4 py-3 text-sm hidden sm:table-cell">
                          {(product as any).category?.name || "—"}
                        </td>
                        <td className="px-4 py-3 text-right font-medium">
                          ${Number(product.sale_price).toFixed(2)}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Badge variant={getStockVariant(product.stock_quantity, product.min_stock_alert)}>
                            {product.stock_quantity} uds.
                          </Badge>
                        </td>
                        <td className="px-4 py-3 text-right text-sm hidden lg:table-cell">
                          <span className="text-green-600">${margin.toFixed(2)}</span>
                          <span className="text-muted-foreground ml-1">({marginPercent}%)</span>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex justify-end gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => { setEditingProduct(product); setShowForm(true) }}
                            >
                              <Pencil className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              onClick={() => handleDelete(product.id)}
                            >
                              <Trash2 className="h-4 w-4 text-destructive" />
                            </Button>
                          </div>
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

      <Dialog open={showForm} onOpenChange={(open) => { setShowForm(open); if (!open) setEditingProduct(null) }}>
        <DialogHeader>
          <DialogTitle>{editingProduct ? "Editar producto" : "Nuevo producto"}</DialogTitle>
          <DialogDescription>
            {editingProduct ? "Actualiza los datos del producto" : "Ingresa los datos del nuevo producto"}
          </DialogDescription>
        </DialogHeader>
        <ProductForm
          product={editingProduct}
          categories={categories}
          onSubmit={handleSubmit}
          onCancel={() => { setShowForm(false); setEditingProduct(null) }}
        />
      </Dialog>

      <Dialog open={showCategories} onOpenChange={setShowCategories}>
        <DialogHeader>
          <DialogTitle>Gestionar categorías</DialogTitle>
          <DialogDescription>Administra las categorías de productos</DialogDescription>
        </DialogHeader>
        <CategoryManager
          categories={categories}
          onUpdate={loadCategories}
        />
      </Dialog>
    </div>
  )
}

function CategoryManager({ categories, onUpdate }: { categories: Category[]; onUpdate: () => void }) {
  const [newName, setNewName] = useState("")
  const [editingId, setEditingId] = useState<string | null>(null)
  const [editName, setEditName] = useState("")

  async function handleAdd() {
    if (!newName.trim()) return
    await supabase.from("categories").insert({ name: newName.trim() })
    setNewName("")
    onUpdate()
  }

  async function handleEdit(id: string) {
    if (!editName.trim()) return
    await supabase.from("categories").update({ name: editName.trim() }).eq("id", id)
    setEditingId(null)
    onUpdate()
  }

  async function handleDelete(id: string) {
    if (!confirm("¿Eliminar categoría?")) return
    await supabase.from("categories").delete().eq("id", id)
    onUpdate()
  }

  return (
    <div className="space-y-4">
      <div className="flex gap-2">
        <Input
          value={newName}
          onChange={(e) => setNewName(e.target.value)}
          placeholder="Nueva categoría..."
          onKeyDown={(e) => e.key === "Enter" && handleAdd()}
        />
        <Button onClick={handleAdd} size="sm">Agregar</Button>
      </div>
      <div className="space-y-1 max-h-60 overflow-y-auto">
        {categories.map((cat) => (
          <div key={cat.id} className="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
            {editingId === cat.id ? (
              <div className="flex flex-1 gap-2">
                <Input
                  value={editName}
                  onChange={(e) => setEditName(e.target.value)}
                  onKeyDown={(e) => e.key === "Enter" && handleEdit(cat.id)}
                  className="h-7"
                />
                <Button size="sm" variant="ghost" onClick={() => handleEdit(cat.id)}>OK</Button>
                <Button size="sm" variant="ghost" onClick={() => setEditingId(null)}>X</Button>
              </div>
            ) : (
              <>
                <span>{cat.name}</span>
                <div className="flex gap-1">
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => { setEditingId(cat.id); setEditName(cat.name) }}
                  >
                    <Pencil className="h-3 w-3" />
                  </Button>
                  <Button size="sm" variant="ghost" onClick={() => handleDelete(cat.id)}>
                    <Trash2 className="h-3 w-3 text-destructive" />
                  </Button>
                </div>
              </>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}

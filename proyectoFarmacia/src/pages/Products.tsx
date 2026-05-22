import { useEffect, useState } from "react"
import { Plus, Search, Pencil, Trash2, RefreshCw, Layers } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select } from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Dialog, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod/v3"
import { Label } from "@/components/ui/card"
import { Textarea } from "@/components/ui/textarea"
import { formatCurrency } from "@/utils/formatters"
import { supabase } from "@/lib/supabase"
import type { Product, Category } from "@/types"

const productSchema = z.object({
  name: z.string({ message: "El nombre es requerido" }),
  barcode: z.string().default(""),
  category_id: z.string().default(""),
  description: z.string().default(""),
  cost_price: z.coerce.number({ message: "Precio inválido" }).min(0),
  sale_price: z.coerce.number({ message: "Precio inválido" }).min(0),
  stock_quantity: z.coerce.number({ message: "Cantidad inválida" }).int().min(0),
  min_stock_alert: z.coerce.number({ message: "Alerta inválida" }).int().min(0),
  principio_activo: z.string().default(""),
  concentracion: z.string().default(""),
  forma_farmaceutica: z.string().default(""),
  registro_sanitario: z.string().default(""),
})

type ProductFormData = z.input<typeof productSchema>

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
    if (!supabase) return
    const { data } = await supabase.from("categories").select("*").order("name")
    if (data) setCategories(data)
  }

  async function loadProducts() {
    if (!supabase) return
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
    if (!confirm("¿Desactivar este producto?")) return
    if (!supabase) return
    await supabase.from("products").update({ is_active: false }).eq("id", id)
    loadProducts()
  }

  async function handleSubmit(data: Record<string, unknown>) {
    if (!supabase) return
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

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Productos</h1>
          <p className="text-muted-foreground">Catálogo de medicamentos y productos</p>
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
                  <th className="px-4 py-3 font-medium text-right">P. Venta</th>
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
                            <p className="text-xs text-muted-foreground">
                              {[product.principio_activo, product.concentracion, product.forma_farmaceutica]
                                .filter(Boolean)
                                .join(" — ") || product.name}
                            </p>
                          </div>
                        </td>
                        <td className="px-4 py-3 text-sm hidden md:table-cell">
                          {product.barcode || "—"}
                        </td>
                        <td className="px-4 py-3 text-sm hidden sm:table-cell">
                          {(product as any).category?.name || "—"}
                        </td>
                        <td className="px-4 py-3 text-right font-medium">
                          {formatCurrency(Number(product.sale_price))}
                        </td>
                        <td className="px-4 py-3 text-right">
                          <Badge
                            variant={
                              product.stock_quantity === 0 ? "destructive" :
                              product.stock_quantity <= product.min_stock_alert ? "warning" : "success"
                            }
                          >
                            {product.stock_quantity} uds.
                          </Badge>
                        </td>
                        <td className="px-4 py-3 text-right text-sm hidden lg:table-cell">
                          <span className="text-success">{formatCurrency(margin)}</span>
                          <span className="text-muted-foreground ml-1">({marginPercent}%)</span>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <div className="flex justify-end gap-1">
                            <Button variant="ghost" size="icon" onClick={() => { setEditingProduct(product); setShowForm(true) }}>
                              <Pencil className="h-4 w-4" />
                            </Button>
                            <Button variant="ghost" size="icon" onClick={() => handleDelete(product.id)}>
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

      <ProductFormDialog
        open={showForm}
        onOpenChange={(open) => { setShowForm(open); if (!open) setEditingProduct(null) }}
        product={editingProduct}
        categories={categories}
        onSubmit={handleSubmit}
      />

      <CategoriesDialog
        open={showCategories}
        onOpenChange={setShowCategories}
        categories={categories}
        onUpdate={loadCategories}
      />
    </div>
  )
}

function ProductFormDialog({
  open, onOpenChange, product, categories, onSubmit,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  product: Product | null
  categories: Category[]
  onSubmit: (data: Record<string, unknown>) => Promise<void>
}) {
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ProductFormData>({
    resolver: zodResolver(productSchema),
    defaultValues: {
      name: "", barcode: "", category_id: "", description: "",
      cost_price: 0, sale_price: 0, stock_quantity: 0, min_stock_alert: 5,
      principio_activo: "", concentracion: "", forma_farmaceutica: "", registro_sanitario: "",
    },
  })

  useEffect(() => {
    if (product) {
      reset({
        name: product.name,
        barcode: product.barcode ?? "",
        category_id: product.category_id ?? "",
        description: product.description ?? "",
        cost_price: Number(product.cost_price),
        sale_price: Number(product.sale_price),
        stock_quantity: product.stock_quantity,
        min_stock_alert: product.min_stock_alert,
        principio_activo: product.principio_activo ?? "",
        concentracion: product.concentracion ?? "",
        forma_farmaceutica: product.forma_farmaceutica ?? "",
        registro_sanitario: product.registro_sanitario ?? "",
      })
    }
  }, [product, reset])

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>{product ? "Editar producto" : "Nuevo producto"}</DialogTitle>
        <DialogDescription>
          {product ? "Actualiza los datos del producto" : "Ingresa los datos del nuevo producto"}
        </DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit((data) => onSubmit(data as Record<string, unknown>))} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-2 sm:col-span-2">
            <Label htmlFor="name">Nombre del producto *</Label>
            <Input id="name" {...register("name")} placeholder="Ej: Paracetamol 500mg" />
            {errors.name && <p className="text-xs text-destructive">{errors.name.message}</p>}
          </div>
          <div className="space-y-2">
            <Label htmlFor="barcode">Código de barras</Label>
            <Input id="barcode" {...register("barcode")} placeholder="7891234567890" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="category_id">Categoría</Label>
            <Select
              id="category_id"
              {...register("category_id")}
              options={categories.map((c) => ({ value: c.id, label: c.name }))}
              placeholder="Seleccionar categoría"
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="principio_activo">Principio Activo</Label>
            <Input id="principio_activo" {...register("principio_activo")} placeholder="Paracetamol" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="concentracion">Concentración</Label>
            <Input id="concentracion" {...register("concentracion")} placeholder="500mg" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="forma_farmaceutica">Forma Farmacéutica</Label>
            <Input id="forma_farmaceutica" {...register("forma_farmaceutica")} placeholder="Tableta" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="registro_sanitario">Registro Sanitario</Label>
            <Input id="registro_sanitario" {...register("registro_sanitario")} placeholder="NN-XXXXX/2026" />
          </div>
          <div className="space-y-2 sm:col-span-2">
            <Label htmlFor="description">Descripción</Label>
            <Textarea id="description" {...register("description")} placeholder="Descripción del producto" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="cost_price">Precio de compra *</Label>
            <Input id="cost_price" type="number" step="0.01" min="0" {...register("cost_price")} />
            {errors.cost_price && <p className="text-xs text-destructive">{errors.cost_price.message}</p>}
          </div>
          <div className="space-y-2">
            <Label htmlFor="sale_price">Precio de venta *</Label>
            <Input id="sale_price" type="number" step="0.01" min="0" {...register("sale_price")} />
            {errors.sale_price && <p className="text-xs text-destructive">{errors.sale_price.message}</p>}
          </div>
          <div className="space-y-2">
            <Label htmlFor="stock_quantity">Stock inicial *</Label>
            <Input id="stock_quantity" type="number" min="0" {...register("stock_quantity")} />
            {errors.stock_quantity && <p className="text-xs text-destructive">{errors.stock_quantity.message}</p>}
          </div>
          <div className="space-y-2">
            <Label htmlFor="min_stock_alert">Alerta de stock mínimo</Label>
            <Input id="min_stock_alert" type="number" min="0" {...register("min_stock_alert")} />
          </div>
        </div>
        <div className="flex justify-end gap-2 pt-2">
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={isSubmitting}>
            {isSubmitting ? "Guardando..." : product ? "Actualizar" : "Crear producto"}
          </Button>
        </div>
      </form>
    </Dialog>
  )
}

function CategoriesDialog({
  open, onOpenChange, categories, onUpdate,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  categories: Category[]
  onUpdate: () => void
}) {
  const [newName, setNewName] = useState("")
  const [editingId, setEditingId] = useState<string | null>(null)
  const [editName, setEditName] = useState("")

  async function handleAdd() {
    if (!newName.trim() || !supabase) return
    await supabase.from("categories").insert({ name: newName.trim() })
    setNewName("")
    onUpdate()
  }

  async function handleEdit(id: string) {
    if (!editName.trim() || !supabase) return
    await supabase.from("categories").update({ name: editName.trim() }).eq("id", id)
    setEditingId(null)
    onUpdate()
  }

  async function handleDelete(id: string) {
    if (!confirm("¿Eliminar categoría?") || !supabase) return
    await supabase.from("categories").delete().eq("id", id)
    onUpdate()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>Gestionar categorías</DialogTitle>
        <DialogDescription>Administra las categorías de productos</DialogDescription>
      </DialogHeader>
      <div className="space-y-4">
        <div className="flex gap-2">
          <Input value={newName} onChange={(e) => setNewName(e.target.value)} placeholder="Nueva categoría..." onKeyDown={(e) => e.key === "Enter" && handleAdd()} />
          <Button onClick={handleAdd} size="sm">Agregar</Button>
        </div>
        <div className="space-y-1 max-h-60 overflow-y-auto">
          {categories.map((cat) => (
            <div key={cat.id} className="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
              {editingId === cat.id ? (
                <div className="flex flex-1 gap-2">
                  <Input value={editName} onChange={(e) => setEditName(e.target.value)} onKeyDown={(e) => e.key === "Enter" && handleEdit(cat.id)} className="h-7" />
                  <Button size="sm" variant="ghost" onClick={() => handleEdit(cat.id)}>OK</Button>
                  <Button size="sm" variant="ghost" onClick={() => setEditingId(null)}>X</Button>
                </div>
              ) : (
                <>
                  <span>{cat.name}</span>
                  <div className="flex gap-1">
                    <Button size="sm" variant="ghost" onClick={() => { setEditingId(cat.id); setEditName(cat.name) }}>
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
    </Dialog>
  )
}

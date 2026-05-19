import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod/v3"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/card"
import { Select } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import type { Product, Category } from "@/types"
import { useEffect } from "react"

const productSchema = z.object({
  name: z.string({ message: "El nombre es requerido" }),
  barcode: z.string().default(""),
  category_id: z.string().default(""),
  description: z.string().default(""),
  cost_price: z.coerce.number({ message: "Precio inválido" }).min(0),
  sale_price: z.coerce.number({ message: "Precio inválido" }).min(0),
  stock_quantity: z.coerce.number({ message: "Cantidad inválida" }).int().min(0),
  min_stock_alert: z.coerce.number({ message: "Alerta inválida" }).int().min(0),
  alcohol_content: z.coerce.number().default(0),
  volume_ml: z.coerce.number().int().default(0),
})

type ProductFormData = z.input<typeof productSchema>

interface Props {
  product?: Product | null
  categories: Category[]
  onSubmit: (data: Record<string, unknown>) => Promise<void>
  onCancel: () => void
}

export function ProductForm({ product, categories, onSubmit, onCancel }: Props) {
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ProductFormData>({
    resolver: zodResolver(productSchema),
    defaultValues: {
      name: "",
      barcode: "",
      category_id: "",
      description: "",
      cost_price: 0,
      sale_price: 0,
      stock_quantity: 0,
      min_stock_alert: 5,
      alcohol_content: 0,
      volume_ml: 0,
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
        alcohol_content: Number(product.alcohol_content ?? 0),
        volume_ml: product.volume_ml ?? 0,
      })
    }
  }, [product, reset])

  return (
    <form onSubmit={handleSubmit((data) => onSubmit(data as Record<string, unknown>))} className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <div className="space-y-2 sm:col-span-2">
          <Label htmlFor="name">Nombre del producto *</Label>
          <Input id="name" {...register("name")} placeholder="Ej: Jack Daniel's 750ml" />
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
        <div className="space-y-2">
          <Label htmlFor="alcohol_content">% Alcohol</Label>
          <Input id="alcohol_content" type="number" step="0.1" min="0" max="100" {...register("alcohol_content")} placeholder="40" />
        </div>
        <div className="space-y-2">
          <Label htmlFor="volume_ml">Volumen (ml)</Label>
          <Input id="volume_ml" type="number" min="0" {...register("volume_ml")} placeholder="750" />
        </div>
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <Button type="button" variant="outline" onClick={onCancel}>Cancelar</Button>
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting ? "Guardando..." : product ? "Actualizar" : "Crear producto"}
        </Button>
      </div>
    </form>
  )
}

import { useEffect, useState } from "react"
import { Plus, Search, Pencil, Trash2, RefreshCw } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog"
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/card"
import { useForm } from "react-hook-form"
import { supabase } from "@/lib/supabase"
import type { Supplier } from "@/types"

export function SuppliersPage() {
  const [suppliers, setSuppliers] = useState<Supplier[]>([])
  const [search, setSearch] = useState("")
  const [showForm, setShowForm] = useState(false)
  const [editingSupplier, setEditingSupplier] = useState<Supplier | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadSuppliers() }, [])

  async function loadSuppliers() {
    setLoading(true)
    const { data } = await supabase.from("suppliers").select("*").order("name")
    if (data) setSuppliers(data)
    setLoading(false)
  }

  async function handleDelete(id: string) {
    if (!confirm("¿Desactivar este proveedor?")) return
    await supabase.from("suppliers").update({ is_active: false }).eq("id", id)
    loadSuppliers()
  }

  const filtered = suppliers.filter(
    (s) =>
      s.is_active &&
      (s.name.toLowerCase().includes(search.toLowerCase()) ||
        s.contact_name?.toLowerCase().includes(search.toLowerCase()))
  )

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Proveedores</h1>
          <p className="text-muted-foreground">Gestión de distribuidores</p>
        </div>
        <Button onClick={() => { setEditingSupplier(null); setShowForm(true) }}>
          <Plus className="h-4 w-4" />
          Nuevo proveedor
        </Button>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Buscar proveedor..."
                className="pl-9"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <Button variant="ghost" size="icon" onClick={loadSuppliers}>
              <RefreshCw className="h-4 w-4" />
            </Button>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b text-left text-sm text-muted-foreground">
                  <th className="px-4 py-3 font-medium">Nombre</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">Contacto</th>
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Teléfono</th>
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Email</th>
                  <th className="px-4 py-3 font-medium text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={5} className="px-4 py-12 text-center text-muted-foreground">Cargando...</td></tr>
                ) : filtered.length === 0 ? (
                  <tr><td colSpan={5} className="px-4 py-12 text-center text-muted-foreground">No se encontraron proveedores</td></tr>
                ) : (
                  filtered.map((s) => (
                    <tr key={s.id} className="border-b last:border-0 hover:bg-muted/50">
                      <td className="px-4 py-3 font-medium">{s.name}</td>
                      <td className="px-4 py-3 text-sm hidden sm:table-cell">{s.contact_name || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden md:table-cell">{s.phone || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden md:table-cell">{s.email || "—"}</td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-1">
                          <Button variant="ghost" size="icon" onClick={() => { setEditingSupplier(s); setShowForm(true) }}>
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" onClick={() => handleDelete(s.id)}>
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <SupplierFormDialog
        open={showForm}
        onOpenChange={setShowForm}
        supplier={editingSupplier}
        onSuccess={loadSuppliers}
      />
    </div>
  )
}

function SupplierFormDialog({
  open, onOpenChange, supplier, onSuccess,
}: {
  open: boolean
  onOpenChange: (v: boolean) => void
  supplier: Supplier | null
  onSuccess: () => void
}) {
  const { register, handleSubmit, reset, formState: { isSubmitting } } = useForm({
    defaultValues: { name: "", contact_name: "", phone: "", email: "", address: "" },
  })

  useEffect(() => {
    if (supplier) {
      reset({
        name: supplier.name,
        contact_name: supplier.contact_name || "",
        phone: supplier.phone || "",
        email: supplier.email || "",
        address: supplier.address || "",
      })
    } else {
      reset({ name: "", contact_name: "", phone: "", email: "", address: "" })
    }
  }, [supplier, reset])

  async function onSubmit(data: any) {
    if (supplier) {
      await supabase.from("suppliers").update(data).eq("id", supplier.id)
    } else {
      await supabase.from("suppliers").insert(data)
    }
    onOpenChange(false)
    onSuccess()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>{supplier ? "Editar proveedor" : "Nuevo proveedor"}</DialogTitle>
        <DialogDescription>{supplier ? "Actualiza los datos del proveedor" : "Registra un nuevo proveedor"}</DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-2 sm:col-span-2">
            <Label>Nombre del proveedor *</Label>
            <Input {...register("name", { required: true })} placeholder="Distribuidora X" />
          </div>
          <div className="space-y-2">
            <Label>Nombre de contacto</Label>
            <Input {...register("contact_name")} placeholder="Juan Pérez" />
          </div>
          <div className="space-y-2">
            <Label>Teléfono</Label>
            <Input {...register("phone")} placeholder="+52 555 123 4567" />
          </div>
          <div className="space-y-2">
            <Label>Email</Label>
            <Input {...register("email")} type="email" placeholder="contacto@proveedor.com" />
          </div>
          <div className="space-y-2 sm:col-span-2">
            <Label>Dirección</Label>
            <Textarea {...register("address")} placeholder="Dirección del proveedor" />
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={isSubmitting}>{supplier ? "Actualizar" : "Crear proveedor"}</Button>
        </DialogFooter>
      </form>
    </Dialog>
  )
}

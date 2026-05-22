import { useEffect, useState } from "react"
import { Plus, Search, Pencil, Trash2, RefreshCw } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog"
import { Label } from "@/components/ui/card"
import { useForm } from "react-hook-form"
import { formatDate } from "@/utils/formatters"
import { supabase } from "@/lib/supabase"
import type { Customer } from "@/types"

export function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[]>([])
  const [search, setSearch] = useState("")
  const [showForm, setShowForm] = useState(false)
  const [editingCustomer, setEditingCustomer] = useState<Customer | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadCustomers() }, [])

  async function loadCustomers() {
    if (!supabase) return
    setLoading(true)
    const { data } = await supabase.from("customers").select("*").order("full_name")
    if (data) setCustomers(data)
    setLoading(false)
  }

  async function handleDelete(id: string) {
    if (!confirm("¿Desactivar este cliente?") || !supabase) return
    await supabase.from("customers").update({ is_active: false }).eq("id", id)
    loadCustomers()
  }

  const filtered = customers.filter(
    (c) => c.is_active && (
      c.full_name.toLowerCase().includes(search.toLowerCase()) ||
      c.dni?.includes(search) || c.phone?.includes(search)
    )
  )

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Clientes</h1>
          <p className="text-muted-foreground">Registro de clientes</p>
        </div>
        <Button onClick={() => { setEditingCustomer(null); setShowForm(true) }}>
          <Plus className="h-4 w-4" />
          Nuevo cliente
        </Button>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input placeholder="Buscar por nombre, DNI o teléfono..." className="pl-9" value={search} onChange={(e) => setSearch(e.target.value)} />
            </div>
            <Button variant="ghost" size="icon" onClick={loadCustomers}><RefreshCw className="h-4 w-4" /></Button>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b text-left text-sm text-muted-foreground">
                  <th className="px-4 py-3 font-medium">Nombre</th>
                  <th className="px-4 py-3 font-medium hidden sm:table-cell">DNI</th>
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Teléfono</th>
                  <th className="px-4 py-3 font-medium hidden md:table-cell">Email</th>
                  <th className="px-4 py-3 font-medium hidden lg:table-cell">Nacimiento</th>
                  <th className="px-4 py-3 font-medium text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">Cargando...</td></tr>
                ) : filtered.length === 0 ? (
                  <tr><td colSpan={6} className="px-4 py-12 text-center text-muted-foreground">No se encontraron clientes</td></tr>
                ) : (
                  filtered.map((c) => (
                    <tr key={c.id} className="border-b last:border-0 hover:bg-muted/50">
                      <td className="px-4 py-3 font-medium">{c.full_name}</td>
                      <td className="px-4 py-3 text-sm hidden sm:table-cell">{c.dni || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden md:table-cell">{c.phone || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden md:table-cell">{c.email || "—"}</td>
                      <td className="px-4 py-3 text-sm hidden lg:table-cell">{c.birth_date ? formatDate(c.birth_date) : "—"}</td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-1">
                          <Button variant="ghost" size="icon" onClick={() => { setEditingCustomer(c); setShowForm(true) }}>
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" onClick={() => handleDelete(c.id)}>
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

      <CustomerFormDialog open={showForm} onOpenChange={setShowForm} customer={editingCustomer} onSuccess={loadCustomers} />
    </div>
  )
}

function CustomerFormDialog({ open, onOpenChange, customer, onSuccess }: {
  open: boolean; onOpenChange: (v: boolean) => void; customer: Customer | null; onSuccess: () => void
}) {
  const { register, handleSubmit, reset, formState: { isSubmitting } } = useForm({
    defaultValues: { full_name: "", dni: "", phone: "", email: "", birth_date: "" },
  })

  useEffect(() => {
    if (customer) {
      reset({ full_name: customer.full_name, dni: customer.dni || "", phone: customer.phone || "", email: customer.email || "", birth_date: customer.birth_date || "" })
    } else {
      reset({ full_name: "", dni: "", phone: "", email: "", birth_date: "" })
    }
  }, [customer, reset])

  async function onSubmit(data: any) {
    if (!supabase) return
    if (customer) {
      await supabase.from("customers").update(data).eq("id", customer.id)
    } else {
      await supabase.from("customers").insert(data)
    }
    onOpenChange(false)
    onSuccess()
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogHeader>
        <DialogTitle>{customer ? "Editar cliente" : "Nuevo cliente"}</DialogTitle>
        <DialogDescription>{customer ? "Actualiza los datos del cliente" : "Registra un nuevo cliente"}</DialogDescription>
      </DialogHeader>
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-2 sm:col-span-2">
            <Label>Nombre completo *</Label>
            <Input {...register("full_name", { required: true })} placeholder="Nombre del cliente" />
          </div>
          <div className="space-y-2">
            <Label>DNI</Label>
            <Input {...register("dni")} placeholder="12345678" />
          </div>
          <div className="space-y-2">
            <Label>Teléfono</Label>
            <Input {...register("phone")} placeholder="2 222111" />
          </div>
          <div className="space-y-2">
            <Label>Email</Label>
            <Input {...register("email")} type="email" placeholder="cliente@ejemplo.com" />
          </div>
          <div className="space-y-2">
            <Label>Fecha de nacimiento</Label>
            <Input {...register("birth_date")} type="date" />
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancelar</Button>
          <Button type="submit" disabled={isSubmitting}>{customer ? "Actualizar" : "Crear cliente"}</Button>
        </DialogFooter>
      </form>
    </Dialog>
  )
}

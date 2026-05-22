import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Label } from "@/components/ui/card"
import { Select } from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { useAppStore } from "@/stores/useAppStore"
import { RefreshCw, Wifi, WifiOff } from "lucide-react"

export function SettingsPage() {
  const { sinConfig, updateSIN } = useAppStore()
  const [saving, setSaving] = useState(false)

  function handleSave(e: React.FormEvent) {
    e.preventDefault()
    setSaving(true)
    const formData = new FormData(e.target as HTMLFormElement)
    updateSIN({
      nit: formData.get("nit") as string,
      razon_social: formData.get("razon_social") as string,
      actividad_economica: formData.get("actividad_economica") as string,
      domicilio_fiscal: formData.get("domicilio_fiscal") as string,
      telefono: formData.get("telefono") as string,
      tipo_documento_sector: formData.get("tipo_documento_sector") as string,
      leyenda_ley_453: formData.get("leyenda_ley_453") as string,
      leyenda_sin: formData.get("leyenda_sin") as string,
    })
    localStorage.setItem("sin-config", JSON.stringify(sinConfig))
    setTimeout(() => setSaving(false), 1000)
  }

  return (
    <div className="space-y-6 max-w-3xl">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Configuración</h1>
        <p className="text-muted-foreground">Configuración del sistema y facturación electrónica</p>
      </div>

      <form onSubmit={handleSave} className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Datos del Contribuyente</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="nit">NIT</Label>
              <Input id="nit" name="nit" defaultValue={sinConfig.nit} placeholder="123456022" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="razon_social">Razón Social</Label>
              <Input id="razon_social" name="razon_social" defaultValue={sinConfig.razon_social} placeholder="Farmacia Salud Bolivia S.R.L." />
            </div>
            <div className="space-y-2 sm:col-span-2">
              <Label htmlFor="actividad_economica">Actividad Económica</Label>
              <Input id="actividad_economica" name="actividad_economica" defaultValue={sinConfig.actividad_economica} placeholder="Venta de productos farmacéuticos" />
            </div>
            <div className="space-y-2 sm:col-span-2">
              <Label htmlFor="domicilio_fiscal">Domicilio Fiscal</Label>
              <Input id="domicilio_fiscal" name="domicilio_fiscal" defaultValue={sinConfig.domicilio_fiscal} placeholder="Calle Mercado N°50, La Paz" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="telefono">Teléfono</Label>
              <Input id="telefono" name="telefono" defaultValue={sinConfig.telefono} placeholder="2-222111" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Estado SIN</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-4">
              <Badge variant={sinConfig.is_online ? "success" : "destructive"} className="gap-1">
                {sinConfig.is_online ? <><Wifi className="h-3 w-3" /> En línea</> : <><WifiOff className="h-3 w-3" /> Desconectado</>}
              </Badge>
              <Button type="button" variant="outline" size="sm" onClick={() => updateSIN({ is_online: !sinConfig.is_online })}>
                <RefreshCw className="h-3 w-3" />
                {sinConfig.is_online ? "Desconectar" : "Conectar"}
              </Button>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-1">
                <Label className="text-xs text-muted-foreground">CUIS</Label>
                <p className="text-sm font-mono">{sinConfig.cuis || "—"}</p>
              </div>
              <div className="space-y-1">
                <Label className="text-xs text-muted-foreground">CUFD</Label>
                <p className="text-sm font-mono">{sinConfig.cufd || "—"}</p>
              </div>
              <div className="space-y-1">
                <Label className="text-xs text-muted-foreground">Vencimiento CUFD</Label>
                <p className="text-sm">{sinConfig.cufd_expiry ? new Date(sinConfig.cufd_expiry).toLocaleString("es-BO") : "—"}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Configuración de Facturas</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4">
            <div className="space-y-2">
              <Label htmlFor="tipo_documento_sector">Tipo Documento Sector</Label>
              <Select id="tipo_documento_sector" name="tipo_documento_sector"
                defaultValue={sinConfig.tipo_documento_sector}
                options={[
                  { value: "1", label: "Factura Compra-Venta" },
                  { value: "2", label: "Factura de Exportación" },
                  { value: "3", label: "Nota de Débito" },
                  { value: "4", label: "Nota de Crédito" },
                ]}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="leyenda_ley_453">Leyenda Ley 453</Label>
              <Input id="leyenda_ley_453" name="leyenda_ley_453" defaultValue={sinConfig.leyenda_ley_453} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="leyenda_sin">Leyenda SIN</Label>
              <Input id="leyenda_sin" name="leyenda_sin" defaultValue={sinConfig.leyenda_sin} />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={saving}>
            {saving ? "Guardado" : "Guardar configuración"}
          </Button>
        </div>
      </form>
    </div>
  )
}

import { Settings } from "lucide-react"

export function SettingsPage() {
  return (
    <div className="flex h-full items-center justify-center">
      <div className="text-center">
        <Settings className="mx-auto h-16 w-16 text-muted-foreground" />
        <h2 className="mt-4 text-xl font-semibold">Configuración</h2>
        <p className="text-muted-foreground">Próximamente</p>
      </div>
    </div>
  )
}

import { useEffect, useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select } from "@/components/ui/select"
import { formatCurrency } from "@/utils/formatters"
import { IVA_RATE } from "@/utils/constants"
import { supabase } from "@/lib/supabase"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from "recharts"

const COLORS = ["#2563eb", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"]

interface FinanceSummary {
  totalSales: number
  totalPurchases: number
  profit: number
  margin: number
  creditFiscal: number
  debitFiscal: number
  ivaNeto: number
  inventoryValue: number
  salesCount: number
}

export function FinanzasPage() {
  const [period, setPeriod] = useState("month")
  const [summary, setSummary] = useState<FinanceSummary>({
    totalSales: 0, totalPurchases: 0, profit: 0, margin: 0,
    creditFiscal: 0, debitFiscal: 0, ivaNeto: 0, inventoryValue: 0, salesCount: 0,
  })
  const [dailySales, setDailySales] = useState<any[]>([])
  const [categoryDist, setCategoryDist] = useState<any[]>([])

  useEffect(() => { loadFinanzas() }, [period])

  async function loadFinanzas() {
    if (!supabase) return
    const now = new Date()
    let startDate: Date
    switch (period) {
      case "today": startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate()); break
      case "week": startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7); break
      case "month": startDate = new Date(now.getFullYear(), now.getMonth(), 1); break
      case "year": startDate = new Date(now.getFullYear(), 0, 1); break
      default: startDate = new Date(0)
    }

    const startISO = startDate.toISOString()

    const { data: sales } = await supabase
      .from("sales")
      .select("total_amount, tax_amount, created_at")
      .gte("created_at", startISO)
      .eq("status", "completed")

    const { data: purchases } = await supabase
      .from("purchases")
      .select("total_amount, credito_fiscal")
      .gte("created_at", startISO)
      .eq("status", "completed")

    const totalSales = (sales as Array<{ total_amount: number }> | null)?.reduce((s: number, sale: { total_amount: number }) => s + Number(sale.total_amount), 0) ?? 0
    const totalPurchases = (purchases as Array<{ total_amount: number }> | null)?.reduce((s: number, p: { total_amount: number }) => s + Number(p.total_amount), 0) ?? 0
    const debitFiscal = totalSales * IVA_RATE
    const creditFiscal = (purchases as Array<{ credito_fiscal: number }> | null)?.reduce((s: number, p: { credito_fiscal: number }) => s + Number(p.credito_fiscal), 0) ?? 0
    const profit = totalSales - totalPurchases
    const margin = totalSales > 0 ? profit / totalSales : 0

    // Daily sales
    const { data: dailyData } = await supabase
      .from("sales")
      .select("total_amount, created_at")
      .gte("created_at", startISO)
      .eq("status", "completed")
      .order("created_at")

    if (dailyData) {
      const grouped: Record<string, number> = {} as Record<string, number>
      const dailyArr = dailyData as Array<{ total_amount: number; created_at: string }>
      dailyArr.forEach((s) => {
        const day = new Date(s.created_at).toLocaleDateString("es-BO", { day: "2-digit", month: "short" })
        grouped[day] = (grouped[day] || 0) + Number(s.total_amount)
      })
      setDailySales(Object.entries(grouped).map(([name, total]) => ({ name, total: Math.round(total * 100) / 100 })))
    }

    // Inventory value
    const { data: products } = await supabase
      .from("products")
      .select("cost_price, stock_quantity, category:categories(name)")
      .eq("is_active", true)

    const inventoryValue = (products as Array<{ cost_price: number; stock_quantity: number }> | null)?.reduce((s: number, p: { cost_price: number; stock_quantity: number }) => s + Number(p.cost_price) * p.stock_quantity, 0) ?? 0
    if (products) {
      const catMap: Record<string, number> = {}
      products.forEach((p: any) => {
        const name = p.category?.name || "Sin categoría"
        catMap[name] = (catMap[name] || 0) + (Number(p.cost_price) * p.stock_quantity)
      })
      setCategoryDist(Object.entries(catMap).map(([name, value]) => ({ name, value: Math.round(value) })))
    }

    setSummary({
      totalSales,
      totalPurchases,
      profit,
      margin,
      creditFiscal,
      debitFiscal,
      ivaNeto: debitFiscal - creditFiscal,
      inventoryValue,
      salesCount: sales?.length ?? 0,
    })
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Finanzas</h1>
          <p className="text-muted-foreground">Análisis financiero y fiscal</p>
        </div>
        <Select
          value={period}
          onChange={(e) => setPeriod(e.target.value)}
          options={[
            { value: "today", label: "Hoy" },
            { value: "week", label: "Últimos 7 días" },
            { value: "month", label: "Este mes" },
            { value: "year", label: "Este año" },
            { value: "all", label: "Todo" },
          ]}
          className="w-44"
        />
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Ventas totales</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-primary">{formatCurrency(summary.totalSales)}</div>
            <p className="text-xs text-muted-foreground">{summary.salesCount} transacciones</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Compras totales</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-destructive">{formatCurrency(summary.totalPurchases)}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Ganancia bruta</CardTitle>
          </CardHeader>
          <CardContent>
            <div className={`text-2xl font-bold ${summary.profit >= 0 ? "text-success" : "text-destructive"}`}>
              {formatCurrency(summary.profit)}
            </div>
            <p className="text-xs text-muted-foreground">Margen: {(summary.margin * 100).toFixed(1)}%</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Inversión inventario</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{formatCurrency(summary.inventoryValue)}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-sm font-medium">Crédito Fiscal vs Débito Fiscal (IVA)</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-6 md:grid-cols-3">
            <div className="space-y-1">
              <p className="text-sm text-muted-foreground">Crédito Fiscal (Compras)</p>
              <p className="text-2xl font-bold text-success">{formatCurrency(summary.creditFiscal)}</p>
              <p className="text-xs text-muted-foreground">13% de las compras</p>
            </div>
            <div className="space-y-1">
              <p className="text-sm text-muted-foreground">Débito Fiscal (Ventas)</p>
              <p className="text-2xl font-bold text-destructive">{formatCurrency(summary.debitFiscal)}</p>
              <p className="text-xs text-muted-foreground">13% de las ventas</p>
            </div>
            <div className="space-y-1">
              <p className="text-sm text-muted-foreground">IVA Neto a Pagar</p>
              <p className={`text-2xl font-bold ${summary.ivaNeto >= 0 ? "text-destructive" : "text-success"}`}>
                {formatCurrency(Math.abs(summary.ivaNeto))}
              </p>
              <p className="text-xs text-muted-foreground">{summary.ivaNeto >= 0 ? "A pagar al SIN" : "Crédito a favor"}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Ventas por día</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72">
              {dailySales.length > 0 ? (
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={dailySales}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" fontSize={12} />
                    <YAxis fontSize={12} />
                    <Tooltip formatter={(value: any) => formatCurrency(Number(value))} />
                    <Bar dataKey="total" fill="#2563eb" radius={[4, 4, 0, 0]} />
                  </BarChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">Sin datos</div>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Inventario por categoría</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72">
              {categoryDist.length > 0 ? (
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie data={categoryDist} cx="50%" cy="50%" innerRadius={60} outerRadius={100} dataKey="value" label={({ name, percent }: any) => `${name} ${((percent ?? 0) * 100).toFixed(0)}%`}>
                      {categoryDist.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                    </Pie>
                    <Tooltip formatter={(value: any) => formatCurrency(Number(value))} />
                    <Legend />
                  </PieChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">Sin datos</div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

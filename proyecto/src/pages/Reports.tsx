import { useEffect, useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select } from "@/components/ui/select"
import { supabase } from "@/lib/supabase"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from "recharts"

const COLORS = ["#2563eb", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6", "#ec4899", "#14b8a6"]

export function ReportsPage() {
  const [period, setPeriod] = useState("today")
  const [dailySales, setDailySales] = useState<any[]>([])
  const [topProducts, setTopProducts] = useState<any[]>([])
  const [categoryDist, setCategoryDist] = useState<any[]>([])
  const [summary, setSummary] = useState({ total: 0, count: 0, avg: 0, profit: 0 })

  useEffect(() => {
    loadReports()
  }, [period])

  async function loadReports() {
    const now = new Date()
    let startDate: Date

    switch (period) {
      case "today": startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate()); break
      case "week": startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7); break
      case "month": startDate = new Date(now.getFullYear(), now.getMonth(), 1); break
      case "year": startDate = new Date(now.getFullYear(), 0, 1); break
      default: startDate = new Date(0)
    }

    // Sales summary
    const { data: sales } = await supabase
      .from("sales")
      .select("total_amount, tax_amount, created_at")
      .gte("created_at", startDate.toISOString())
      .eq("status", "completed")

    if (sales && sales.length > 0) {
      const total = sales.reduce((s, sale) => s + Number(sale.total_amount), 0)
      const count = sales.length
      setSummary({
        total,
        count,
        avg: total / count,
        profit: total * 0.3,
      })
    }

    // Daily sales for chart
    const { data: dailyData } = await supabase
      .from("sales")
      .select("total_amount, created_at")
      .gte("created_at", startDate.toISOString())
      .eq("status", "completed")
      .order("created_at")

    if (dailyData) {
      const grouped: Record<string, number> = {}
      dailyData.forEach((s) => {
        const day = new Date(s.created_at).toLocaleDateString("es-MX", { day: "2-digit", month: "short" })
        grouped[day] = (grouped[day] || 0) + Number(s.total_amount)
      })
      setDailySales(Object.entries(grouped).map(([name, total]) => ({ name, total: Math.round(total * 100) / 100 })))
    }

    // Top products
    const { data: salesInPeriod } = await supabase
      .from("sales")
      .select("id")
      .gte("created_at", startDate.toISOString())
      .eq("status", "completed")
    const saleIds = salesInPeriod?.map(s => s.id) ?? []

    if (saleIds.length > 0) {
      const { data: items } = await supabase
        .from("sale_items")
        .select("product_id, quantity, subtotal, product:products(name)")
        .in("sale_id", saleIds)
        .limit(500)

      if (items) {
        const prodMap: Record<string, { name: string; qty: number; total: number }> = {}
        items.forEach((item: any) => {
          const id = item.product_id
          if (!prodMap[id]) prodMap[id] = { name: item.product?.name || "Producto", qty: 0, total: 0 }
          prodMap[id].qty += item.quantity
          prodMap[id].total += Number(item.subtotal)
        })
        setTopProducts(Object.values(prodMap).sort((a, b) => b.qty - a.qty).slice(0, 10))
      }
    }

    // Category distribution
    const { data: products } = await supabase
      .from("products")
      .select("category:categories(name), stock_quantity")
      .eq("is_active", true)

    if (products) {
      const catMap: Record<string, number> = {}
      products.forEach((p: any) => {
        const name = p.category?.name || "Sin categoría"
        catMap[name] = (catMap[name] || 0) + p.stock_quantity
      })
      setCategoryDist(Object.entries(catMap).map(([name, value]) => ({ name, value })))
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Reportes</h1>
          <p className="text-muted-foreground">Análisis de ventas y rendimiento</p>
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

      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Ventas totales</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${summary.total.toFixed(2)}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Transacciones</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{summary.count}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Ticket promedio</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${summary.avg.toFixed(2)}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Ganancia estimada</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${summary.profit.toFixed(2)}</div>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Ventas por día</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={dailySales}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="name" fontSize={12} />
                  <YAxis fontSize={12} />
                  <Tooltip />
                  <Bar dataKey="total" fill="#2563eb" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Productos más vendidos</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {topProducts.length === 0 ? (
                <p className="text-sm text-muted-foreground text-center py-8">Sin datos</p>
              ) : (
                topProducts.map((p, i) => (
                  <div key={i} className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-bold text-muted-foreground">#{i + 1}</span>
                      <span className="text-sm">{p.name}</span>
                    </div>
                    <div className="text-right">
                      <span className="text-sm font-bold">{p.qty} uds.</span>
                      <span className="text-xs text-muted-foreground ml-2">${p.total.toFixed(2)}</span>
                    </div>
                  </div>
                ))
              )}
            </div>
          </CardContent>
        </Card>

        <Card className="md:col-span-2">
          <CardHeader>
            <CardTitle>Distribución de inventario por categoría</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                    <Pie
                    data={categoryDist}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={100}
                    dataKey="value"
                    label={({ name, percent }: any) => `${name} ${((percent ?? 0) * 100).toFixed(0)}%`}
                  >
                    {categoryDist.map((_, i) => (
                      <Cell key={i} fill={COLORS[i % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

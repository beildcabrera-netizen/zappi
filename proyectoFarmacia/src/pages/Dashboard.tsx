import { useEffect, useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { formatCurrency } from "@/utils/formatters"
import { supabase } from "@/lib/supabase"
import { DollarSign, Package, AlertTriangle, Clock } from "lucide-react"
import { XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from "recharts"

interface DashboardStats {
  todaySales: number
  totalProducts: number
  lowStock: number
  expiringSoon: number
  todayTransactions: number
  salesTrend: { name: string; total: number }[]
  topProducts: { name: string; qty: number; total: number }[]
}

export function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats>({
    todaySales: 0,
    totalProducts: 0,
    lowStock: 0,
    expiringSoon: 0,
    todayTransactions: 0,
    salesTrend: [],
    topProducts: [],
  })
  useEffect(() => {
    loadStats()
  }, [])

  async function loadStats() {
    if (!supabase) return
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const thirtyDaysFromNow = new Date()
    thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30)

    const now = new Date()
    const weekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7)

    const { count: totalProducts } = await supabase
      .from("products")
      .select("*", { count: "exact", head: true })
      .eq("is_active", true)

    const { data: allProducts } = await supabase
      .from("products")
      .select("stock_quantity, min_stock_alert")

    const lowStockCount = (allProducts as Array<{ stock_quantity: number; min_stock_alert: number }> | null)?.filter(
      (p) => p.stock_quantity > 0 && p.stock_quantity <= p.min_stock_alert
    ).length ?? 0

    const { data: expiring } = await supabase
      .from("product_batches")
      .select("id")
      .lte("expiry_date", thirtyDaysFromNow.toISOString())
      .gt("expiry_date", today.toISOString())

    const { data: todaySales } = await supabase
      .from("sales")
      .select("total_amount")
      .gte("created_at", today.toISOString())
      .eq("status", "completed")

    const todayTotal = (todaySales as Array<{ total_amount: number }> | null)?.reduce((sum: number, s: { total_amount: number }) => sum + Number(s.total_amount), 0) ?? 0
    const todayCount = todaySales?.length ?? 0

    const { data: weeklySales } = await supabase
      .from("sales")
      .select("total_amount, created_at")
      .gte("created_at", weekAgo.toISOString())
      .eq("status", "completed")
      .order("created_at")

    const grouped: Record<string, number> = {}
      weeklySales?.forEach((s: { total_amount: number; created_at: string }) => {
      const day = new Date(s.created_at).toLocaleDateString("es-BO", { day: "2-digit", month: "short" })
      grouped[day] = (grouped[day] || 0) + Number(s.total_amount)
    })
    const salesTrend = Object.entries(grouped).map(([name, total]) => ({ name, total: Math.round(total * 100) / 100 }))

    const { data: saleIds } = await supabase
      .from("sales")
      .select("id")
      .gte("created_at", weekAgo.toISOString())
      .eq("status", "completed")

    let topProducts: { name: string; qty: number; total: number }[] = []
    if (saleIds && saleIds.length > 0) {
      const { data: items } = await supabase
        .from("sale_items")
        .select("product_id, quantity, subtotal, product:products(name)")
        .in("sale_id", saleIds.map((s) => s.id))
        .limit(500)

      if (items) {
        const prodMap: Record<string, { name: string; qty: number; total: number }> = {} as Record<string, { name: string; qty: number; total: number }>
        const itemsArr = items as Array<any>
        itemsArr.forEach((item: any) => {
          const id = item.product_id
          if (!prodMap[id]) prodMap[id] = { name: item.product?.name || "Producto", qty: 0, total: 0 }
          prodMap[id].qty += item.quantity
          prodMap[id].total += Number(item.subtotal)
        })
        topProducts = Object.values(prodMap).sort((a, b) => b.qty - a.qty).slice(0, 5)
      }
    }

    setStats({
      todaySales: todayTotal,
      totalProducts: totalProducts ?? 0,
      lowStock: lowStockCount,
        expiringSoon: (expiring as Array<any> | null)?.length ?? 0,
      todayTransactions: todayCount,
      salesTrend,
      topProducts,
    })
  }

  const summaryCards = [
    { title: "Ventas del día", value: formatCurrency(stats.todaySales), icon: DollarSign, color: "text-primary" },
    { title: "Productos activos", value: stats.totalProducts.toString(), icon: Package, color: "text-secondary" },
    { title: "Stock bajo", value: stats.lowStock.toString(), icon: AlertTriangle, color: "text-warning" },
    { title: "Por vencer (30d)", value: stats.expiringSoon.toString(), icon: Clock, color: "text-destructive" },
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground">Resumen del negocio — Farmacia Boliviana</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {summaryCards.map((card) => (
          <Card key={card.title}>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">{card.title}</CardTitle>
              <card.icon className={`h-4 w-4 ${card.color}`} />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{card.value}</div>
              {card.title === "Ventas del día" && (
                <p className="text-xs text-muted-foreground">{stats.todayTransactions} transacciones</p>
              )}
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Ventas (7 días)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              {stats.salesTrend.length > 0 ? (
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart data={stats.salesTrend}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" fontSize={12} />
                    <YAxis fontSize={12} />
                    <Tooltip formatter={(value: any) => formatCurrency(Number(value))} />
                    <Line type="monotone" dataKey="total" stroke="var(--color-primary)" strokeWidth={2} dot={{ r: 4 }} />
                  </LineChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">Sin datos</div>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Productos más vendidos</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {stats.topProducts.length > 0 ? (
                stats.topProducts.map((p, i) => (
                  <div key={i} className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-bold text-muted-foreground">#{i + 1}</span>
                      <span className="text-sm truncate max-w-[200px]">{p.name}</span>
                    </div>
                    <div className="text-right text-sm">
                      <span className="font-bold">{p.qty} uds.</span>
                      <span className="text-muted-foreground ml-2">{formatCurrency(p.total)}</span>
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-sm text-muted-foreground text-center py-8">Sin datos de ventas</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {(stats.lowStock > 0 || stats.expiringSoon > 0) && (
        <Card className="border-warning/50">
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium flex items-center gap-2">
              <AlertTriangle className="h-4 w-4 text-warning" />
              Alertas
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {stats.lowStock > 0 && (
              <div className="flex items-center justify-between">
                <span className="text-sm">Productos con stock bajo</span>
                <Badge variant="warning">{stats.lowStock}</Badge>
              </div>
            )}
            {stats.expiringSoon > 0 && (
              <div className="flex items-center justify-between">
                <span className="text-sm">Lotes por vencer en 30 días</span>
                <Badge variant="destructive">{stats.expiringSoon}</Badge>
              </div>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  )
}

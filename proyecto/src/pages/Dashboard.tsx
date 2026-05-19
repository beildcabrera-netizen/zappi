import { useEffect, useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { supabase } from "@/lib/supabase"
import { DollarSign, Package, AlertTriangle, ShoppingCart } from "lucide-react"

export function DashboardPage() {
  const [stats, setStats] = useState({
    todaySales: 0,
    totalProducts: 0,
    lowStock: 0,
    todayTransactions: 0,
  })

  useEffect(() => {
    async function loadStats() {
      const today = new Date()
      today.setHours(0, 0, 0, 0)

      const { count: totalProducts } = await supabase
        .from('products')
        .select('*', { count: 'exact', head: true })
        .eq('is_active', true)

      const { data: allProducts } = await supabase
        .from('products')
        .select('stock_quantity, min_stock_alert')

      const lowStockCount = allProducts?.filter(
        p => p.stock_quantity > 0 && p.stock_quantity <= p.min_stock_alert
      ).length ?? 0

      const { data: todaySales } = await supabase
        .from('sales')
        .select('total_amount')
        .gte('created_at', today.toISOString())
        .eq('status', 'completed')

      const todayTotal = todaySales?.reduce((sum, s) => sum + Number(s.total_amount), 0) ?? 0
      const todayCount = todaySales?.length ?? 0

      setStats({
        todaySales: todayTotal,
        totalProducts: totalProducts ?? 0,
        lowStock: lowStockCount,
        todayTransactions: todayCount,
      })
    }

    loadStats()
  }, [])

  const cards = [
    { title: "Ventas del día", value: `$${stats.todaySales.toFixed(2)}`, icon: DollarSign, color: "text-green-600" },
    { title: "Productos activos", value: stats.totalProducts.toString(), icon: Package, color: "text-blue-600" },
    { title: "Stock bajo", value: stats.lowStock.toString(), icon: AlertTriangle, color: "text-red-600" },
    { title: "Transacciones hoy", value: stats.todayTransactions.toString(), icon: ShoppingCart, color: "text-purple-600" },
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground">Resumen del negocio — AdmiLico</p>
      </div>
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {cards.map((card) => (
          <Card key={card.title}>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">{card.title}</CardTitle>
              <card.icon className={`h-4 w-4 ${card.color}`} />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{card.value}</div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}

import jsPDF from "jspdf"
import type { CartItem } from "@/stores/posStore"
import type { Customer } from "@/types"

interface TicketData {
  invoiceNumber: string
  cashierName: string
  customer: Customer | null
  items: CartItem[]
  subtotal: number
  tax: number
  discount: number
  total: number
  paymentMethod: string
  amountReceived: number
  change: number
  date: Date
}

export function generateTicket(data: TicketData): jsPDF {
  const doc = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: [80, 200],
  })

  const pageWidth = 80
  const margin = 5
  const centerX = pageWidth / 2
  let y = margin

  const font = (size: number, style: "normal" | "bold" = "normal") => {
    doc.setFont("helvetica", style)
    doc.setFontSize(size)
  }

  // Header
  font(14, "bold")
  doc.text("AdmiLico", centerX, y, { align: "center" })
  y += 5

  font(7, "normal")
  doc.text("Sistema POS para licorería", centerX, y, { align: "center" })
  y += 4

  // Separator
  doc.setDrawColor(0)
  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  // Receipt info
  font(7, "bold")
  doc.text(`Ticket: ${data.invoiceNumber}`, margin, y)
  y += 3.5
  font(7, "normal")
  doc.text(`Fecha: ${data.date.toLocaleString("es-MX", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  })}`, margin, y)
  y += 3.5
  doc.text(`Cajero: ${data.cashierName}`, margin, y)
  y += 3.5

  if (data.customer) {
    doc.text(`Cliente: ${data.customer.full_name}`, margin, y)
    y += 3.5
  }

  // Separator
  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  // Column headers
  font(6, "bold")
  doc.text("Cant", margin, y)
  doc.text("Producto", margin + 8, y)
  doc.text("P.U.", pageWidth - margin - 20, y, { align: "right" })
  doc.text("Total", pageWidth - margin, y, { align: "right" })
  y += 3

  doc.line(margin, y, pageWidth - margin, y)
  y += 2

  // Items
  font(6, "normal")
  for (const item of data.items) {
    const name = item.product.name.length > 22
      ? item.product.name.substring(0, 22) + "..."
      : item.product.name

    doc.text(`${item.quantity}`, margin, y)
    doc.text(name, margin + 8, y)
    doc.text(`$${item.unit_price.toFixed(2)}`, pageWidth - margin - 25, y, { align: "right" })
    doc.text(`$${item.subtotal.toFixed(2)}`, pageWidth - margin, y, { align: "right" })
    y += 4

    // Check if we need a new page
    if (y > 270) {
      doc.addPage()
      y = margin
    }
  }

  // Separator
  y += 2
  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  // Totals
  const totals = [
    { label: "Subtotal", value: data.subtotal },
    { label: "IVA (18%)", value: data.tax },
  ]

  if (data.discount > 0) {
    totals.push({ label: "Descuento", value: -data.discount })
  }

  for (const t of totals) {
    font(7, "normal")
    doc.text(t.label, margin, y)
    doc.text(`$${t.value.toFixed(2)}`, pageWidth - margin, y, { align: "right" })
    y += 4
  }

  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(10, "bold")
  doc.text("TOTAL", margin, y)
  doc.text(`$${data.total.toFixed(2)}`, pageWidth - margin, y, { align: "right" })
  y += 5

  // Payment info
  font(7, "normal")
  const methodLabels: Record<string, string> = {
    cash: "Efectivo",
    card: "Tarjeta",
    transfer: "Transferencia",
  }
  const methodLabel = methodLabels[data.paymentMethod] || data.paymentMethod

  doc.text(`Método de pago: ${methodLabel}`, margin, y)
  y += 3.5

  if (data.paymentMethod === "cash") {
    doc.text(`Recibido: $${data.amountReceived.toFixed(2)}`, margin, y)
    y += 3.5
    doc.text(`Cambio: $${data.change.toFixed(2)}`, margin, y)
    y += 3.5
  }

  // Footer
  y += 4
  doc.line(margin, y, pageWidth - margin, y)
  y += 5

  font(7, "bold")
  doc.text("¡Gracias por su compra!", centerX, y, { align: "center" })
  y += 4
  font(6, "normal")
  doc.text("Consumo responsable. No compartir con", centerX, y, { align: "center" })
  y += 3
  doc.text("menores de edad.", centerX, y, { align: "center" })

  // Final page height
  const finalHeight = y + margin
  doc.internal.pageSize.height = Math.max(finalHeight, 40)
  doc.internal.pageSize.width = pageWidth

  return doc
}

export function printTicket(doc: jsPDF) {
  window.open(doc.output("bloburl"), "_blank")
}

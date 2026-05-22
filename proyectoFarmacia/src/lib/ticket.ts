import jsPDF from "jspdf"
import { CURRENCY_SYMBOL, IVA_RATE_LABEL } from "@/utils/constants"
import type { CartItem } from "@/types"

interface TicketData {
  invoiceNumber: string
  businessName: string
  businessNIT: string
  cashierName: string
  customerName: string | null
  customerDNI: string | null
  items: CartItem[]
  subtotal: number
  tax: number
  discount: number
  total: number
  paymentMethod: string
  cuf: string | null
  codigoAutorizacion: string | null
  leyenda: string
  date: Date
}

function formatCurrency(value: number): string {
  return `${CURRENCY_SYMBOL} ${value.toFixed(2)}`
}

export function generateInvoicePDF(data: TicketData): jsPDF {
  const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: [80, 280] })
  const pageWidth = 80
  const margin = 5
  const centerX = pageWidth / 2
  let y = margin

  const font = (size: number, style: "normal" | "bold" = "normal") => {
    doc.setFont("helvetica", style)
    doc.setFontSize(size)
  }

  font(12, "bold")
  doc.text(data.businessName, centerX, y, { align: "center" })
  y += 5

  font(6, "normal")
  doc.text(`NIT: ${data.businessNIT}`, centerX, y, { align: "center" })
  y += 4

  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(6, "bold")
  doc.text("FACTURA", centerX, y, { align: "center" })
  y += 5

  font(6, "normal")
  doc.text(`N°: ${data.invoiceNumber}`, margin, y)
  y += 3.5
  doc.text(`Fecha: ${data.date.toLocaleDateString("es-BO")} ${data.date.toLocaleTimeString("es-BO", { hour: "2-digit", minute: "2-digit" })}`, margin, y)
  y += 3.5
  doc.text(`Cajero: ${data.cashierName}`, margin, y)
  y += 3.5

  if (data.customerName) {
    doc.text(`Cliente: ${data.customerName}`, margin, y)
    y += 3.5
  }
  if (data.customerDNI) {
    doc.text(`NIT/CI: ${data.customerDNI}`, margin, y)
    y += 3.5
  }

  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(6, "bold")
  doc.text("Cant", margin, y)
  doc.text("Producto", margin + 8, y)
  doc.text("P.U.", pageWidth - margin - 20, y, { align: "right" })
  doc.text("Total", pageWidth - margin, y, { align: "right" })
  y += 3
  doc.line(margin, y, pageWidth - margin, y)
  y += 2

  font(6, "normal")
  for (const item of data.items) {
    const name = item.product.name.length > 20 ? item.product.name.substring(0, 20) + "..." : item.product.name
    doc.text(`${item.quantity}`, margin, y)
    doc.text(name, margin + 8, y)
    doc.text(formatCurrency(item.unit_price), pageWidth - margin - 25, y, { align: "right" })
    doc.text(formatCurrency(item.subtotal), pageWidth - margin, y, { align: "right" })
    y += 4
    if (y > 270) { doc.addPage(); y = margin }
  }

  y += 2
  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(7, "normal")
  doc.text("Subtotal", margin, y)
  doc.text(formatCurrency(data.subtotal), pageWidth - margin, y, { align: "right" })
  y += 4

  doc.text(`IVA (${IVA_RATE_LABEL})`, margin, y)
  doc.text(formatCurrency(data.tax), pageWidth - margin, y, { align: "right" })
  y += 4

  if (data.discount > 0) {
    doc.text("Descuento", margin, y)
    doc.text(`-${formatCurrency(data.discount)}`, pageWidth - margin, y, { align: "right" })
    y += 4
  }

  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(10, "bold")
  doc.text("TOTAL", margin, y)
  doc.text(formatCurrency(data.total), pageWidth - margin, y, { align: "right" })
  y += 5

  font(6, "normal")
  const methodLabels: Record<string, string> = { cash: "Efectivo", card: "Tarjeta", transfer: "Transferencia" }
  doc.text(`Método de pago: ${methodLabels[data.paymentMethod] || data.paymentMethod}`, margin, y)
  y += 3.5

  y += 2
  doc.line(margin, y, pageWidth - margin, y)
  y += 4

  font(5, "normal")

  if (data.cuf) {
    doc.text(`CUF: ${data.cuf}`, margin, y)
    y += 3
  }
  if (data.codigoAutorizacion) {
    doc.text(`Código Autorización: ${data.codigoAutorizacion}`, margin, y)
    y += 3
  }

  y += 2
  doc.text(data.leyenda, centerX, y, { align: "center" })
  y += 4
  doc.text("Esta factura contribuye al desarrollo del país.", centerX, y, { align: "center" })
  y += 6

  font(7, "bold")
  doc.text("¡Gracias por su preferencia!", centerX, y, { align: "center" })

  const finalHeight = y + margin
  doc.internal.pageSize.height = Math.max(finalHeight, 60)
  doc.internal.pageSize.width = pageWidth

  return doc
}

export function printPDF(doc: jsPDF) {
  window.open(doc.output("bloburl"), "_blank")
}

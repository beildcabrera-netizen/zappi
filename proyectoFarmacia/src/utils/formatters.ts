const CURRENCY_FORMATTER = new Intl.NumberFormat("es-BO", {
  style: "currency",
  currency: "BOB",
  minimumFractionDigits: 2,
})

const DATE_FORMATTER = new Intl.DateTimeFormat("es-BO", {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
})

const DATETIME_FORMATTER = new Intl.DateTimeFormat("es-BO", {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
})

export function formatCurrency(value: number): string {
  return CURRENCY_FORMATTER.format(value)
}

export function formatDate(date: string | Date): string {
  return DATE_FORMATTER.format(new Date(date))
}

export function formatDateTime(date: string | Date): string {
  return DATETIME_FORMATTER.format(new Date(date))
}

export function formatPercentage(value: number): string {
  return `${(value * 100).toFixed(1)}%`
}

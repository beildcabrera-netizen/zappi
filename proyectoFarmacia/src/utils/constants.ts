export const IVA_RATE = 0.13
export const IVA_RATE_LABEL = "13%"
export const CURRENCY_CODE = "BOB"
export const CURRENCY_SYMBOL = "Bs"

export const STOCK_ALERT_DAYS = 30
export const SEARCH_DEBOUNCE_MS = 200

export const ROLES = {
  ADMIN: "admin" as const,
  CASHIER: "cashier" as const,
  STOCK_MANAGER: "stock_manager" as const,
} as const

export const PAYMENT_METHODS = {
  CASH: "cash" as const,
  CARD: "card" as const,
  TRANSFER: "transfer" as const,
} as const

export const PURCHASE_STATUS = {
  PENDING: "pending" as const,
  COMPLETED: "completed" as const,
  CANCELLED: "cancelled" as const,
} as const

export const SALE_STATUS = {
  COMPLETED: "completed" as const,
  CANCELLED: "cancelled" as const,
  REFUNDED: "refunded" as const,
} as const

export const INVENTORY_LOG_TYPES = {
  ENTRY: "entry" as const,
  SALE: "sale" as const,
  ADJUSTMENT: "adjustment" as const,
  LOSS: "loss" as const,
} as const

export const SIN_DEFAULT_VALUES = {
  TIPO_DOCUMENTO_SECTOR: "1",
  LEYENDA_LEY_453: "Tienes derecho a la reclamación de tus medicamentos",
  LEYENDA_SIN: "Esta factura contribuye al desarrollo del país",
} as const

export type Role = "admin" | "cashier" | "stock_manager"

export interface Profile {
  id: string
  email: string
  full_name: string
  role: Role
  created_at: string
}

export interface Category {
  id: string
  name: string
  description: string | null
}

export interface Product {
  id: string
  name: string
  barcode: string | null
  category_id: string | null
  description: string | null
  image_url: string | null
  cost_price: number
  sale_price: number
  stock_quantity: number
  min_stock_alert: number
  alcohol_content: number | null
  volume_ml: number | null
  is_active: boolean
  created_at: string
  category?: Category
}

export interface Customer {
  id: string
  full_name: string
  phone: string | null
  email: string | null
  dni: string | null
  birth_date: string | null
  is_active: boolean
}

export interface Sale {
  id: string
  invoice_number: string
  customer_id: string | null
  cashier_id: string
  total_amount: number
  tax_amount: number
  discount: number
  payment_method: "cash" | "card" | "transfer"
  status: "completed" | "cancelled" | "refunded"
  created_at: string
  cashier?: Profile
  customer?: Customer
  items?: SaleItem[]
}

export interface SaleItem {
  id: string
  sale_id: string
  product_id: string
  quantity: number
  unit_price: number
  subtotal: number
  product?: Product
}

export interface InventoryLog {
  id: string
  product_id: string
  type: "entry" | "sale" | "adjustment" | "loss"
  quantity: number
  previous_stock: number
  new_stock: number
  notes: string | null
  created_by: string
  created_at: string
}

export interface Supplier {
  id: string
  name: string
  contact_name: string | null
  phone: string | null
  email: string | null
  address: string | null
  is_active: boolean
}

export interface PurchaseOrder {
  id: string
  supplier_id: string
  status: "pending" | "received" | "cancelled"
  total_amount: number
  created_by: string
  created_at: string
  supplier?: Supplier
  items?: PurchaseItem[]
}

export interface PurchaseItem {
  id: string
  purchase_order_id: string
  product_id: string
  quantity: number
  unit_cost: number
  subtotal: number
  product?: Product
}

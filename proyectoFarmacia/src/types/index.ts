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
  principio_activo: string | null
  concentracion: string | null
  forma_farmaceutica: string | null
  registro_sanitario: string | null
  is_active: boolean
  created_at: string
  category?: Category
}

export interface ProductBatch {
  id: string
  product_id: string
  batch_number: string
  expiry_date: string
  stock_quantity: number
  cost_price: number
  created_at: string
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
  cuf: string | null
  codigo_autorizacion: string | null
  created_at: string
  cashier?: Profile
  customer?: Customer
  items?: SaleItem[]
}

export interface SaleItem {
  id: string
  sale_id: string
  product_id: string
  batch_id: string | null
  quantity: number
  unit_price: number
  subtotal: number
  product?: Product
}

export interface InventoryLog {
  id: string
  product_id: string
  batch_id: string | null
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
  nit: string | null
  contact_name: string | null
  phone: string | null
  email: string | null
  address: string | null
  is_active: boolean
}

export interface Purchase {
  id: string
  supplier_id: string
  invoice_number: string
  nit_proveedor: string
  fecha_compra: string
  total_amount: number
  tax_amount: number
  credito_fiscal: number
  status: "pending" | "completed" | "cancelled"
  created_by: string
  created_at: string
  supplier?: Supplier
  items?: PurchaseItem[]
}

export interface PurchaseItem {
  id: string
  purchase_id: string
  product_id: string
  batch_number: string
  expiry_date: string
  quantity: number
  unit_cost: number
  subtotal: number
  product?: Product
}

export type PaymentMethod = "cash" | "card" | "transfer"

export interface CartItem {
  product: Product
  batch_id?: string
  quantity: number
  unit_price: number
  subtotal: number
}

export interface SINConfig {
  nit: string
  razon_social: string
  actividad_economica: string
  domicilio_fiscal: string
  telefono: string
  cuis: string
  cufd: string
  cufd_expiry: string | null
  tipo_documento_sector: string
  leyenda_ley_453: string
  leyenda_sin: string
  fecha_limite_emision: string | null
  is_online: boolean
}

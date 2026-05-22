import { create } from "zustand"
import type { Product, Customer, SINConfig, CartItem } from "@/types"

interface AppState {
  user: null | { id: string; email: string }
  profile: null | { id: string; full_name: string; role: string }
  isAuthenticated: boolean
  isLoading: boolean

  cart: CartItem[]
  customer: Customer | null
  discount: number
  paymentMethod: "cash" | "card" | "transfer"
  amountReceived: number

  sinConfig: SINConfig

  alerts: { id: string; type: "warning" | "error" | "info"; message: string }[]

  setUser: (user: null | { id: string; email: string }) => void
  setProfile: (profile: null | { id: string; full_name: string; role: string }) => void
  setLoading: (loading: boolean) => void
  logout: () => void

  addToCart: (product: Product) => void
  removeFromCart: (productId: string) => void
  updateQuantity: (productId: string, quantity: number) => void
  setCustomer: (customer: Customer | null) => void
  setDiscount: (discount: number) => void
  setPaymentMethod: (method: "cash" | "card" | "transfer") => void
  setAmountReceived: (amount: number) => void
  clearCart: () => void

  updateSIN: (config: Partial<SINConfig>) => void

  addAlert: (alert: { type: "warning" | "error" | "info"; message: string }) => void
  removeAlert: (id: string) => void
}

export const useAppStore = create<AppState>((set, get) => ({
  user: null,
  profile: null,
  isAuthenticated: false,
  isLoading: true,

  cart: [],
  customer: null,
  discount: 0,
  paymentMethod: "cash",
  amountReceived: 0,

  sinConfig: {
    nit: "",
    razon_social: "",
    actividad_economica: "",
    domicilio_fiscal: "",
    telefono: "",
    cuis: "",
    cufd: "",
    cufd_expiry: null,
    tipo_documento_sector: "1",
    leyenda_ley_453: "Tienes derecho a la reclamación de tus medicamentos",
    leyenda_sin: "Esta factura contribuye al desarrollo del país",
    fecha_limite_emision: null,
    is_online: false,
  },

  alerts: [],

  setUser: (user) => set({ user, isAuthenticated: !!user }),
  setProfile: (profile) => set({ profile }),
  setLoading: (isLoading) => set({ isLoading }),
  logout: () => set({ user: null, profile: null, isAuthenticated: false, cart: [], customer: null }),

  addToCart: (product) => {
    const { cart } = get()
    const existing = cart.find((item) => item.product.id === product.id)
    if (existing) {
      set({
        cart: cart.map((item) =>
          item.product.id === product.id
            ? { ...item, quantity: item.quantity + 1, subtotal: (item.quantity + 1) * item.unit_price }
            : item
        ),
      })
    } else {
      set({
        cart: [...cart, { product, quantity: 1, unit_price: product.sale_price, subtotal: product.sale_price }],
      })
    }
  },

  removeFromCart: (productId) => {
    set({ cart: get().cart.filter((item) => item.product.id !== productId) })
  },

  updateQuantity: (productId, quantity) => {
    if (quantity <= 0) {
      get().removeFromCart(productId)
      return
    }
    set({
      cart: get().cart.map((item) =>
        item.product.id === productId
          ? { ...item, quantity, subtotal: quantity * item.unit_price }
          : item
      ),
    })
  },

  setCustomer: (customer) => set({ customer }),
  setDiscount: (discount) => set({ discount }),
  setPaymentMethod: (method) => set({ paymentMethod: method }),
  setAmountReceived: (amount) => set({ amountReceived: amount }),
  clearCart: () => set({ cart: [], customer: null, discount: 0, amountReceived: 0 }),

  updateSIN: (config) => set({ sinConfig: { ...get().sinConfig, ...config } }),

  addAlert: (alert) =>
    set({ alerts: [...get().alerts, { ...alert, id: crypto.randomUUID() }] }),
  removeAlert: (id) =>
    set({ alerts: get().alerts.filter((a) => a.id !== id) }),
}))

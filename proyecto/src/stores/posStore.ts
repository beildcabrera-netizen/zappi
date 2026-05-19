import { create } from "zustand"
import type { Product, Customer } from "@/types"

export interface CartItem {
  product: Product
  quantity: number
  unit_price: number
  subtotal: number
}

interface PosState {
  cart: CartItem[]
  customer: Customer | null
  discount: number
  paymentMethod: "cash" | "card" | "transfer"
  amountReceived: number
  addToCart: (product: Product) => void
  removeFromCart: (productId: string) => void
  updateQuantity: (productId: string, quantity: number) => void
  setCustomer: (customer: Customer | null) => void
  setDiscount: (discount: number) => void
  setPaymentMethod: (method: "cash" | "card" | "transfer") => void
  setAmountReceived: (amount: number) => void
  clearCart: () => void
  getSubtotal: () => number
  getTax: () => number
  getTotal: () => number
  getChange: () => number
}

export const usePosStore = create<PosState>((set, get) => ({
  cart: [],
  customer: null,
  discount: 0,
  paymentMethod: "cash",
  amountReceived: 0,

  addToCart: (product) => {
    const { cart } = get()
    const existing = cart.find((item) => item.product.id === product.id)
    if (existing) {
      set({
        cart: cart.map((item) =>
          item.product.id === product.id
            ? {
                ...item,
                quantity: item.quantity + 1,
                subtotal: (item.quantity + 1) * item.unit_price,
              }
            : item
        ),
      })
    } else {
      set({
        cart: [
          ...cart,
          {
            product,
            quantity: 1,
            unit_price: Number(product.sale_price),
            subtotal: Number(product.sale_price),
          },
        ],
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

  getSubtotal: () => get().cart.reduce((sum, item) => sum + item.subtotal, 0),
  getTax: () => get().getSubtotal() * 0.18,
  getTotal: () => {
    const subtotal = get().getSubtotal()
    const tax = subtotal * 0.18
    return subtotal + tax - get().discount
  },
  getChange: () => {
    const total = get().getTotal()
    return Math.max(0, get().amountReceived - total)
  },
}))

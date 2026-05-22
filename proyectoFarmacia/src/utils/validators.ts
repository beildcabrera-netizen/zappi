const NIT_REGEX = /^\d{4,14}$/
const DNI_REGEX = /^\d{5,10}$/
const PHONE_REGEX = /^[2-9]\d{6,7}$/
const BARCODE_REGEX = /^\d{8,13}$/

const NIT_SUM_WEIGHTS = [2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5]

export function validateNIT(nit: string): boolean {
  if (!NIT_REGEX.test(nit)) return false
  const digits = nit.split("").map(Number)
  const lastDigit = digits.pop()!
  let sum = 0
  for (let i = 0; i < digits.length; i++) {
    sum += digits[i] * NIT_SUM_WEIGHTS[i]
  }
  const remainder = sum % 11
  const checkDigit = remainder === 0 ? 0 : 11 - remainder
  return checkDigit === lastDigit
}

export function validateDNI(dni: string): boolean {
  return DNI_REGEX.test(dni)
}

export function validatePhone(phone: string): boolean {
  const cleaned = phone.replace(/[\s-]/g, "")
  return PHONE_REGEX.test(cleaned)
}

export function validateBarcode(barcode: string): boolean {
  return BARCODE_REGEX.test(barcode)
}

export function validateEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

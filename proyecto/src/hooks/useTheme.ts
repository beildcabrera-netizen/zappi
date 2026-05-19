import { useEffect, useState } from "react"

type Theme = "light" | "dark"

export function useTheme() {
  const [theme, setThemeState] = useState<Theme>(() => {
    if (typeof window !== "undefined") {
      const stored = localStorage.getItem("admilico-theme") as Theme | null
      if (stored) return stored
      return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
    }
    return "light"
  })

  useEffect(() => {
    const root = document.documentElement
    root.classList.toggle("dark", theme === "dark")
    localStorage.setItem("admilico-theme", theme)
  }, [theme])

  useEffect(() => {
    const mq = window.matchMedia("(prefers-color-scheme: dark)")
    const handler = (e: MediaQueryListEvent) => {
      const stored = localStorage.getItem("admilico-theme")
      if (!stored) setThemeState(e.matches ? "dark" : "light")
    }
    mq.addEventListener("change", handler)
    return () => mq.removeEventListener("change", handler)
  }, [])

  const toggleTheme = () => setThemeState((prev) => (prev === "dark" ? "light" : "dark"))

  return { theme, toggleTheme }
}

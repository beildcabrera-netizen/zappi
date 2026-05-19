import { forwardRef, type ElementRef, type ComponentPropsWithoutRef } from "react"
import { cn } from "@/lib/utils"

const Dialog = ({ open, onOpenChange, children }: { open: boolean; onOpenChange: (open: boolean) => void; children: React.ReactNode }) => {
  if (!open) return null
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="fixed inset-0 bg-black/50" onClick={() => onOpenChange(false)} />
      <div className="relative z-50 w-full max-w-lg rounded-lg border bg-card p-6 shadow-lg mx-4">
        {children}
      </div>
    </div>
  )
}

const DialogTrigger = forwardRef<
  ElementRef<"button">,
  ComponentPropsWithoutRef<"button"> & { asChild?: boolean }
>(({ className, ...props }, ref) => (
  <button ref={ref} className={cn(className)} {...props} />
))
DialogTrigger.displayName = "DialogTrigger"

const DialogHeader = ({ className, ...props }: ComponentPropsWithoutRef<"div">) => (
  <div className={cn("mb-4 space-y-1.5", className)} {...props} />
)

const DialogTitle = ({ className, ...props }: ComponentPropsWithoutRef<"h2">) => (
  <h2 className={cn("text-lg font-semibold leading-none tracking-tight", className)} {...props} />
)

const DialogDescription = ({ className, ...props }: ComponentPropsWithoutRef<"p">) => (
  <p className={cn("text-sm text-muted-foreground", className)} {...props} />
)

const DialogFooter = ({ className, ...props }: ComponentPropsWithoutRef<"div">) => (
  <div className={cn("mt-6 flex items-center justify-end gap-2", className)} {...props} />
)

export { Dialog, DialogTrigger, DialogHeader, DialogTitle, DialogDescription, DialogFooter }

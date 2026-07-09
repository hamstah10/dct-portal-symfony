import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva } from "class-variance-authority";

import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[4px] text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-0 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 uppercase italic tracking-[0.02em] font-heading",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground border border-transparent hover:bg-[hsl(var(--primary-hover))] hover:shadow-[0_0_0_1px_hsla(var(--primary),0.35),0_8px_24px_hsla(var(--primary),0.22)]",
        destructive:
          "bg-destructive text-destructive-foreground border border-transparent hover:brightness-95",
        outline:
          "border border-border bg-transparent text-foreground hover:border-primary hover:text-primary",
        secondary:
          "bg-[hsl(var(--sidebar-bg))] text-primary-foreground border border-transparent hover:bg-[hsl(var(--card))]",
        ghost: "bg-transparent text-muted-foreground hover:bg-secondary hover:text-foreground",
        link: "text-primary underline-offset-4 hover:underline not-italic normal-case tracking-normal font-body",
      },
      size: {
        default: "h-11 px-5 py-[11px]",
        sm: "h-8 px-3 py-1.5 text-xs",
        lg: "h-12 px-8 py-3 text-base",
        icon: "h-9 w-9 not-italic normal-case tracking-normal",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

const Button = React.forwardRef(({ className, variant, size, asChild = false, ...props }, ref) => {
  const Comp = asChild ? Slot : "button"
  return (
    <Comp
      className={cn(buttonVariants({ variant, size, className }))}
      ref={ref}
      {...props} />
  );
})
Button.displayName = "Button"

export { Button, buttonVariants }

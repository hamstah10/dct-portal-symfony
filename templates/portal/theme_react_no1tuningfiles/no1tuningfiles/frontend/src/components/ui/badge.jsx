import * as React from "react"
import { cva } from "class-variance-authority";

import { cn } from "@/lib/utils"

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors focus:outline-none focus:ring-0 uppercase tracking-[0.06em] font-mono gap-1.5",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-danger-soft text-danger-foreground",
        secondary:
          "border-transparent bg-success-soft text-success-foreground",
        destructive:
          "border-transparent bg-warning-soft text-warning-foreground",
        outline: "border-border bg-secondary text-muted-foreground",
      },
      dot: {
        true: "before:content-[''] before:inline-block before:w-1.5 before:h-1.5 before:rounded-full before:bg-current before:shrink-0",
        false: "",
      },
    },
    defaultVariants: {
      variant: "default",
      dot: true,
    },
  }
)

function Badge({
  className,
  variant,
  dot,
  ...props
}) {
  return (<div className={cn(badgeVariants({ variant, dot }), className)} {...props} />);
}

export { Badge, badgeVariants }

import * as React from "react"

import { cn } from "@/lib/utils"

const Input = React.forwardRef(({ className, type, style, ...props }, ref) => {
  return (
    <input
      type={type}
      className={cn(
        "flex h-11 w-full rounded-[6px] border border-border bg-background px-3 py-2 text-sm text-foreground shadow-none transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground/90 focus-visible:outline-none focus-visible:border-primary focus-visible:ring-0 disabled:cursor-not-allowed disabled:opacity-50",
        className
      )}
      ref={ref}
      style={style}
      {...props} />
  );
})
Input.displayName = "Input"

export { Input }

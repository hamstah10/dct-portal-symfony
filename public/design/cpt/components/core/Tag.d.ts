import React from 'react';

export interface TagProps extends React.HTMLAttributes<HTMLSpanElement> {
  /** Highlighted (selected filter) state. */
  active?: boolean;
  /** When provided, renders a remove (×) affordance. */
  onRemove?: (() => void) | null;
  children?: React.ReactNode;
}

/** Pill chip for filters and selected facets (make, fuel, stage). */
export function Tag(props: TagProps): JSX.Element;

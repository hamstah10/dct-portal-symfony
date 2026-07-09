import React from 'react';

export interface TooltipProps {
  /** Tooltip text. */
  label: React.ReactNode;
  placement?: 'top' | 'bottom';
  /** Trigger element. */
  children: React.ReactNode;
}

/** Mono micro-label tooltip on hover/focus — explains stages, ECU terms, icons. */
export function Tooltip(props: TooltipProps): JSX.Element;

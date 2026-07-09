import React from 'react';

export interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  /** Semantic tone. */
  tone?: 'neutral' | 'accent' | 'success' | 'warning' | 'danger' | 'info';
  /** Solid ignition fill (overrides tone). */
  solid?: boolean;
  /** Leading status dot. */
  dot?: boolean;
  children?: React.ReactNode;
}

/** Small uppercase mono status label — stages, file states, gains. */
export function Badge(props: BadgeProps): JSX.Element;

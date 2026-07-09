import React from 'react';

export interface LogoProps extends React.HTMLAttributes<HTMLElement> {
  size?: 'sm' | 'md' | 'lg';
  /** Show the "Chiptuningfile" wordmark next to the mark. */
  showWordmark?: boolean;
  /** Small mono tagline under the wordmark; pass '' to hide. */
  tagline?: string;
  /** Use the red solid mark (for dark/colored backgrounds). */
  solidMark?: boolean;
  /** Render as a link. */
  href?: string | null;
  /** Path prefix to reach /assets (e.g. "../" from a nested page). */
  assetBase?: string;
}

/** Brand lockup: geometric boost-mark + wordmark. Placeholder mark — replace with official asset. */
export function Logo(props: LogoProps): JSX.Element;

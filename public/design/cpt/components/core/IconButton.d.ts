import React from 'react';

export interface IconButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /** Accessible label (also used as tooltip). Required. */
  label: string;
  variant?: 'ghost' | 'outline' | 'solid';
  size?: 'sm' | 'md' | 'lg';
  /** Icon node (Lucide glyph or svg). */
  children?: React.ReactNode;
}

/** Square icon-only button for toolbars and dense controls. */
export function IconButton(props: IconButtonProps): JSX.Element;

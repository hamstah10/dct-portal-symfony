import React from 'react';

export interface ToastProps {
  tone?: 'accent' | 'success' | 'warning' | 'danger';
  title?: React.ReactNode;
  /** Optional secondary message. */
  children?: React.ReactNode;
  /** When provided, shows a close affordance. */
  onClose?: () => void;
}

/** Dark carbon notification with semantic accent edge + Lucide status icon. */
export function Toast(props: ToastProps): JSX.Element;

import React from 'react';

export interface ModalProps {
  open: boolean;
  onClose?: () => void;
  /** Uppercase display title. */
  title?: React.ReactNode;
  size?: 'sm' | 'md' | 'lg';
  /** Footer actions row (right-aligned buttons). */
  footer?: React.ReactNode;
  children?: React.ReactNode;
}

/** Centered dialog with carbon scrim + backdrop blur. Closes on Esc / scrim click. */
export function Modal(props: ModalProps): JSX.Element | null;

import React from 'react';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /** Visual style. */
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  /** Control height. */
  size?: 'sm' | 'md' | 'lg';
  /** Stretch to container width. */
  block?: boolean;
  /** Show spinner and disable. */
  loading?: boolean;
  disabled?: boolean;
  /** Icon node placed before the label. */
  iconLeft?: React.ReactNode;
  /** Icon node placed after the label. */
  iconRight?: React.ReactNode;
  children?: React.ReactNode;
}

/**
 * Primary action control. One primary per view; imperative German verbs
 * ("Fahrzeug wählen", "Datei hochladen").
 * @startingPoint section="Core" subtitle="Buttons — primary, secondary, ghost, danger" viewport="700x150"
 */
export function Button(props: ButtonProps): JSX.Element;

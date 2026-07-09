import React from 'react';

export interface CheckboxProps extends React.InputHTMLAttributes<HTMLInputElement> {
  /** Inline label text. */
  label?: React.ReactNode;
  checked?: boolean;
  disabled?: boolean;
}

/** Checkbox with ignition-fill checked state. */
export function Checkbox(props: CheckboxProps): JSX.Element;

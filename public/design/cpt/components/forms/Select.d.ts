import React from 'react';

export interface SelectOption { value: string; label: string; }

export interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  disabled?: boolean;
  /** Placeholder shown as a disabled first option. */
  placeholder?: string;
  /** Options as strings or {value,label}. Alternatively pass <option> children. */
  options?: Array<string | SelectOption>;
}

/** Styled native select — used heavily in the vehicle picker (Marke → Modell → Motor). */
export function Select(props: SelectProps): JSX.Element;

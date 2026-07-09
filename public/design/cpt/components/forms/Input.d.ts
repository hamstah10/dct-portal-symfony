import React from 'react';

export interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'prefix'> {
  label?: string;
  /** Helper text below the field. */
  hint?: string;
  /** Error message — also flips the field into the error state. */
  error?: string;
  required?: boolean;
  disabled?: boolean;
  /** Leading affix (icon or short text/unit). */
  prefix?: React.ReactNode;
  /** Trailing affix (icon or unit, e.g. "PS"). */
  suffix?: React.ReactNode;
}

/** Text input with label, hint, error, and icon/unit affixes. */
export function Input(props: InputProps): JSX.Element;

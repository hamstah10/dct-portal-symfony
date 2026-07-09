import React from 'react';

export interface RadioProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: React.ReactNode;
  /** Group name — same across one set. */
  name: string;
  value?: string;
  checked?: boolean;
  disabled?: boolean;
}

/** Single-choice radio (tuning stage, fuel type). Group by shared `name`. */
export function Radio(props: RadioProps): JSX.Element;

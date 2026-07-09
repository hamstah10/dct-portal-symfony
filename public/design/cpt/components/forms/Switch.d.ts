import React from 'react';

export interface SwitchProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: React.ReactNode;
  checked?: boolean;
  disabled?: boolean;
}

/** Binary toggle — e.g. light/dark theme, eco-tuning on/off. Slight mechanical overshoot on flip. */
export function Switch(props: SwitchProps): JSX.Element;

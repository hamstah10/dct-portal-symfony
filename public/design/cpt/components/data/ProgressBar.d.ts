import React from 'react';

export interface ProgressBarProps extends React.HTMLAttributes<HTMLDivElement> {
  /** 0–100. Ignored when indeterminate. */
  value?: number;
  label?: string;
  showValue?: boolean;
  tone?: 'accent' | 'success';
  /** Looping bar for unknown-duration work (file processing). */
  indeterminate?: boolean;
}

/** Linear progress — upload/processing state, credit usage. */
export function ProgressBar(props: ProgressBarProps): JSX.Element;

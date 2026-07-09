import React from 'react';

export interface TabItem { id: string; label: string; icon?: string; count?: number; }

export interface TabsProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'onChange'> {
  /** Tabs as strings or {id,label,icon,count}. */
  tabs: Array<string | TabItem>;
  /** Selected tab id. */
  value: string;
  onChange?: (id: string) => void;
}

/** Underline tab bar with optional Lucide icon and count chip per tab. */
export function Tabs(props: TabsProps): JSX.Element;

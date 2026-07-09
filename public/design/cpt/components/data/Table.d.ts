import React from 'react';

export interface TableColumn {
  key: string;
  header: React.ReactNode;
  /** Right-align + mono numerals. */
  numeric?: boolean;
  /** Fixed column width (CSS value). */
  width?: string;
  /** Custom cell renderer: (row) => node. */
  render?: (row: any) => React.ReactNode;
}

export interface TableProps extends React.TableHTMLAttributes<HTMLTableElement> {
  columns: TableColumn[];
  rows: Array<Record<string, any>>;
  /** Row hover highlight. */
  hover?: boolean;
}

/** Data table for file lists, orders, and vehicle results. Mono uppercase headers; numeric columns right-align. */
export function Table(props: TableProps): JSX.Element;

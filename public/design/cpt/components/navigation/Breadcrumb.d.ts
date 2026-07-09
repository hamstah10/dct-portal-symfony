import React from 'react';

export interface BreadcrumbItem { label: string; href?: string; }

export interface BreadcrumbProps extends React.HTMLAttributes<HTMLElement> {
  /** Trail as strings or {label,href}; last item is the current page. */
  items: Array<string | BreadcrumbItem>;
}

/** Uppercase mono breadcrumb trail — vehicle drill-down (Start › PKW › Audi › A4). */
export function Breadcrumb(props: BreadcrumbProps): JSX.Element;

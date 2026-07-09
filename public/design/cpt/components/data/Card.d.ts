import React from 'react';

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  /** Uppercase display title in the header. */
  title?: React.ReactNode;
  /** Top media — image URL string or a node (e.g. a chart). */
  media?: React.ReactNode;
  /** Node rendered in the header's top-right (often a Badge). */
  badge?: React.ReactNode;
  /** Footer row (price + CTA, meta). */
  footer?: React.ReactNode;
  /** Accent top rule for highlighted/featured cards. */
  featured?: boolean;
  /** Hover lift + pointer for clickable cards. */
  interactive?: boolean;
  children?: React.ReactNode;
}

/**
 * Surface container — vehicle/file/product cards. Flat fill, hairline border, tight radius.
 * @startingPoint section="Data" subtitle="Card — product, vehicle, and file surfaces" viewport="700x420"
 */
export function Card(props: CardProps): JSX.Element;

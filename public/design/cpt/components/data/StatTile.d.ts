import React from 'react';

export interface StatTileProps extends React.HTMLAttributes<HTMLDivElement> {
  /** Uppercase mono key, e.g. "Leistung". */
  label: string;
  /** Main figure (number or formatted string). */
  value: React.ReactNode;
  /** Small unit appended to the value, e.g. "PS", "Nm". */
  unit?: string;
  /** Optional delta string ("+38") — coloured by sign with a trend arrow. */
  delta?: string | number;
  /** Render the value in ignition red (headline gain). */
  gain?: boolean;
  /** Dark carbon tile (for use on light backgrounds / dashboards). */
  dark?: boolean;
}

/**
 * Metric tile for power/torque figures and dyno readouts — tabular mono numerals.
 * @startingPoint section="Data" subtitle="StatTile — power, torque & dyno figures" viewport="700x180"
 */
export function StatTile(props: StatTileProps): JSX.Element;

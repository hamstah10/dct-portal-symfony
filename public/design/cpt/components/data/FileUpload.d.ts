import React from 'react';

export interface FileUploadProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'onDrop'> {
  /** Drop-prompt label. */
  title?: string;
  /** Mono hint line (accepted formats / size). */
  hint?: string;
  accept?: string;
  multiple?: boolean;
  /** Called with a File[] on pick or drop. */
  onFiles?: (files: File[]) => void;
}

/**
 * Drag-and-drop file dropzone for ECU file submission (BIN/ORI/MOD).
 * @startingPoint section="Data" subtitle="FileUpload — ECU file dropzone" viewport="700x240"
 */
export function FileUpload(props: FileUploadProps): JSX.Element;

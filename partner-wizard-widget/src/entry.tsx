import React from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { PartnerWizard } from './PartnerWizard';
import './styles.css';

class TfdPartnerWizardElement extends HTMLElement {
  private root?: Root;
  private mount?: HTMLDivElement;

  connectedCallback(): void {
    if (this.root) {
      return;
    }

    this.mount = document.createElement('div');
    this.appendChild(this.mount);

    const clientId = this.getAttribute('client-id') || 'no1';
    const apiBaseUrl = this.getAttribute('api-base-url') || '';
    const primaryColor = this.getAttribute('primary-color') || '#E31E24';
    const themeAttr = this.getAttribute('theme') || 'light';
    const theme = themeAttr === 'dark' ? 'dark' : 'light';
    // Nur die Admin-Vorschau setzt diese Attribute; ohne gültige Admin-Session lehnt die API ab.
    const editMode = this.getAttribute('edit-mode') === '1';
    const editCsrf = this.getAttribute('edit-csrf') || '';

    this.style.setProperty('--partner-wizard-primary', primaryColor);

    this.root = createRoot(this.mount);
    this.root.render(
      <PartnerWizard
        clientId={clientId}
        apiBaseUrl={apiBaseUrl}
        primaryColor={primaryColor}
        theme={theme}
        editMode={editMode}
        editCsrf={editCsrf}
      />,
    );
  }

  disconnectedCallback(): void {
    this.root?.unmount();
    this.root = undefined;
    this.mount = undefined;
  }
}

if (!customElements.get('tfd-partner-wizard')) {
  customElements.define('tfd-partner-wizard', TfdPartnerWizardElement);
}

export { PartnerWizard };

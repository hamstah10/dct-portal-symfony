const asNumber = (value) => {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === 'string' && value.trim() !== '') {
    const normalized = Number(value.replace(',', '.'));
    if (Number.isFinite(normalized)) {
      return normalized;
    }
  }

  return null;
};

export const PORTAL_CREDITS_UPDATED_EVENT = 'tfd:portal-credits-updated';

export const getBootstrapCredits = () => {
  const bootstrap = window?.portalReactBootstrap;
  if (!bootstrap || typeof bootstrap !== 'object') {
    return 0;
  }

  const fromSession = asNumber(bootstrap?.session?.credits);
  if (null !== fromSession) {
    return fromSession;
  }

  const fromDashboard = asNumber(bootstrap?.dashboard?.credits);
  if (null !== fromDashboard) {
    return fromDashboard;
  }

  const fromProfile = asNumber(bootstrap?.profile?.credits);
  if (null !== fromProfile) {
    return fromProfile;
  }

  return 0;
};

export const setBootstrapCredits = (credits) => {
  if (typeof window === 'undefined') {
    return;
  }

  const normalized = asNumber(credits);
  if (null === normalized) {
    return;
  }

  window.portalReactBootstrap = window.portalReactBootstrap && typeof window.portalReactBootstrap === 'object'
    ? window.portalReactBootstrap
    : {};

  window.portalReactBootstrap.session = window.portalReactBootstrap.session && typeof window.portalReactBootstrap.session === 'object'
    ? window.portalReactBootstrap.session
    : {};

  window.portalReactBootstrap.session.credits = normalized;

  window.dispatchEvent(new CustomEvent(PORTAL_CREDITS_UPDATED_EVENT, {
    detail: { credits: normalized },
  }));
};

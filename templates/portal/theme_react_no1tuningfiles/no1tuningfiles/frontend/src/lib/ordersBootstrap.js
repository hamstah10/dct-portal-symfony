const ensureString = (value, fallback = '') => {
  return typeof value === 'string' ? value : fallback;
};

const ensureNumber = (value, fallback = null) => {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === 'string' && value.trim() !== '' && Number.isFinite(Number(value))) {
    return Number(value);
  }

  return fallback;
};

const ensurePositiveInteger = (value, fallback = null) => {
  const normalized = ensureNumber(value, fallback);
  return Number.isInteger(normalized) && normalized > 0 ? normalized : fallback;
};

const normalizeOrder = (order, index) => {
  const safeId = ensureString(order?.id, `ORDER-${index + 1}`);
  const safeVehicle = order?.vehicle && typeof order.vehicle === 'object' ? order.vehicle : {};
  const safeTuning = order?.tuning && typeof order.tuning === 'object' ? order.tuning : {};
  const safePerformance = safeTuning.performance && typeof safeTuning.performance === 'object' ? safeTuning.performance : {};
  const safePricing = safeTuning.pricing && typeof safeTuning.pricing === 'object' ? safeTuning.pricing : {};
  const safeFiles = order?.files && typeof order.files === 'object' ? order.files : {};

  return {
    id: safeId,
    createdAt: ensureString(order?.createdAt, null),
    updatedAt: ensureString(order?.updatedAt, order?.createdAt ?? null),
    vehicle: {
      manufacturer: ensureString(safeVehicle.manufacturer, ''),
      model: ensureString(safeVehicle.model, ''),
      series: ensureString(safeVehicle.series, ''),
      year: ensureString(safeVehicle.year, ''),
      engine: ensureString(safeVehicle.engine, ''),
      power: ensureString(safeVehicle.power, ''),
      ecu: ensureString(safeVehicle.ecu, ''),
    },
    tuning: {
      type: ensureString(safeTuning.type, ''),
      options: Array.isArray(safeTuning.options)
        ? safeTuning.options
            .map((option) => ensureString(option, ''))
            .filter((option) => option.trim() !== '')
        : [],
      performance: {
        originalPs: ensureNumber(safePerformance.originalPs, null),
        originalNm: ensureNumber(safePerformance.originalNm, null),
        tunedPs: ensureNumber(safePerformance.tunedPs, null),
        tunedNm: ensureNumber(safePerformance.tunedNm, null),
        gainPs: ensureNumber(safePerformance.gainPs, null),
        gainNm: ensureNumber(safePerformance.gainNm, null),
      },
      pricing: {
        stageCredits: ensureNumber(safePricing.stageCredits, null),
        optionCredits: ensureNumber(safePricing.optionCredits, null),
        totalCredits: ensureNumber(safePricing.totalCredits, ensureNumber(order?.credits, 0) || 0),
        stagePriceEuro: ensureNumber(safePricing.stagePriceEuro, null),
        optionPriceEuro: ensureNumber(safePricing.optionPriceEuro, null),
        totalPriceEuro: ensureNumber(safePricing.totalPriceEuro, null),
      },
    },
    status: mapPortalStatusToReact(order?.status),
    credits: Number.isFinite(order?.credits) ? order.credits : 0,
    files: {
      original: ensureString(safeFiles.original, ''),
      originalSize: ensureNumber(safeFiles.originalSize, null),
      modified: ensureString(safeFiles.modified, ''),
      modifiedSize: ensureNumber(safeFiles.modifiedSize, null),
      modifiedDownloadUrl: ensureString(safeFiles.modifiedDownloadUrl, null),
    },
    autoMod: {
      enabled: Boolean(order?.autoMod?.enabled),
      status: ensureNumber(order?.autoMod?.status, null),
      failureReason: ensureString(order?.autoMod?.failureReason, null),
    },
    notes: ensureString(order?.notes, ''),
  };
};

export const AUTOMOD_STATUS = {
  UNAVAILABLE: 0,
  PENDING: 1,
  DECRYPTED: 2,
  ENCRYPTED: 3,
  COMPLETED: 4,
  FAILED: 9,
};

export const mapAutoModPhase = (autoMod) => {
  if (!autoMod?.enabled) {
    return 'none';
  }

  switch (autoMod.status) {
    case AUTOMOD_STATUS.PENDING:
      return 'pending';
    case AUTOMOD_STATUS.DECRYPTED:
      return 'processing';
    case AUTOMOD_STATUS.COMPLETED:
      return 'done';
    case AUTOMOD_STATUS.FAILED:
      return 'failed';
    default:
      return 'none';
  }
};

export const readOrdersBootstrap = () => {
  if (typeof window === 'undefined') {
    return [];
  }

  const items = window?.portalReactBootstrap?.orders?.items;
  if (!Array.isArray(items)) {
    return [];
  }

  return items.map((order, index) => normalizeOrder(order, index));
};

export const readOrdersCustomerId = () => {
  if (typeof window === 'undefined') {
    return null;
  }

  const ordersCustomerId = ensurePositiveInteger(window?.portalReactBootstrap?.orders?.customerId, null);
  if (ordersCustomerId !== null) {
    return ordersCustomerId;
  }

  const sessionCustomerId = ensurePositiveInteger(window?.portalReactBootstrap?.session?.customerId, null);
  return sessionCustomerId;
};

export const buildOrdersTopic = (customerId) => {
  const normalizedCustomerId = ensurePositiveInteger(customerId, null);
  if (normalizedCustomerId === null) {
    return null;
  }

  return `tfd/portal/customer/${normalizedCustomerId}/orders`;
};

export const getOrdersData = () => {
  return readOrdersBootstrap();
};

export const mapPortalStatusToReact = (status) => {
  switch (String(status || '').toLowerCase()) {
    case 'completed':
    case 'abgeschlossen':
      return 'completed';
    case 'in_progress':
    case 'processing':
    case 'in_bearbeitung':
      return 'processing';
    case 'parked':
    case 'geparkt':
      return 'parked';
    case 'rejected':
    case 'cancelled':
    case 'abgelehnt':
      return 'cancelled';
    case 'received':
    case 'pending':
    case 'eingegangen':
    default:
      return 'pending';
  }
};

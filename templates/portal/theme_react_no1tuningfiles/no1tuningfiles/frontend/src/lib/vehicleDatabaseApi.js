const parseJson = async (response) => {
  const text = await response.text();
  if (!text) {
    return null;
  }

  try {
    return JSON.parse(text);
  } catch (_error) {
    return null;
  }
};

const RETRYABLE_STATUSES = new Set([429, 502, 503, 504]);
const MAX_RETRIES = 2;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const toList = (payload) => {
  if (Array.isArray(payload)) {
    return payload;
  }

  if (payload && typeof payload === 'object' && Array.isArray(payload.data)) {
    return payload.data;
  }

  return [];
};

const fetchWithRetry = async (url, signal) => {
  let attempt = 0;

  while (attempt <= MAX_RETRIES) {
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal,
      });

      if (response.ok) {
        return response;
      }

      const shouldRetry = RETRYABLE_STATUSES.has(response.status) && attempt < MAX_RETRIES;
      if (!shouldRetry) {
        throw new Error(`Vehicle API request failed (${response.status})`);
      }
    } catch (error) {
      if (signal?.aborted) {
        throw error;
      }

      const isLastAttempt = attempt >= MAX_RETRIES;
      if (isLastAttempt) {
        throw error;
      }
    }

    attempt += 1;
    await sleep(150 * attempt);
  }
};

const fetchList = async (url, signal) => {
  const response = await fetchWithRetry(url, signal);
  const payload = await parseJson(response);
  return toList(payload);
};

const fetchObject = async (url, signal) => {
  const response = await fetchWithRetry(url, signal);

  const payload = await parseJson(response);
  if (payload && typeof payload === 'object' && payload.data && typeof payload.data === 'object') {
    return payload.data;
  }

  if (payload && typeof payload === 'object') {
    return payload;
  }

  return null;
};

export const vehicleDatabaseApi = {
  fetchTypes: (signal) => {
    return fetchList('/vehicle-api/types', signal);
  },
  fetchBrands: (typeId, signal) => {
    return fetchList(`/vehicle-api/brands/${encodeURIComponent(typeId)}`, signal);
  },
  fetchSeries: (brandId, signal) => {
    return fetchList(`/vehicle-api/series/${encodeURIComponent(brandId)}`, signal);
  },
  fetchModels: (seriesId, signal) => {
    return fetchList(`/vehicle-api/models/${encodeURIComponent(seriesId)}`, signal);
  },
  fetchEngines: (modelId, signal) => {
    return fetchList(`/vehicle-api/engines/${encodeURIComponent(modelId)}`, signal);
  },
  fetchEngineDetails: (engineId, signal) => {
    return fetchObject(`/vehicle-api/engine-details/${encodeURIComponent(engineId)}?noTrack=1`, signal);
  },
};

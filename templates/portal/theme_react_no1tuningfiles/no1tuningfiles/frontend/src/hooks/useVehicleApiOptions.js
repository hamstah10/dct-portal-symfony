import { useEffect, useMemo, useState } from 'react';
import { vehicleDatabaseApi } from '../lib/vehicleDatabaseApi';

const TYPE_ALIASES = {
  pkw: ['pkw', 'car', 'auto'],
  lkw: ['lkw', 'truck', 'transporter', 'van', 'nfz', 'nutzfahrzeug', 'commercial', 'bus'],
  motorrad: ['motorrad', 'motorcycle', 'bike'],
  agrar: ['agrar', 'agriculture', 'tractor', 'landmaschine'],
  jetski: ['jetski', 'boat', 'boot', 'marine'],
  andere: ['andere', 'other'],
};

const normalize = (value) => String(value ?? '').trim().toLowerCase();

const resolveType = (types, vehicleTypeName, vehicleTypeKey) => {
  if (!Array.isArray(types) || types.length === 0) {
    return null;
  }

  const requestedName = normalize(vehicleTypeName);
  if (requestedName) {
    const exactByName = types.find((item) => {
      const itemName = normalize(item?.name);
      const itemSlug = normalize(item?.slug);
      const itemId = normalize(item?.id);

      return itemName === requestedName || itemSlug === requestedName || itemId === requestedName;
    });
    if (exactByName) {
      return exactByName;
    }

    const containsByName = types.find((item) => {
      const itemName = normalize(item?.name);
      const itemSlug = normalize(item?.slug);

      return itemName.includes(requestedName) || itemSlug.includes(requestedName);
    });
    if (containsByName) {
      return containsByName;
    }
  }

  const aliases = TYPE_ALIASES[vehicleTypeKey] ?? TYPE_ALIASES.pkw;
  const exactMatch = types.find((item) => {
    const itemName = normalize(item?.name);
    const itemSlug = normalize(item?.slug);

    return aliases.includes(itemName) || aliases.includes(itemSlug);
  });
  if (exactMatch) {
    return exactMatch;
  }

  const containsMatch = types.find((item) => {
    const itemName = normalize(item?.name);
    const itemSlug = normalize(item?.slug);

    return aliases.some((alias) => itemName.includes(alias) || itemSlug.includes(alias));
  });
  if (containsMatch) {
    return containsMatch;
  }

  if (requestedName) {
    return null;
  }

  return types[0] ?? null;
};

const findByName = (items, name) => {
  const target = normalize(name);
  if (!target || !Array.isArray(items)) {
    return null;
  }

  return items.find((item) => normalize(item?.name) === target) ?? null;
};

const asNames = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }

  return items
    .map((item) => String(item?.name ?? '').trim())
    .filter((name) => name !== '');
};

const normalizeBrandItems = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }

  return items
    .map((item) => ({
      id: item?.id ?? null,
      name: String(item?.name ?? '').trim(),
      image: String(item?.image ?? item?.icon ?? '').trim(),
    }))
    .filter((item) => item.name !== '');
};

const normalizeText = (value) => String(value ?? '').trim();

export const useVehicleApiOptions = ({ vehicleTypeKey = 'pkw', vehicleTypeName = '', manufacturer = '', series = '', model = '', engine = '' }) => {
  const [types, setTypes] = useState([]);
  const [brands, setBrands] = useState([]);
  const [seriesRows, setSeriesRows] = useState([]);
  const [modelRows, setModelRows] = useState([]);
  const [engines, setEngines] = useState([]);
  const [engineDetails, setEngineDetails] = useState(null);

  useEffect(() => {
    const controller = new AbortController();

    vehicleDatabaseApi
      .fetchTypes(controller.signal)
      .then((rows) => setTypes(Array.isArray(rows) ? rows : []))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setTypes([]);
      });

    return () => controller.abort();
  }, []);

  const selectedType = useMemo(() => resolveType(types, vehicleTypeName, vehicleTypeKey), [types, vehicleTypeName, vehicleTypeKey]);
  const selectedBrand = useMemo(() => findByName(brands, manufacturer), [brands, manufacturer]);
  const selectedSeries = useMemo(() => findByName(seriesRows, series), [seriesRows, series]);
  const selectedModel = useMemo(() => findByName(modelRows, model), [modelRows, model]);
  const selectedEngine = useMemo(() => findByName(engines, engine), [engines, engine]);

  useEffect(() => {
    setBrands([]);
    setSeriesRows([]);
    setModelRows([]);
    setEngines([]);
    setEngineDetails(null);

    if (!selectedType?.id) {
      return;
    }

    const controller = new AbortController();
    vehicleDatabaseApi
      .fetchBrands(selectedType.id, controller.signal)
      .then((rows) => setBrands(Array.isArray(rows) ? rows : []))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setBrands([]);
      });

    return () => controller.abort();
  }, [selectedType?.id]);

  useEffect(() => {
    setSeriesRows([]);
    setModelRows([]);
    setEngines([]);
    setEngineDetails(null);

    if (!selectedBrand?.id) {
      return;
    }

    const controller = new AbortController();
    vehicleDatabaseApi
      .fetchSeries(selectedBrand.id, controller.signal)
      .then((rows) => setSeriesRows(Array.isArray(rows) ? rows : []))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setSeriesRows([]);
      });

    return () => controller.abort();
  }, [selectedBrand?.id]);

  useEffect(() => {
    setModelRows([]);
    setEngines([]);
    setEngineDetails(null);

    if (!selectedSeries?.id) {
      return;
    }

    const controller = new AbortController();
    vehicleDatabaseApi
      .fetchModels(selectedSeries.id, controller.signal)
      .then((rows) => setModelRows(Array.isArray(rows) ? rows : []))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setModelRows([]);
      });

    return () => controller.abort();
  }, [selectedSeries?.id]);

  useEffect(() => {
    setEngines([]);
    setEngineDetails(null);

    if (!selectedModel?.id) {
      return;
    }

    const controller = new AbortController();
    vehicleDatabaseApi
      .fetchEngines(selectedModel.id, controller.signal)
      .then((rows) => setEngines(Array.isArray(rows) ? rows : []))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setEngines([]);
      });

    return () => controller.abort();
  }, [selectedModel?.id]);

  useEffect(() => {
    if (!selectedEngine?.id) {
      setEngineDetails(null);
      return;
    }

    const controller = new AbortController();
    vehicleDatabaseApi
      .fetchEngineDetails(selectedEngine.id, controller.signal)
      .then((details) => setEngineDetails(details && typeof details === 'object' ? details : null))
      .catch(() => {
        if (controller.signal.aborted) {
          return;
        }
        setEngineDetails(null);
      });

    return () => controller.abort();
  }, [selectedEngine?.id]);

  const ecuOptions = useMemo(() => {
    if (!Array.isArray(engineDetails?.ecus)) {
      return [];
    }

    return engineDetails.ecus
      .map((ecu) => String(ecu?.name ?? '').trim())
      .filter((name) => name !== '');
  }, [engineDetails]);

  const brandInfo = useMemo(() => {
    const payload = engineDetails?.brand;
    if (!payload || typeof payload !== 'object') {
      return null;
    }

    return {
      id: payload?.id ?? null,
      name: normalizeText(payload?.name),
      image: normalizeText(payload?.image),
    };
  }, [engineDetails]);

  const seriesInfo = useMemo(() => {
    const payload = engineDetails?.series;
    if (!payload || typeof payload !== 'object') {
      return null;
    }

    return {
      id: payload?.id ?? null,
      name: normalizeText(payload?.name),
    };
  }, [engineDetails]);

  const stageItems = useMemo(() => {
    if (!Array.isArray(engineDetails?.stages)) {
      return [];
    }

    return engineDetails.stages
      .map((stage) => ({
        id: stage?.id ?? null,
        name: String(stage?.name ?? '').trim(),
        price: stage?.price ?? null,
        hp: Number.isFinite(stage?.hp) ? stage.hp : null,
        nm: Number.isFinite(stage?.nm) ? stage.nm : null,
        ecu: Number.isFinite(stage?.ecu) ? stage.ecu : null,
        description: String(stage?.description ?? '').trim(),
        icon: String(stage?.icon ?? '').trim(),
        images: Array.isArray(stage?.images)
          ? stage.images.map((image) => String(image ?? '').trim()).filter((image) => image !== '')
          : [],
        videos: String(stage?.videos ?? '').trim(),
      }))
      .filter((stage) => stage.name !== '');
  }, [engineDetails]);

  const engineInfo = useMemo(() => {
    const payload = engineDetails?.engine;
    if (!payload || typeof payload !== 'object') {
      return null;
    }

    return {
      id: payload?.id ?? null,
      name: normalizeText(payload?.name),
      horsepower: Number.isFinite(payload?.horsepower) ? payload.horsepower : null,
      torque: Number.isFinite(payload?.torque) ? payload.torque : null,
      fuel: normalizeText(payload?.fuel),
      displacement: normalizeText(payload?.displacement),
      image: normalizeText(payload?.image),
      glbModel: normalizeText(payload?.glbModel),
      mediaDisplayMode: normalizeText(payload?.mediaDisplayMode),
    };
  }, [engineDetails]);

  const ecuItems = useMemo(() => {
    if (!Array.isArray(engineDetails?.ecus)) {
      return [];
    }

    return engineDetails.ecus
      .map((ecu) => ({
        id: ecu?.id ?? null,
        name: normalizeText(ecu?.name),
        type: normalizeText(ecu?.type),
        controlUnitKind: normalizeText(ecu?.controlUnitKind),
        manufacturer: normalizeText(ecu?.manufacturer),
        manufacturerLogo: normalizeText(ecu?.manufacturerLogo),
        options: Array.isArray(ecu?.options)
          ? ecu.options
              .map((option) => ({
                id: option?.id ?? null,
                name: normalizeText(option?.name),
                description: normalizeText(option?.description),
                icon: normalizeText(option?.icon),
                price: Number.isFinite(option?.price) ? option.price : null,
                isMainOption: Boolean(option?.isMainOption),
              }))
              .filter((option) => option.name !== '')
          : [],
      }))
      .filter((ecu) => ecu.name !== '');
  }, [engineDetails]);

  const gearboxItems = useMemo(() => {
    if (!Array.isArray(engineDetails?.gearboxes)) {
      return [];
    }

    return engineDetails.gearboxes
      .map((gearbox) => ({
        id: gearbox?.id ?? null,
        title: normalizeText(gearbox?.title),
        description: normalizeText(gearbox?.description),
        icon: normalizeText(gearbox?.icon),
        images: Array.isArray(gearbox?.images)
          ? gearbox.images.map((image) => normalizeText(image)).filter((image) => image !== '')
          : [],
        type: normalizeText(gearbox?.type),
        manufacturer: normalizeText(gearbox?.manufacturer),
        manufacturerLogo: normalizeText(gearbox?.manufacturerLogo),
      }))
      .filter((gearbox) => gearbox.title !== '');
  }, [engineDetails]);

  return {
    typeItems: normalizeBrandItems(types),
    manufacturerItems: normalizeBrandItems(brands),
    manufacturers: asNames(brands),
    series: asNames(seriesRows),
    models: asNames(modelRows),
    engines: asNames(engines),
    ecuOptions,
    stageItems,
    brandInfo,
    seriesInfo,
    engineInfo,
    ecuItems,
    gearboxItems,
  };
};

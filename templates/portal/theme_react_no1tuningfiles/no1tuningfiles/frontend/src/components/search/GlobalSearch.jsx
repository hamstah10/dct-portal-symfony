import { useDeferredValue, useEffect, useRef, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import {
  ArrowsOutCardinal,
  CarProfile,
  ClockCounterClockwise,
  CreditCard,
  House,
  Lightning,
  MagnifyingGlass,
  Receipt,
  Rows,
  User,
} from '@phosphor-icons/react';
import {
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
  CommandShortcut,
} from '../ui/command';
import { Badge } from '../ui/badge';
import { buildGlobalSearchIndex, getSearchLabels } from '../../data/portalSearchData';

const typeOrder = ['action', 'navigation', 'order', 'parked_order', 'customer', 'vehicle', 'invoice', 'credit_transaction'];

const typeIcons = {
  action: Lightning,
  navigation: House,
  order: ClockCounterClockwise,
  parked_order: ArrowsOutCardinal,
  customer: User,
  vehicle: CarProfile,
  invoice: Receipt,
  credit_transaction: CreditCard,
};

const mapRemoteResultType = (result) => {
  const group = String(result?.group || '').trim().toLowerCase();

  if (group === 'fahrzeugdatenbank') {
    return 'vehicle';
  }

  return 'navigation';
};

const normalizeRemoteResult = (result, index) => ({
  id: `remote-${mapRemoteResultType(result)}-${index}-${String(result?.url || '')}`,
  type: mapRemoteResultType(result),
  title: String(result?.text || '').trim(),
  subtitle: String(result?.subtext || '').trim() || null,
  badge: String(result?.meta || '').trim() || null,
  url: String(result?.url || '').trim() || '/',
  keywords: [
    String(result?.keywords || '').trim(),
    String(result?.group || '').trim(),
  ].filter(Boolean),
});

const mergeResults = (localResults, remoteResults) => {
  const seen = new Set();
  const merged = [];

  for (const result of [...remoteResults, ...localResults]) {
    const key = [
      String(result?.type || '').trim().toLowerCase(),
      String(result?.url || '').trim().toLowerCase(),
      String(result?.title || '').trim().toLowerCase(),
    ].join('::');

    if (seen.has(key)) {
      continue;
    }

    seen.add(key);
    merged.push(result);
  }

  return merged;
};

const filterResults = (results, query) => {
  const normalizedQuery = query.trim().toLowerCase();
  if (normalizedQuery === '') {
    return results;
  }

  const tokens = normalizedQuery.split(/\s+/).filter(Boolean);

  return results
    .map((result) => {
      const haystack = [
        result.title,
        result.subtitle || '',
        result.badge || '',
        ...(result.keywords || []),
      ]
        .join(' ')
        .toLowerCase();

      const score = tokens.reduce((sum, token) => {
        if (result.title.toLowerCase().includes(token)) {
          return sum + 5;
        }
        if ((result.subtitle || '').toLowerCase().includes(token)) {
          return sum + 3;
        }
        if ((result.keywords || []).some((keyword) => String(keyword).toLowerCase().includes(token))) {
          return sum + 2;
        }
        return haystack.includes(token) ? sum + 1 : sum;
      }, 0);

      return { ...result, score };
    })
    .filter((result) => result.score >= tokens.length)
    .sort((left, right) => right.score - left.score || left.title.localeCompare(right.title));
};

export const GlobalSearch = ({ language, open, onOpenChange }) => {
  const navigate = useNavigate();
  const location = useLocation();
  const inputRef = useRef(null);
  const [query, setQuery] = useState('');
  const [remoteResults, setRemoteResults] = useState([]);
  const deferredQuery = useDeferredValue(query);
  const labels = getSearchLabels(language);
  const localResults = filterResults(buildGlobalSearchIndex(language), deferredQuery);
  const results = mergeResults(localResults, remoteResults);

  useEffect(() => {
    const normalizedQuery = deferredQuery.trim();
    if (normalizedQuery.length < 2) {
      setRemoteResults([]);
      return undefined;
    }

    const controller = new AbortController();
    const timeoutId = window.setTimeout(async () => {
      try {
        const response = await fetch(`/header-search/autocomplete?query=${encodeURIComponent(normalizedQuery)}`, {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });

        if (!response.ok) {
          setRemoteResults([]);
          return;
        }

        const payload = await response.json();
        const items = Array.isArray(payload?.results)
          ? payload.results
              .map((item, index) => normalizeRemoteResult(item, index))
              .filter((item) => item.title !== '')
          : [];

        setRemoteResults(items);
      } catch (error) {
        if (error?.name !== 'AbortError') {
          setRemoteResults([]);
        }
      }
    }, 120);

    return () => {
      window.clearTimeout(timeoutId);
      controller.abort();
    };
  }, [deferredQuery]);

  useEffect(() => {
    const handleShortcut = (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        onOpenChange(true);
      }
    };

    window.addEventListener('keydown', handleShortcut);
    return () => window.removeEventListener('keydown', handleShortcut);
  }, [onOpenChange]);

  useEffect(() => {
    onOpenChange(false);
    setQuery('');
    setRemoteResults([]);
  }, [location.pathname, location.search, onOpenChange]);

  useEffect(() => {
    if (!open) {
      return;
    }

    window.requestAnimationFrame(() => {
      inputRef.current?.focus();
    });
  }, [open]);

  const groupedResults = typeOrder
    .map((type) => ({
      type,
      heading: labels.groups[type],
      items: results.filter((result) => result.type === type),
    }))
    .filter((group) => group.items.length > 0);

  const handleSelect = (result) => {
    onOpenChange(false);
    setQuery('');
    navigate(result.url);
  };

  return (
    <CommandDialog open={open} onOpenChange={onOpenChange}>
      <CommandInput
        ref={inputRef}
        value={query}
        onValueChange={setQuery}
        placeholder={language === 'de' ? 'Portal durchsuchen, Aktionen starten, Auftraege finden...' : 'Search portal, run actions, find records...'}
        className="h-14 text-base"
      />
      <CommandList className="max-h-[70vh]">
        <CommandEmpty className="py-10 text-sm text-muted-foreground">
          {language === 'de' ? 'Keine Treffer gefunden.' : 'No results found.'}
        </CommandEmpty>
        {groupedResults.map((group, index) => (
          <div key={group.type}>
            {index > 0 && <CommandSeparator />}
            <CommandGroup heading={group.heading}>
              {group.items.map((result) => {
                const Icon = typeIcons[result.type] || Rows;

                return (
                  <CommandItem
                    key={result.id}
                    value={[result.title, result.subtitle, result.badge, ...(result.keywords || [])].filter(Boolean).join(' ')}
                    onSelect={() => handleSelect(result)}
                    className="gap-3 px-3 py-3"
                  >
                    <div className="flex h-10 w-10 items-center justify-center rounded-sm border border-border bg-secondary text-primary">
                      <Icon weight="duotone" className="h-5 w-5" />
                    </div>
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2">
                        <span className="truncate font-medium text-foreground">{result.title}</span>
                        {result.badge ? (
                          <Badge variant="outline" className="border-border bg-background/70 text-[10px] uppercase tracking-wide text-muted-foreground">
                            {result.badge}
                          </Badge>
                        ) : null}
                      </div>
                      {result.subtitle ? <p className="truncate text-xs text-muted-foreground">{result.subtitle}</p> : null}
                    </div>
                    {result.type === 'action' || result.type === 'navigation' ? (
                      <CommandShortcut>Enter</CommandShortcut>
                    ) : (
                      <MagnifyingGlass className="h-4 w-4 text-muted-foreground/60" />
                    )}
                  </CommandItem>
                );
              })}
            </CommandGroup>
          </div>
        ))}
      </CommandList>
    </CommandDialog>
  );
};

import { useEffect, useMemo, useState } from 'react';
import { MagnifyingGlass, WarningCircle, FolderOpen, Hash } from '@phosphor-icons/react';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Badge } from '../components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';

const ITEMS_PER_PAGE = 25;

const readDtcCodesBootstrap = () => {
  const bootstrap = window?.portalReactBootstrap?.dtcCodes;
  if (!bootstrap || typeof bootstrap !== 'object') {
    return { items: [], total: 0, categories: [] };
  }

  const items = Array.isArray(bootstrap.items) ? bootstrap.items : [];
  const categories = Array.isArray(bootstrap.categories) ? bootstrap.categories : [];

  return {
    items,
    total: Number.isFinite(bootstrap.total) ? bootstrap.total : items.length,
    categories,
  };
};

const normalizeText = (value) => (typeof value === 'string' ? value.trim() : '');

export default function DtcCodes() {
  const bootstrap = readDtcCodesBootstrap();
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [currentPage, setCurrentPage] = useState(1);
  const items = bootstrap.items;
  const categories = bootstrap.categories;

  const filteredItems = useMemo(() => {
    const needle = search.trim().toLowerCase();

    return items.filter((item) => {
      const code = normalizeText(item?.code);
      const description = normalizeText(item?.description);
      const categoryName = normalizeText(item?.category?.name);
      const categoryLetter = normalizeText(item?.category?.letter);
      const categoryKey = `${categoryLetter}|${categoryName}`;

      if (selectedCategory !== 'all' && categoryKey !== selectedCategory) {
        return false;
      }

      if (needle === '') {
        return true;
      }

      const haystack = [code, description, categoryName, categoryLetter].join(' ').toLowerCase();
      return haystack.includes(needle);
    });
  }, [items, search, selectedCategory]);

  useEffect(() => {
    setCurrentPage(1);
  }, [search, selectedCategory]);

  const totalPages = Math.max(1, Math.ceil(filteredItems.length / ITEMS_PER_PAGE));
  const safeCurrentPage = Math.min(currentPage, totalPages);
  const pageStart = (safeCurrentPage - 1) * ITEMS_PER_PAGE;
  const visibleItems = filteredItems.slice(pageStart, pageStart + ITEMS_PER_PAGE);

  return (
    <DashboardLayout title="DTC Codes">
      <div className="space-y-6">
        <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div className="grid gap-3 sm:grid-cols-3">
            <Card className="border-border bg-card">
              <CardContent className="flex items-center gap-3 p-4">
                <WarningCircle className="h-8 w-8 text-primary" weight="fill" />
                <div>
                  <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Codes gesamt</p>
                  <p className="text-2xl font-bold text-foreground">{bootstrap.total}</p>
                </div>
              </CardContent>
            </Card>
            <Card className="border-border bg-card">
              <CardContent className="flex items-center gap-3 p-4">
                <FolderOpen className="h-8 w-8 text-primary" weight="fill" />
                <div>
                  <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Kategorien</p>
                  <p className="text-2xl font-bold text-foreground">{categories.length}</p>
                </div>
              </CardContent>
            </Card>
            <Card className="border-border bg-card">
              <CardContent className="flex items-center gap-3 p-4">
                <Hash className="h-8 w-8 text-primary" weight="fill" />
                <div>
                  <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Treffer</p>
                  <p className="text-2xl font-bold text-foreground">{filteredItems.length}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <Card className="border-border bg-card">
          <CardHeader className="border-b border-border pb-4">
            <CardTitle className="flex items-center gap-2 text-lg">
              <MagnifyingGlass className="h-5 w-5 text-primary" weight="bold" />
              Filter
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4 pt-6">
            <Input
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              className="border-border bg-secondary"
              placeholder="P0420, Beschreibung, Kategorie ..."
              data-testid="dtc-search-input"
            />
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => setSelectedCategory('all')}
                className={selectedCategory === 'all'
                  ? 'btn-gradient rounded-sm px-3 py-2 text-sm font-medium text-white'
                  : 'rounded-sm border border-border bg-secondary px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground'}
                data-testid="dtc-category-all"
              >
                Alle Kategorien
              </button>
              {categories.map((category) => {
                const key = normalizeText(category?.key);
                const label = [normalizeText(category?.letter), normalizeText(category?.name)].filter(Boolean).join(' - ');

                return (
                  <button
                    key={key}
                    type="button"
                    onClick={() => setSelectedCategory(key)}
                    className={selectedCategory === key
                      ? 'btn-gradient rounded-sm px-3 py-2 text-sm font-medium text-white'
                      : 'rounded-sm border border-border bg-secondary px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground'}
                    data-testid={`dtc-category-${key.replace(/[^a-zA-Z0-9]+/g, '-').toLowerCase()}`}
                  >
                    {label || 'Ohne Kategorie'}
                  </button>
                );
              })}
            </div>
          </CardContent>
        </Card>

        <Card className="border-border bg-card">
          <CardHeader className="border-b border-border pb-4">
            <CardTitle className="text-lg">
              DTC Codes
              <span className="ml-3 text-sm font-medium text-muted-foreground">
                Seite {safeCurrentPage} von {totalPages}
              </span>
            </CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <Table>
              <TableHeader className="bg-secondary/50">
                <TableRow>
                  <TableHead className="px-6 py-3">Code</TableHead>
                  <TableHead className="px-6 py-3">Kategorie</TableHead>
                  <TableHead className="px-6 py-3">Beschreibung</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {visibleItems.map((item) => {
                  const code = normalizeText(item?.code);
                  const description = normalizeText(item?.description);
                  const categoryName = normalizeText(item?.category?.name);
                  const categoryLetter = normalizeText(item?.category?.letter);

                  return (
                    <TableRow key={`${code}-${categoryLetter}-${categoryName}`}>
                      <TableCell className="px-6 py-4">
                        <span className="font-mono text-sm font-semibold text-primary">{code || '-'}</span>
                      </TableCell>
                      <TableCell className="px-6 py-4">
                        <div className="flex flex-wrap items-center gap-2">
                          {categoryLetter !== '' && (
                            <Badge variant="outline" className="border-primary/30 bg-primary/10 text-primary">
                              {categoryLetter}
                            </Badge>
                          )}
                          <span className="text-foreground">{categoryName || 'Keine Kategorie'}</span>
                        </div>
                      </TableCell>
                      <TableCell className="px-6 py-4 text-muted-foreground">
                        {description || 'Keine Beschreibung hinterlegt.'}
                      </TableCell>
                    </TableRow>
                  );
                })}
                {visibleItems.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={3} className="px-6 py-10 text-center text-muted-foreground">
                      Keine DTC-Codes fuer den aktuellen Filter gefunden.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
            {filteredItems.length > 0 && (
              <div className="flex items-center justify-between border-t border-border px-6 py-4">
                <div className="flex items-center gap-3">
                  <button
                    type="button"
                    onClick={() => setCurrentPage((page) => Math.max(1, page - 1))}
                    disabled={safeCurrentPage === 1}
                    className="rounded-sm border border-border bg-secondary px-3 py-2 text-sm font-medium text-foreground transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                    data-testid="dtc-pagination-prev"
                  >
                    Zurueck
                  </button>
                  <button
                    type="button"
                    onClick={() => setCurrentPage((page) => Math.min(totalPages, page + 1))}
                    disabled={safeCurrentPage >= totalPages}
                    className="btn-gradient rounded-sm px-3 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                    data-testid="dtc-pagination-next"
                  >
                    Weiter
                  </button>
                </div>
                <p className="text-sm text-muted-foreground">
                  Zeige {pageStart + 1} bis {Math.min(pageStart + ITEMS_PER_PAGE, filteredItems.length)} von {filteredItems.length}
                </p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}

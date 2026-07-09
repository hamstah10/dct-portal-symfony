import { Sidebar } from './Sidebar';
import { Header } from './Header';

export const DashboardLayout = ({ children, title }) => {
  const headline = typeof title === 'string' && title.trim() !== '' ? title : '';

  return (
    <div
      className="min-h-screen bg-background"
      data-testid="dashboard-layout"
      style={{ backgroundColor: 'hsl(var(--background))' }}
    >
      <Sidebar />
      <div className="pl-64">
        <Header />
        <main className="px-6 py-6" style={{ backgroundColor: 'hsl(var(--background))' }}>
          {headline !== '' && (
            <div className="mb-6" data-testid="page-headline">
              <h1 className="font-heading text-2xl font-bold text-foreground tracking-tight">{headline}</h1>
            </div>
          )}
          {children}
        </main>
      </div>
    </div>
  );
};

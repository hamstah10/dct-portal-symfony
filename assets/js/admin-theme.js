function updateScrollableState(element) {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    const hasOverflow = element.scrollWidth > element.clientWidth + 4;
    element.classList.toggle('is-scrollable', hasOverflow);
    element.classList.toggle('is-scroll-start', hasOverflow && element.scrollLeft > 4);
    element.classList.toggle(
        'is-scroll-end',
        hasOverflow && element.scrollLeft + element.clientWidth < element.scrollWidth - 4
    );
}

function bindDashboardRows() {
    document.querySelectorAll('.dashboard-order-row[data-href]').forEach((row) => {
        if (!(row instanceof HTMLElement) || row.dataset.tfdRowBound === 'true') {
            return;
        }

        row.dataset.tfdRowBound = 'true';

        const isInteractive = (target) => target instanceof Element && !!target.closest(
            'a, button, input, select, textarea, .dropdown, [data-bs-toggle], .btn'
        );

        const follow = () => {
            const url = row.dataset.href;
            if (url) {
                window.location.href = url;
            }
        };

        row.addEventListener('click', (event) => {
            if (!isInteractive(event.target)) {
                follow();
            }
        });

        row.addEventListener('keydown', (event) => {
            if ((event.key === 'Enter' || event.key === ' ') && !isInteractive(event.target)) {
                event.preventDefault();
                follow();
            }
        });
    });
}

function bindSidebarToggle() {
    document.querySelectorAll('[data-tfd-sidebar-toggle]').forEach((toggle) => {
        if (!(toggle instanceof HTMLElement) || toggle.dataset.tfdSidebarBound === 'true') {
            return;
        }

        toggle.dataset.tfdSidebarBound = 'true';
        toggle.addEventListener('click', () => {
            document.body?.classList.toggle('tfd-sidebar-open');
        });
    });
}

function bindSidebarSubmenus() {
    document.querySelectorAll('.tfd-ea-sidebar-menu .slide.has-sub > .side-menu__item').forEach((trigger) => {
        if (!(trigger instanceof HTMLElement) || trigger.dataset.tfdSubmenuBound === 'true') {
            return;
        }

        trigger.dataset.tfdSubmenuBound = 'true';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();

            const item = trigger.closest('.slide.has-sub');
            if (!(item instanceof HTMLElement)) {
                return;
            }

            item.classList.toggle('open');
        });
    });
}

function bootAdminTheme() {
    document.body?.classList.add('tfd-admin-theme-ready');

    document.querySelectorAll('.content-panel, .table-responsive').forEach((element) => {
        updateScrollableState(element);

        if (element.dataset.tfdScrollBound === 'true') {
            return;
        }

        element.dataset.tfdScrollBound = 'true';
        element.addEventListener('scroll', () => updateScrollableState(element), { passive: true });
    });

    bindDashboardRows();
    bindSidebarToggle();
    bindSidebarSubmenus();
}

document.addEventListener('DOMContentLoaded', bootAdminTheme);
document.addEventListener('turbo:load', bootAdminTheme);

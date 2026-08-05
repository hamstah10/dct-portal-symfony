# Admin Issue Reporter

Saubere React-/TypeScript-Komponente für einen visuellen Fehler-Reporter im Superadmin.

## 1. Dateien kopieren

Kopiere in dein React-Projekt:

```text
src/components/AdminIssueReporter/
src/adapters/symfonyApi.ts
```

## 2. Abhängigkeiten installieren

```bash
npm install html2canvas lucide-react
```

## 3. Komponente einbinden

```tsx
import { AdminIssueReporter } from "./components/AdminIssueReporter";
import { createSymfonyIssueApi } from "./adapters/symfonyApi";

const issueApi = createSymfonyIssueApi("/api/admin/issues");

<AdminIssueReporter
  enabled={user.roles.includes("ROLE_SUPER_ADMIN")}
  currentUser={{ id: String(user.id), name: user.name }}
  users={employees.map((employee) => ({
    id: String(employee.id),
    name: employee.name,
  }))}
  api={issueApi}
/>
```

## 4. Reportbare Elemente markieren

```tsx
<div
  data-report-type="stage"
  data-report-id={stage.id}
  data-report-label="Stage 1"
  data-vehicle-id={vehicle.id}
  data-stage-id={stage.id}
  data-report-field="hp"
  data-report-value={stage.hp}
>
  <StageCard stage={stage} />
</div>
```

Unterstützte Attribute:

```text
data-report-type
data-report-id
data-report-label
data-vehicle-id
data-engine-id
data-stage-id
data-report-field
data-report-value
```

## 5. Erwartete Symfony-Endpunkte

```text
POST /api/admin/issues
PUT  /api/admin/issues/{id}
POST /api/admin/issues/{id}/comments
```

Der Adapter liegt in:

```text
src/adapters/symfonyApi.ts
```

## 6. Ohne Backend testen

Lässt du `api` weg, hält die Komponente alle Einträge lokal im React-State:

```tsx
<AdminIssueReporter
  currentUser={{ id: "1", name: "André" }}
/>
```

## Funktionen

- schwebender Fehler-Button
- `Shift + F` zur Elementauswahl
- DOM- und Entity-Kontext
- Screenshot
- Sprachkommentar
- Kategorien und Prioritäten
- Labels
- Mitarbeiterzuweisung
- Suche und Statusfilter
- Kommentare
- Bearbeiten und Lösen
- Symfony-API-Adapter

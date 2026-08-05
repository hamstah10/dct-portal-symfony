import { AdminIssueReporter } from "../components/AdminIssueReporter";
import { createSymfonyIssueApi } from "../adapters/symfonyApi";

const issueApi = createSymfonyIssueApi("/api/admin/issues");

export function ExampleIntegration() {
  const currentUser = { id: "1", name: "André" };
  const users = [currentUser, { id: "2", name: "Max" }, { id: "3", name: "Sven" }];

  return (
    <>
      <div
        data-report-type="stage"
        data-report-id="4711"
        data-report-label="Stage 1"
        data-vehicle-id="18442"
        data-stage-id="4711"
        data-report-field="hp"
        data-report-value="225"
      >
        Stage 1 · 225 PS
      </div>

      <AdminIssueReporter
        enabled
        currentUser={currentUser}
        users={users}
        api={issueApi}
        onError={(error) => console.error(error)}
      />
    </>
  );
}

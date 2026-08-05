import type { AdminIssue, CreateAdminIssueInput, IssueComment } from "../components/AdminIssueReporter";

export function createSymfonyIssueApi(baseUrl = "/api/admin/issues", token?: string) {
  const request = async <T>(url: string, init?: RequestInit): Promise<T> => {
    const response = await fetch(url, {
      ...init,
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...init?.headers,
      },
    });

    if (!response.ok) {
      throw new Error(`Issue API error: ${response.status} ${response.statusText}`);
    }

    return response.json() as Promise<T>;
  };

  return {
    createIssue: (input: CreateAdminIssueInput) =>
      request<AdminIssue>(baseUrl, { method: "POST", body: JSON.stringify(input) }),
    updateIssue: (issue: AdminIssue) =>
      request<AdminIssue>(`${baseUrl}/${issue.id}`, { method: "PUT", body: JSON.stringify(issue) }),
    addComment: (issueId: string, comment: Omit<IssueComment, "id" | "createdAt">) =>
      request<AdminIssue>(`${baseUrl}/${issueId}/comments`, { method: "POST", body: JSON.stringify(comment) }),
  };
}

export type IssueStatus = "open" | "in_progress" | "resolved" | "rejected";
export type IssuePriority = "low" | "normal" | "high" | "critical";
export type IssueCategory =
  | "incorrect_data"
  | "missing_data"
  | "translation"
  | "layout"
  | "technical_error"
  | "other";

export interface ReporterUser {
  id: string;
  name: string;
}

export interface IssueComment {
  id: string;
  authorId?: string;
  authorName: string;
  text: string;
  createdAt: string;
}

export interface IssueTargetContext {
  selector?: string;
  entityType?: string;
  entityId?: string;
  entityLabel?: string;
  vehicleId?: string;
  engineId?: string;
  stageId?: string;
  field?: string;
  currentValue?: string;
}

export interface AdminIssue extends IssueTargetContext {
  id: string;
  title: string;
  description: string;
  category: IssueCategory;
  status: IssueStatus;
  priority: IssuePriority;
  labels: string[];
  assignedTo?: ReporterUser;
  pageUrl: string;
  pageTitle?: string;
  screenshotDataUrl?: string;
  audioUrl?: string;
  comments: IssueComment[];
  createdAt: string;
  createdBy: ReporterUser;
  updatedAt?: string;
  resolvedAt?: string;
}

export interface CreateAdminIssueInput extends Omit<AdminIssue, "id" | "status" | "comments" | "createdAt" | "updatedAt" | "resolvedAt"> {
  status?: IssueStatus;
  comments?: IssueComment[];
}

export interface AdminIssueReporterProps {
  enabled?: boolean;
  currentUser: ReporterUser;
  users?: ReporterUser[];
  initialIssues?: AdminIssue[];
  shortcut?: string;
  labels?: string[];
  api?: {
    createIssue?: (input: CreateAdminIssueInput) => Promise<AdminIssue>;
    updateIssue?: (issue: AdminIssue) => Promise<AdminIssue>;
    addComment?: (issueId: string, comment: Omit<IssueComment, "id" | "createdAt">) => Promise<AdminIssue>;
    uploadScreenshot?: (dataUrl: string) => Promise<string>;
    uploadAudio?: (blob: Blob) => Promise<string>;
  };
  onError?: (error: unknown) => void;
}

import html2canvas from "html2canvas";
import {
  AlertTriangle,
  Bug,
  Camera,
  Check,
  ChevronRight,
  ListTodo,
  Mic,
  MicOff,
  MousePointer2,
  Plus,
  Search,
  X,
} from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";
import { getReportableElement, readTargetContext } from "./dom";
import type {
  AdminIssue,
  AdminIssueReporterProps,
  CreateAdminIssueInput,
  IssueCategory,
  IssueComment,
  IssuePriority,
  IssueStatus,
  IssueTargetContext,
  ReporterUser,
} from "./types";
import "./styles.css";

const CATEGORY_LABELS: Record<IssueCategory, string> = {
  incorrect_data: "Falsche Daten",
  missing_data: "Fehlende Daten",
  translation: "Text / Übersetzung",
  layout: "Layout",
  technical_error: "Technischer Fehler",
  other: "Sonstiges",
};

const PRIORITY_LABELS: Record<IssuePriority, string> = {
  low: "Niedrig",
  normal: "Normal",
  high: "Hoch",
  critical: "Kritisch",
};

const STATUS_LABELS: Record<IssueStatus, string> = {
  open: "Offen",
  in_progress: "In Bearbeitung",
  resolved: "Gelöst",
  rejected: "Abgelehnt",
};

const createId = () => crypto.randomUUID();

function createLocalIssue(input: CreateAdminIssueInput): AdminIssue {
  return {
    ...input,
    id: createId(),
    status: input.status ?? "open",
    comments: input.comments ?? [],
    createdAt: new Date().toISOString(),
  };
}

export function AdminIssueReporter({
  enabled = true,
  currentUser,
  users = [],
  initialIssues = [],
  shortcut = "Shift+F",
  labels: availableLabels = ["Stage 1", "Fahrzeugdaten", "API", "Übersetzung", "Layout"],
  api,
  onError,
}: AdminIssueReporterProps) {
  const [mode, setMode] = useState<"idle" | "selecting" | "form" | "list">("idle");
  const [hoveredElement, setHoveredElement] = useState<HTMLElement | null>(null);
  const [targetElement, setTargetElement] = useState<HTMLElement | null>(null);
  const [targetContext, setTargetContext] = useState<IssueTargetContext | null>(null);
  const [issues, setIssues] = useState<AdminIssue[]>(initialIssues);
  const [selectedIssueId, setSelectedIssueId] = useState<string | null>(null);
  const [statusFilter, setStatusFilter] = useState<IssueStatus | "all">("all");
  const [query, setQuery] = useState("");
  const [saving, setSaving] = useState(false);

  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [category, setCategory] = useState<IssueCategory>("incorrect_data");
  const [priority, setPriority] = useState<IssuePriority>("normal");
  const [selectedLabels, setSelectedLabels] = useState<string[]>([]);
  const [assignedToId, setAssignedToId] = useState("");
  const [screenshotDataUrl, setScreenshotDataUrl] = useState<string>();
  const [audioBlob, setAudioBlob] = useState<Blob>();
  const [audioPreviewUrl, setAudioPreviewUrl] = useState<string>();
  const [recording, setRecording] = useState(false);
  const [comment, setComment] = useState("");

  const overlayRef = useRef<HTMLDivElement>(null);
  const recorderRef = useRef<MediaRecorder | null>(null);

  const selectedIssue = issues.find((issue) => issue.id === selectedIssueId) ?? null;

  const filteredIssues = useMemo(() => {
    const normalizedQuery = query.trim().toLowerCase();
    return issues.filter((issue) => {
      const statusMatches = statusFilter === "all" || issue.status === statusFilter;
      const queryMatches =
        !normalizedQuery ||
        [issue.title, issue.description, issue.entityLabel, issue.entityId, ...issue.labels]
          .filter(Boolean)
          .join(" ")
          .toLowerCase()
          .includes(normalizedQuery);
      return statusMatches && queryMatches;
    });
  }, [issues, query, statusFilter]);

  useEffect(() => {
    if (!enabled || shortcut.toLowerCase() !== "shift+f") return;
    const handleShortcut = (event: KeyboardEvent) => {
      if (event.shiftKey && event.key.toLowerCase() === "f") {
        event.preventDefault();
        setMode("selecting");
      }
    };
    window.addEventListener("keydown", handleShortcut);
    return () => window.removeEventListener("keydown", handleShortcut);
  }, [enabled, shortcut]);

  useEffect(() => {
    if (mode !== "selecting") {
      setHoveredElement(null);
      return;
    }

    const handleMove = (event: MouseEvent) => {
      const element = document.elementFromPoint(event.clientX, event.clientY) as HTMLElement | null;
      if (!element || element.closest(".air-root")) return;
      setHoveredElement(getReportableElement(element));
    };

    const handleClick = (event: MouseEvent) => {
      const element = document.elementFromPoint(event.clientX, event.clientY) as HTMLElement | null;
      if (!element || element.closest(".air-root")) return;
      event.preventDefault();
      event.stopPropagation();
      const reportable = getReportableElement(element);
      const context = readTargetContext(reportable);
      setTargetElement(reportable);
      setTargetContext(context);
      setTitle(context.entityLabel ?? "");
      setMode("form");
    };

    document.addEventListener("mousemove", handleMove, true);
    document.addEventListener("click", handleClick, true);
    return () => {
      document.removeEventListener("mousemove", handleMove, true);
      document.removeEventListener("click", handleClick, true);
    };
  }, [mode]);

  useEffect(() => {
    if (!hoveredElement || !overlayRef.current || mode !== "selecting") return;
    const rect = hoveredElement.getBoundingClientRect();
    Object.assign(overlayRef.current.style, {
      display: "block",
      top: `${rect.top}px`,
      left: `${rect.left}px`,
      width: `${rect.width}px`,
      height: `${rect.height}px`,
    });
  }, [hoveredElement, mode]);

  useEffect(() => {
    return () => {
      if (audioPreviewUrl) URL.revokeObjectURL(audioPreviewUrl);
    };
  }, [audioPreviewUrl]);

  const resetForm = () => {
    setTargetElement(null);
    setTargetContext(null);
    setTitle("");
    setDescription("");
    setCategory("incorrect_data");
    setPriority("normal");
    setSelectedLabels([]);
    setAssignedToId("");
    setScreenshotDataUrl(undefined);
    setAudioBlob(undefined);
    setAudioPreviewUrl(undefined);
  };

  const handleError = (error: unknown) => {
    onError?.(error);
    console.error("AdminIssueReporter", error);
  };

  const captureScreenshot = async () => {
    try {
      const root = document.querySelector<HTMLElement>(".air-root");
      if (root) root.style.visibility = "hidden";
      await new Promise((resolve) => requestAnimationFrame(() => resolve(undefined)));
      const canvas = await html2canvas(document.body, {
        useCORS: true,
        logging: false,
        backgroundColor: null,
      });
      setScreenshotDataUrl(canvas.toDataURL("image/jpeg", 0.82));
      if (root) root.style.visibility = "visible";
    } catch (error) {
      const root = document.querySelector<HTMLElement>(".air-root");
      if (root) root.style.visibility = "visible";
      handleError(error);
    }
  };

  const toggleRecording = async () => {
    try {
      if (recording) {
        recorderRef.current?.stop();
        setRecording(false);
        return;
      }

      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const chunks: Blob[] = [];
      const recorder = new MediaRecorder(stream);
      recorder.ondataavailable = (event) => chunks.push(event.data);
      recorder.onstop = () => {
        const blob = new Blob(chunks, { type: "audio/webm" });
        setAudioBlob(blob);
        setAudioPreviewUrl(URL.createObjectURL(blob));
        stream.getTracks().forEach((track) => track.stop());
      };
      recorder.start();
      recorderRef.current = recorder;
      setRecording(true);
    } catch (error) {
      handleError(error);
    }
  };

  const saveIssue = async () => {
    if (!targetContext || !title.trim() || !description.trim()) return;
    setSaving(true);

    try {
      const assignedTo = users.find((user) => user.id === assignedToId);
      const screenshotUrl =
        screenshotDataUrl && api?.uploadScreenshot
          ? await api.uploadScreenshot(screenshotDataUrl)
          : screenshotDataUrl;
      const audioUrl = audioBlob && api?.uploadAudio ? await api.uploadAudio(audioBlob) : audioPreviewUrl;

      const input: CreateAdminIssueInput = {
        ...targetContext,
        title: title.trim(),
        description: description.trim(),
        category,
        priority,
        labels: selectedLabels,
        assignedTo,
        pageUrl: window.location.href,
        pageTitle: document.title,
        screenshotDataUrl: screenshotUrl,
        audioUrl,
        createdBy: currentUser,
      };

      const created = api?.createIssue ? await api.createIssue(input) : createLocalIssue(input);
      setIssues((current) => [created, ...current]);
      resetForm();
      setMode("list");
    } catch (error) {
      handleError(error);
    } finally {
      setSaving(false);
    }
  };

  const updateIssue = async (issue: AdminIssue) => {
    try {
      const updated = api?.updateIssue ? await api.updateIssue(issue) : issue;
      setIssues((current) => current.map((item) => (item.id === updated.id ? updated : item)));
    } catch (error) {
      handleError(error);
    }
  };

  const changeStatus = async (issue: AdminIssue, status: IssueStatus) => {
    await updateIssue({
      ...issue,
      status,
      updatedAt: new Date().toISOString(),
      resolvedAt: status === "resolved" ? new Date().toISOString() : undefined,
    });
  };

  const addComment = async () => {
    if (!selectedIssue || !comment.trim()) return;
    try {
      if (api?.addComment) {
        const updated = await api.addComment(selectedIssue.id, {
          authorId: currentUser.id,
          authorName: currentUser.name,
          text: comment.trim(),
        });
        setIssues((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      } else {
        const nextComment: IssueComment = {
          id: createId(),
          authorId: currentUser.id,
          authorName: currentUser.name,
          text: comment.trim(),
          createdAt: new Date().toISOString(),
        };
        await updateIssue({ ...selectedIssue, comments: [...selectedIssue.comments, nextComment] });
      }
      setComment("");
    } catch (error) {
      handleError(error);
    }
  };

  if (!enabled) return null;

  return (
    <div className="air-root">
      {mode === "selecting" && (
        <>
          <div ref={overlayRef} className="air-selection-overlay" />
          <div className="air-selection-hint">
            <MousePointer2 size={16} />
            Element auswählen
            <button onClick={() => setMode("idle")} aria-label="Auswahl abbrechen">
              <X size={16} />
            </button>
          </div>
        </>
      )}

      {mode === "idle" && (
        <div className="air-floating-actions">
          <button className="air-count-button" onClick={() => setMode("list")} aria-label="Fehlerübersicht öffnen">
            <ListTodo size={18} />
            <span>{issues.filter((issue) => issue.status !== "resolved").length}</span>
          </button>
          <button className="air-primary-button" onClick={() => setMode("selecting")}>
            <Bug size={18} /> Fehler melden
          </button>
        </div>
      )}

      {mode === "form" && targetElement && targetContext && (
        <section className="air-panel">
          <header className="air-panel-header">
            <div><small>NEUER EINTRAG</small><h2>Fehler melden</h2></div>
            <button onClick={() => { resetForm(); setMode("idle"); }} aria-label="Schließen"><X size={18} /></button>
          </header>

          <div className="air-panel-body">
            <div className="air-target-card">
              <strong>{targetContext.entityLabel ?? targetElement.tagName}</strong>
              <span>{targetContext.entityType ?? "DOM"}{targetContext.entityId ? ` · ID ${targetContext.entityId}` : ""}</span>
            </div>

            <label>Titel<input value={title} onChange={(event) => setTitle(event.target.value)} placeholder="Stage 1 Daten falsch" /></label>
            <label>Beschreibung<textarea rows={5} value={description} onChange={(event) => setDescription(event.target.value)} placeholder="Was ist falsch und wie sollte es korrekt sein?" /></label>

            <div className="air-form-grid">
              <label>Kategorie<select value={category} onChange={(event) => setCategory(event.target.value as IssueCategory)}>{Object.entries(CATEGORY_LABELS).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></label>
              <label>Priorität<select value={priority} onChange={(event) => setPriority(event.target.value as IssuePriority)}>{Object.entries(PRIORITY_LABELS).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></label>
            </div>

            <label>Zuweisen<select value={assignedToId} onChange={(event) => setAssignedToId(event.target.value)}><option value="">Nicht zugewiesen</option>{users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}</select></label>

            <div className="air-labels">
              {availableLabels.map((label) => (
                <button key={label} type="button" className={selectedLabels.includes(label) ? "is-active" : ""} onClick={() => setSelectedLabels((current) => current.includes(label) ? current.filter((item) => item !== label) : [...current, label])}>{label}</button>
              ))}
            </div>

            <div className="air-tools">
              <button type="button" onClick={captureScreenshot}><Camera size={16} /> Screenshot</button>
              <button type="button" onClick={toggleRecording}>{recording ? <MicOff size={16} /> : <Mic size={16} />}{recording ? "Aufnahme stoppen" : "Sprachkommentar"}</button>
            </div>

            {screenshotDataUrl && <img className="air-screenshot" src={screenshotDataUrl} alt="Screenshot-Vorschau" />}
            {audioPreviewUrl && <audio controls src={audioPreviewUrl} />}
          </div>

          <footer className="air-panel-footer">
            <button onClick={() => setMode("selecting")}>Neu auswählen</button>
            <button className="air-primary-button" disabled={saving || !title.trim() || !description.trim()} onClick={saveIssue}>{saving ? "Speichert…" : "Speichern"}</button>
          </footer>
        </section>
      )}

      {mode === "list" && (
        <section className="air-panel air-panel-wide">
          <header className="air-panel-header">
            <div><small>SUPERADMIN</small><h2>Fehlerübersicht</h2></div>
            <button onClick={() => setMode("idle")} aria-label="Schließen"><X size={18} /></button>
          </header>

          <div className="air-toolbar">
            <div className="air-search"><Search size={16} /><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Suchen…" /></div>
            <button className="air-primary-button" onClick={() => setMode("selecting")}><Plus size={16} /> Neu</button>
          </div>

          <div className="air-status-filter">
            {(["all", "open", "in_progress", "resolved"] as const).map((status) => <button key={status} className={statusFilter === status ? "is-active" : ""} onClick={() => setStatusFilter(status)}>{status === "all" ? "Alle" : STATUS_LABELS[status]}</button>)}
          </div>

          <div className="air-issue-list">
            {filteredIssues.map((issue) => (
              <button key={issue.id} onClick={() => setSelectedIssueId(issue.id)}>
                <i className={`air-status-${issue.status}`} />
                <span><strong>{issue.title}</strong><small>{issue.entityLabel ?? issue.pageUrl}</small></span>
                <em>{PRIORITY_LABELS[issue.priority]}</em>
                <ChevronRight size={16} />
              </button>
            ))}
          </div>

          {selectedIssue && (
            <aside className="air-detail-panel">
              <button className="air-detail-close" onClick={() => setSelectedIssueId(null)}><X size={16} /></button>
              <div className="air-detail-title"><AlertTriangle size={18} /><div><h3>{selectedIssue.title}</h3><span>{CATEGORY_LABELS[selectedIssue.category]}</span></div></div>
              <p>{selectedIssue.description}</p>
              <dl>
                <div><dt>Status</dt><dd>{STATUS_LABELS[selectedIssue.status]}</dd></div>
                <div><dt>Element</dt><dd>{selectedIssue.entityLabel ?? "–"}</dd></div>
                <div><dt>ID</dt><dd>{selectedIssue.entityId ?? "–"}</dd></div>
                <div><dt>Zugewiesen</dt><dd>{selectedIssue.assignedTo?.name ?? "–"}</dd></div>
              </dl>
              <div className="air-labels-readonly">{selectedIssue.labels.map((label) => <span key={label}>{label}</span>)}</div>
              {selectedIssue.screenshotDataUrl && <img className="air-screenshot" src={selectedIssue.screenshotDataUrl} alt="Fehler-Screenshot" />}
              {selectedIssue.audioUrl && <audio controls src={selectedIssue.audioUrl} />}
              <div className="air-comments">{selectedIssue.comments.map((item) => <div key={item.id}><strong>{item.authorName}</strong><small>{new Date(item.createdAt).toLocaleString("de-DE")}</small><p>{item.text}</p></div>)}</div>
              <div className="air-comment-form"><input value={comment} onChange={(event) => setComment(event.target.value)} placeholder="Kommentar…" /><button onClick={addComment}>Senden</button></div>
              <div className="air-detail-actions"><button onClick={() => changeStatus(selectedIssue, "in_progress")}>Bearbeiten</button><button className="air-primary-button" onClick={() => changeStatus(selectedIssue, "resolved")}><Check size={16} /> Gelöst</button></div>
            </aside>
          )}
        </section>
      )}
    </div>
  );
}

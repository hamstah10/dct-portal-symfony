import type { IssueTargetContext } from "./types";

export function buildCssSelector(element: HTMLElement): string {
  if (element.id) return `#${CSS.escape(element.id)}`;

  const parts: string[] = [];
  let current: HTMLElement | null = element;

  while (current && current !== document.body && parts.length < 6) {
    let part = current.tagName.toLowerCase();
    const classes = Array.from(current.classList)
      .filter((name) => !name.startsWith("air-"))
      .slice(0, 2);

    if (classes.length) part += `.${classes.map(CSS.escape).join(".")}`;

    const parent = current.parentElement;
    if (parent) {
      const siblings = Array.from(parent.children).filter(
        (child) => child.tagName === current?.tagName,
      );
      if (siblings.length > 1) {
        part += `:nth-of-type(${siblings.indexOf(current) + 1})`;
      }
    }

    parts.unshift(part);
    current = parent;
  }

  return parts.join(" > ");
}

export function getReportableElement(element: HTMLElement): HTMLElement {
  return element.closest<HTMLElement>("[data-report-type]") ?? element;
}

export function readTargetContext(element: HTMLElement): IssueTargetContext {
  const target = getReportableElement(element);

  return {
    selector: buildCssSelector(target),
    entityType: target.dataset.reportType,
    entityId: target.dataset.reportId,
    entityLabel:
      target.dataset.reportLabel ||
      target.getAttribute("aria-label") ||
      target.textContent?.trim().slice(0, 140) ||
      target.tagName,
    vehicleId: target.dataset.vehicleId,
    engineId: target.dataset.engineId,
    stageId: target.dataset.stageId,
    field: target.dataset.reportField,
    currentValue: target.dataset.reportValue,
  };
}

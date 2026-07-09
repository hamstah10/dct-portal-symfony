import type { WizardAnswers, WizardConfig, WizardRecommendation } from './types';

export function getLocalRecommendation(
  answers: WizardAnswers,
  _config: WizardConfig,
): WizardRecommendation {
  if (answers.tool === 'none') {
    return {
      status: 'tool_required',
      packageName: 'Tool Starter',
      leadStatus: 'tool_consulting',
      cta: 'Tool-Beratung anfragen',
      summary:
        'Für den Händlerstart wird zuerst ein passendes Lese-/Schreibtool benötigt. Danach kann die Partner-Aktivierung vorbereitet werden.',
      bullets: [
        'Tool-Beratung nach Fahrzeugbereich',
        'Prüfung der passenden Protokolle',
        'Startplan für den Händlerzugang',
      ],
    };
  }

  if (answers.goal === 'network' || answers.volume === 'network') {
    return {
      status: 'ready',
      packageName: 'Master / Netzwerk Partner',
      leadStatus: 'new_partner_lead',
      cta: 'Partnerstart anfragen',
      summary:
        'Du passt in Richtung Master, Händlernetz oder White-Label-Struktur mit eigenem Tool und skalierbaren Abläufen.',
      bullets: [
        'Partnerzugang mit Netzwerk-Fokus',
        'Skalierbare File-Service-Prozesse',
        'Technische Abstimmung für Slaves und Abläufe',
      ],
    };
  }

  if (answers.volume === 'high' || answers.experience === 'tuning') {
    return {
      status: 'ready',
      packageName: 'Profi Partner',
      leadStatus: 'new_partner_lead',
      cta: 'Partnerstart anfragen',
      summary:
        'Du hast bereits die richtige Basis für regelmäßige Fahrzeuge und einen professionellen Partner-Workflow.',
      bullets: [
        'Händlerkonditionen',
        'Schnelle Bearbeitung',
        'Technischer Support',
      ],
    };
  }

  return {
    status: 'ready',
    packageName: 'Werkstatt / Slave Partner',
    leadStatus: 'new_partner_lead',
    cta: 'Partnerstart anfragen',
    summary:
      'Du kannst mit eigenem Tool und klarer Fahrzeugausrichtung als Werkstatt- oder Slave-Partner starten.',
    bullets: [
      'Eigener Händlerzugang',
      'File-Service für Kundenfahrzeuge',
      'Geführter Einstieg in den Ablauf',
    ],
  };
}

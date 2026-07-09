import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Textarea } from '../components/ui/textarea';
import { ScrollArea } from '../components/ui/scroll-area';
import { 
  ArrowLeft,
  Ticket,
  Clock,
  CheckCircle,
  ChatCircle,
  Warning,
  PaperPlaneTilt,
  User,
  Headset,
  Link as LinkIcon,
  Info,
  Paperclip,
  DownloadSimple,
  X,
  FileArrowUp,
  Image as ImageIcon,
  FilePdf
} from '@phosphor-icons/react';
import { getTicketsData } from './Tickets';

// Translations
const pageTranslations = {
  en: {
    backToTickets: 'Back to Tickets',
    ticketDetails: 'Ticket Details',
    conversation: 'Conversation',
    ticketInfo: 'Ticket Information',
    status: 'Status',
    priority: 'Priority',
    category: 'Category',
    created: 'Created',
    lastUpdate: 'Last Update',
    relatedOrder: 'Related Order',
    attachments: 'Attachments',
    attachmentOptional: 'Add files',
    uploadHint: 'Upload a corrected original file, screenshots, images or PDFs here.',
    selectedFiles: 'Selected files',
    correctedFile: 'Corrected original file',
    imagePreview: 'Image preview',
    pdfDocument: 'PDF document',
    genericFile: 'File attachment',
    removeFile: 'Remove file',
    noAttachments: 'No attachments yet',
    replyPlaceholder: 'Type your message...',
    sendReply: 'Send',
    open: 'Open',
    answered: 'Answered',
    closed: 'Closed',
    high: 'High',
    normal: 'Normal',
    low: 'Low',
    technical: 'Technical',
    sales: 'Sales',
    billing: 'Billing',
    other: 'Other',
    you: 'You',
    support: 'Support',
    ticketNotFound: 'Ticket not found',
    closeTicket: 'Close Ticket',
    reopenTicket: 'Reopen Ticket',
  },
  de: {
    backToTickets: 'Zurück zu Tickets',
    ticketDetails: 'Ticket Details',
    conversation: 'Konversation',
    ticketInfo: 'Ticket Informationen',
    status: 'Status',
    priority: 'Priorität',
    category: 'Kategorie',
    created: 'Erstellt',
    lastUpdate: 'Letzte Aktualisierung',
    relatedOrder: 'Zugehöriger Auftrag',
    attachments: 'Anhänge',
    attachmentOptional: 'Dateien hinzufügen',
    uploadHint: 'Hier kannst du ein korrigiertes Originalfile, Screenshots, Bilder oder PDFs hochladen.',
    selectedFiles: 'Ausgewählte Dateien',
    correctedFile: 'Korrigiertes Originalfile',
    imagePreview: 'Bildvorschau',
    pdfDocument: 'PDF Dokument',
    genericFile: 'Dateianhang',
    removeFile: 'Datei entfernen',
    noAttachments: 'Noch keine Anhänge vorhanden',
    replyPlaceholder: 'Ihre Nachricht eingeben...',
    sendReply: 'Senden',
    open: 'Offen',
    answered: 'Beantwortet',
    closed: 'Geschlossen',
    high: 'Hoch',
    normal: 'Normal',
    low: 'Niedrig',
    technical: 'Technisch',
    sales: 'Vertrieb',
    billing: 'Rechnung',
    other: 'Sonstiges',
    you: 'Sie',
    support: 'Support',
    ticketNotFound: 'Ticket nicht gefunden',
    closeTicket: 'Ticket schließen',
    reopenTicket: 'Ticket wiedereröffnen',
  }
};

export default function TicketDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language][key] || key;
  const [replyMessage, setReplyMessage] = useState('');
  const [ticket, setTicket] = useState(() => getTicketsData().find((item) => String(item.id) === String(id)) || null);
  const [replyAttachments, setReplyAttachments] = useState([]);
  const [sendingReply, setSendingReply] = useState(false);
  const [replyError, setReplyError] = useState('');
  const [loadingTicket, setLoadingTicket] = useState(true);

  useEffect(() => {
    let isActive = true;

    const loadTicket = async () => {
      setLoadingTicket(true);

      try {
        const response = await fetch(`/tickets/api/${encodeURIComponent(String(id || ''))}`, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        if (isActive && payload?.ticket && typeof payload.ticket === 'object') {
          setTicket(payload.ticket);
        }
      } catch (_error) {
        // Keep bootstrap fallback ticket.
      } finally {
        if (isActive) {
          setLoadingTicket(false);
        }
      }
    };

    loadTicket();
    return () => {
      isActive = false;
    };
  }, [id]);

  if (loadingTicket && !ticket) {
    return (
      <DashboardLayout title={t('ticketDetails')}>
        <div className="p-6 text-sm text-muted-foreground">
          {language === 'de' ? 'Ticket wird geladen...' : 'Loading ticket...'}
        </div>
      </DashboardLayout>
    );
  }

  if (!ticket) {
    return (
      <DashboardLayout title={t('ticketDetails')}>
        <div className="flex flex-col items-center justify-center py-20">
          <Info weight="fill" className="w-16 h-16 text-muted-foreground mb-4" />
          <h2 className="font-heading font-semibold text-xl text-foreground">{t('ticketNotFound')}</h2>
          <Button 
            variant="outline" 
            className="mt-4 border-border"
            onClick={() => { window.location.href = '/tickets'; }}
          >
            <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
            {t('backToTickets')}
          </Button>
        </div>
      </DashboardLayout>
    );
  }

  const getStatusConfig = (status) => {
    const configs = {
      open: { 
        bg: 'bg-yellow-500/20', 
        text: 'text-yellow-400', 
        border: 'border-yellow-500/30',
        icon: Clock,
        label: t('open')
      },
      answered: { 
        bg: 'bg-green-500/20', 
        text: 'text-green-400', 
        border: 'border-green-500/30',
        icon: ChatCircle,
        label: t('answered')
      },
      closed: { 
        bg: 'bg-muted', 
        text: 'text-muted-foreground', 
        border: 'border-border',
        icon: CheckCircle,
        label: t('closed')
      },
    };
    return configs[status] || configs.open;
  };

  const getPriorityConfig = (priority) => {
    const configs = {
      high: { bg: 'bg-red-500/20', text: 'text-red-400', border: 'border-red-500/30', label: t('high') },
      normal: { bg: 'bg-blue-500/20', text: 'text-blue-400', border: 'border-blue-500/30', label: t('normal') },
      low: { bg: 'bg-muted', text: 'text-muted-foreground', border: 'border-border', label: t('low') },
    };
    return configs[priority] || configs.normal;
  };

  const getCategoryLabel = (category) => {
    const labels = {
      technical: t('technical'),
      sales: t('sales'),
      billing: t('billing'),
      other: t('other'),
    };
    return labels[category] || category;
  };

  const formatDateTime = (dateStr) => {
    if (!dateStr) {
      return '-';
    }

    const parsed = new Date(dateStr);
    if (Number.isNaN(parsed.getTime())) {
      return '-';
    }

    return new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(parsed);
  };

  const statusConfig = getStatusConfig(ticket.status);
  const priorityConfig = getPriorityConfig(ticket.priority);
  const StatusIcon = statusConfig.icon;

  const handleSendReply = () => {
    if ((!replyMessage.trim() && replyAttachments.length === 0) || sendingReply) {
      return;
    }

    setReplyError('');
    setSendingReply(true);

    const formData = new FormData();
    formData.append(
      'message',
      replyMessage.trim() || (language === 'de' ? 'Neue Dateien hochgeladen' : 'New files uploaded')
    );
    replyAttachments.forEach((file) => {
      formData.append('attachments[]', file);
    });

    fetch(`/tickets/api/${encodeURIComponent(String(ticket.id))}/reply`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
      },
      body: formData,
    })
      .then(async (response) => {
        const payload = await response.json().catch(() => null);
        if (!response.ok) {
          throw new Error(payload?.message || 'Antwort konnte nicht gesendet werden.');
        }

        if (payload?.ticket && typeof payload.ticket === 'object') {
          setTicket(payload.ticket);
        }

        setReplyMessage('');
        setReplyAttachments([]);
      })
      .catch((error) => {
        setReplyError(error instanceof Error ? error.message : 'Antwort konnte nicht gesendet werden.');
      })
      .finally(() => {
        setSendingReply(false);
      });
  };

  const formatFileSize = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) {
      return '';
    }

    if (bytes < 1024) {
      return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
      return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  };

  const isImageAttachment = (attachment) => {
    const mimeType = String(attachment?.mimeType || '').toLowerCase();
    const fileName = String(attachment?.name || '').toLowerCase();
    return mimeType.startsWith('image/') || /\.(jpg|jpeg|png|gif|webp)$/i.test(fileName);
  };

  const isPdfAttachment = (attachment) => {
    const mimeType = String(attachment?.mimeType || '').toLowerCase();
    const fileName = String(attachment?.name || '').toLowerCase();
    return mimeType === 'application/pdf' || fileName.endsWith('.pdf');
  };

  const isCorrectedOriginalFile = (attachment) => {
    const fileName = String(attachment?.name || '').toLowerCase();
    return /\.(bin|ori|mod|frf|hex|ols|ecu|tuning)$/i.test(fileName);
  };

  return (
    <DashboardLayout title={t('ticketDetails')}>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="flex items-center gap-4">
            <Button 
              variant="ghost" 
              size="icon"
              className="text-muted-foreground hover:text-foreground hover:bg-secondary"
              onClick={() => { window.location.href = '/tickets'; }}
              data-testid="back-btn"
            >
              <ArrowLeft weight="bold" className="w-5 h-5" />
            </Button>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="font-heading font-bold text-xl text-foreground">{ticket.id}</h1>
                <Badge variant="outline" className={`${statusConfig.bg} ${statusConfig.text} border ${statusConfig.border} gap-1`}>
                  <StatusIcon weight="fill" className="w-3 h-3" />
                  {statusConfig.label}
                </Badge>
                {ticket.priority === 'high' && (
                  <Badge variant="outline" className={`${priorityConfig.bg} ${priorityConfig.text} border ${priorityConfig.border} gap-1`}>
                    <Warning weight="fill" className="w-3 h-3" />
                    {priorityConfig.label}
                  </Badge>
                )}
              </div>
              <p className="text-muted-foreground mt-1">{ticket.subject}</p>
            </div>
          </div>
          
          <div className="flex items-center gap-3">
            {ticket.status !== 'closed' ? (
              <Button variant="outline" className="border-border hover:bg-secondary">
                <CheckCircle weight="bold" className="w-4 h-4 mr-2" />
                {t('closeTicket')}
              </Button>
            ) : (
              <Button variant="outline" className="border-border hover:bg-secondary">
                <Clock weight="bold" className="w-4 h-4 mr-2" />
                {t('reopenTicket')}
              </Button>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Conversation */}
          <div className="lg:col-span-2 space-y-4">
            <Card className="bg-card border-border" data-testid="conversation-card">
              <CardHeader className="border-b border-border pb-4">
                <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                  <ChatCircle weight="fill" className="w-5 h-5 text-primary" />
                  {t('conversation')}
                </CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                <ScrollArea className="h-[400px]">
                  <div className="p-4 space-y-4">
                    {ticket.messages.map((message) => {
                      const isCustomer = message.sender === 'customer';
                      return (
                        <div 
                          key={message.id}
                          className={`flex gap-3 ${isCustomer ? '' : 'flex-row-reverse'}`}
                        >
                          {/* Avatar */}
                          <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${
                            isCustomer ? 'bg-primary/20' : 'bg-green-500/20'
                          }`}>
                            {isCustomer ? (
                              <User weight="fill" className="w-4 h-4 text-primary" />
                            ) : (
                              <Headset weight="fill" className="w-4 h-4 text-green-500" />
                            )}
                          </div>
                          
                          {/* Message */}
                          <div className={`flex-1 max-w-[80%] ${isCustomer ? '' : 'text-right'}`}>
                            <div className={`inline-block p-3 rounded-sm ${
                              isCustomer 
                                ? 'bg-secondary text-left' 
                                : 'bg-green-500/10 border border-green-500/20 text-left'
                            }`}>
                              <p className="text-xs text-muted-foreground mb-1">
                                {isCustomer ? t('you') : t('support')}
                              </p>
                              <p className="text-sm text-foreground">{message.message}</p>
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                              {formatDateTime(message.timestamp)}
                            </p>
                          </div>
                        </div>
                      );
                    })}
                    {ticket.messages.length === 0 && (
                      <p className="text-sm text-muted-foreground text-center py-6">
                        {language === 'de' ? 'Noch keine Nachrichten vorhanden.' : 'No messages yet.'}
                      </p>
                    )}
                  </div>
                </ScrollArea>
                
                {/* Reply Box */}
                {ticket.status !== 'closed' && (
                  <div className="border-t border-border p-4">
                    {replyError && (
                      <div className="mb-3 rounded-sm border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
                        {replyError}
                      </div>
                    )}
                    <div className="flex gap-3">
                      <Textarea
                        placeholder={t('replyPlaceholder')}
                        value={replyMessage}
                        onChange={(e) => setReplyMessage(e.target.value)}
                        className="bg-secondary border-border min-h-[80px] resize-none"
                        data-testid="reply-input"
                      />
                    </div>
                    <div className="mt-3 flex flex-col gap-3">
                      <p className="text-xs text-muted-foreground">{t('uploadHint')}</p>
                      <div className="flex items-center gap-3">
                        <label className="inline-flex items-center gap-2 rounded-sm border border-border bg-secondary px-3 py-2 text-sm font-medium text-foreground cursor-pointer hover:bg-secondary/80">
                          <Paperclip weight="bold" className="w-4 h-4" />
                          {t('attachmentOptional')}
                          <input
                            type="file"
                            className="hidden"
                            multiple
                            onChange={(event) => {
                              const files = Array.from(event.target.files || []);
                              setReplyAttachments((current) => {
                                const next = [...current];
                                const seen = new Set(current.map((file) => `${file.name}:${file.size}:${file.lastModified}`));

                                files.forEach((file) => {
                                  const key = `${file.name}:${file.size}:${file.lastModified}`;
                                  if (!seen.has(key)) {
                                    seen.add(key);
                                    next.push(file);
                                  }
                                });

                                return next;
                              });
                              event.target.value = '';
                            }}
                            data-testid="reply-attachment-input"
                          />
                        </label>
                      </div>
                      {replyAttachments.length > 0 && (
                        <div className="space-y-2">
                          <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider">{t('selectedFiles')}</p>
                          <div className="space-y-2">
                            {replyAttachments.map((file, index) => (
                              <div key={`${file.name}:${file.size}:${file.lastModified}:${index}`} className="flex items-center gap-2 rounded-sm border border-border bg-card px-3 py-2 text-sm text-foreground">
                                <Paperclip weight="fill" className="w-4 h-4 text-primary flex-shrink-0" />
                                <span className="font-medium truncate">{file.name}</span>
                                <span className="text-muted-foreground flex-shrink-0">{formatFileSize(file.size)}</span>
                                <button
                                  type="button"
                                  className="ml-auto text-muted-foreground hover:text-foreground"
                                  onClick={() => {
                                    setReplyAttachments((current) => current.filter((_, currentIndex) => currentIndex !== index));
                                  }}
                                  data-testid={`reply-attachment-remove-${index}`}
                                  aria-label={t('removeFile')}
                                >
                                  <X weight="bold" className="w-4 h-4" />
                                </button>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                    <div className="flex justify-end mt-3">
                      <Button 
                        className="bg-primary hover:bg-primary/90"
                        onClick={handleSendReply}
                        disabled={(!replyMessage.trim() && replyAttachments.length === 0) || sendingReply}
                        data-testid="send-reply-btn"
                      >
                        <PaperPlaneTilt weight="bold" className="w-4 h-4 mr-2" />
                        {t('sendReply')}
                      </Button>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Ticket Info Sidebar */}
          <div className="space-y-6">
            <Card className="bg-card border-border" data-testid="ticket-info-card">
              <CardHeader className="border-b border-border pb-4">
                <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                  <Ticket weight="fill" className="w-5 h-5 text-primary" />
                  {t('ticketInfo')}
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4 space-y-4">
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('attachments')}</p>
                  <div className="mt-2 space-y-2">
                    {Array.isArray(ticket.attachments) && ticket.attachments.length > 0 ? ticket.attachments.map((attachment) => (
                      <div
                        key={attachment.id}
                        className="rounded-sm border border-border bg-secondary/60 p-3"
                      >
                        {isImageAttachment(attachment) && attachment.previewUrl && (
                          <a href={attachment.previewUrl} target="_blank" rel="noreferrer" className="block mb-3 overflow-hidden rounded-sm border border-border bg-card">
                            <img
                              src={attachment.previewUrl}
                              alt={attachment.name}
                              className="h-40 w-full object-cover"
                            />
                          </a>
                        )}
                        <div className="flex items-start justify-between gap-3">
                          <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2 mb-1">
                              <p className="text-sm font-medium text-foreground truncate">{attachment.name}</p>
                              {isCorrectedOriginalFile(attachment) && (
                                <Badge className="bg-primary/15 text-primary border-primary/30 text-[10px]">
                                  <FileArrowUp weight="bold" className="w-3 h-3 mr-1" />
                                  {t('correctedFile')}
                                </Badge>
                              )}
                              {isImageAttachment(attachment) && (
                                <Badge className="bg-info-soft text-info-theme border-info-soft text-[10px]">
                                  <ImageIcon weight="bold" className="w-3 h-3 mr-1" />
                                  {t('imagePreview')}
                                </Badge>
                              )}
                              {isPdfAttachment(attachment) && (
                                <Badge className="bg-warning-soft text-warning-theme border-warning-soft text-[10px]">
                                  <FilePdf weight="bold" className="w-3 h-3 mr-1" />
                                  {t('pdfDocument')}
                                </Badge>
                              )}
                              {!isImageAttachment(attachment) && !isPdfAttachment(attachment) && !isCorrectedOriginalFile(attachment) && (
                                <Badge className="bg-muted text-muted-foreground border-border text-[10px]">
                                  {t('genericFile')}
                                </Badge>
                              )}
                            </div>
                            <p className="text-xs text-muted-foreground">
                              {[formatFileSize(attachment.size), formatDateTime(attachment.createdAt)].filter(Boolean).join(' · ')}
                            </p>
                          </div>
                          <a
                            href={attachment.downloadUrl}
                            className="inline-flex items-center gap-1 rounded-sm border border-border bg-card px-2.5 py-2 text-xs font-medium text-foreground hover:bg-secondary"
                          >
                            <DownloadSimple weight="bold" className="w-4 h-4 text-primary flex-shrink-0" />
                          </a>
                        </div>
                      </div>
                    )) : (
                      <p className="text-sm text-muted-foreground">{t('noAttachments')}</p>
                    )}
                  </div>
                </div>

                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('status')}</p>
                  <Badge variant="outline" className={`mt-1 ${statusConfig.bg} ${statusConfig.text} border ${statusConfig.border}`}>
                    {statusConfig.label}
                  </Badge>
                </div>
                
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('priority')}</p>
                  <Badge variant="outline" className={`mt-1 ${priorityConfig.bg} ${priorityConfig.text} border ${priorityConfig.border}`}>
                    {priorityConfig.label}
                  </Badge>
                </div>
                
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('category')}</p>
                  <p className="text-sm text-foreground mt-1">{getCategoryLabel(ticket.category)}</p>
                </div>
                
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('created')}</p>
                  <p className="text-sm text-foreground mt-1">{formatDateTime(ticket.createdAt)}</p>
                </div>
                
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('lastUpdate')}</p>
                  <p className="text-sm text-foreground mt-1">{formatDateTime(ticket.updatedAt)}</p>
                </div>
                
                {ticket.relatedOrder && (
                  <div>
                    <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('relatedOrder')}</p>
                    <Button 
                      variant="link" 
                      className="p-0 h-auto text-primary hover:text-primary/80"
                      onClick={() => { window.location.href = `/orders/${ticket.relatedOrder}`; }}
                    >
                      <LinkIcon weight="bold" className="w-3 h-3 mr-1" />
                      {ticket.relatedOrder}
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

import { useEffect, useState } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import { Textarea } from '../components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '../components/ui/dialog';
import { Label } from '../components/ui/label';
import { 
  Ticket, 
  Plus,
  MagnifyingGlass,
  FunnelSimple,
  ChatCircle,
  Clock,
  CheckCircle,
  Warning,
  ArrowRight,
  PaperPlaneTilt,
  Paperclip
} from '@phosphor-icons/react';
import { useNavigate } from 'react-router-dom';

const readTicketsBootstrap = () => {
  if (typeof window === 'undefined') {
    return [];
  }

  const items = window?.portalReactBootstrap?.tickets?.items;
  return Array.isArray(items) ? items : [];
};

const ensureString = (value, fallback = '') => {
  return typeof value === 'string' ? value : fallback;
};

const normalizeTickets = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }

  return items.map((ticket, index) => {
    const id = ensureString(ticket?.id, `TICKET-${index + 1}`);
    const messages = Array.isArray(ticket?.messages)
      ? ticket.messages
          .map((message, messageIndex) => ({
            id: message?.id ?? `${id}-msg-${messageIndex + 1}`,
            sender: message?.sender === 'support' ? 'support' : 'customer',
            message: ensureString(message?.message, ''),
            timestamp: ensureString(message?.timestamp, null),
          }))
          .filter((message) => message.message.trim() !== '')
      : [];

    return {
      id,
      subject: ensureString(ticket?.subject, ''),
      category: ensureString(ticket?.category, 'other'),
      status: ensureString(ticket?.status, 'open'),
      priority: ensureString(ticket?.priority, 'normal'),
      createdAt: ensureString(ticket?.createdAt, null),
      updatedAt: ensureString(ticket?.updatedAt, ticket?.createdAt ?? null),
      messages,
      attachments: Array.isArray(ticket?.attachments)
        ? ticket.attachments.map((attachment, attachmentIndex) => ({
            id: attachment?.id ?? `${id}-attachment-${attachmentIndex + 1}`,
            name: ensureString(attachment?.name, ''),
            description: ensureString(attachment?.description, ''),
            size: typeof attachment?.size === 'number' ? attachment.size : 0,
            mimeType: ensureString(attachment?.mimeType, ''),
            createdAt: ensureString(attachment?.createdAt, null),
            previewUrl: ensureString(attachment?.previewUrl, ''),
            downloadUrl: ensureString(attachment?.downloadUrl, ''),
          })).filter((attachment) => attachment.name.trim() !== '')
        : [],
      relatedOrder: ensureString(ticket?.relatedOrder, null),
    };
  });
};

export const mockTickets = [];

export const getTicketsData = () => {
  const bootstrapTickets = normalizeTickets(readTicketsBootstrap());
  if (bootstrapTickets.length > 0) {
    return bootstrapTickets;
  }

  return [];
};

// Translations
const pageTranslations = {
  en: {
    pageTitle: 'Support Tickets',
    allTickets: 'All Tickets',
    newTicket: 'New Ticket',
    searchPlaceholder: 'Search tickets...',
    filterByStatus: 'Status',
    allStatus: 'All Status',
    open: 'Open',
    answered: 'Answered',
    closed: 'Closed',
    subject: 'Subject',
    category: 'Category',
    status: 'Status',
    priority: 'Priority',
    created: 'Created',
    lastUpdate: 'Last Update',
    actions: 'Actions',
    noTickets: 'No tickets found',
    technical: 'Technical',
    sales: 'Sales',
    billing: 'Billing',
    other: 'Other',
    high: 'High',
    normal: 'Normal',
    low: 'Low',
    totalTickets: 'Total Tickets',
    openTickets: 'Open',
    awaitingReply: 'Awaiting Reply',
    createTicket: 'Create Ticket',
    ticketSubject: 'Subject',
    ticketCategory: 'Category',
    ticketPriority: 'Priority',
    ticketMessage: 'Message',
    submit: 'Submit',
    cancel: 'Cancel',
    relatedOrder: 'Related Order',
    optional: 'Optional',
  },
  de: {
    pageTitle: 'Support Tickets',
    allTickets: 'Alle Tickets',
    newTicket: 'Neues Ticket',
    searchPlaceholder: 'Tickets suchen...',
    filterByStatus: 'Status',
    allStatus: 'Alle Status',
    open: 'Offen',
    answered: 'Beantwortet',
    closed: 'Geschlossen',
    subject: 'Betreff',
    category: 'Kategorie',
    status: 'Status',
    priority: 'Priorität',
    created: 'Erstellt',
    lastUpdate: 'Letzte Aktualisierung',
    actions: 'Aktionen',
    noTickets: 'Keine Tickets gefunden',
    technical: 'Technisch',
    sales: 'Vertrieb',
    billing: 'Rechnung',
    other: 'Sonstiges',
    high: 'Hoch',
    normal: 'Normal',
    low: 'Niedrig',
    totalTickets: 'Gesamt Tickets',
    openTickets: 'Offen',
    awaitingReply: 'Warten auf Antwort',
    createTicket: 'Ticket erstellen',
    ticketSubject: 'Betreff',
    ticketCategory: 'Kategorie',
    ticketPriority: 'Priorität',
    ticketMessage: 'Nachricht',
    submit: 'Absenden',
    cancel: 'Abbrechen',
    relatedOrder: 'Zugehöriger Auftrag',
    optional: 'Optional',
  }
};

export default function Tickets() {
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language][key] || key;
  const navigate = useNavigate();
  
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [tickets, setTickets] = useState(() => getTicketsData());
  const [subject, setSubject] = useState('');
  const [category, setCategory] = useState('technical');
  const [priority, setPriority] = useState('normal');
  const [message, setMessage] = useState('');
  const [relatedOrder, setRelatedOrder] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState('');

  useEffect(() => {
    let isActive = true;

    const loadTickets = async () => {
      try {
        const response = await fetch('/tickets/api', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        const items = normalizeTickets(Array.isArray(payload?.items) ? payload.items : []);
        if (isActive) {
          setTickets(items);
        }
      } catch (_error) {
        // Keep bootstrap data on fetch errors.
      }
    };

    loadTickets();
    return () => {
      isActive = false;
    };
  }, []);

  const handleCreateTicket = async () => {
    setSubmitError('');
    setSubmitting(true);

    try {
      const response = await fetch('/tickets/api', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          subject,
          category,
          priority,
          message,
          relatedOrder,
        }),
      });

      const payload = await response.json().catch(() => null);
      if (!response.ok) {
        setSubmitError(payload?.message || 'Ticket konnte nicht erstellt werden.');
        return;
      }

      const createdTicket = payload?.ticket && typeof payload.ticket === 'object' ? payload.ticket : null;
      if (createdTicket) {
        setTickets((current) => [normalizeTickets([createdTicket])[0], ...current]);
      }

      setSubject('');
      setCategory('technical');
      setPriority('normal');
      setMessage('');
      setRelatedOrder('');
      setIsDialogOpen(false);

      if (createdTicket?.id) {
        navigate(`/tickets/${createdTicket.id}`);
      }
    } catch (_error) {
      setSubmitError('Ticket konnte nicht erstellt werden.');
    } finally {
      setSubmitting(false);
    }
  };

  // Filter tickets
  const filteredTickets = tickets.filter(ticket => {
    const statusMatch = statusFilter === 'all' || ticket.status === statusFilter;
    const searchMatch = searchQuery === '' || 
      ticket.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      ticket.subject.toLowerCase().includes(searchQuery.toLowerCase());
    return statusMatch && searchMatch;
  });

  // Stats
  const stats = {
    total: tickets.length,
    open: tickets.filter(t => t.status === 'open').length,
    answered: tickets.filter(t => t.status === 'answered').length,
  };

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

  const formatDate = (dateStr) => {
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

  return (
    <DashboardLayout title={t('pageTitle')}>
      <div className="space-y-6 max-w-[1400px]">
        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4" data-testid="tickets-stats">
          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('totalTickets')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.total}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-primary/10 flex items-center justify-center">
                  <Ticket weight="fill" className="w-6 h-6 text-primary" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('openTickets')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.open}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-yellow-500/10 flex items-center justify-center">
                  <Clock weight="fill" className="w-6 h-6 text-yellow-500" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('awaitingReply')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.answered}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-green-500/10 flex items-center justify-center">
                  <ChatCircle weight="fill" className="w-6 h-6 text-green-500" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Tickets List */}
        <Card className="bg-card border-border" data-testid="tickets-list-card">
          <CardHeader className="border-b border-border pb-4">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                <Ticket weight="fill" className="w-5 h-5 text-primary" />
                {t('allTickets')}
              </CardTitle>
              
              <div className="flex items-center gap-3">
                {/* Search */}
                <div className="relative">
                  <MagnifyingGlass className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    type="search"
                    placeholder={t('searchPlaceholder')}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="w-48 pl-9 bg-secondary border-border"
                    data-testid="tickets-search"
                  />
                </div>
                
                {/* Status Filter */}
                <FunnelSimple weight="bold" className="w-4 h-4 text-muted-foreground" />
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-36 bg-secondary border-border" data-testid="status-filter">
                    <SelectValue placeholder={t('filterByStatus')} />
                  </SelectTrigger>
                  <SelectContent className="bg-card border-border">
                    <SelectItem value="all">{t('allStatus')}</SelectItem>
                    <SelectItem value="open">{t('open')}</SelectItem>
                    <SelectItem value="answered">{t('answered')}</SelectItem>
                    <SelectItem value="closed">{t('closed')}</SelectItem>
                  </SelectContent>
                </Select>

                {/* New Ticket Button */}
                <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="bg-primary hover:bg-primary/90" data-testid="new-ticket-btn">
                      <Plus weight="bold" className="w-4 h-4 mr-2" />
                      {t('newTicket')}
                    </Button>
                  </DialogTrigger>
                  <DialogContent className="bg-card border-border max-w-lg">
                    <DialogHeader>
                      <DialogTitle className="font-heading text-xl">{t('createTicket')}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4 mt-4">
                      {submitError && (
                        <div className="rounded-sm border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
                          {submitError}
                        </div>
                      )}
                      <div className="space-y-2">
                        <Label>{t('ticketSubject')}</Label>
                        <Input 
                          placeholder={t('ticketSubject')}
                          value={subject}
                          onChange={(event) => setSubject(event.target.value)}
                          className="bg-secondary border-border"
                          data-testid="ticket-subject-input"
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                          <Label>{t('ticketCategory')}</Label>
                          <Select value={category} onValueChange={setCategory}>
                            <SelectTrigger className="bg-secondary border-border">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent className="bg-card border-border">
                              <SelectItem value="technical">{t('technical')}</SelectItem>
                              <SelectItem value="sales">{t('sales')}</SelectItem>
                              <SelectItem value="billing">{t('billing')}</SelectItem>
                              <SelectItem value="other">{t('other')}</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                        <div className="space-y-2">
                          <Label>{t('ticketPriority')}</Label>
                          <Select value={priority} onValueChange={setPriority}>
                            <SelectTrigger className="bg-secondary border-border">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent className="bg-card border-border">
                              <SelectItem value="high">{t('high')}</SelectItem>
                              <SelectItem value="normal">{t('normal')}</SelectItem>
                              <SelectItem value="low">{t('low')}</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label>{t('relatedOrder')} <span className="text-muted-foreground text-xs">({t('optional')})</span></Label>
                        <Input 
                          placeholder="TF-2025-XXXX"
                          value={relatedOrder}
                          onChange={(event) => setRelatedOrder(event.target.value)}
                          className="bg-secondary border-border"
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{t('ticketMessage')}</Label>
                        <Textarea 
                          placeholder={t('ticketMessage')}
                          value={message}
                          onChange={(event) => setMessage(event.target.value)}
                          className="bg-secondary border-border min-h-[120px]"
                          data-testid="ticket-message-input"
                        />
                      </div>
                      <div className="flex justify-end gap-3 pt-4">
                        <Button 
                          variant="outline" 
                          className="border-border"
                          onClick={() => setIsDialogOpen(false)}
                          disabled={submitting}
                        >
                          {t('cancel')}
                        </Button>
                        <Button
                          className="bg-primary hover:bg-primary/90"
                          onClick={handleCreateTicket}
                          disabled={submitting || subject.trim() === '' || message.trim() === ''}
                        >
                          <PaperPlaneTilt weight="bold" className="w-4 h-4 mr-2" />
                          {t('submit')}
                        </Button>
                      </div>
                    </div>
                  </DialogContent>
                </Dialog>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-0">
            {filteredTickets.length > 0 ? (
              <div className="divide-y divide-border">
                {filteredTickets.map((ticket) => {
                  const statusConfig = getStatusConfig(ticket.status);
                  const priorityConfig = getPriorityConfig(ticket.priority);
                  const StatusIcon = statusConfig.icon;
                  const lastMessage = ticket.messages[ticket.messages.length - 1];
                  
                  return (
                    <div 
                      key={ticket.id}
                      className="p-4 hover:bg-secondary/50 transition-colors duration-200 cursor-pointer"
                      onClick={() => { window.location.href = `/tickets/${ticket.id}`; }}
                      data-testid={`ticket-item-${ticket.id}`}
                    >
                      <div className="flex items-start justify-between gap-4">
                        <div className="flex items-start gap-4 flex-1 min-w-0">
                          {/* Icon */}
                          <div className={`w-10 h-10 rounded-sm flex items-center justify-center flex-shrink-0 ${statusConfig.bg}`}>
                            <StatusIcon weight="fill" className={`w-5 h-5 ${statusConfig.text}`} />
                          </div>
                          
                          {/* Content */}
                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 flex-wrap">
                              <span className="font-mono text-xs text-primary">{ticket.id}</span>
                              <Badge variant="outline" className={`${statusConfig.bg} ${statusConfig.text} border ${statusConfig.border}`}>
                                {statusConfig.label}
                              </Badge>
                              {ticket.priority === 'high' && (
                                <Badge variant="outline" className={`${priorityConfig.bg} ${priorityConfig.text} border ${priorityConfig.border} gap-1`}>
                                  <Warning weight="fill" className="w-3 h-3" />
                                  {priorityConfig.label}
                                </Badge>
                              )}
                              <Badge variant="outline" className="bg-secondary border-border text-xs">
                                {getCategoryLabel(ticket.category)}
                              </Badge>
                            </div>
                            <h4 className="font-semibold text-foreground mt-1 truncate">{ticket.subject}</h4>
                            <p className="text-sm text-muted-foreground mt-1 line-clamp-1">
                              {lastMessage?.message || '-'}
                            </p>
                            <div className="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                              <span>{formatDate(ticket.createdAt)}</span>
                              {ticket.relatedOrder && (
                                <span className="text-primary">→ {ticket.relatedOrder}</span>
                              )}
                            </div>
                          </div>
                        </div>
                        
                        {/* Message Count & Arrow */}
                        <div className="flex items-center gap-4">
                          <div className="text-center">
                            <p className="font-heading font-bold text-lg text-foreground">{ticket.messages.length}</p>
                            <p className="text-xs text-muted-foreground">
                              {language === 'de' ? 'Nachrichten' : 'Messages'}
                            </p>
                          </div>
                          <ArrowRight weight="bold" className="w-5 h-5 text-muted-foreground" />
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="p-8 text-center text-muted-foreground">
                {t('noTickets')}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}

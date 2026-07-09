import { createContext, useContext, useState, useEffect } from 'react';

const translations = {
  en: {
    // Navigation
    dashboard: 'Dashboard',
    fileWizard: 'File Wizard',
    configurator: 'Configurator',
    credits: 'Credits',
    orders: 'Orders',
    invoices: 'Invoices',
    tickets: 'Tickets',
    profile: 'Profile',
    logout: 'Logout',
    
    // Dashboard
    welcome: 'Welcome back',
    overview: 'Overview',
    availableCredits: 'Available Credits',
    pendingOrders: 'Pending Orders',
    completedFiles: 'Completed Files',
    totalSpent: 'Total Spent',
    recentActivity: 'Recent Activity',
    quickActions: 'Quick Actions',
    newRequest: 'New Request',
    buyCredits: 'Buy Credits',
    viewOrders: 'View Orders',
    noActivity: 'No recent activity',
    
    // File Wizard
    fileWizardTitle: 'File Upload Wizard',
    selectVehicle: 'Select Vehicle',
    uploadFile: 'Upload File',
    selectOptions: 'Select Options',
    review: 'Review & Submit',
    
    // Configurator
    configuratorTitle: 'Chiptuning Configurator',
    selectManufacturer: 'Select Manufacturer',
    selectModel: 'Select Model',
    selectEngine: 'Select Engine',
    selectTuningType: 'Select Tuning Type',
    estimatedPrice: 'Estimated Price',
    
    // Credits
    creditsTitle: 'Credits & Balance',
    currentBalance: 'Current Balance',
    purchaseCredits: 'Purchase Credits',
    transactionHistory: 'Transaction History',
    
    // Orders
    ordersTitle: 'Order History',
    orderNumber: 'Order #',
    date: 'Date',
    status: 'Status',
    vehicle: 'Vehicle',
    type: 'Type',
    amount: 'Amount',
    
    // Profile
    profileTitle: 'Profile Settings',
    personalInfo: 'Personal Information',
    companyInfo: 'Company Information',
    apiAccess: 'API Access',
    
    // Status
    pending: 'Pending',
    processing: 'Processing',
    parked: 'Parked',
    completed: 'Completed',
    cancelled: 'Cancelled',
    
    // Common
    search: 'Search...',
    notifications: 'Notifications',
    settings: 'Settings',
    language: 'Language',
    german: 'German',
    english: 'English',
  },
  de: {
    // Navigation
    dashboard: 'Dashboard',
    fileWizard: 'Datei-Assistent',
    configurator: 'Konfigurator',
    credits: 'Guthaben',
    orders: 'Bestellungen',
    invoices: 'Rechnungen',
    tickets: 'Tickets',
    profile: 'Profil',
    logout: 'Abmelden',
    
    // Dashboard
    welcome: 'Willkommen zurück',
    overview: 'Übersicht',
    availableCredits: 'Verfügbares Guthaben',
    pendingOrders: 'Offene Bestellungen',
    completedFiles: 'Abgeschlossene Dateien',
    totalSpent: 'Gesamtausgaben',
    recentActivity: 'Letzte Aktivität',
    quickActions: 'Schnellaktionen',
    newRequest: 'Neue Anfrage',
    buyCredits: 'Guthaben kaufen',
    viewOrders: 'Bestellungen ansehen',
    noActivity: 'Keine aktuelle Aktivität',
    
    // File Wizard
    fileWizardTitle: 'Datei-Upload Assistent',
    selectVehicle: 'Fahrzeug wählen',
    uploadFile: 'Datei hochladen',
    selectOptions: 'Optionen wählen',
    review: 'Überprüfen & Absenden',
    
    // Configurator
    configuratorTitle: 'Chiptuning Konfigurator',
    selectManufacturer: 'Hersteller wählen',
    selectModel: 'Modell wählen',
    selectEngine: 'Motor wählen',
    selectTuningType: 'Tuning-Typ wählen',
    estimatedPrice: 'Geschätzter Preis',
    
    // Credits
    creditsTitle: 'Guthaben & Kontostand',
    currentBalance: 'Aktueller Kontostand',
    purchaseCredits: 'Guthaben kaufen',
    transactionHistory: 'Transaktionsverlauf',
    
    // Orders
    ordersTitle: 'Bestellverlauf',
    orderNumber: 'Bestellung #',
    date: 'Datum',
    status: 'Status',
    vehicle: 'Fahrzeug',
    type: 'Typ',
    amount: 'Betrag',
    
    // Profile
    profileTitle: 'Profileinstellungen',
    personalInfo: 'Persönliche Informationen',
    companyInfo: 'Firmeninformationen',
    apiAccess: 'API-Zugang',
    
    // Status
    pending: 'Ausstehend',
    processing: 'In Bearbeitung',
    parked: 'Geparkt',
    completed: 'Abgeschlossen',
    cancelled: 'Storniert',
    
    // Common
    search: 'Suchen...',
    notifications: 'Benachrichtigungen',
    settings: 'Einstellungen',
    language: 'Sprache',
    german: 'Deutsch',
    english: 'Englisch',
  }
};

const LanguageContext = createContext();

export const LanguageProvider = ({ children }) => {
  const [language, setLanguage] = useState(() => {
    const saved = localStorage.getItem('language');
    return saved || 'de';
  });

  useEffect(() => {
    localStorage.setItem('language', language);
  }, [language]);

  const t = (key) => {
    return translations[language][key] || key;
  };

  const toggleLanguage = () => {
    setLanguage(prev => prev === 'en' ? 'de' : 'en');
  };

  return (
    <LanguageContext.Provider value={{ language, setLanguage, toggleLanguage, t }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

import "@/App.css";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { LanguageProvider } from "./context/LanguageContext";
import { ThemeProvider } from "./context/ThemeContext";
import { Toaster } from "./components/ui/sonner";

// Pages
import Dashboard from "./pages/Dashboard";
import FileWizard from "./pages/FileWizard";
import Configurator from "./pages/Configurator";
import Credits from "./pages/Credits";
import OrdersNew from "./pages/OrdersNew";
import OrderDetail from "./pages/OrderDetail";
import Profile from "./pages/Profile";
import Invoices from "./pages/Invoices";
import InvoiceDetail from "./pages/InvoiceDetail";
import Tickets from "./pages/Tickets";
import TicketDetail from "./pages/TicketDetail";
import PriceList from "./pages/PriceList";
import NewOrderCombined from "./pages/NewOrderCombined";
import OrderLive from "./pages/OrderLive";
import EmailTemplates from "./pages/EmailTemplates";
import Statistics from "./pages/Statistics";
import Uploads from "./pages/Uploads";
import Vehicles from "./pages/Vehicles";
import DtcCodes from "./pages/DtcCodes";
import Login from "./pages/Login";
import Register from "./pages/Register";
import PasswordResetRequest from "./pages/PasswordResetRequest";
import PasswordResetConfirm from "./pages/PasswordResetConfirm";

function App() {
  return (
    <ThemeProvider>
      <LanguageProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/login" element={<Login />} />
            <Route path="/registrieren" element={<Register />} />
            <Route path="/passwort-vergessen" element={<PasswordResetRequest />} />
            <Route path="/passwort-zuruecksetzen/:token" element={<PasswordResetConfirm />} />
            <Route path="/auftrag" element={<FileWizard />} />
            <Route path="/file-wizard" element={<FileWizard />} />
            <Route path="/new-order" element={<NewOrderCombined />} />
            <Route path="/configurator" element={<Configurator />} />
            <Route path="/credits" element={<Credits />} />
            <Route path="/orders" element={<OrdersNew />} />
            <Route path="/orders/:id" element={<OrderDetail />} />
            <Route path="/price-list" element={<PriceList />} />
            <Route path="/invoices" element={<Invoices />} />
            <Route path="/invoices/:id" element={<InvoiceDetail />} />
            <Route path="/tickets" element={<Tickets />} />
            <Route path="/tickets/:id" element={<TicketDetail />} />
            <Route path="/uploads" element={<Uploads />} />
            <Route path="/fahrzeuge" element={<Vehicles />} />
            <Route path="/profil" element={<Profile />} />
            <Route path="/profile" element={<Profile />} />
            <Route path="/order-live" element={<OrderLive />} />
            <Route path="/email-templates" element={<EmailTemplates />} />
            <Route path="/dtc-codes" element={<DtcCodes />} />
            <Route path="/statistics" element={<Statistics />} />
          </Routes>
        </BrowserRouter>
        <Toaster />
      </LanguageProvider>
    </ThemeProvider>
  );
}

export default App;

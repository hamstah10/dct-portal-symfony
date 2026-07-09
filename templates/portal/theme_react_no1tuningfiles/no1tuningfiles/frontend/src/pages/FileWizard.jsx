import { useState, useCallback, useRef, useMemo, useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Textarea } from '../components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { 
  Wrench,
  PlugsConnected,
  BookOpen,
  CircleHalf,
  Timer,
  CloudArrowUp,
  CloudArrowDown,
  ArrowRight,
  ArrowLeft,
  X,
  File as FileIcon,
  Upload,
  Sliders,
  CheckCircle,
  CarProfile,
  HardDrives,
  Crown,
  Link,
  Car,
  Engine,
  Calendar,
  Tag,
  GasPump,
  Lightning,
  Leaf,
  Gear,
  Drop,
  Fan,
  Gauge,
  Warning,
  Power,
  Fire,
  RocketLaunch,
  Prohibit,
  Thermometer,
  PaperPlaneTilt,
  CreditCard,
  Pencil
} from '@phosphor-icons/react';
import { Badge } from '../components/ui/badge';
import { Progress } from '../components/ui/progress';
import {
  Tooltip as UiTooltip,
  TooltipContent as UiTooltipContent,
  TooltipProvider as UiTooltipProvider,
  TooltipTrigger as UiTooltipTrigger,
} from '../components/ui/tooltip';
import { cn } from '../lib/utils';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { useVehicleApiOptions } from '../hooks/useVehicleApiOptions';
import { getBootstrapCredits, setBootstrapCredits } from '../lib/sessionBootstrap';

// Vehicle database: Hersteller -> Baureihe -> Modell -> Motor
const vehicleData = {
  'Audi': {
    'A1': {
      '8X - 2010': ['1.0 TFSI - 95 PS', '1.4 TFSI - 125 PS', '1.4 TDI - 90 PS', '1.6 TDI - 105 PS', '1.8 TFSI - 192 PS', '2.0 TDI - 143 PS'],
      'GB - 2018': ['1.0 TFSI - 116 PS', '1.5 TFSI - 150 PS', '2.0 TDI - 116 PS', '2.0 TFSI - 207 PS'],
    },
    'A3': {
      '8P - 2003': ['1.6 TDI - 105 PS', '1.8 TFSI - 160 PS', '2.0 TDI - 140 PS', '2.0 TDI - 170 PS', '2.0 TFSI - 200 PS', '3.2 V6 - 250 PS'],
      '8V - 2012': ['1.0 TFSI - 116 PS', '1.4 TFSI - 125 PS', '1.4 TFSI - 150 PS', '1.6 TDI - 110 PS', '2.0 TDI - 150 PS', '2.0 TDI - 184 PS', '2.0 TFSI - 190 PS', '2.0 TFSI - 310 PS (RS3)'],
      '8Y - 2020': ['1.0 TFSI - 110 PS', '1.5 TFSI - 150 PS', '2.0 TDI - 150 PS', '2.0 TFSI - 310 PS (RS3)', '2.5 TFSI - 400 PS (RS3)'],
    },
    'A4': {
      'B7 - 2004': ['1.8 T - 163 PS', '2.0 TDI - 140 PS', '2.0 TDI - 170 PS', '2.0 TFSI - 200 PS', '3.0 TDI - 204 PS', '4.2 FSI - 344 PS (RS4)'],
      'B8 - 2008': ['1.8 TFSI - 120 PS', '1.8 TFSI - 160 PS', '2.0 TDI - 143 PS', '2.0 TDI - 177 PS', '2.0 TFSI - 211 PS', '3.0 TDI - 245 PS', '3.2 FSI - 265 PS'],
      'B9 - 2015': ['1.4 TFSI - 150 PS', '2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TFSI - 190 PS', '2.0 TFSI - 252 PS', '3.0 TDI - 218 PS', '3.0 TDI - 272 PS', '2.9 TFSI - 450 PS (RS4)'],
    },
    'A5': {
      '8T - 2007': ['2.0 TDI - 143 PS', '2.0 TDI - 177 PS', '2.0 TFSI - 211 PS', '3.0 TDI - 204 PS', '3.0 TDI - 245 PS', '3.2 FSI - 265 PS', '4.2 FSI - 450 PS (RS5)'],
      'F5 - 2016': ['2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TFSI - 190 PS', '2.0 TFSI - 252 PS', '3.0 TDI - 218 PS', '3.0 TDI - 286 PS', '2.9 TFSI - 450 PS (RS5)'],
    },
    'A6': {
      'C6 - 2004': ['2.0 TDI - 140 PS', '2.7 TDI - 180 PS', '3.0 TDI - 225 PS', '3.0 TDI - 233 PS', '2.0 TFSI - 170 PS', '4.2 FSI - 350 PS', '5.0 V10 - 580 PS (RS6)'],
      'C7 - 2011': ['2.0 TDI - 177 PS', '2.0 TFSI - 180 PS', '3.0 TDI - 204 PS', '3.0 TDI EU6 - 218 PS', '3.0 TDI - 272 PS', '3.0 TDI - 326 PS', '3.0 TFSI - 310 PS', '4.0 TFSI - 560 PS (RS6)'],
      'C8 - 2018': ['2.0 TDI - 163 PS', '2.0 TDI - 204 PS', '3.0 TDI - 231 PS', '3.0 TDI - 286 PS', '2.0 TFSI - 245 PS', '3.0 TFSI - 340 PS', '4.0 TFSI - 600 PS (RS6)'],
    },
    'Q5': {
      '8R - 2008': ['2.0 TDI - 143 PS', '2.0 TDI - 170 PS', '2.0 TDI - 177 PS', '2.0 TFSI - 180 PS', '2.0 TFSI - 211 PS', '3.0 TDI - 240 PS', '3.0 TDI - 258 PS'],
      'FY - 2017': ['2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TFSI - 190 PS', '2.0 TFSI - 252 PS', '3.0 TDI - 286 PS', '2.9 TFSI - 381 PS (SQ5)'],
    },
    'Q7': {
      '4L - 2006': ['3.0 TDI - 204 PS', '3.0 TDI - 233 PS', '3.0 TDI - 240 PS', '4.2 TDI - 326 PS', '4.2 FSI - 350 PS', '6.0 TDI V12 - 500 PS'],
      '4M - 2015': ['3.0 TDI - 218 PS', '3.0 TDI - 272 PS', '2.0 TFSI - 252 PS', '3.0 TFSI - 340 PS', '4.0 TDI - 435 PS (SQ7)'],
    },
  },
  'BMW': {
    '1er': {
      'E87 - 2004': ['116i - 115 PS', '118d - 143 PS', '120d - 163 PS', '120d - 177 PS', '123d - 204 PS', '130i - 265 PS'],
      'F20 - 2011': ['114d - 95 PS', '116d - 116 PS', '118d - 143 PS', '118d - 150 PS', '120d - 184 PS', '120d - 190 PS', '125d - 218 PS', '125i - 218 PS', 'M135i - 326 PS'],
      'F40 - 2019': ['116d - 116 PS', '118d - 150 PS', '118i - 140 PS', '120d - 190 PS', '128ti - 265 PS', 'M135i - 306 PS'],
    },
    '3er': {
      'E90 - 2005': ['318d - 143 PS', '320d - 163 PS', '320d - 177 PS', '325d - 197 PS', '330d - 231 PS', '330d - 245 PS', '335d - 286 PS', '320i - 150 PS', '325i - 218 PS', '335i - 306 PS'],
      'F30 - 2012': ['316d - 116 PS', '318d - 143 PS', '318d - 150 PS', '320d - 163 PS', '320d - 184 PS', '320d - 190 PS', '325d - 218 PS', '330d - 258 PS', '335d - 313 PS', '320i - 184 PS', '328i - 245 PS', '335i - 306 PS', '340i - 326 PS'],
      'G20 - 2019': ['318d - 150 PS', '320d - 190 PS', '330d - 286 PS', '330e - 292 PS', '320i - 184 PS', '330i - 258 PS', 'M340i - 374 PS', 'M3 - 480 PS', 'M3 Competition - 510 PS'],
    },
    '5er': {
      'E60 - 2003': ['520d - 163 PS', '520d - 177 PS', '525d - 197 PS', '530d - 218 PS', '530d - 231 PS', '530d - 245 PS', '535d - 272 PS', '535d - 286 PS', '525i - 218 PS', '530i - 258 PS', '550i - 367 PS', 'M5 - 507 PS'],
      'F10 - 2010': ['518d - 143 PS', '520d - 184 PS', '520d - 190 PS', '525d - 218 PS', '530d - 258 PS', '535d - 313 PS', '520i - 184 PS', '528i - 245 PS', '535i - 306 PS', '550i - 449 PS', 'M5 - 560 PS'],
      'G30 - 2017': ['518d - 150 PS', '520d - 190 PS', '530d - 265 PS', '530d - 286 PS', '540d - 320 PS', '520i - 184 PS', '530i - 252 PS', '540i - 340 PS', 'M550i - 462 PS', 'M5 - 600 PS'],
    },
    'X3': {
      'F25 - 2010': ['sDrive18d - 143 PS', 'xDrive20d - 184 PS', 'xDrive20d - 190 PS', 'xDrive30d - 258 PS', 'xDrive35d - 313 PS', 'xDrive20i - 184 PS', 'xDrive28i - 245 PS', 'xDrive35i - 306 PS'],
      'G01 - 2017': ['xDrive20d - 190 PS', 'xDrive30d - 265 PS', 'xDrive20i - 184 PS', 'xDrive30i - 252 PS', 'M40i - 360 PS', 'M Competition - 510 PS'],
    },
    'X5': {
      'E70 - 2006': ['xDrive30d - 235 PS', 'xDrive30d - 245 PS', 'xDrive40d - 306 PS', 'xDrive50i - 407 PS', 'M50d - 381 PS', 'M - 555 PS'],
      'F15 - 2013': ['sDrive25d - 218 PS', 'xDrive25d - 218 PS', 'xDrive30d - 258 PS', 'xDrive40d - 313 PS', 'M50d - 381 PS', 'xDrive35i - 306 PS', 'xDrive50i - 449 PS', 'M - 575 PS'],
      'G05 - 2018': ['xDrive25d - 231 PS', 'xDrive30d - 265 PS', 'xDrive40d - 340 PS', 'M50d - 400 PS', 'xDrive40i - 340 PS', 'M50i - 530 PS', 'M Competition - 625 PS'],
    },
  },
  'VW': {
    'Golf': {
      'Golf 6 - 2008': ['1.2 TSI - 105 PS', '1.4 TSI - 122 PS', '1.4 TSI - 160 PS', '1.6 TDI - 105 PS', '2.0 TDI - 110 PS', '2.0 TDI - 140 PS', '2.0 TDI - 170 PS', '2.0 TSI GTI - 210 PS', '2.0 TSI R - 270 PS'],
      'Golf 7 - 2012': ['1.0 TSI - 110 PS', '1.2 TSI - 105 PS', '1.4 TSI - 125 PS', '1.4 TSI - 150 PS', '1.6 TDI - 110 PS', '2.0 TDI - 150 PS', '2.0 TDI - 184 PS', '2.0 TSI GTI - 230 PS', '2.0 TSI GTI TCR - 290 PS', '2.0 TSI R - 300 PS', '2.0 TSI R - 310 PS'],
      'Golf 8 - 2019': ['1.0 TSI - 110 PS', '1.5 TSI - 130 PS', '1.5 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS', '2.0 TSI GTI - 245 PS', '2.0 TSI GTI Clubsport - 300 PS', '2.0 TSI R - 320 PS'],
    },
    'Passat': {
      'B7 - 2010': ['1.4 TSI - 122 PS', '1.8 TSI - 160 PS', '2.0 TDI - 140 PS', '2.0 TDI - 170 PS', '2.0 TSI - 210 PS'],
      'B8 - 2014': ['1.4 TSI - 125 PS', '1.4 TSI - 150 PS', '1.6 TDI - 120 PS', '2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TDI - 240 PS (Bi-TDI)', '2.0 TSI - 190 PS', '2.0 TSI - 272 PS'],
    },
    'Tiguan': {
      'AD - 2016': ['1.4 TSI - 125 PS', '1.4 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TDI - 240 PS', '2.0 TSI - 180 PS', '2.0 TSI - 190 PS', '2.0 TSI - 230 PS', '2.0 TSI R - 320 PS'],
    },
    'T-Roc': {
      'A11 - 2017': ['1.0 TSI - 110 PS', '1.5 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS', '2.0 TSI - 190 PS', '2.0 TSI R - 300 PS'],
    },
    'Touareg': {
      'CR - 2018': ['3.0 TDI - 231 PS', '3.0 TDI - 286 PS', '3.0 TSI - 340 PS', '4.0 TDI - 422 PS'],
    },
  },
  'Mercedes': {
    'A-Klasse': {
      'W176 - 2012': ['A160 CDI - 90 PS', 'A180 CDI - 109 PS', 'A200 CDI - 136 PS', 'A220 CDI - 177 PS', 'A180 - 122 PS', 'A200 - 156 PS', 'A250 - 211 PS', 'A45 AMG - 360 PS', 'A45 AMG - 381 PS'],
      'W177 - 2018': ['A160d - 95 PS', 'A180d - 116 PS', 'A200d - 150 PS', 'A220d - 190 PS', 'A180 - 136 PS', 'A200 - 163 PS', 'A250 - 224 PS', 'A35 AMG - 306 PS', 'A45 AMG - 387 PS', 'A45 S AMG - 421 PS'],
    },
    'C-Klasse': {
      'W204 - 2007': ['C180 CDI - 120 PS', 'C200 CDI - 136 PS', 'C220 CDI - 170 PS', 'C250 CDI - 204 PS', 'C350 CDI - 265 PS', 'C180 - 156 PS', 'C200 - 184 PS', 'C250 - 204 PS', 'C350 - 306 PS', 'C63 AMG - 457 PS'],
      'W205 - 2014': ['C180d - 116 PS', 'C200d - 160 PS', 'C220d - 170 PS', 'C250d - 204 PS', 'C300d - 245 PS', 'C180 - 156 PS', 'C200 - 184 PS', 'C300 - 245 PS', 'C43 AMG - 367 PS', 'C43 AMG - 390 PS', 'C63 AMG - 476 PS', 'C63 S AMG - 510 PS'],
      'W206 - 2021': ['C200d - 163 PS', 'C220d - 200 PS', 'C300d - 265 PS', 'C200 - 204 PS', 'C300 - 258 PS', 'C43 AMG - 408 PS', 'C63 S AMG E - 680 PS'],
    },
    'E-Klasse': {
      'W212 - 2009': ['E200 CDI - 136 PS', 'E220 CDI - 170 PS', 'E250 CDI - 204 PS', 'E300 CDI - 231 PS', 'E350 CDI - 265 PS', 'E200 - 184 PS', 'E300 - 252 PS', 'E400 - 333 PS', 'E63 AMG - 557 PS', 'E63 S AMG - 585 PS'],
      'W213 - 2016': ['E200d - 150 PS', 'E220d - 194 PS', 'E300d - 245 PS', 'E350d - 286 PS', 'E400d - 340 PS', 'E200 - 184 PS', 'E300 - 245 PS', 'E450 - 367 PS', 'E53 AMG - 435 PS', 'E63 AMG - 571 PS', 'E63 S AMG - 612 PS'],
    },
    'GLC': {
      'X253 - 2015': ['GLC 200d - 163 PS', 'GLC 220d - 170 PS', 'GLC 250d - 204 PS', 'GLC 300d - 245 PS', 'GLC 200 - 184 PS', 'GLC 300 - 245 PS', 'GLC 43 AMG - 367 PS', 'GLC 63 AMG - 476 PS', 'GLC 63 S AMG - 510 PS'],
    },
  },
  'Seat': {
    'Leon': {
      '5F - 2012': ['1.0 TSI - 110 PS', '1.2 TSI - 110 PS', '1.4 TSI - 150 PS', '1.6 TDI - 110 PS', '2.0 TDI - 150 PS', '2.0 TDI - 184 PS', '2.0 TSI Cupra - 280 PS', '2.0 TSI Cupra - 290 PS', '2.0 TSI Cupra R - 310 PS'],
      'KL - 2020': ['1.0 TSI - 110 PS', '1.5 TSI - 130 PS', '1.5 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS'],
    },
    'Ateca': {
      'KH - 2016': ['1.0 TSI - 110 PS', '1.5 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TSI - 190 PS', '2.0 TSI Cupra - 300 PS'],
    },
  },
  'Skoda': {
    'Octavia': {
      '5E - 2012': ['1.0 TSI - 110 PS', '1.2 TSI - 110 PS', '1.4 TSI - 150 PS', '1.6 TDI - 110 PS', '2.0 TDI - 150 PS', '2.0 TDI - 184 PS', '2.0 TSI RS - 230 PS', '2.0 TSI RS - 245 PS'],
      'NX - 2020': ['1.0 TSI - 110 PS', '1.5 TSI - 150 PS', '2.0 TDI - 115 PS', '2.0 TDI - 150 PS', '2.0 TDI - 200 PS', '2.0 TSI RS - 245 PS'],
    },
    'Superb': {
      '3V - 2015': ['1.4 TSI - 150 PS', '1.5 TSI - 150 PS', '2.0 TDI - 150 PS', '2.0 TDI - 190 PS', '2.0 TSI - 272 PS', '2.0 TSI - 280 PS'],
    },
  },
  'Opel': {
    'Astra': {
      'J - 2009': ['1.4 Turbo - 120 PS', '1.4 Turbo - 140 PS', '1.6 CDTI - 110 PS', '1.6 CDTI - 136 PS', '2.0 CDTI - 165 PS', '2.0 Turbo OPC - 280 PS'],
      'K - 2015': ['1.0 Turbo - 105 PS', '1.2 Turbo - 130 PS', '1.4 Turbo - 150 PS', '1.6 CDTI - 110 PS', '1.6 CDTI - 136 PS', '1.6 CDTI - 160 PS', '1.6 Turbo - 200 PS'],
    },
    'Insignia': {
      'B - 2017': ['1.5 Turbo - 140 PS', '1.5 Turbo - 165 PS', '2.0 CDTI - 170 PS', '2.0 CDTI - 210 PS', '2.0 Turbo - 260 PS', '2.0 Turbo GSi - 230 PS'],
    },
  },
  'Ford': {
    'Focus': {
      'MK3 - 2011': ['1.0 EcoBoost - 100 PS', '1.0 EcoBoost - 125 PS', '1.5 EcoBoost - 150 PS', '1.5 TDCi - 95 PS', '1.5 TDCi - 120 PS', '2.0 TDCi - 150 PS', '2.3 EcoBoost RS - 350 PS', '2.3 EcoBoost ST - 280 PS'],
      'MK4 - 2018': ['1.0 EcoBoost - 100 PS', '1.0 EcoBoost - 125 PS', '1.0 EcoBoost - 155 PS', '1.5 EcoBlue - 120 PS', '1.5 EcoBoost - 150 PS', '1.5 EcoBoost - 182 PS', '2.0 EcoBlue - 150 PS', '2.3 EcoBoost ST - 280 PS'],
    },
    'Kuga': {
      'MK3 - 2019': ['1.5 EcoBlue - 120 PS', '1.5 EcoBoost - 150 PS', '2.0 EcoBlue - 150 PS', '2.0 EcoBlue - 190 PS', '2.5 PHEV - 225 PS'],
    },
  },
  'Porsche': {
    'Cayenne': {
      '92A - 2010': ['Diesel - 245 PS', 'S Diesel - 382 PS', 'S - 400 PS', 'S - 420 PS', 'GTS - 440 PS', 'Turbo - 500 PS', 'Turbo S - 570 PS'],
      'E3 - 2017': ['3.0 V6 - 340 PS', 'S 2.9 V6 - 440 PS', 'GTS 4.0 V8 - 460 PS', 'Turbo 4.0 V8 - 550 PS', 'Turbo S E-Hybrid - 680 PS'],
    },
    'Macan': {
      '95B - 2014': ['2.0 TFSI - 237 PS', '2.0 TFSI - 252 PS', 'S 3.0 V6 - 340 PS', 'S 3.0 V6 - 354 PS', 'GTS 2.9 V6 - 380 PS', 'Turbo 3.6 V6 - 400 PS'],
    },
    '911': {
      '991 - 2011': ['Carrera 3.4 - 350 PS', 'Carrera S 3.8 - 400 PS', 'Carrera 3.0T - 370 PS', 'Carrera S 3.0T - 420 PS', 'GTS 3.0T - 450 PS', 'Turbo 3.8T - 540 PS', 'Turbo S 3.8T - 580 PS', 'GT3 4.0 - 500 PS'],
      '992 - 2019': ['Carrera 3.0T - 385 PS', 'Carrera S 3.0T - 450 PS', 'GTS 3.0T - 480 PS', 'Turbo 3.7T - 580 PS', 'Turbo S 3.7T - 650 PS', 'GT3 4.0 - 510 PS'],
    },
  },
};

const allManufacturers = Object.keys(vehicleData).sort();

// ECU database: mapped by manufacturer + fuel type
const ecuData = {
  'Audi': {
    'TDI': ['Bosch EDC17CP14', 'Bosch EDC17CP20', 'Bosch EDC17CP44', 'Bosch EDC17CP46', 'Bosch EDC17CP54', 'Bosch EDC17C46', 'Bosch EDC17C54', 'Bosch EDC17C74', 'Bosch MD1CP004', 'Bosch MD1CS004', 'Continental PCR2.1', 'Continental Simos 7.1'],
    'TSI': ['Bosch MED17.5', 'Bosch MED17.5.2', 'Bosch MED17.1', 'Bosch MED17.1.1', 'Bosch MG1CS011', 'Bosch MG1CS002', 'Continental Simos 12.1', 'Continental Simos 18.1', 'Continental Simos 18.10', 'Continental Simos 19.6'],
    'TFSI': ['Bosch MED17.1', 'Bosch MED17.1.1', 'Bosch MED17.5', 'Bosch MG1CS002', 'Bosch MG1CS011', 'Continental Simos 12.1', 'Continental Simos 18.1', 'Continental Simos 18.10'],
    'FSI': ['Bosch MED9.1', 'Bosch MED9.5', 'Bosch MED17.1'],
    'default': ['Bosch EDC17CP14', 'Bosch EDC17CP54', 'Bosch MED17.1', 'Bosch MG1CS002', 'Continental Simos 18.1'],
  },
  'BMW': {
    'd': ['Bosch EDC17C06', 'Bosch EDC17C41', 'Bosch EDC17C50', 'Bosch EDC17CP02', 'Bosch EDC17CP09', 'Bosch EDC17CP45', 'Bosch EDC17C76', 'Bosch MD1CP002', 'Bosch MD1CS001'],
    'i': ['Bosch MEVD17.2', 'Bosch MEVD17.2.4', 'Bosch MEVD17.2.9', 'Bosch MG1CS003', 'Bosch MG1CS201', 'Continental MSD80', 'Continental MSD81', 'Continental MSD85', 'Continental MSD87'],
    'M': ['Bosch MEVD17.2', 'Bosch MG1CS201', 'Continental MSD85', 'Continental MSD87'],
    'default': ['Bosch EDC17C50', 'Bosch EDC17CP45', 'Bosch MEVD17.2', 'Bosch MG1CS003', 'Continental MSD80'],
  },
  'VW': {
    'TDI': ['Bosch EDC17CP14', 'Bosch EDC17CP20', 'Bosch EDC17CP44', 'Bosch EDC17CP46', 'Bosch EDC17C46', 'Bosch EDC17C54', 'Bosch EDC17C74', 'Bosch MD1CP004', 'Continental PCR2.1', 'Delphi DCM6.2'],
    'TSI': ['Bosch MED17.5', 'Bosch MED17.5.2', 'Bosch MED17.1', 'Bosch MG1CS011', 'Continental Simos 12.1', 'Continental Simos 18.1', 'Continental Simos 18.10', 'Continental Simos 19.6'],
    'default': ['Bosch EDC17CP14', 'Bosch MED17.5', 'Continental Simos 18.1', 'Continental PCR2.1'],
  },
  'Mercedes': {
    'CDI': ['Bosch EDC17CP01', 'Bosch EDC17CP10', 'Bosch EDC17CP46', 'Bosch EDC17CP57', 'Bosch EDC17C43', 'Bosch EDC17C66', 'Bosch MD1CP001', 'Delphi CRD2', 'Delphi CRD3'],
    'd': ['Bosch EDC17CP46', 'Bosch EDC17CP57', 'Bosch EDC17C66', 'Bosch MD1CP001'],
    'AMG': ['Bosch MED17.7.2', 'Bosch MED17.7.3', 'Bosch MG1CP002', 'Continental SIM271DE'],
    'default': ['Bosch EDC17CP46', 'Bosch EDC17CP57', 'Bosch MED17.7.2', 'Bosch MD1CP001', 'Delphi CRD3'],
  },
  'Seat': {
    'TDI': ['Bosch EDC17CP14', 'Bosch EDC17CP20', 'Bosch EDC17C46', 'Bosch EDC17C54', 'Bosch EDC17C74', 'Continental PCR2.1'],
    'TSI': ['Bosch MED17.5', 'Bosch MED17.5.2', 'Continental Simos 12.1', 'Continental Simos 18.1', 'Continental Simos 18.10'],
    'default': ['Bosch EDC17CP14', 'Bosch MED17.5', 'Continental Simos 18.1'],
  },
  'Skoda': {
    'TDI': ['Bosch EDC17CP14', 'Bosch EDC17CP20', 'Bosch EDC17C46', 'Bosch EDC17C54', 'Bosch EDC17C74', 'Continental PCR2.1'],
    'TSI': ['Bosch MED17.5', 'Bosch MED17.5.2', 'Continental Simos 12.1', 'Continental Simos 18.1', 'Continental Simos 18.10'],
    'default': ['Bosch EDC17CP14', 'Bosch MED17.5', 'Continental Simos 18.1'],
  },
  'Opel': {
    'CDTI': ['Bosch EDC17C18', 'Bosch EDC17C19', 'Bosch EDC17C59', 'Delphi DCM3.7', 'Delphi DCM6.2'],
    'Turbo': ['Bosch ME17.9', 'Bosch ME17.9.6', 'Delco E80', 'Delco E87'],
    'default': ['Bosch EDC17C18', 'Bosch EDC17C59', 'Delphi DCM3.7', 'Delco E80'],
  },
  'Ford': {
    'TDCi': ['Bosch EDC17C10', 'Bosch EDC17C70', 'Continental SID807', 'Continental SID211', 'Delphi DCM3.5', 'Delphi DCM6.1'],
    'EcoBoost': ['Bosch MED17.0', 'Bosch MED17.2', 'Continental EMS2510', 'Continental EMS2511'],
    'EcoBlue': ['Bosch EDC17C70', 'Bosch MD1CS005', 'Continental SID211'],
    'default': ['Bosch EDC17C10', 'Bosch MED17.0', 'Continental SID807', 'Delphi DCM3.5'],
  },
  'Porsche': {
    'default': ['Bosch MED17.1.1', 'Bosch MG1CP007', 'Bosch MG1CS111', 'Continental SDI10', 'Continental SDI21', 'Bosch EDC17CP44', 'Bosch MD1CP014'],
  },
};

// Get ECU options based on manufacturer and engine
function getEcuOptions(manufacturer, engine) {
  if (!manufacturer || !ecuData[manufacturer]) return [];
  const mfrEcus = ecuData[manufacturer];
  
  // Try to find fuel type from engine string
  const fuelTypes = Object.keys(mfrEcus).filter(k => k !== 'default');
  const engineLower = (engine || '').toLowerCase();
  
  for (const fuelType of fuelTypes) {
    if (engineLower.includes(fuelType.toLowerCase())) {
      return mfrEcus[fuelType];
    }
  }
  
  return mfrEcus['default'] || [];
}

const readingDevices = [
  { group: 'Autotuner', logo: `${process.env.PUBLIC_URL}/logos/autotuner.png`, options: ['Tool'] },
  { group: 'Alientech', logo: `${process.env.PUBLIC_URL}/logos/alientech.png`, options: ['Kess3'] },
  { group: 'Magic Motorsport', logo: `${process.env.PUBLIC_URL}/logos/magicmotorsport.png`, options: ['Flex'] },
  { group: null, logo: `${process.env.PUBLIC_URL}/logos/autoflasher.png`, options: ['Autoflasher'] },
  { group: null, logo: null, options: ['CMD Flash'] },
  { group: 'Dimsport', logo: `${process.env.PUBLIC_URL}/logos/dimsport.png`, options: ['NewGenius'] },
];

const PROFILE_TUNING_TOOLS_STORAGE_KEY = 'portal.profile.tuningTools';
const PROFILE_SIMULATE_ZERO_CREDITS_STORAGE_KEY = 'portal.profile.simulateZeroCredits';

const normalizeToolValue = (value) => (value || '').toLowerCase().replace(/[^a-z0-9]/g, '');

const getReadingDeviceValues = () => readingDevices.flatMap((entry) =>
  entry.group
    ? entry.options.map((option) => `${entry.group} - ${option}`)
    : entry.options
);

const readStoredTuningTools = () => {
  if (typeof window === 'undefined') {
    return [];
  }

  try {
    const raw = window.localStorage.getItem(PROFILE_TUNING_TOOLS_STORAGE_KEY);
    if (!raw) {
      return [];
    }

    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) {
      return [];
    }

    return parsed.filter((item) => typeof item === 'string' && item.trim().length > 0);
  } catch (_error) {
    return [];
  }
};

const readBootstrapTuningTools = () => {
  if (typeof window === 'undefined') {
    return [];
  }

  const tools = window?.portalReactBootstrap?.profile?.tuningTools;
  if (!Array.isArray(tools)) {
    return [];
  }

  return tools.filter((item) => typeof item === 'string' && item.trim().length > 0);
};

const readProfileTuningTools = () => {
  const bootstrapTools = readBootstrapTuningTools();
  if (bootstrapTools.length > 0) {
    return bootstrapTools;
  }

  return readStoredTuningTools();
};

const mapProfileToolToReadingDevice = (toolName) => {
  const normalizedTool = normalizeToolValue(toolName);
  if (!normalizedTool) {
    return '';
  }

  const explicitMap = {
    autotuner: 'Autotuner - Tool',
    alientechkess3: 'Alientech - Kess3',
    magicmotorsportflex: 'Magic Motorsport - Flex',
    cmdflash: 'CMD Flash',
    dimsportnewgenius: 'Dimsport - NewGenius',
  };

  const mappedFromExplicit = explicitMap[normalizedTool];
  if (mappedFromExplicit) {
    return mappedFromExplicit;
  }

  const readingValues = getReadingDeviceValues();
  const directMatch = readingValues.find((value) => normalizeToolValue(value) === normalizedTool);
  if (directMatch) {
    return directMatch;
  }

  for (const entry of readingDevices) {
    if (entry.group) {
      const normalizedGroup = normalizeToolValue(entry.group);
      for (const option of entry.options) {
        const normalizedOption = normalizeToolValue(option);
        if (normalizedTool.includes(normalizedGroup) && normalizedTool.includes(normalizedOption)) {
          return `${entry.group} - ${option}`;
        }
      }
    } else {
      const option = entry.options.find((value) => normalizeToolValue(value) === normalizedTool);
      if (option) {
        return option;
      }
    }
  }

  return '';
};

const ALLOWED_EXTENSIONS = ['.bin', '.ori', '.mod', '.ecu', '.hex', '.s19', '.bkp', '.slave'];

const readPriceListBootstrap = () => {
  if (typeof window === 'undefined') {
    return null;
  }

  const value = window?.portalReactBootstrap?.priceList;
  return value && typeof value === 'object' ? value : null;
};

const readOrderFlowCreditTopupBootstrap = () => {
  if (typeof window === 'undefined') {
    return { configured: false, clientId: '', csrfToken: '' };
  }

  const value = window?.portalReactBootstrap?.orderFlowCreditTopup;
  return value && typeof value === 'object'
    ? {
        configured: Boolean(value.configured),
        clientId: String(value.clientId || ''),
        csrfToken: String(value.csrfToken || ''),
      }
    : { configured: false, clientId: '', csrfToken: '' };
};

const readSimulateZeroCredits = () => {
  if (typeof window === 'undefined') {
    return false;
  }

  try {
    return window.localStorage.getItem(PROFILE_SIMULATE_ZERO_CREDITS_STORAGE_KEY) === '1';
  } catch (_error) {
    return false;
  }
};

function ToggleOption({ label, selected, onClick, extra, testId, icon, IconComponent }) {
  return (
    <button
      type="button"
      onClick={onClick}
      data-testid={testId}
      className={cn(
        "flex-1 py-1.5 px-4 text-sm font-semibold rounded-sm border transition-all duration-200 cursor-pointer flex items-center justify-center gap-2",
        selected
          ? "bg-primary/10 border-primary text-foreground"
          : "bg-secondary/50 border-border text-muted-foreground hover:bg-secondary hover:text-foreground"
      )}
    >
      {icon && <img src={icon} alt="" className="h-9 w-9 object-contain" />}
      {IconComponent && <IconComponent weight="bold" className="w-5 h-5" />}
      <span>{label}</span>
      {extra && (
        <span className="ml-1 text-primary font-bold">{extra}</span>
      )}
    </button>
  );
}

// Tuning stages
const defaultTuningStages = [
  { id: 'stage1', name: { de: 'Stage 1', en: 'Stage 1' }, credits: 100, Icon: Lightning, color: 'primary', desc: { de: 'Optimierte Kennfelder', en: 'Optimized maps' } },
  { id: 'stage2', name: { de: 'Stage 2', en: 'Stage 2' }, credits: 150, Icon: Fire, color: 'orange', desc: { de: 'Maximale Performance', en: 'Maximum performance' } },
  { id: 'eco', name: { de: 'Eco', en: 'Eco' }, credits: 100, Icon: Leaf, color: 'green', desc: { de: 'Verbrauch optimiert', en: 'Fuel optimized' } },
  { id: 'gearbox', name: { de: 'Getriebe', en: 'Gearbox' }, credits: 0, Icon: Gear, color: 'blue', desc: { de: 'Schaltoptimierung', en: 'Shift optimization' } },
  { id: 'optionsOnly', name: { de: 'Nur Optionen', en: 'Options Only' }, credits: 0, Icon: Sliders, color: 'muted', desc: { de: 'Einzelne Optionen wählen', en: 'Select individual options' } },
];

// Gearbox sub-stages (from PriceList)
const defaultGearboxStages = [
  { id: 'gearbox_stage1', name: { de: 'Stage 1', en: 'Stage 1' }, credits: 120 },
  { id: 'gearbox_stage2', name: { de: 'Stage 2', en: 'Stage 2' }, credits: 160 },
  { id: 'gearbox_stage3', name: { de: 'Stage 3', en: 'Stage 3' }, credits: 200 },
];

// Manufacturers that support gearbox tuning
const gearboxManufacturers = ['VW', 'Audi', 'Seat', 'Cupra', 'Skoda', 'BMW'];

// Generate mock dyno curve data from engine string
function getPerformanceData(engine) {
  if (!engine) return null;
  const psMatch = engine.match(/(\d{2,4})\s*PS/);
  if (!psMatch) return null;
  const originalPs = parseInt(psMatch[1]);
  
  const isDiesel = /TDI|CDI|CDTI|HDI|CRDi|EcoBlue|TDCi|d\b/i.test(engine);
  const torqueMultiplier = isDiesel ? 2.0 : 1.35;
  const originalNm = Math.round(originalPs * torqueMultiplier);
  
  const s1PsGain = isDiesel ? 0.25 : 0.22;
  const s1NmGain = isDiesel ? 0.30 : 0.25;
  const s2PsGain = isDiesel ? 0.40 : 0.35;
  const s2NmGain = isDiesel ? 0.45 : 0.38;
  const stage1Ps = Math.round(originalPs * (1 + s1PsGain));
  const stage1Nm = Math.round(originalNm * (1 + s1NmGain));
  const stage2Ps = Math.round(originalPs * (1 + s2PsGain));
  const stage2Nm = Math.round(originalNm * (1 + s2NmGain));
  
  // Generate RPM-based dyno curve
  const maxRpm = isDiesel ? 5200 : 7200;
  const peakPsRpm = isDiesel ? 3800 : 5800;
  const peakNmRpm = isDiesel ? 2000 : 3500;
  const steps = 20;
  
  const curve = (rpm, peak, peakRpm, riseSharp) => {
    const x = rpm / peakRpm;
    if (x <= 1) {
      return peak * (1 - Math.pow(1 - x, riseSharp));
    }
    const falloff = (rpm - peakRpm) / (maxRpm - peakRpm);
    return peak * (1 - 0.25 * Math.pow(falloff, 1.5));
  };
  
  const curveData = [];
  for (let i = 0; i <= steps; i++) {
    const rpm = Math.round(800 + (maxRpm - 800) * (i / steps));
    const rpmLabel = rpm >= 1000 ? `${(rpm/1000).toFixed(1)}k` : String(rpm);
    curveData.push({
      rpm,
      rpmLabel,
      seriePs: Math.round(curve(rpm, originalPs, peakPsRpm, 1.8)),
      stage1Ps: Math.round(curve(rpm, stage1Ps, peakPsRpm * 0.97, 1.8)),
      stage2Ps: Math.round(curve(rpm, stage2Ps, peakPsRpm * 0.95, 1.8)),
      serieNm: Math.round(curve(rpm, originalNm, peakNmRpm, 2.2)),
      stage1Nm: Math.round(curve(rpm, stage1Nm, peakNmRpm * 0.95, 2.2)),
      stage2Nm: Math.round(curve(rpm, stage2Nm, peakNmRpm * 0.92, 2.2)),
    });
  }
  
  return {
    originalPs, originalNm, stage1Ps, stage1Nm, stage2Ps, stage2Nm, isDiesel, curveData,
    psGainS1: stage1Ps - originalPs, psGainS2: stage2Ps - originalPs,
    nmGainS1: stage1Nm - originalNm, nmGainS2: stage2Nm - originalNm,
  };
}

function getPerformanceDataFromApi(engineLabel, engineInfo, stageItems) {
  const isDiesel = /TDI|CDI|CDTI|HDI|CRDi|EcoBlue|TDCi|d\b/i.test(String(engineLabel || ''));
  const originalPs = Number.isFinite(engineInfo?.horsepower) ? Number(engineInfo.horsepower) : null;
  const originalNm = Number.isFinite(engineInfo?.torque) ? Number(engineInfo.torque) : null;

  if (!originalPs && !originalNm) {
    return null;
  }

  const safeOriginalPs = originalPs || Math.max(40, Math.round((originalNm || 200) / (isDiesel ? 2.0 : 1.35)));
  const safeOriginalNm = originalNm || Math.max(80, Math.round(safeOriginalPs * (isDiesel ? 2.0 : 1.35)));
  const stages = Array.isArray(stageItems) ? stageItems : [];
  const stage1 = stages.find((stage) => /stage\s*1/i.test(String(stage?.name || ''))) || null;
  const stage2 = stages.find((stage) => /stage\s*2/i.test(String(stage?.name || ''))) || null;
  const hasStage2 = Boolean(stage2);

  const stage1Ps = Number.isFinite(stage1?.hp) ? Number(stage1.hp) : Math.round(safeOriginalPs * (1 + (isDiesel ? 0.25 : 0.22)));
  const stage1Nm = Number.isFinite(stage1?.nm) ? Number(stage1.nm) : Math.round(safeOriginalNm * (1 + (isDiesel ? 0.30 : 0.25)));
  const stage2Ps = hasStage2
    ? (Number.isFinite(stage2?.hp) ? Number(stage2.hp) : Math.round(safeOriginalPs * (1 + (isDiesel ? 0.40 : 0.35))))
    : null;
  const stage2Nm = hasStage2
    ? (Number.isFinite(stage2?.nm) ? Number(stage2.nm) : Math.round(safeOriginalNm * (1 + (isDiesel ? 0.45 : 0.38))))
    : null;

  const maxRpm = isDiesel ? 5200 : 7200;
  const peakPsRpm = isDiesel ? 3800 : 5800;
  const peakNmRpm = isDiesel ? 2000 : 3500;
  const curve = (rpm, peak, peakRpm, riseSharp) => {
    const x = rpm / peakRpm;
    if (x <= 1) {
      return peak * (1 - Math.pow(1 - x, riseSharp));
    }

    const falloff = (rpm - peakRpm) / (maxRpm - peakRpm);
    return peak * (1 - 0.25 * Math.pow(falloff, 1.5));
  };

  const curveData = [];
  for (let i = 0; i <= 20; i += 1) {
    const rpm = Math.round(800 + (maxRpm - 800) * (i / 20));
    curveData.push({
      rpm,
      rpmLabel: rpm >= 1000 ? `${(rpm / 1000).toFixed(1)}k` : String(rpm),
      seriePs: Math.round(curve(rpm, safeOriginalPs, peakPsRpm, 1.8)),
      stage1Ps: Math.round(curve(rpm, stage1Ps, peakPsRpm * 0.97, 1.8)),
      stage2Ps: hasStage2 && Number.isFinite(stage2Ps) ? Math.round(curve(rpm, stage2Ps, peakPsRpm * 0.95, 1.8)) : null,
      serieNm: Math.round(curve(rpm, safeOriginalNm, peakNmRpm, 2.2)),
      stage1Nm: Math.round(curve(rpm, stage1Nm, peakNmRpm * 0.95, 2.2)),
      stage2Nm: hasStage2 && Number.isFinite(stage2Nm) ? Math.round(curve(rpm, stage2Nm, peakNmRpm * 0.92, 2.2)) : null,
    });
  }

  return {
    originalPs: safeOriginalPs,
    originalNm: safeOriginalNm,
    stage1Ps,
    stage1Nm,
    stage2Ps,
    stage2Nm,
    isDiesel,
    curveData,
    psGainS1: stage1Ps - safeOriginalPs,
    psGainS2: Number.isFinite(stage2Ps) ? stage2Ps - safeOriginalPs : null,
    nmGainS1: stage1Nm - safeOriginalNm,
    nmGainS2: Number.isFinite(stage2Nm) ? stage2Nm - safeOriginalNm : null,
  };
}

// Custom tooltip for dyno chart
const DynoTooltip = ({ active, payload, label }) => {
  if (!active || !payload?.length) return null;
  const rpm = payload[0]?.payload?.rpm;
  return (
    <div className="bg-[#1a1a1a] border border-border rounded-sm px-3 py-2.5 shadow-xl min-w-[140px]">
      <p className="text-[11px] font-bold text-muted-foreground mb-1.5">{rpm?.toLocaleString()} RPM</p>
      {payload.map((entry, i) => (
        <div key={i} className="flex items-center gap-2 text-[11px] leading-relaxed">
          <span className="w-2.5 h-2.5 rounded-full flex-shrink-0" style={{ background: entry.stroke || entry.color }} />
          <span className="text-muted-foreground">{entry.name}:</span>
          <span className="font-bold text-foreground ml-auto">{entry.value}</span>
        </div>
      ))}
    </div>
  );
};

// Tuning options
const defaultTuningOptions = [
  { id: 'dpf', name: { de: 'DPF-Off', en: 'DPF-Off' }, credits: 0, included: true, Icon: Drop, desc: { de: 'Partikelfilter', en: 'Particle Filter' } },
  { id: 'egr', name: { de: 'EGR-Off', en: 'EGR-Off' }, credits: 0, included: true, Icon: Fan, desc: { de: 'AGR Ventil', en: 'EGR Valve' } },
  { id: 'adblue', name: { de: 'AdBlue-Off', en: 'AdBlue-Off' }, credits: 0, included: true, Icon: Drop, desc: { de: 'SCR System', en: 'SCR System' } },
  { id: 'vmax', name: { de: 'Vmax-Off', en: 'Vmax-Off' }, credits: 0, included: true, Icon: Gauge, desc: { de: 'Geschwindigkeit', en: 'Speed Limiter' } },
  { id: 'dtc', name: { de: 'DTC-Off', en: 'DTC-Off' }, credits: 25, included: false, Icon: Warning, desc: { de: 'Fehlercode', en: 'Error Code' } },
  { id: 'startstop', name: { de: 'Start-Stop-Off', en: 'Start-Stop-Off' }, credits: 20, included: false, Icon: Power, desc: { de: 'Start-Stop', en: 'Start-Stop' } },
  { id: 'pops', name: { de: 'Pops & Bangs', en: 'Pops & Bangs' }, credits: 45, included: false, Icon: Fire, desc: { de: 'Auspuff Knallen', en: 'Exhaust Crackle' } },
  { id: 'launch', name: { de: 'Launch Control', en: 'Launch Control' }, credits: 55, included: false, Icon: RocketLaunch, desc: { de: 'Rennstart', en: 'Race Start' } },
  { id: 'swirl', name: { de: 'Swirl Flaps-Off', en: 'Swirl Flaps-Off' }, credits: 30, included: false, Icon: Fan, desc: { de: 'Drallklappen', en: 'Swirl Flaps' } },
  { id: 'cat', name: { de: 'Kat-Off', en: 'Cat-Off' }, credits: 35, included: false, Icon: Prohibit, desc: { de: 'Katalysator', en: 'Catalyst' } },
  { id: 'o2', name: { de: 'O2-Off', en: 'O2-Off' }, credits: 25, included: false, Icon: Thermometer, desc: { de: 'Lambdasonde', en: 'O2 Sensor' } },
  { id: 'hotstart', name: { de: 'Hot Start Fix', en: 'Hot Start Fix' }, credits: 30, included: false, Icon: Thermometer, desc: { de: 'Heißstart', en: 'Hot Start' } },
];

const optionIconMap = {
  drop: Drop,
  fan: Fan,
  gauge: Gauge,
  warning: Warning,
  power: Power,
  fire: Fire,
  rocket: RocketLaunch,
  prohibit: Prohibit,
  thermometer: Thermometer,
  gear: Gear,
};

const stagePresentationMap = {
  stage1: { Icon: Lightning, color: 'primary', desc: { de: 'Optimierte Kennfelder', en: 'Optimized maps' } },
  stage2: { Icon: Fire, color: 'orange', desc: { de: 'Maximale Performance', en: 'Maximum performance' } },
  eco: { Icon: Leaf, color: 'green', desc: { de: 'Verbrauch optimiert', en: 'Fuel optimized' } },
  tuning: { Icon: Lightning, color: 'primary', desc: { de: 'Leistungsoptimierung', en: 'Performance tuning' } },
  gearbox: { Icon: Gear, color: 'blue', desc: { de: 'Schaltoptimierung', en: 'Shift optimization' } },
  optionsOnly: { Icon: Sliders, color: 'muted', desc: { de: 'Einzelne Optionen wählen', en: 'Select individual options' } },
};

const defaultLkwStages = [
  { id: 'eco', name: { de: 'Eco', en: 'Eco' }, credits: 200, Icon: Leaf, color: 'green', desc: { de: 'Verbrauch optimiert', en: 'Fuel optimized' } },
  { id: 'tuning', name: { de: 'Tuning', en: 'Tuning' }, credits: 200, Icon: Lightning, color: 'primary', desc: { de: 'Leistungsoptimierung', en: 'Performance tuning' } },
];

const defaultLkwOptions = [
  { id: 'adblue', stageCode: 'tuning', name: { de: 'ADBLUE (SCR) OFF', en: 'ADBLUE (SCR) OFF' }, credits: 100, included: false, Icon: Drop, desc: { de: 'AdBlue', en: 'AdBlue' } },
  { id: 'egr', stageCode: 'tuning', name: { de: 'AGR OFF', en: 'AGR OFF' }, credits: 60, included: false, Icon: Fan, desc: { de: 'AGR', en: 'EGR' } },
  { id: 'dpf', stageCode: 'tuning', name: { de: 'DPF OFF', en: 'DPF OFF' }, credits: 60, included: false, Icon: Drop, desc: { de: 'Partikelfilter', en: 'DPF' } },
];

const normalizeVehicleTypeKey = (value) => {
  const normalized = String(value || '').trim().toLowerCase();
  if (normalized === '') {
    return 'pkw';
  }

  if (/(lkw|truck|agrar|tractor|bus|nfz)/i.test(normalized)) {
    return 'lkw';
  }

  return 'pkw';
};

const defaultOptionDescriptionMap = Object.fromEntries(
  defaultTuningOptions.map((option) => [option.id, option.desc])
);

const normalizeStageKey = (value) => {
  const normalized = String(value || '').trim().toLowerCase();

  if (normalized.includes('stage 1') || normalized.includes('stage1')) {
    return 'stage1';
  }

  if (normalized.includes('stage 2') || normalized.includes('stage2')) {
    return 'stage2';
  }

  if (normalized.includes('eco')) {
    return 'eco';
  }

  if (normalized.includes('gearbox') || normalized.includes('getriebe')) {
    return 'gearbox';
  }

  return normalized.replace(/[^a-z0-9]+/g, '') || 'stage';
};

const createLocalizedText = (value, fallback = '') => {
  if (value && typeof value === 'object') {
    return {
      de: String(value.de || fallback || ''),
      en: String(value.en || value.de || fallback || ''),
    };
  }

  const text = String(value || fallback || '').trim();
  return { de: text, en: text };
};

const normalizeOptionMatchKey = (value) => (
  String(value || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '')
);

const optionCodeAliases = {
  adblue: ['adblue', 'adblueoff', 'adbluescroff'],
  auspuffklappen: ['auspuffklappen', 'exhaustflaps'],
  bmw_mini_garantie_patch: ['bmwminigarantiepatch', 'garantiepatch'],
  cat: ['cat', 'katoff', 'catoff', 'catalystoff'],
  cvn_patch: ['cvnpatch'],
  dpf: ['dpf', 'dpfoff', 'partikelfilter', 'partikelfilteroff'],
  drehmomentueberwachung_off: ['drehmomentueberwachungoff', 'torquemonitoringoff'],
  dtc: ['dtc', 'fehlercodesloeschen', 'fehlercodeslosen', 'errorcodes', 'dtcoff'],
  egr: ['egr', 'agroff', 'egroff', 'agr'],
  hardcut_popcorn_limiter: ['hardcutpopcornlimiter', 'hardcut', 'popcornlimiter'],
  harter_begrenzer: ['harterbegrenzer', 'hardrevlimit', 'hardlimiter'],
  hotstart: ['hotstart', 'warmstartproblembeheben', 'warmstart'],
  kaltstart_off: ['kaltstartoff', 'coldstartoff'],
  launch: ['launch', 'launchcontrol'],
  launch_control_anti_lag_me7: ['launchcontrolantilagme7', 'antilagme7'],
  launch_control_anti_lag_me9: ['launchcontrolantilagme9', 'antilagme9'],
  launch_control_anti_lag_med17: ['launchcontrolantilagmed17', 'antilagmed17'],
  luftmengenmesser_off: ['luftmengenmesseroff', 'maf', 'mafoff'],
  nox_off: ['noxoff'],
  oeldruck_fix: ['oeldruckfix', 'oldruckfix', 'oilpressurefix'],
  opf_gpf_off: ['opfgpfoff', 'gpfoff'],
  opf_off: ['opfoff'],
  pop_bang_sport: ['popbangaufsport', 'popbangsport'],
  pop_bang_tempomat_me9: ['popbangauftempomatme9', 'tempomatme9'],
  pops: ['pops', 'popbang', 'popsbangs', 'popsandbangs'],
  sekundaerluftpumpe_off: ['sekundaerluftpumpeoff', 'secondaryairpumpoff'],
  sport_display: ['sportdisplay'],
  startstop: ['startstop', 'startstopoff'],
  swirl: ['swirl', 'drallklappenoff', 'swirlflapsoff'],
  tuning_auf_sport_button: ['tuningaufsportbutton', 'sportbutton'],
  vmax: ['vmax', 'vmaxoff', 'speedlimitoff'],
  vmax_limit_setzen: ['vmaxlimitsetzen', 'speedlimitset'],
  wegfahrsperre_immo_off: ['wegfahrsperreimmooff', 'immooff', 'immobilizeroff'],
  zylinderabschaltung_off: ['zylinderabschaltungoff', 'cylinderdeactivationoff'],
};

const buildOptionLookupKeys = (item) => {
  const keys = new Set();
  const push = (value) => {
    const normalized = normalizeOptionMatchKey(value);
    if (normalized !== '') {
      keys.add(normalized);
    }
  };

  push(item?.id);
  push(item?.code);
  push(item?.name?.de);
  push(item?.name?.en);

  const aliases = optionCodeAliases[String(item?.id || '').trim()] || [];
  aliases.forEach(push);

  return Array.from(keys);
};

const findPriceListOptionEntry = (items, optionName, stageKey = '') => {
  if (!Array.isArray(items) || items.length === 0) {
    return null;
  }

  const normalizedName = normalizeOptionMatchKey(optionName);
  const normalizedStageKey = String(stageKey || '').trim().toLowerCase();

  const matchingItems = items.filter((item) => {
    const itemStageCode = String(item?.stageCode || '').trim().toLowerCase();
    return itemStageCode === '' || itemStageCode === normalizedStageKey;
  });

  return matchingItems.find((item) => {
    const itemKeys = buildOptionLookupKeys(item);
    return normalizedName !== '' && itemKeys.includes(normalizedName);
  }) || null;
};

const resolvePortalAssetPath = (value) => {
  const normalized = String(value || '').trim();
  if (!normalized) {
    return '';
  }

  if (normalized.startsWith('/') || /^https?:\/\//i.test(normalized)) {
    return normalized;
  }

  return `/${normalized}`;
};

const resolvePriceListIconPath = (value) => {
  const raw = String(value ?? '').trim();
  if (!raw) {
    return '';
  }

  if (optionIconMap[raw]) {
    return '';
  }

  if (/^https?:\/\//i.test(raw) || raw.startsWith('/')) {
    return resolvePortalAssetPath(raw);
  }

  if (/\.(svg|png|jpe?g|gif|webp)$/i.test(raw)) {
    return resolvePortalAssetPath(raw);
  }

  return '';
};

const OptionVisual = ({ option, selected = false, className = 'w-5 h-5' }) => {
  const resolvedIconPath = resolvePortalAssetPath(option.iconPath || '');
  if (resolvedIconPath) {
    return <img src={resolvedIconPath} alt="" className={cn(className, 'object-contain brightness-0 invert')} loading="lazy" />;
  }

  const Icon = option.Icon || Gear;

  return (
    <Icon
      weight={selected ? 'fill' : 'regular'}
      className={cn(className, 'text-white')}
    />
  );
};

const normalizePriceListItems = (items, mapper) => (
  Array.isArray(items)
    ? items
        .filter((item) => item && typeof item === 'object')
        .map(mapper)
        .filter(Boolean)
    : []
);

const filterStageBoundOptions = (items, stageCode) => (
  items.filter((item) => {
    const itemStageCode = String(item.stageCode || '').trim();
    return itemStageCode === '' || itemStageCode === stageCode;
  })
);

const buildConfiguratorPricing = (priceListBootstrap, sectionKey = 'pkw') => {
  const sectionSections = priceListBootstrap?.sections?.[sectionKey] && typeof priceListBootstrap.sections[sectionKey] === 'object'
    ? priceListBootstrap.sections[sectionKey]
    : {};

  const stageItems = normalizePriceListItems(sectionSections.stage, (item) => {
    const code = String(item.code || item.id || '').trim();
    if (!code) {
      return null;
    }

    const fallbackMeta = stagePresentationMap[code] || {
      Icon: Lightning,
      color: 'primary',
      desc: { de: String(item.description || ''), en: String(item.description || '') },
    };

    return {
      id: code,
      name: createLocalizedText(item.name, code),
      credits: Number(item.credits) || 0,
      Icon: fallbackMeta.Icon,
      color: fallbackMeta.color,
      desc: createLocalizedText(item.description, fallbackMeta.desc.de),
    };
  });

  const gearboxItems = normalizePriceListItems(sectionSections.gearbox, (item) => {
    const code = String(item.code || item.id || '').trim();
    if (!code) {
      return null;
    }

    return {
      id: code,
      name: createLocalizedText(item.name, code),
      credits: Number(item.credits) || 0,
    };
  });

  const stageOptions = normalizePriceListItems(sectionSections.option, (item) => {
    const code = String(item.code || item.id || '').trim();
    if (!code) {
      return null;
    }

    return {
      id: code,
      stageCode: String(item.stageCode || '').trim(),
      name: createLocalizedText(item.name, code),
      credits: Number(item.credits) || 0,
      included: Boolean(item.included),
      Icon: optionIconMap[item.icon] || Gear,
      iconPath: resolvePriceListIconPath(item.icon || ''),
      desc: createLocalizedText(item.description, defaultOptionDescriptionMap[code]?.de || ''),
    };
  });

  const optionsOnlyItems = normalizePriceListItems(sectionSections.options_only, (item) => {
    const code = String(item.code || item.id || '').trim();
    if (!code) {
      return null;
    }

    return {
      id: code,
      stageCode: String(item.stageCode || '').trim(),
      name: createLocalizedText(item.name, code),
      credits: Number(item.credits) || 0,
      included: Boolean(item.included),
      Icon: optionIconMap[item.icon] || Gear,
      iconPath: resolvePriceListIconPath(item.icon || ''),
      desc: createLocalizedText(item.description, defaultOptionDescriptionMap[code]?.de || ''),
    };
  });

  const fallbackStages = sectionKey === 'lkw' ? defaultLkwStages : defaultTuningStages;
  const fallbackOptions = sectionKey === 'lkw' ? defaultLkwOptions : defaultTuningOptions;
  const fallbackGearboxStages = sectionKey === 'lkw' ? [] : defaultGearboxStages;
  const tuningStages = stageItems.length > 0 ? [...stageItems] : [...fallbackStages];
  const gearboxStages = gearboxItems.length > 0 ? gearboxItems : fallbackGearboxStages;
  const tuningOptions = stageOptions.length > 0 ? stageOptions : fallbackOptions;
  const optionsOnlyOptions = optionsOnlyItems.length > 0 ? optionsOnlyItems : tuningOptions;

  if (sectionKey !== 'lkw' && gearboxStages.length > 0 && !tuningStages.some((stage) => stage.id === 'gearbox')) {
    tuningStages.push(defaultTuningStages.find((stage) => stage.id === 'gearbox'));
  }

  if (optionsOnlyOptions.length > 0 && !tuningStages.some((stage) => stage.id === 'optionsOnly')) {
    tuningStages.push(defaultTuningStages.find((stage) => stage.id === 'optionsOnly'));
  }

  return {
    tuningStages: tuningStages.filter(Boolean),
    gearboxStages,
    tuningOptions,
    optionsOnlyOptions,
  };
};

const parseParkedOrderIdFromSearch = (search) => {
  if (typeof search !== 'string' || search === '') {
    return 0;
  }

  try {
    const params = new URLSearchParams(search);
    const raw = params.get('parkedOrderId');
    if (raw === null) {
      return 0;
    }

    const parsed = Number(raw);
    if (!Number.isFinite(parsed)) {
      return 0;
    }

    const normalized = Math.floor(parsed);
    return normalized > 0 ? normalized : 0;
  } catch (_error) {
    return 0;
  }
};

export default function FileWizard() {
  const { language } = useLanguage();
  const navigate = useNavigate();
  const location = useLocation();
  const priceListBootstrap = useMemo(() => readPriceListBootstrap(), []);
  const orderFlowCreditTopupConfig = useMemo(() => readOrderFlowCreditTopupBootstrap(), []);

  const [formData, setFormData] = useState({
    vehicleType: 'PKW',
    readingDevice: '',
    readingMethod: 'OBD',
    readingType: 'Virtuell',
    masterSlave: 'Slave',
    priority: 'Normal',
    comment: '',
    // Step 2 - Fahrzeug
    manufacturer: '',
    series: '',
    vehicleModel: '',
    engine: '',
    ecu: '',
  });
  const [currentStep, setCurrentStep] = useState(1);
  const [selectedStage, setSelectedStage] = useState('');
  const [selectedGearboxStage, setSelectedGearboxStage] = useState('');
  const [selectedOptions, setSelectedOptions] = useState([]);
  const [showPs, setShowPs] = useState(true);
  const [showNm, setShowNm] = useState(true);
  const [uploadedFiles, setUploadedFiles] = useState([]);
  const [isDragOver, setIsDragOver] = useState(false);
  const [isSubmittingOrder, setIsSubmittingOrder] = useState(false);
  const [submitError, setSubmitError] = useState('');
  const [isAutoMatchTest, setIsAutoMatchTest] = useState(true);
  const [currentCredits, setCurrentCredits] = useState(() => getBootstrapCredits());
  const [simulateZeroCredits, setSimulateZeroCredits] = useState(() => readSimulateZeroCredits());
  const [creditQuote, setCreditQuote] = useState(null);
  const [isLoadingCreditQuote, setIsLoadingCreditQuote] = useState(false);
  const [pendingOrderId, setPendingOrderId] = useState(null);
  const [isPaypalOpen, setIsPaypalOpen] = useState(false);
  const [paypalBusy, setPaypalBusy] = useState(false);
  const [purchaseError, setPurchaseError] = useState('');
  const [paypalSdkReady, setPaypalSdkReady] = useState(false);
  const paypalButtonsRef = useRef(null);
  const [parkMessage, setParkMessage] = useState('');
  const [parkError, setParkError] = useState('');
  const [isParkBusy, setIsParkBusy] = useState(false);
  const searchParkedOrderId = useMemo(() => parseParkedOrderIdFromSearch(location.search), [location.search]);
  const [parkedOrderId, setParkedOrderId] = useState(searchParkedOrderId);
  const [parkedOrderDetail, setParkedOrderDetail] = useState(null);
  const [isLoadingParkedOrder, setIsLoadingParkedOrder] = useState(false);
  const [parkedOrderError, setParkedOrderError] = useState('');
  const pendingOrderIdRef = useRef(null);
  const fileInputRef = useRef(null);
  const followUpTemplateAppliedRef = useRef(false);
  useEffect(() => {
    if (searchParkedOrderId > 0 && searchParkedOrderId !== parkedOrderId) {
      setParkedOrderId(searchParkedOrderId);
    }
  }, [searchParkedOrderId, parkedOrderId]);
  useEffect(() => {
    let isActive = true;

    if (parkedOrderId <= 0) {
      setParkedOrderDetail(null);
      setParkedOrderError('');
      setParkMessage('');
      return () => {
        isActive = false;
      };
    }

    setIsLoadingParkedOrder(true);
    setParkedOrderError('');

    fetch(`/auftrag/parked/${parkedOrderId}`, {
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
      },
    })
      .then(async (response) => {
        if (!isActive) {
          return null;
        }

        if (!response.ok) {
          const payload = await response.json().catch(() => ({}));
          throw new Error(payload?.error || payload?.message || `HTTP ${response.status}`);
        }

        return response.json();
      })
      .then((payload) => {
        if (!isActive || !payload) {
          return;
        }

        setParkedOrderDetail(payload);
        setParkMessage(
          payload?.orderNumber
            ? (language === 'de'
              ? `Geparkter Auftrag ${payload.orderNumber} geladen.`
              : `Parked order ${payload.orderNumber} loaded.`)
            : ''
        );
        setParkError('');
        setFormData((current) => ({
          ...current,
          vehicleType: payload.vehicleType || current.vehicleType,
          manufacturer: payload.manufacturer || current.manufacturer,
          series: payload.series || current.series,
          vehicleModel: payload.model || current.vehicleModel,
          engine: payload.engine || current.engine,
          ecu: payload.ecu || current.ecu,
          readingDevice: payload.readingDevice || current.readingDevice,
          readingMethod: payload.readingMethod || current.readingMethod,
          readingType: payload.readingType || current.readingType,
          masterSlave: payload.masterSlave || current.masterSlave,
          priority: payload.priority || current.priority,
          comment: payload.comment || current.comment,
        }));
        setSelectedStage(payload.selectedStage || '');
        setSelectedGearboxStage(payload.selectedGearboxStage || '');
        setSelectedOptions(Array.isArray(payload.selectedOptionCodes) ? payload.selectedOptionCodes : []);
        setSimulateZeroCredits(Boolean(payload.simulateZeroCredits));
        setIsAutoMatchTest(Boolean(payload.autoReady));
        setUploadedFiles([{
          name: payload.fileName || 'geparkte-datei.bin',
          size: Number.isFinite(payload.fileSize) ? Math.max(1, payload.fileSize) : 0,
          type: 'application/octet-stream',
          lastModified: Date.now(),
        }]);
        setCurrentStep(3);
      })
      .catch((error) => {
        if (!isActive) {
          return;
        }

        const message = error instanceof Error && error.message ? error.message : '';
        setParkedOrderDetail(null);
        setParkedOrderError(
          message
            ? message
            : (language === 'de' ? 'Geparkter Auftrag konnte nicht geladen werden.' : 'Could not load parked order.')
        );
      })
      .finally(() => {
        if (isActive) {
          setIsLoadingParkedOrder(false);
        }
      });

    return () => {
      isActive = false;
    };
  }, [parkedOrderId, language]);
  const vehicleTypeKey = useMemo(() => normalizeVehicleTypeKey(formData.vehicleType), [formData.vehicleType]);
  const vehicleApi = useVehicleApiOptions({
    vehicleTypeKey,
    vehicleTypeName: formData.vehicleType,
    manufacturer: formData.manufacturer,
    series: formData.series,
    model: formData.vehicleModel,
    engine: formData.engine,
  });
  const pricingConfig = useMemo(() => buildConfiguratorPricing(priceListBootstrap, vehicleTypeKey), [priceListBootstrap, vehicleTypeKey]);
  const tuningStages = pricingConfig.tuningStages;
  const gearboxStages = pricingConfig.gearboxStages;
  const tuningOptions = pricingConfig.tuningOptions;
  const optionsOnlyOptions = pricingConfig.optionsOnlyOptions;
  const hasTcuControlUnit = useMemo(
    () => Array.isArray(vehicleApi.ecuItems)
      && vehicleApi.ecuItems.some((ecuItem) => String(ecuItem?.controlUnitKind || '').trim().toLowerCase() === 'tcu'),
    [vehicleApi.ecuItems]
  );
  const stagePricingMap = useMemo(() => new Map(
    tuningStages.map((stage) => [String(stage.id || '').trim().toLowerCase(), stage])
  ), [tuningStages]);
  const apiStageItems = useMemo(() => vehicleApi.stageItems.map((stage, index) => {
    const stageKey = normalizeStageKey(stage.name);
    const stageMeta = stagePresentationMap[stageKey] || stagePresentationMap.tuning;
    const priceListStage = stagePricingMap.get(stageKey) || null;

    return {
      id: stage.id ?? `${stageKey || 'stage'}-${index}`,
      name: createLocalizedText(stage.name, stage.name || stageKey || 'Stage'),
      credits: Number.isFinite(priceListStage?.credits) ? Number(priceListStage.credits) : 0,
      Icon: stageMeta.Icon,
      color: stageMeta.color,
      desc: createLocalizedText(stage.description, stageMeta.desc?.de || ''),
      hp: Number.isFinite(stage.hp) ? Number(stage.hp) : null,
      nm: Number.isFinite(stage.nm) ? Number(stage.nm) : null,
      extra: Number.isFinite(stage.ecu) ? `${stage.ecu} ECU` : '',
      stageKey,
    };
  }), [vehicleApi.stageItems, stagePricingMap]);
  const displayTuningStages = useMemo(() => {
    const baseStages = apiStageItems.length > 0 ? [...apiStageItems] : [...tuningStages];

    if (vehicleTypeKey !== 'lkw' && hasTcuControlUnit && gearboxStages.length > 0 && !baseStages.some((stage) => stage.id === 'gearbox')) {
      const gearboxStage = defaultTuningStages.find((stage) => stage.id === 'gearbox');
      if (gearboxStage) {
        baseStages.push(gearboxStage);
      }
    }

    if (optionsOnlyOptions.length > 0 && !baseStages.some((stage) => stage.id === 'optionsOnly')) {
      const optionsOnlyStage = defaultTuningStages.find((stage) => stage.id === 'optionsOnly');
      if (optionsOnlyStage) {
        baseStages.push(optionsOnlyStage);
      }
    }

    return baseStages;
  }, [apiStageItems, tuningStages, vehicleTypeKey, hasTcuControlUnit, gearboxStages, optionsOnlyOptions]);
  const selectedEcuItem = useMemo(() => {
    if (!Array.isArray(vehicleApi.ecuItems) || vehicleApi.ecuItems.length === 0) {
      return null;
    }

    return vehicleApi.ecuItems.find((ecu) => normalizeOptionMatchKey(ecu.name) === normalizeOptionMatchKey(formData.ecu))
      || vehicleApi.ecuItems[0]
      || null;
  }, [vehicleApi.ecuItems, formData.ecu]);
  const activeSelectableOptions = useMemo(() => {
    let baseOptions = [];
    const selectedStageEntry = displayTuningStages.find((stage) => stage.id === selectedStage);
    const selectedStageKey = String(selectedStageEntry?.stageKey || selectedStage || '').trim().toLowerCase();

    if (selectedStage === 'optionsOnly') {
      baseOptions = optionsOnlyOptions;
    } else if (!selectedStage || selectedStage === 'gearbox') {
      return [];
    } else {
      baseOptions = filterStageBoundOptions(tuningOptions, selectedStageKey);
    }

    if (!selectedEcuItem || !Array.isArray(selectedEcuItem.options) || selectedEcuItem.options.length === 0) {
      return baseOptions;
    }

    const apiOptionKeys = new Set();
    const apiOptionsByKey = new Map();

    selectedEcuItem.options.forEach((option) => {
      const normalizedName = normalizeOptionMatchKey(option?.name);
      if (normalizedName !== '') {
        apiOptionKeys.add(normalizedName);
        apiOptionsByKey.set(normalizedName, option);
      }
    });

    return baseOptions
      .filter((option) => {
        const optionKeys = buildOptionLookupKeys(option);
        if (option.included) {
          return true;
        }

        return optionKeys.some((key) => apiOptionKeys.has(key));
      })
      .map((option) => {
      const optionKeys = buildOptionLookupKeys(option);
      const apiKey = optionKeys.find((key) => apiOptionsByKey.has(key)) || '';
      const apiOption = apiKey !== '' ? apiOptionsByKey.get(apiKey) : null;
      if (!apiOption) {
        return option;
      }
      const apiDescription = String(apiOption.description || '').trim();

      return {
        ...option,
        iconPath: option.iconPath || resolvePortalAssetPath(apiOption.icon),
        desc: apiDescription !== '' ? createLocalizedText(apiDescription, option.desc?.de || '') : option.desc,
      };
      });
  }, [displayTuningStages, optionsOnlyOptions, selectedEcuItem, selectedStage, tuningOptions]);
  const selectedStageResolvedKey = useMemo(() => {
    const selectedStageEntry = displayTuningStages.find((stage) => stage.id === selectedStage);
    return String(selectedStageEntry?.stageKey || selectedStage || '').trim().toLowerCase();
  }, [displayTuningStages, selectedStage]);
  const previewEcuOptions = useMemo(() => {
    if (!Array.isArray(vehicleApi.ecuItems) || vehicleApi.ecuItems.length === 0) {
      return [];
    }

    const previewStageKey = selectedStageResolvedKey !== '' && selectedStageResolvedKey !== 'gearbox' && selectedStageResolvedKey !== 'optionsonly'
      ? selectedStageResolvedKey
      : (
          displayTuningStages
            .map((stage) => String(stage?.stageKey || '').trim().toLowerCase())
            .find((stageKey) => stageKey !== '' && stageKey !== 'gearbox')
          || ''
        );

    const optionMap = new Map();
    vehicleApi.ecuItems
      .filter((ecuItem) => String(ecuItem?.controlUnitKind || '').trim().toLowerCase() !== 'tcu')
      .forEach((ecuItem) => {
      if (!Array.isArray(ecuItem?.options)) {
        return;
      }

      ecuItem.options.forEach((option) => {
        const optionKey = normalizeOptionMatchKey(option?.name || option?.id);
        if (!optionKey || optionMap.has(optionKey)) {
          return;
        }

        const optionName = String(option?.name || '').trim();
        const priceListEntry = findPriceListOptionEntry(tuningOptions, optionName, previewStageKey);

        if (!priceListEntry) {
          return;
        }

        optionMap.set(optionKey, {
          id: optionKey,
          name: createLocalizedText(option?.name, optionName),
          credits: Number.isFinite(priceListEntry?.credits) ? Number(priceListEntry.credits) : 0,
          included: Boolean(priceListEntry?.included || option?.isMainOption),
          Icon: priceListEntry?.Icon || optionIconMap[normalizeOptionMatchKey(option?.name)] || Gear,
          iconPath: priceListEntry?.iconPath || resolvePortalAssetPath(option?.icon),
          desc: createLocalizedText(option?.description, priceListEntry?.desc?.de || ''),
        });
      });
    });

    return Array.from(optionMap.values());
  }, [displayTuningStages, selectedStageResolvedKey, tuningOptions, vehicleApi.ecuItems]);
  const vehicleTypeItems = vehicleApi.typeItems.length > 0
    ? vehicleApi.typeItems
    : [
        { id: 'pkw', name: 'PKW', image: '' },
        { id: 'lkw', name: 'LKW', image: '' },
      ];
  const perfData = useMemo(() => {
    const apiPerformance = getPerformanceDataFromApi(formData.engine, vehicleApi.engineInfo, vehicleApi.stageItems);
    if (apiPerformance) {
      return apiPerformance;
    }

    return getPerformanceData(formData.engine);
  }, [formData.engine, vehicleApi.engineInfo, vehicleApi.stageItems]);

  const buildOrderPayload = useCallback((fileOverride = null) => {
    const file = fileOverride || uploadedFiles[0] || null;
    const selectedOptionLabels = selectedOptions
      .map((id) => {
        const option = activeSelectableOptions.find((entry) => entry.id === id);
        if (!option) {
          return id;
        }

        return option.name?.[language] || option.name?.de || id;
      })
      .filter(Boolean);

    let stageCredits = 0;
    let optionCredits = 0;
    let totalCredits = 0;
    let stageLabel = '';
    let stagePerformance = null;

    if (selectedStage === 'gearbox') {
      stageCredits = gearboxStages.find((gs) => gs.id === selectedGearboxStage)?.credits || 0;
      totalCredits = stageCredits;
      const gearStage = gearboxStages.find((gs) => gs.id === selectedGearboxStage);
      stageLabel = gearStage ? `Getriebe ${gearStage.name[language] || gearStage.name.de}` : 'Getriebe';
    } else {
      stageCredits = displayTuningStages.find((s) => s.id === selectedStage)?.credits || 0;
      optionCredits = selectedOptions.reduce((sum, id) => sum + (activeSelectableOptions.find((o) => o.id === id)?.credits || 0), 0);
      totalCredits = stageCredits + optionCredits;
      const stage = displayTuningStages.find((s) => s.id === selectedStage);
      stageLabel = stage ? (stage.name?.[language] || stage.name?.de || '') : '';
    }

    if (vehicleTypeKey !== 'lkw' && perfData && selectedStage && selectedStage !== 'gearbox' && selectedStage !== 'optionsOnly') {
      if (selectedStageResolvedKey === 'stage1') {
        stagePerformance = {
          originalPs: perfData.originalPs,
          originalNm: perfData.originalNm,
          tunedPs: perfData.stage1Ps,
          tunedNm: perfData.stage1Nm,
          gainPs: perfData.psGainS1,
          gainNm: perfData.nmGainS1,
        };
      } else if (selectedStageResolvedKey === 'stage2') {
        stagePerformance = {
          originalPs: perfData.originalPs,
          originalNm: perfData.originalNm,
          tunedPs: perfData.stage2Ps,
          tunedNm: perfData.stage2Nm,
          gainPs: perfData.psGainS2,
          gainNm: perfData.nmGainS2,
        };
      } else if (selectedStageResolvedKey === 'eco') {
        stagePerformance = {
          originalPs: perfData.originalPs,
          originalNm: perfData.originalNm,
          tunedPs: perfData.originalPs,
          tunedNm: perfData.originalNm,
          gainPs: 0,
          gainNm: 0,
        };
      }
    }

    return {
      pendingOrderId,
      parkedOrderId,
      fileName: file?.name || '',
      fileSize: file?.size || 0,
      vehicleType: formData.vehicleType || 'PKW',
      manufacturer: formData.manufacturer || '',
      series: formData.series || '',
      model: formData.vehicleModel || '',
      built: '',
      engine: formData.engine || '',
      ecu: formData.ecu || '',
      readingDevice: formData.readingDevice || '',
      readingMethod: formData.readingMethod || '',
      readingType: formData.readingType || '',
      masterSlave: formData.masterSlave || '',
      priority: formData.priority || 'Normal',
      comment: formData.comment || '',
      stageLabel,
      selectedStage,
      selectedStageKey: selectedStageResolvedKey,
      selectedGearboxStage,
      stageCredits,
      optionCredits,
      selectedOptions: selectedOptionLabels,
      selectedOptionCodes: selectedOptions,
      totalCredits,
      stagePerformance,
      autoReady: isAutoMatchTest,
      simulateZeroCredits,
    };
  }, [
    activeSelectableOptions,
    displayTuningStages,
    formData,
    gearboxStages,
    isAutoMatchTest,
    language,
    pendingOrderId,
    selectedGearboxStage,
    selectedOptions,
    selectedStage,
    selectedStageResolvedKey,
    simulateZeroCredits,
    uploadedFiles,
    vehicleTypeKey,
    perfData,
    parkedOrderId,
  ]);

  const postOrderJson = useCallback(async (url, payload) => {
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const json = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(String(json?.error || json?.message || `HTTP ${response.status}`));
    }

    return json;
  }, []);

  const navigateToLiveOrder = useCallback((result, fileOverride = null) => {
    const file = fileOverride || uploadedFiles[0] || null;
    navigate('/order-live', {
      state: {
        orderData: {
          orderId: result?.orderNumber || result?.id || '',
          fileName: file?.name || '',
          mfr: formData.manufacturer,
          ser: formData.series,
          mod: formData.vehicleModel,
          eng: formData.engine,
          ecuVal: formData.ecu,
          readingDevice: formData.readingDevice,
          readingMethod: formData.readingMethod,
          readingType: formData.readingType,
          masterSlave: formData.masterSlave,
          priority: formData.priority,
          selectedStage,
          selectedGearboxStage,
          selectedOptions,
          totalCredits: result?.deductedCredits ?? creditQuote?.requiredCredits ?? 0,
          matchPositive: isAutoMatchTest,
          autoModEnabled: Boolean(result?.autoModEnabled),
          autoModStatus: typeof result?.autoModStatus === 'number' ? result.autoModStatus : null,
        },
      },
    });
  }, [creditQuote?.requiredCredits, formData, isAutoMatchTest, navigate, selectedGearboxStage, selectedOptions, selectedStage, uploadedFiles]);

  const clearParkedOrderSelection = useCallback(() => {
    setParkedOrderId(0);
    setParkedOrderDetail(null);
    setParkMessage('');
    setParkError('');
    setParkedOrderError('');
    navigate({ pathname: location.pathname, search: '' }, { replace: true });
  }, [location.pathname, navigate]);

  const handleParkOrder = async () => {
    setParkError('');
    setParkMessage('');
    const file = uploadedFiles[0];
    if (!file) {
      setParkError(language === 'de' ? 'Bitte zuerst eine Datei hochladen.' : 'Upload a file first.');
      return;
    }

    setIsParkBusy(true);
    try {
      const result = await postOrderJson('/auftrag/park', buildOrderPayload(file));
      if (Number.isFinite(result?.parkedOrderId)) {
        setParkedOrderId(result.parkedOrderId);
      }
      setParkedOrderDetail(null);
      setParkError('');
      setParkMessage(
        result?.orderNumber
          ? (language === 'de'
            ? `Auftrag ${result.orderNumber} wurde geparkt.`
            : `Order ${result.orderNumber} parked.`)
          : (result?.message || (language === 'de' ? 'Auftrag wurde geparkt.' : 'Order parked.'))
      );
    } catch (error) {
      setParkError(error instanceof Error ? error.message : (language === 'de' ? 'Parken fehlgeschlagen.' : 'Parking failed.'));
    } finally {
      setIsParkBusy(false);
    }
  };

  useEffect(() => {
    const handleCreditsUpdated = () => {
      setCurrentCredits(getBootstrapCredits());
    };

    window.addEventListener('tfd:portal-credits-updated', handleCreditsUpdated);

    return () => {
      window.removeEventListener('tfd:portal-credits-updated', handleCreditsUpdated);
    };
  }, []);

  useEffect(() => {
    const syncSimulationFlag = () => {
      setSimulateZeroCredits(readSimulateZeroCredits());
    };

    window.addEventListener('storage', syncSimulationFlag);

    return () => {
      window.removeEventListener('storage', syncSimulationFlag);
    };
  }, []);

  useEffect(() => {
    const file = uploadedFiles[0];
    if (currentStep !== 3 || !file || !selectedStage) {
      setCreditQuote(null);
      setPurchaseError('');
      return;
    }

    if (isPaypalOpen) {
      return;
    }

    let cancelled = false;
    const loadQuote = async () => {
      setIsLoadingCreditQuote(true);
      try {
        const quote = await postOrderJson('/auftrag/quote', buildOrderPayload(file));
        if (!cancelled) {
          setCreditQuote(quote);
          setPurchaseError('');
        }
      } catch (error) {
        if (!cancelled) {
          setCreditQuote(null);
          setPurchaseError(error instanceof Error ? error.message : '');
        }
      } finally {
        if (!cancelled) {
          setIsLoadingCreditQuote(false);
        }
      }
    };

    loadQuote();

    return () => {
      cancelled = true;
    };
  }, [buildOrderPayload, currentStep, isPaypalOpen, postOrderJson, selectedStage, uploadedFiles]);

  useEffect(() => {
    if (!isPaypalOpen || !creditQuote?.missingCredits || !orderFlowCreditTopupConfig.configured || !orderFlowCreditTopupConfig.csrfToken) {
      return undefined;
    }

    let canceled = false;
    const container = paypalButtonsRef.current;

    const renderButtons = async () => {
      try {
        const ensurePaypalSdk = async () => {
          if (typeof window === 'undefined') {
            throw new Error('PayPal ist im Browser nicht verfuegbar.');
          }

          if (!orderFlowCreditTopupConfig.clientId) {
            throw new Error('PayPal Client-ID fehlt.');
          }

          if (window.paypal?.Buttons) {
            setPaypalSdkReady(true);
            return;
          }

          const existing = document.getElementById('paypal-sdk-js');
          if (existing) {
            await new Promise((resolve, reject) => {
              existing.addEventListener('load', resolve, { once: true });
              existing.addEventListener('error', reject, { once: true });
            });
            setPaypalSdkReady(Boolean(window.paypal?.Buttons));
            return;
          }

          await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.id = 'paypal-sdk-js';
            script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(orderFlowCreditTopupConfig.clientId)}&currency=EUR&intent=capture`;
            script.async = true;
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
          });
          setPaypalSdkReady(Boolean(window.paypal?.Buttons));
        };

        await ensurePaypalSdk();
        if (canceled || !window.paypal?.Buttons || !container) {
          return;
        }

        container.innerHTML = '';
        const buttons = window.paypal.Buttons({
          style: {
            layout: 'vertical',
            shape: 'rect',
            label: 'paypal',
          },
          createOrder: async () => {
            setPaypalBusy(true);
            setPurchaseError('');
            try {
              const json = await postOrderJson('/auftrag/paypal/order', {
                ...buildOrderPayload(uploadedFiles[0]),
                _token: orderFlowCreditTopupConfig.csrfToken,
              });

              const orderId = String(json?.id || '').trim();
              if (!orderId) {
                throw new Error(language === 'de' ? 'PayPal Order konnte nicht erstellt werden.' : 'Could not create PayPal order.');
              }

              if (json?.pendingOrderId) {
                const nextPendingOrderId = Number(json.pendingOrderId);
                pendingOrderIdRef.current = nextPendingOrderId;
                setPendingOrderId(nextPendingOrderId);
              }

              return orderId;
            } finally {
              setPaypalBusy(false);
            }
          },
          onApprove: async (data) => {
            setPaypalBusy(true);
            setPurchaseError('');
            try {
              const json = await postOrderJson('/auftrag/paypal/capture', {
                pendingOrderId: pendingOrderIdRef.current || pendingOrderId,
                orderId: String(data?.orderID || ''),
                _token: orderFlowCreditTopupConfig.csrfToken,
              });

              if (typeof json?.credits === 'number' && Number.isFinite(json.credits)) {
                setCurrentCredits(json.credits);
                setBootstrapCredits(json.credits);
              }

              setIsPaypalOpen(false);
              navigateToLiveOrder(json, uploadedFiles[0]);
            } catch (error) {
              setPurchaseError(error instanceof Error ? error.message : 'PayPal-Zahlung konnte nicht verbucht werden.');
            } finally {
              setPaypalBusy(false);
            }
          },
          onCancel: () => {
            setPaypalBusy(false);
          },
          onError: (error) => {
            setPaypalBusy(false);
            setPurchaseError(error instanceof Error ? error.message : 'PayPal konnte nicht gestartet werden.');
          },
        });

        if (!buttons.isEligible()) {
          setPurchaseError('PayPal ist fuer dieses Konto aktuell nicht verfuegbar.');
          return;
        }

        await buttons.render(container);
      } catch (error) {
        if (canceled) {
          return;
        }
        setPurchaseError(error instanceof Error ? error.message : 'PayPal konnte nicht geladen werden.');
        setPaypalBusy(false);
      }
    };

    renderButtons();

    return () => {
      canceled = true;
      if (container) {
        container.innerHTML = '';
      }
    };
  }, [buildOrderPayload, creditQuote?.missingCredits, isPaypalOpen, language, navigateToLiveOrder, orderFlowCreditTopupConfig.clientId, orderFlowCreditTopupConfig.configured, orderFlowCreditTopupConfig.csrfToken, pendingOrderId, postOrderJson, uploadedFiles]);

  useEffect(() => {
    setFormData((current) => {
      if (current.readingDevice) {
        return current;
      }

      const profileTools = readProfileTuningTools();
      if (profileTools.length === 0) {
        return current;
      }

      const firstMapped = profileTools
        .map((tool) => mapProfileToolToReadingDevice(tool))
        .find((value) => Boolean(value));

      if (!firstMapped) {
        return current;
      }

      return {
        ...current,
        readingDevice: firstMapped,
      };
    });
  }, []);

  useEffect(() => {
    if (followUpTemplateAppliedRef.current) {
      return;
    }

    const template = location.state?.followUpTemplate;
    if (!template || typeof template !== 'object') {
      return;
    }

    const vehicle = template.vehicle && typeof template.vehicle === 'object' ? template.vehicle : {};
    const templateFileName = String(template.fileName || '').trim();
    const templateFileSize = Number.isFinite(template.fileSize) ? Math.max(1, Number(template.fileSize)) : 1;

    setFormData((current) => ({
      ...current,
      manufacturer: String(vehicle.manufacturer || current.manufacturer || ''),
      series: String(vehicle.series || current.series || ''),
      vehicleModel: String(vehicle.model || current.vehicleModel || ''),
      engine: String(vehicle.engine || current.engine || ''),
      ecu: String(vehicle.ecu || current.ecu || ''),
    }));

    setUploadedFiles([{
      name: templateFileName !== '' ? templateFileName : 'original.bin',
      size: templateFileSize,
      type: 'application/octet-stream',
      lastModified: Date.now(),
    }]);
    setCurrentStep(3);

    followUpTemplateAppliedRef.current = true;
  }, [location.state]);

  useEffect(() => {
    if (selectedStage !== 'gearbox') {
      return;
    }

    if (hasTcuControlUnit) {
      return;
    }

    setSelectedStage('');
    setSelectedGearboxStage('');
  }, [hasTcuControlUnit, selectedStage]);

  useEffect(() => {
    setFormData((current) => ({
      ...current,
      manufacturer: '',
      series: '',
      vehicleModel: '',
      engine: '',
      ecu: '',
    }));
    setSelectedStage('');
    setSelectedGearboxStage('');
    setSelectedOptions([]);
  }, [vehicleTypeKey]);

  // Parse filename into readable parts
  const parsedFilename = useMemo(() => {
    if (uploadedFiles.length === 0) return null;
    const name = uploadedFiles[0].name;
    const nameWithoutExt = name.replace(/\.[^/.]+$/, '');
    const normalizedFilename = nameWithoutExt.toLowerCase();
    // Split by underscore, dash, space — keep parenthesized groups together
    const parts = nameWithoutExt.split(/[_\-\s]+/).filter(Boolean);
    
    const manufacturers = ['BMW', 'VW', 'Volkswagen', 'Audi', 'Mercedes', 'Benz', 'Porsche', 'Seat', 'Skoda', 'Opel', 'Ford', 'Toyota', 'Honda', 'Renault', 'Peugeot', 'Citroen', 'Fiat', 'Alfa', 'Hyundai', 'Kia', 'Volvo', 'Jaguar', 'Land', 'Range', 'Mini', 'Mazda', 'Nissan', 'Subaru', 'Mitsubishi', 'Suzuki', 'Dacia', 'Chevrolet', 'Dodge', 'Jeep', 'Cupra', 'DS', 'MAN', 'DAF', 'Scania', 'Iveco'];
    
    let parsed = { manufacturer: '', series: '', model: '', engine: '', ecu: '', readMethod: '', readType: '', rest: [] };
    let remaining = [...parts];

    // Detect read method globally first so trailing tokens like "Bench" never fall back to OBD.
    if (/(^|[_\-\s])bench([_\-\s]|$)|\bbenchnr\b|\bbenchread\b/i.test(normalizedFilename)) {
      parsed.readMethod = 'Bench';
    } else if (/(^|[_\-\s])boot([_\-\s]|$)|\bbootmode\b|\bbootread\b/i.test(normalizedFilename)) {
      parsed.readMethod = 'Boot';
    } else if (/(^|[_\-\s])obd([_\-\s]|$)|\bobd(read)?\b|\bobdvr\b|\bobdfr\b/i.test(normalizedFilename)) {
      parsed.readMethod = 'OBD';
    }

    if (/\bfullread\b|\bfull_read\b|\bfull-read\b|(^|[_\-\s])fr([_\-\s]|$)|\bobdfr\b/i.test(normalizedFilename)) {
      parsed.readType = 'Full Read';
    } else if (/\bvirtualread\b|\bvirtual_read\b|\bvirtual-read\b|(^|[_\-\s])vr([_\-\s]|$)|\bobdvr\b/i.test(normalizedFilename)) {
      parsed.readType = 'Virtuell';
    }
    
    // 1. Find manufacturer
    const mfIdx = remaining.findIndex(p => 
      manufacturers.some(m => m.toLowerCase() === p.toLowerCase())
    );
    if (mfIdx !== -1) {
      parsed.manufacturer = remaining.splice(mfIdx, 1)[0];
    }
    
    // 2. Find ECU (Bosch, EDC17, MED17, Siemens, Delphi, Continental etc.)
    const ecuStartPattern = /^(EDC|MED|PCR|MD1|MG1|SID|DCM|MEDC|Bosch|Siemens|Delphi|Continental|Marelli)/i;
    const ecuIdx = remaining.findIndex(p => ecuStartPattern.test(p));
    if (ecuIdx !== -1) {
      const ecuParts = remaining.splice(ecuIdx);
      // Separate reading method/type from ECU parts
      const readMethods = ['OBD', 'Bench', 'Boot'];
      const readTypes = ['VR', 'FR'];
      const cleanEcuParts = [];
      ecuParts.forEach(p => {
        const upper = p.toUpperCase();
        if (readMethods.includes(upper)) {
          parsed.readMethod = upper === 'OBD' ? 'OBD' : upper.charAt(0).toUpperCase() + upper.slice(1).toLowerCase();
        } else if (readTypes.includes(upper)) {
          parsed.readType = upper === 'VR' ? 'Virtuell' : 'Full Read';
        } else {
          cleanEcuParts.push(p);
        }
      });
      parsed.ecu = cleanEcuParts.join(' ');
    }
    
    // 3. Find year (4-digit number 1990-2030)
    let year = '';
    const yearIdx = remaining.findIndex(p => /^(19|20)\d{2}$/.test(p));
    if (yearIdx !== -1) {
      year = remaining.splice(yearIdx, 1)[0];
    }
    
    // 4. Find generation code in parentheses like (F5), (C7) or plain like F30, B8
    let genCode = '';
    const parenIdx = remaining.findIndex(p => /^\(.*\)$/.test(p));
    if (parenIdx !== -1) {
      genCode = remaining.splice(parenIdx, 1)[0].replace(/[()]/g, '');
    }
    
    // 5. Engine tokens: displacement, fuel type, norms, power
    const engineTokens = [];
    const enginePatterns = [
      /^\d+\.?\d*$/, // displacement: 2.0, 3.0
      /^(TDI|TSI|TFSI|CDI|HDI|JTD|CDTI|CRDi|CRDI|FSI|BlueHDI|EcoBoost|EcoBlue|dCi|JTDM|Turbo|Bi|CR|CRTD|BiTDI|BiTurbo)$/i,
      /^(EU\d|Euro\d)$/i, // emission norm
      /^\d{2,4}$/,  // power number (190, 218)
      /^(PS|hp|kW|bhp|pk)$/i, // power unit
      /^(N\d{2}|M\d{2,3}|EA\d{3}|OM\d{3}|B\d{2}|S\d{2})$/i, // engine codes
    ];
    
    remaining = remaining.filter(p => {
      if (enginePatterns.some(pattern => pattern.test(p))) {
        engineTokens.push(p);
        return false;
      }
      return true;
    });
    if (engineTokens.length > 0) {
      parsed.engine = engineTokens.join(' ');
    }
    
    // 6. First remaining = series (A5, A6, Golf, 3er etc.)
    if (remaining.length > 0) parsed.series = remaining.shift();
    
    // 7. If no genCode yet, check if next remaining looks like a gen code (B8, C7, F30, etc.)
    if (!genCode && remaining.length > 0) {
      const genPattern = /^[A-Z]\d{1,2}$/i;
      if (genPattern.test(remaining[0])) {
        genCode = remaining.shift();
      }
    }
    
    // 8. Build model from year + genCode
    if (year && genCode) {
      parsed.model = `${genCode} - ${year}`;
    } else if (genCode) {
      parsed.model = genCode;
    } else if (year) {
      parsed.model = year;
    }
    
    parsed.rest = remaining;
    
    const readableParts = [parsed.manufacturer, parsed.series, parsed.model, parsed.engine, parsed.ecu, ...parsed.rest].filter(Boolean);
    
    return {
      original: name,
      readable: readableParts.join(' '),
      parts: parsed,
    };
  }, [uploadedFiles]);

  const t = (key) => {
    const texts = {
      de: {
        pageTitle: 'Neuer Auftrag',
        stepTitle: 'File & Lesegerät',
        step: 'STEP',
        tuningTool: 'TUNING TOOL / LESEGERÄT',
        selectDevice: 'Lesegerät auswählen...',
        readingMethod: 'LESEMETHODE',
        readingType: 'LESEART',
        masterSlave: 'MASTER / SLAVE',
        priority: 'PRIORITÄT',
        comment: 'KOMMENTAR (OPTIONAL)',
        commentPlaceholder: 'Zusätzliche Hinweise, Wünsche oder Informationen...',
        dropFile: 'Datei hierher ziehen',
        orClick: 'oder klicken zum Auswählen',
        addressesMore: 'Adressen und mehr',
        nextStep: 'Weiter zu Schritt 2',
        matchTestMode: 'WINOLS TEST-MODUS',
        matchPositive: 'Match positiv (Auto-Fertig)',
        matchNegative: 'Kein Match (manuell/offen)',
        prevStep: 'Zurück',
        fileAdded: 'Datei hinzugefügt',
        removeFile: 'Entfernen',
        // Step 2
        step2Title: 'Fahrzeugauswahl',
        vehicleType: 'FAHRZEUGTYP',
        uploadedFile: 'HOCHGELADENE DATEI',
        parsedAs: 'Erkannt als',
        manufacturer: 'HERSTELLER',
        manufacturerPlaceholder: 'Hersteller wählen...',
        series: 'BAUREIHE',
        seriesPlaceholder: 'Baureihe wählen...',
        vehicleModel: 'MODELL',
        vehicleModelPlaceholder: 'Modell wählen...',
        engine: 'MOTOR',
        enginePlaceholder: 'Motor wählen...',
        ecu: 'STEUERGERÄT',
        ecuPlaceholder: 'Steuergerät wählen...',
        readMethodBadge: 'LESEMETHODE',
        readTypeBadge: 'LESEART',
        applyParsed: 'Übernehmen',
        nextStep3: 'Weiter zu Schritt 3',
        // Step 2 - Performance
        performanceTitle: 'LEISTUNGSDATEN',
        originalPower: 'Serie',
        stage1Power: 'Stage 1',
        stage2Power: 'Stage 2',
        power: 'Leistung',
        torque: 'Drehmoment',
        gain: 'Zugewinn',
        availableOptionsTitle: 'VERFÜGBARE TUNING-OPTIONEN',
        // Step 3
        step3Title: 'Optionen',
        selectedVehicle: 'AUSGEWÄHLTES FAHRZEUG',
        selectTuning: 'TUNING WÄHLEN',
        additionalOptions: 'ZUSÄTZLICHE OPTIONEN',
        includedInStage: 'Inklusive',
        totalCredits: 'GESAMT',
        nextStep4: 'Weiter zur Übersicht',
        gearboxNote: 'Nur bei VW, Audi, Seat, Cupra, Skoda und BMW möglich',
        selectGearboxStage: 'GETRIEBE-STUFE WÄHLEN',
        // Step 4
        step4Title: 'Übersicht',
        reviewFile: 'DATEI & LESEGERÄT',
        reviewVehicle: 'FAHRZEUG',
        reviewTuning: 'TUNING & OPTIONEN',
        reviewCost: 'KOSTEN',
        submitOrder: 'Auftrag absenden',
        editStep: 'Bearbeiten',
        noFileUploaded: 'Keine Datei hochgeladen',
        noVehicleSelected: 'Kein Fahrzeug ausgewählt',
        noTuningSelected: 'Kein Tuning ausgewählt',
        stage: 'Stufe',
        options: 'Optionen',
        baseCredits: 'Grundpreis',
        optionsCredits: 'Optionen',
        prioritySurcharge: 'Prioritäts-Zuschlag',
      },
      en: {
        pageTitle: 'New Order',
        stepTitle: 'File & Reading Device',
        step: 'STEP',
        tuningTool: 'TUNING TOOL / READING DEVICE',
        selectDevice: 'Select reading device...',
        readingMethod: 'READING METHOD',
        readingType: 'READING TYPE',
        masterSlave: 'MASTER / SLAVE',
        priority: 'PRIORITY',
        comment: 'COMMENT (OPTIONAL)',
        commentPlaceholder: 'Additional notes, wishes or information...',
        dropFile: 'Drag file here',
        orClick: 'or click to select',
        addressesMore: 'Addresses and more',
        nextStep: 'Continue to Step 2',
        matchTestMode: 'WINOLS TEST MODE',
        matchPositive: 'Positive match (auto complete)',
        matchNegative: 'No match (manual/open)',
        prevStep: 'Back',
        fileAdded: 'File added',
        removeFile: 'Remove',
        // Step 2
        step2Title: 'Vehicle Selection',
        vehicleType: 'VEHICLE TYPE',
        uploadedFile: 'UPLOADED FILE',
        parsedAs: 'Detected as',
        manufacturer: 'MANUFACTURER',
        manufacturerPlaceholder: 'Select manufacturer...',
        series: 'SERIES',
        seriesPlaceholder: 'Select series...',
        vehicleModel: 'MODEL',
        vehicleModelPlaceholder: 'Select model...',
        engine: 'ENGINE',
        enginePlaceholder: 'Select engine...',
        ecu: 'ECU',
        ecuPlaceholder: 'Select ECU...',
        readMethodBadge: 'METHOD',
        readTypeBadge: 'READ TYPE',
        applyParsed: 'Apply',
        nextStep3: 'Continue to Step 3',
        // Step 2 - Performance
        performanceTitle: 'PERFORMANCE DATA',
        originalPower: 'Stock',
        stage1Power: 'Stage 1',
        stage2Power: 'Stage 2',
        power: 'Power',
        torque: 'Torque',
        gain: 'Gain',
        availableOptionsTitle: 'AVAILABLE TUNING OPTIONS',
        // Step 3
        step3Title: 'Options',
        selectedVehicle: 'SELECTED VEHICLE',
        selectTuning: 'SELECT TUNING',
        additionalOptions: 'ADDITIONAL OPTIONS',
        includedInStage: 'Included',
        totalCredits: 'TOTAL',
        nextStep4: 'Continue to Review',
        gearboxNote: 'Only available for VW, Audi, Seat, Cupra, Skoda and BMW',
        selectGearboxStage: 'SELECT GEARBOX STAGE',
        // Step 4
        step4Title: 'Review',
        reviewFile: 'FILE & READING DEVICE',
        reviewVehicle: 'VEHICLE',
        reviewTuning: 'TUNING & OPTIONS',
        reviewCost: 'COST',
        submitOrder: 'Submit Order',
        editStep: 'Edit',
        noFileUploaded: 'No file uploaded',
        noVehicleSelected: 'No vehicle selected',
        noTuningSelected: 'No tuning selected',
        stage: 'Stage',
        options: 'Options',
        baseCredits: 'Base price',
        optionsCredits: 'Options',
        prioritySurcharge: 'Priority surcharge',
      },
    };
    return texts[language]?.[key] || texts.de[key] || key;
  };

  const handleFileSelect = useCallback((files) => {
    const validFiles = Array.from(files).filter((file) => {
      const ext = '.' + file.name.split('.').pop().toLowerCase();
      return ALLOWED_EXTENSIONS.includes(ext);
    });
    if (validFiles.length > 0) {
      setUploadedFiles((prev) => [...prev, ...validFiles]);
    }
  }, []);

  const handleDrop = useCallback((e) => {
    e.preventDefault();
    setIsDragOver(false);
    handleFileSelect(e.dataTransfer.files);
  }, [handleFileSelect]);

  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    setIsDragOver(true);
  }, []);

  const handleDragLeave = useCallback((e) => {
    e.preventDefault();
    setIsDragOver(false);
  }, []);

  const removeFile = (index) => {
    setUploadedFiles((prev) => prev.filter((_, i) => i !== index));
  };

  // Apply parsed filename data to the cascade dropdowns
  const handleApplyParsed = useCallback(() => {
    if (!parsedFilename) return;
    const { manufacturer, series, model, engine } = parsedFilename.parts;
    
    // Fuzzy match helper: check if a string contains all keywords from search
    const fuzzyMatch = (haystack, needle) => {
      if (!needle) return false;
      const h = haystack.toLowerCase();
      const keywords = needle.toLowerCase().split(/\s+/);
      return keywords.every(kw => h.includes(kw));
    };
    
    // Match manufacturer
    const matchedMfr = allManufacturers.find(m => m.toLowerCase() === manufacturer.toLowerCase()) || '';
    
    let matchedSeries = '';
    let matchedModel = '';
    let matchedEngine = '';
    
    if (matchedMfr && vehicleData[matchedMfr]) {
      // Match series (e.g. "A6" matches "A6")
      const seriesKeys = Object.keys(vehicleData[matchedMfr]);
      matchedSeries = seriesKeys.find(s => s.toLowerCase() === series.toLowerCase()) || '';
      
      if (matchedSeries && vehicleData[matchedMfr][matchedSeries]) {
        // Match model fuzzy: parsed.model could be "F5 - 2016", DB has "F5 - 2016"
        const modelKeys = Object.keys(vehicleData[matchedMfr][matchedSeries]);
        matchedModel = modelKeys.find(m => {
          const mLower = m.toLowerCase().replace(/\s/g, '');
          const parsedModel = model.toLowerCase().replace(/\s/g, '');
          // Extract gen code and year from both
          const getGenYear = (str) => {
            const yearM = str.match(/(19|20)\d{2}/);
            const codeM = str.match(/([a-z]\d{1,2})/i);
            return { year: yearM?.[0] || '', code: codeM?.[1]?.toLowerCase() || '' };
          };
          const db = getGenYear(m);
          const parsed2 = getGenYear(model);
          // Match if gen code matches (or year matches)
          if (db.code && parsed2.code && db.code === parsed2.code) return true;
          if (db.year && parsed2.year && db.year === parsed2.year && db.code === parsed2.code) return true;
          return mLower.includes(parsedModel) || parsedModel.includes(mLower);
        }) || '';
        
        if (matchedModel) {
          // Match engine fuzzy: extract key parts (displacement + type) from parsed engine
          // e.g. "2 0 TDI EU6 190PS" -> look for entries containing "2.0 TDI" and "190"
          const engines = vehicleData[matchedMfr][matchedSeries][matchedModel];
          if (engine) {
            // Extract displacement (e.g. "2.0" or "3 0" -> "3.0")
            const dispMatch = engine.match(/(\d)[\s.]?(\d)/);
            const displacement = dispMatch ? `${dispMatch[1]}.${dispMatch[2]}` : '';
            // Extract fuel type (TDI, TSI, etc.)
            const fuelMatch = engine.match(/(TDI|TSI|TFSI|CDI|HDI|FSI|EcoBoost|EcoBlue|CDTI|CRDi|Turbo)/i);
            const fuel = fuelMatch ? fuelMatch[1] : '';
            // Extract power (190PS, 218PS, 190 hp, 190hp)
            const powerMatch = engine.match(/(\d{2,4})\s?(PS|hp|kW|bhp|pk)/i);
            const power = powerMatch ? powerMatch[1] : '';
            
            // Score all engines and pick the best match
            let bestScore = 0;
            let bestEngine = '';
            engines.forEach(e => {
              const eLower = e.toLowerCase();
              let score = 0;
              if (displacement && eLower.includes(displacement)) score++;
              if (fuel && eLower.includes(fuel.toLowerCase())) score++;
              if (power && eLower.includes(power)) score++;
              if (score > bestScore) {
                bestScore = score;
                bestEngine = e;
              }
            });
            if (bestScore >= 2) matchedEngine = bestEngine;
          }
        }
      }
    }
    
    // ECU: directly use the parsed ECU value
    let matchedEcu = parsedFilename.parts.ecu || '';
    
    setFormData(prev => ({
      ...prev,
      manufacturer: matchedMfr,
      series: matchedSeries,
      vehicleModel: matchedModel,
      engine: matchedEngine,
      ecu: matchedEcu,
      readingMethod: parsedFilename.parts.readMethod || prev.readingMethod,
      readingType: parsedFilename.parts.readType || prev.readingType,
    }));
  }, [parsedFilename]);

  // Compute which parsed badges have a match in the database
  const matchStatus = useMemo(() => {
    if (!parsedFilename) return {};
    const { manufacturer, series, model, engine, ecu } = parsedFilename.parts;
    const status = { manufacturer: false, series: false, model: false, engine: false, ecu: false };
    
    const matchedMfr = allManufacturers.find(m => m.toLowerCase() === manufacturer.toLowerCase());
    if (matchedMfr) {
      status.manufacturer = true;
      const seriesKeys = Object.keys(vehicleData[matchedMfr] || {});
      const matchedSeries = seriesKeys.find(s => s.toLowerCase() === series.toLowerCase());
      if (matchedSeries) {
        status.series = true;
        const modelKeys = Object.keys(vehicleData[matchedMfr][matchedSeries] || {});
        const getGenYear = (str) => {
          const codeM = str.match(/([a-z]\d{1,2})/i);
          return codeM?.[1]?.toLowerCase() || '';
        };
        const parsedCode = getGenYear(model);
        const matchedModel = modelKeys.find(m => parsedCode && getGenYear(m) === parsedCode);
        if (matchedModel) {
          status.model = true;
          const engines = vehicleData[matchedMfr][matchedSeries][matchedModel] || [];
          const dispMatch = engine.match(/(\d)[\s.]?(\d)/);
          const displacement = dispMatch ? `${dispMatch[1]}.${dispMatch[2]}` : '';
          const fuelMatch = engine.match(/(TDI|TSI|TFSI|CDI|HDI|FSI|EcoBoost|EcoBlue|CDTI|CRDi|Turbo)/i);
          const fuel = fuelMatch ? fuelMatch[1]?.toLowerCase() : '';
          const powerMatch = engine.match(/(\d{2,4})\s?(PS|hp|kW|bhp|pk)/i);
          const power = powerMatch ? powerMatch[1] : '';
          const hasEngineMatch = engines.some(e => {
            const el = e.toLowerCase();
            let score = 0;
            if (displacement && el.includes(displacement)) score++;
            if (fuel && el.includes(fuel)) score++;
            if (power && el.includes(power)) score++;
            return score >= 2;
          });
          if (hasEngineMatch) status.engine = true;
        }
      }
      // ECU match
      if (ecu) {
        const ecuOptions = getEcuOptions(matchedMfr, engine);
        const ecuCode = ecu.split(' ').find(p => /^(EDC|MED|PCR|MD1|MG1|SID|DCM|Simos)/i.test(p));
        if (ecuCode && ecuOptions.some(e => e.toLowerCase().includes(ecuCode.toLowerCase()))) {
          status.ecu = true;
        }
      }
    }
    return status;
  }, [parsedFilename]);

  // Performance data based on selected engine
  const manufacturerOptions = vehicleApi.manufacturers.length > 0
    ? vehicleApi.manufacturers
    : (vehicleTypeKey === 'pkw' ? allManufacturers : []);
  const seriesOptions = vehicleApi.series.length > 0
    ? vehicleApi.series
    : (formData.manufacturer && vehicleData[formData.manufacturer] ? Object.keys(vehicleData[formData.manufacturer]) : []);
  const modelOptions = vehicleApi.models.length > 0
    ? vehicleApi.models
    : (formData.manufacturer && formData.series && vehicleData[formData.manufacturer]?.[formData.series]
      ? Object.keys(vehicleData[formData.manufacturer][formData.series])
      : []);
  const engineOptions = vehicleApi.engines.length > 0
    ? vehicleApi.engines
    : (formData.manufacturer && formData.series && formData.vehicleModel && vehicleData[formData.manufacturer]?.[formData.series]?.[formData.vehicleModel]
      ? vehicleData[formData.manufacturer][formData.series][formData.vehicleModel]
      : []);
  const ecuSelectOptions = useMemo(() => {
    if (vehicleApi.ecuOptions.length > 0) {
      return vehicleApi.ecuOptions;
    }

    return getEcuOptions(formData.manufacturer, formData.engine);
  }, [vehicleApi.ecuOptions, formData.manufacturer, formData.engine]);

  useEffect(() => {
    if (!formData.engine) {
      return;
    }

    if (!formData.ecu && ecuSelectOptions.length > 0) {
      setFormData((prev) => {
        if (prev.ecu || !prev.engine) {
          return prev;
        }

        return {
          ...prev,
          ecu: ecuSelectOptions[0],
        };
      });
    }
  }, [formData.engine, formData.ecu, ecuSelectOptions]);

  const submitOrder = async () => {
    setSubmitError('');
    setPurchaseError('');

    const file = uploadedFiles[0];
    if (!file) {
      setSubmitError(language === 'de' ? 'Bitte zuerst eine Datei hochladen.' : 'Please upload a file first.');
      return;
    }

    if (creditQuote?.missingCredits > 0) {
      setSubmitError(language === 'de' ? 'Für diesen Auftrag müssen zuerst Credits gekauft werden.' : 'Please buy credits before submitting this order.');
      return;
    }

    setIsSubmittingOrder(true);

    try {
      const result = await postOrderJson('/auftrag/submit', buildOrderPayload(file));

      if (typeof result?.credits === 'number' && Number.isFinite(result.credits)) {
        setCurrentCredits(result.credits);
        setBootstrapCredits(result.credits);
      }

      if (parkedOrderId > 0) {
        setParkedOrderId(0);
        setParkedOrderDetail(null);
        setParkMessage('');
        setParkError('');
      }

      navigateToLiveOrder(result, file);
    } catch (error) {
      const message = error instanceof Error && error.message ? error.message : '';
      setSubmitError(
        message
          ? message
          : (language === 'de' ? 'Auftrag konnte nicht gespeichert werden.' : 'Could not save order.')
      );
    } finally {
      setIsSubmittingOrder(false);
    }
  };

  const handleStartOrderTopup = () => {
    setSubmitError('');
    setPurchaseError('');

    if (!creditQuote?.missingCredits) {
      return;
    }

    if (!orderFlowCreditTopupConfig.configured || !orderFlowCreditTopupConfig.clientId || !orderFlowCreditTopupConfig.csrfToken) {
      setPurchaseError(language === 'de' ? 'PayPal ist aktuell nicht verfügbar.' : 'PayPal is currently unavailable.');
      return;
    }

    setIsPaypalOpen(true);
  };

  const wizardSteps = [
    { id: 1, icon: Upload, label: language === 'de' ? 'File & Lesegerät' : 'File & Device' },
    { id: 2, icon: CarProfile, label: language === 'de' ? 'Fahrzeug + Tuning' : 'Vehicle + Tuning' },
    { id: 3, icon: CheckCircle, label: language === 'de' ? 'Übersicht' : 'Review' },
  ];
  const progress = (currentStep / wizardSteps.length) * 100;

  return (
    <DashboardLayout>
      <div className="max-w-6xl mx-auto space-y-6">
        {/* Step Progress Indicator */}
        <Card className="bg-card border-border" data-testid="wizard-progress-card">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              {wizardSteps.map((step, index) => {
                const Icon = step.icon;
                const isActive = currentStep === step.id;
                const isCompleted = currentStep > step.id;
                return (
                  <div key={step.id} className="flex items-center">
                    <div className="flex flex-col items-center">
                      <div
                        className={cn(
                          "w-12 h-12 rounded-sm flex items-center justify-center transition-colors duration-200",
                          isActive && "bg-primary text-white",
                          isCompleted && "bg-green-500/20 text-green-400 border border-green-500/30",
                          !isActive && !isCompleted && "bg-secondary text-muted-foreground"
                        )}
                      >
                        {isCompleted ? (
                          <CheckCircle weight="fill" className="w-6 h-6" />
                        ) : (
                          <Icon weight={isActive ? "fill" : "regular"} className="w-6 h-6" />
                        )}
                      </div>
                      <span className={cn(
                        "text-xs mt-2 font-medium",
                        isActive ? "text-foreground" : "text-muted-foreground"
                      )}>
                        {step.label}
                      </span>
                    </div>
                    {index < wizardSteps.length - 1 && (
                      <div className={cn(
                        "h-0.5 w-16 md:w-24 mx-2",
                        isCompleted ? "bg-green-500/50" : "bg-border"
                      )} />
                    )}
                  </div>
                );
              })}
            </div>
            <Progress value={progress} className="h-1" />
          </CardContent>
        </Card>

        {parkedOrderId > 0 && (
          <Card className="border border-amber-500/30 bg-amber-500/10" data-testid="parked-order-alert">
            <CardContent className="p-4">
              {isLoadingParkedOrder ? (
                <p className="text-sm text-muted-foreground">
                  {language === 'de' ? 'Geparkten Auftrag wird geladen...' : 'Loading parked order...'}
                </p>
              ) : parkedOrderError ? (
                <p className="text-sm text-red-400" data-testid="parked-order-error">{parkedOrderError}</p>
              ) : parkedOrderDetail ? (
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                  <div className="space-y-1">
                    <p className="text-sm font-semibold text-foreground">
                      {language === 'de' ? 'Du bearbeitest einen geparkten Auftrag.' : 'You are editing a parked order.'}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {language === 'de' ? 'Auftragsnummer' : 'Order'}: {parkedOrderDetail.orderNumber}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {language === 'de' ? 'Fahrzeug' : 'Vehicle'}:{' '}
                      {[
                        parkedOrderDetail.manufacturer,
                        parkedOrderDetail.model,
                        parkedOrderDetail.engine,
                      ].filter(Boolean).join(' ') || '—'}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {language === 'de' ? 'Zuletzt aktualisiert' : 'Last updated'}:{' '}
                      {parkedOrderDetail.updatedAt ? new Date(parkedOrderDetail.updatedAt).toLocaleString(language === 'de' ? 'de-DE' : 'en-US') : '-'}
                    </p>
                  </div>
                  <Button variant="outline" size="sm" className="whitespace-nowrap" onClick={clearParkedOrderSelection}>
                    {language === 'de' ? 'Neuen Auftrag beginnen' : 'Start new order'}
                  </Button>
                </div>
              ) : (
                <div className="flex items-center justify-between">
                  <p className="text-sm text-muted-foreground">
                    {language === 'de' ? 'Geparkter Auftrag konnte nicht geladen werden.' : 'Parked order could not be loaded.'}
                  </p>
                  <Button variant="outline" size="sm" className="whitespace-nowrap" onClick={clearParkedOrderSelection}>
                    {language === 'de' ? 'Geparkten Auftrag entfernen' : 'Clear parked order'}
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>
        )}

        {/* Header */}
        <div className="flex items-end justify-between">
          <div>
            <h1 className="font-heading text-2xl font-bold text-foreground tracking-tight">
              {currentStep === 1 ? t('stepTitle') : currentStep === 2 ? t('step2Title') : t('step4Title')}
            </h1>
            <div className="w-10 h-1 bg-green-500 rounded-full mt-2" />
          </div>
          <span className="text-primary font-heading font-bold text-sm tracking-widest" data-testid="step-indicator">
            {t('step')} {String(currentStep).padStart(2, '0')}
          </span>
        </div>

        {/* ====== STEP 1: File & Lesegerät ====== */}
        {currentStep === 1 && (<>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Left Column - Form Fields */}
          <Card className="bg-card border-border" data-testid="wizard-form-card">
            <CardContent className="p-6 space-y-7">
              {/* Tuning Tool / Lesegerät */}
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Wrench weight="bold" className="w-3.5 h-3.5" />
                  {t('tuningTool')}
                </label>
                <Select
                  value={formData.readingDevice}
                  onValueChange={(val) => setFormData((prev) => ({ ...prev, readingDevice: val }))}
                >
                  <SelectTrigger
                    className="w-full h-12 bg-secondary/50 border-border text-sm"
                    data-testid="reading-device-select"
                  >
                    <SelectValue placeholder={t('selectDevice')}>
                      {formData.readingDevice && (() => {
                        const selected = readingDevices.find((e) =>
                          e.group
                            ? e.options.some((o) => `${e.group} - ${o}` === formData.readingDevice)
                            : e.options.includes(formData.readingDevice)
                        );
                        const logo = selected?.logo;
                        const label = formData.readingDevice.includes(' - ')
                          ? formData.readingDevice.split(' - ')[1]
                          : formData.readingDevice;
                        return (
                          <span className="flex items-center gap-2.5">
                            {logo && <img src={logo} alt="" className="h-4 w-16 object-contain object-left" />}
                            {label}
                          </span>
                        );
                      })()}
                    </SelectValue>
                  </SelectTrigger>
                  <SelectContent className="bg-card border-border">
                    {readingDevices.map((entry, idx) =>
                      entry.group ? (
                        <SelectGroup key={idx}>
                          {idx > 0 && <div className="h-px bg-border mx-2 my-1.5" />}
                          <SelectLabel className="flex items-center gap-2.5 text-muted-foreground font-semibold text-xs uppercase tracking-wider py-2">
                            {entry.logo ? (
                              <img src={entry.logo} alt={entry.group} className="h-4 w-16 object-contain object-left" />
                            ) : (
                              entry.group
                            )}
                          </SelectLabel>
                          {entry.options.map((opt) => (
                            <SelectItem key={opt} value={`${entry.group} - ${opt}`}>
                              {opt}
                            </SelectItem>
                          ))}
                        </SelectGroup>
                      ) : (
                        <div key={idx}>
                          {idx > 0 && <div className="h-px bg-border mx-2 my-1.5" />}
                          {entry.options.map((opt) => (
                            <SelectItem key={opt} value={opt}>
                              <span className="flex items-center gap-2.5">
                                {entry.logo ? (
                                  <img src={entry.logo} alt="" className="h-4 w-16 object-contain object-left" />
                                ) : (
                                  opt
                                )}
                              </span>
                            </SelectItem>
                          ))}
                        </div>
                      )
                    )}
                  </SelectContent>
                </Select>
              </div>

              {/* Lesemethode */}
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <PlugsConnected weight="bold" className="w-3.5 h-3.5" />
                  {t('readingMethod')}
                </label>
                <div className="flex gap-2" data-testid="reading-method-group">
                  {[
                    { label: 'OBD', icon: `${process.env.PUBLIC_URL}/logos/obd.svg` },
                    { label: 'Bench', icon: `${process.env.PUBLIC_URL}/logos/bench.svg` },
                    { label: 'Boot', icon: `${process.env.PUBLIC_URL}/logos/boot.svg` },
                  ].map((method) => (
                    <ToggleOption
                      key={method.label}
                      label={method.label}
                      icon={method.icon}
                      selected={formData.readingMethod === method.label}
                      onClick={() => setFormData((prev) => ({ ...prev, readingMethod: method.label }))}
                      testId={`method-${method.label.toLowerCase()}`}
                    />
                  ))}
                </div>
              </div>

              {/* Leseart */}
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <BookOpen weight="bold" className="w-3.5 h-3.5" />
                  {t('readingType')}
                </label>
                <div className="flex gap-2" data-testid="reading-type-group">
                  {[
                    { label: 'Virtuell', Icon: CloudArrowDown },
                    { label: 'Full Read', Icon: HardDrives },
                  ].map((type) => (
                    <ToggleOption
                      key={type.label}
                      label={type.label}
                      IconComponent={type.Icon}
                      selected={formData.readingType === type.label}
                      onClick={() => setFormData((prev) => ({ ...prev, readingType: type.label }))}
                      testId={`type-${type.label.toLowerCase().replace(' ', '-')}`}
                    />
                  ))}
                </div>
              </div>

              {/* Master / Slave */}
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <CircleHalf weight="bold" className="w-3.5 h-3.5" />
                  {t('masterSlave')}
                </label>
                <div className="flex gap-2" data-testid="master-slave-group">
                  {[
                    { label: 'Slave', Icon: Link },
                    { label: 'Master', Icon: Crown },
                  ].map((ms) => (
                    <ToggleOption
                      key={ms.label}
                      label={ms.label}
                      IconComponent={ms.Icon}
                      selected={formData.masterSlave === ms.label}
                      onClick={() => setFormData((prev) => ({ ...prev, masterSlave: ms.label }))}
                      testId={`ms-${ms.label.toLowerCase()}`}
                    />
                  ))}
                </div>
              </div>

              {/* Priorität */}
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Timer weight="bold" className="w-3.5 h-3.5" />
                  {t('priority')}
                </label>
                <div className="flex gap-2" data-testid="priority-group">
                  <ToggleOption
                    label="Normal"
                    selected={formData.priority === 'Normal'}
                    onClick={() => setFormData((prev) => ({ ...prev, priority: 'Normal' }))}
                    testId="priority-normal"
                  />
                  <ToggleOption
                    label="Express"
                    extra="+49€"
                    selected={formData.priority === 'Express'}
                    onClick={() => setFormData((prev) => ({ ...prev, priority: 'Express' }))}
                    testId="priority-express"
                  />
                  <ToggleOption
                    label="Sofort"
                    extra="+99€"
                    selected={formData.priority === 'Sofort'}
                    onClick={() => setFormData((prev) => ({ ...prev, priority: 'Sofort' }))}
                    testId="priority-sofort"
                  />
                </div>
              </div>

              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Lightning weight="bold" className="w-3.5 h-3.5" />
                  {t('matchTestMode')}
                </label>
                <div className="flex gap-2" data-testid="winols-match-mode-group">
                  <ToggleOption
                    label={t('matchPositive')}
                    selected={isAutoMatchTest}
                    onClick={() => setIsAutoMatchTest(true)}
                    testId="winols-match-positive"
                  />
                  <ToggleOption
                    label={t('matchNegative')}
                    selected={!isAutoMatchTest}
                    onClick={() => setIsAutoMatchTest(false)}
                    testId="winols-match-negative"
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Right Column - File Upload */}
          <Card className="bg-card border-border" data-testid="wizard-upload-card">
            <CardContent className="p-6 h-full flex flex-col">
              {/* Uploaded files list */}
              {uploadedFiles.length > 0 && (
                <div className="space-y-2 mb-4">
                  {uploadedFiles.map((file, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between px-3 py-2 bg-secondary/50 border border-border rounded-sm"
                      data-testid={`uploaded-file-${index}`}
                    >
                      <div className="flex items-center gap-2">
                        <FileIcon weight="fill" className="w-4 h-4 text-primary" />
                        <span className="text-sm text-foreground font-medium truncate max-w-[200px]">
                          {file.name}
                        </span>
                        <span className="text-xs text-muted-foreground">
                          ({(file.size / 1024).toFixed(1)} KB)
                        </span>
                      </div>
                      <button
                        onClick={() => removeFile(index)}
                        className="text-muted-foreground hover:text-destructive transition-colors"
                        data-testid={`remove-file-${index}`}
                      >
                        <X weight="bold" className="w-4 h-4" />
                      </button>
                    </div>
                  ))}
                </div>
              )}

              {/* Drag & Drop Zone */}
              <div
                className={cn(
                  "flex-1 min-h-[280px] border-2 border-dashed rounded-sm flex flex-col items-center justify-center gap-4 cursor-pointer transition-all duration-200",
                  isDragOver
                    ? "border-primary bg-primary/5"
                    : "border-border hover:border-muted-foreground/50"
                )}
                onDrop={handleDrop}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onClick={() => fileInputRef.current?.click()}
                data-testid="file-drop-zone"
              >
                <CloudArrowUp
                  weight="thin"
                  className={cn(
                    "w-14 h-14 transition-colors",
                    isDragOver ? "text-primary" : "text-muted-foreground"
                  )}
                />
                <div className="text-center">
                  <p className="font-heading font-semibold text-foreground">
                    {t('dropFile')}
                  </p>
                  <p className="text-sm text-muted-foreground mt-1">
                    {t('orClick')}
                  </p>
                </div>
                <p className="text-xs text-muted-foreground tracking-wide">
                  {ALLOWED_EXTENSIONS.join(' ')}
                </p>

                <input
                  ref={fileInputRef}
                  type="file"
                  className="hidden"
                  accept={ALLOWED_EXTENSIONS.join(',')}
                  multiple
                  onChange={(e) => {
                    handleFileSelect(e.target.files);
                    e.target.value = '';
                  }}
                  data-testid="file-input"
                />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Comment Section (Full Width) */}
        <Card className="bg-card border-border" data-testid="wizard-comment-card">
          <CardContent className="p-6">
            <label className="block text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
              {t('comment')}
            </label>
            <Textarea
              value={formData.comment}
              onChange={(e) => setFormData((prev) => ({ ...prev, comment: e.target.value }))}
              placeholder={t('commentPlaceholder')}
              className="min-h-[120px] bg-secondary/30 border-border resize-y"
              data-testid="comment-textarea"
            />
          </CardContent>
        </Card>

        {/* Step 1 Navigation */}
        <div className="flex justify-end">
          <Button
            className="btn-gradient text-white font-semibold px-8 py-3 h-auto"
            data-testid="wizard-next-btn"
            onClick={() => setCurrentStep(2)}
          >
            {t('nextStep')}
            <ArrowRight weight="bold" className="w-4 h-4 ml-2" />
          </Button>
        </div>
        </>)}

        {/* ====== STEP 2: Fahrzeugauswahl ====== */}
        {currentStep === 2 && (<>
          {/* Filename Display with parsed badges */}
          {parsedFilename && (
            <Card className="bg-card border-border" data-testid="filename-display-card">
              <CardContent className="p-5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                  <FileIcon weight="bold" className="w-3.5 h-3.5" />
                  {t('uploadedFile')}
                </label>
                <div className="flex items-center gap-3 bg-secondary/50 border border-border rounded-sm px-4 py-3 mb-4">
                  <FileIcon weight="fill" className="w-5 h-5 text-primary flex-shrink-0" />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm font-mono text-foreground truncate">{parsedFilename.original}</p>
                    {parsedFilename.readable && (
                      <p className="text-xs text-muted-foreground mt-0.5">
                        {t('parsedAs')}: <span className="text-foreground font-medium">{parsedFilename.readable}</span>
                      </p>
                    )}
                  </div>
                </div>
                {/* Parsed badges */}
                <div className="flex flex-wrap items-center gap-3">
                  {parsedFilename.parts.manufacturer && (
                    <div className={cn("flex items-center gap-2 rounded-sm px-4 py-2", matchStatus.manufacturer ? "bg-green-500/10 border border-green-500/40" : "bg-secondary/80 border border-border")}>
                      <Car weight="bold" className={cn("w-4 h-4", matchStatus.manufacturer ? "text-green-500" : "text-muted-foreground")} />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('manufacturer')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.manufacturer}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.series && (
                    <div className={cn("flex items-center gap-2 rounded-sm px-4 py-2", matchStatus.series ? "bg-green-500/10 border border-green-500/40" : "bg-secondary/80 border border-border")}>
                      <Tag weight="bold" className={cn("w-4 h-4", matchStatus.series ? "text-green-500" : "text-muted-foreground")} />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('series')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.series}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.model && (
                    <div className={cn("flex items-center gap-2 rounded-sm px-4 py-2", matchStatus.model ? "bg-green-500/10 border border-green-500/40" : "bg-secondary/80 border border-border")}>
                      <Calendar weight="bold" className={cn("w-4 h-4", matchStatus.model ? "text-green-500" : "text-muted-foreground")} />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('vehicleModel')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.model}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.engine && (
                    <div className={cn("flex items-center gap-2 rounded-sm px-4 py-2", matchStatus.engine ? "bg-green-500/10 border border-green-500/40" : "bg-secondary/80 border border-border")}>
                      <Engine weight="bold" className={cn("w-4 h-4", matchStatus.engine ? "text-green-500" : "text-muted-foreground")} />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('engine')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.engine}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.ecu && (
                    <div className={cn("flex items-center gap-2 rounded-sm px-4 py-2", matchStatus.ecu ? "bg-green-500/10 border border-green-500/40" : "bg-secondary/80 border border-border")}>
                      <GasPump weight="bold" className={cn("w-4 h-4", matchStatus.ecu ? "text-green-500" : "text-muted-foreground")} />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('ecu')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.ecu}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.readMethod && (
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-4 py-2">
                      <PlugsConnected weight="bold" className="w-4 h-4 text-muted-foreground" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('readMethodBadge')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.readMethod}</p>
                      </div>
                    </div>
                  )}
                  {parsedFilename.parts.readType && (
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-4 py-2">
                      <BookOpen weight="bold" className="w-4 h-4 text-muted-foreground" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('readTypeBadge')}</p>
                        <p className="text-sm font-semibold text-foreground">{parsedFilename.parts.readType}</p>
                      </div>
                    </div>
                  )}
                </div>
                {/* Übernehmen Button */}
                <div className="mt-3">
                  <Button
                    onClick={handleApplyParsed}
                    className="btn-gradient text-white font-semibold px-5 py-2 h-auto"
                    data-testid="apply-parsed-btn"
                  >
                    <ArrowRight weight="bold" className="w-4 h-4 mr-1.5" />
                    {t('applyParsed')}
                  </Button>
                </div>
              </CardContent>
            </Card>
          )}

          {/* Cascade Vehicle Selection */}
          <Card className="bg-card border-border" data-testid="vehicle-cascade-card">
            <CardContent className="p-6 space-y-6">
              <div className="space-y-2.5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <HardDrives weight="bold" className="w-3.5 h-3.5" />
                  {t('vehicleType')}
                </label>
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                  {vehicleTypeItems.map((item) => {
                    const isSelected = formData.vehicleType === item.name;
                    return (
                      <button
                        key={item.id || item.name}
                        type="button"
                        onClick={() => setFormData((prev) => ({ ...prev, vehicleType: item.name }))}
                        className={cn(
                          "flex items-center justify-center gap-2 rounded-sm border px-4 py-3 text-sm font-semibold transition-all",
                          isSelected
                            ? "bg-primary/10 border-primary text-foreground"
                            : "bg-secondary/50 border-border text-muted-foreground hover:bg-secondary hover:text-foreground"
                        )}
                      >
                        <span>{item.name}</span>
                      </button>
                    );
                  })}
                </div>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                {/* 1. Hersteller */}
                <div className="space-y-2.5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                    <Car weight="bold" className="w-3.5 h-3.5" />
                    {t('manufacturer')}
                  </label>
                  <Select
                    value={formData.manufacturer}
                    onValueChange={(val) => setFormData(prev => ({ ...prev, manufacturer: val, series: '', vehicleModel: '', engine: '', ecu: '' }))}
                  >
                    <SelectTrigger className="w-full h-12 bg-secondary/50 border-border text-sm" data-testid="select-manufacturer">
                      <SelectValue placeholder={t('manufacturerPlaceholder')} />
                    </SelectTrigger>
                    <SelectContent className="bg-card border-border">
                      {manufacturerOptions.map((m) => (
                        <SelectItem key={m} value={m}>{m}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* 2. Baureihe */}
                <div className="space-y-2.5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                    <Tag weight="bold" className="w-3.5 h-3.5" />
                    {t('series')}
                  </label>
                  <Select
                    value={formData.series}
                    onValueChange={(val) => setFormData(prev => ({ ...prev, series: val, vehicleModel: '', engine: '', ecu: '' }))}
                    disabled={!formData.manufacturer}
                  >
                    <SelectTrigger className="w-full h-12 bg-secondary/50 border-border text-sm" data-testid="select-series">
                      <SelectValue placeholder={formData.manufacturer ? t('seriesPlaceholder') : '—'} />
                    </SelectTrigger>
                    <SelectContent className="bg-card border-border">
                      {seriesOptions.map((s) => (
                        <SelectItem key={s} value={s}>{s}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* 3. Modell */}
                <div className="space-y-2.5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                    <Calendar weight="bold" className="w-3.5 h-3.5" />
                    {t('vehicleModel')}
                  </label>
                  <Select
                    value={formData.vehicleModel}
                    onValueChange={(val) => setFormData(prev => ({ ...prev, vehicleModel: val, engine: '', ecu: '' }))}
                    disabled={!formData.series}
                  >
                    <SelectTrigger className="w-full h-12 bg-secondary/50 border-border text-sm" data-testid="select-model">
                      <SelectValue placeholder={formData.series ? t('vehicleModelPlaceholder') : '—'} />
                    </SelectTrigger>
                    <SelectContent className="bg-card border-border">
                      {modelOptions.map((m) => (
                        <SelectItem key={m} value={m}>{m}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* 4. Motor */}
                <div className="space-y-2.5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                    <Engine weight="bold" className="w-3.5 h-3.5" />
                    {t('engine')}
                  </label>
                  <Select
                    value={formData.engine}
                    onValueChange={(val) => setFormData(prev => ({ ...prev, engine: val, ecu: '' }))}
                    disabled={!formData.vehicleModel}
                  >
                    <SelectTrigger className="w-full h-12 bg-secondary/50 border-border text-sm" data-testid="select-engine">
                      <SelectValue placeholder={formData.vehicleModel ? t('enginePlaceholder') : '—'} />
                    </SelectTrigger>
                    <SelectContent className="bg-card border-border">
                      {engineOptions.map((e) => (
                        <SelectItem key={e} value={e}>{e}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* 5. ECU / Steuergerät */}
                <div className="space-y-2.5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                    <GasPump weight="bold" className="w-3.5 h-3.5" />
                    {t('ecu')}
                  </label>
                  <Select
                    value={formData.ecu}
                    onValueChange={(val) => setFormData(prev => ({ ...prev, ecu: val }))}
                    disabled={!formData.manufacturer}
                  >
                    <SelectTrigger className="w-full h-12 bg-secondary/50 border-border text-sm" data-testid="select-ecu">
                      <SelectValue placeholder={formData.manufacturer ? t('ecuPlaceholder') : '—'} />
                    </SelectTrigger>
                    <SelectContent className="bg-card border-border">
                      {parsedFilename?.parts?.ecu && (
                        <SelectItem value={parsedFilename.parts.ecu}>{parsedFilename.parts.ecu}</SelectItem>
                      )}
                      {ecuSelectOptions.map((ecuName) => (
                        <SelectItem key={ecuName} value={ecuName}>{ecuName}</SelectItem>
                      ))}
                      <SelectItem value="ZF AL552">ZF AL552</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Step 2 Navigation */}
          <div className="hidden">
            <Button
              variant="outline"
              className="border-border hover:bg-secondary font-semibold px-6 py-3 h-auto"
              onClick={() => setCurrentStep(1)}
              data-testid="wizard-prev-btn"
            >
              <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
              {t('prevStep')}
            </Button>
            <Button
              className="btn-gradient text-white font-semibold px-8 py-3 h-auto"
              data-testid="wizard-next-btn-step2"
              onClick={() => setCurrentStep(3)}
            >
              {t('nextStep3')}
              <ArrowRight weight="bold" className="w-4 h-4 ml-2" />
            </Button>
          </div>
        </>)}

        {/* ====== STEP 2: Tuning ====== */}
        {currentStep === 2 && (<>
          <div className="space-y-6">
          <Card className="bg-card border-border" data-testid="vehicle-summary-card">
            <CardContent className="p-5">
              <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                <CarProfile weight="bold" className="w-3.5 h-3.5" />
                {t('selectedVehicle')}
              </label>
              <div className="flex flex-wrap gap-2.5">
                {[formData.vehicleType, formData.manufacturer, formData.series, formData.vehicleModel, formData.engine, formData.ecu]
                  .filter(Boolean)
                  .map((value) => (
                    <span key={value} className="rounded-sm border border-border bg-secondary/60 px-3 py-1.5 text-xs font-semibold text-foreground">
                      {value}
                    </span>
                  ))}
              </div>
            </CardContent>
          </Card>

          <div className="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
          <Card className={cn("bg-card border-border", vehicleTypeKey !== 'lkw' && perfData ? "xl:col-span-6" : "xl:col-span-12")}>
            <CardContent className="p-6 space-y-6">
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-4">
              <Lightning weight="bold" className="w-3.5 h-3.5" />
              {t('selectTuning')}
            </label>
            <div className="grid grid-cols-1 gap-3">
              {displayTuningStages.map((stage) => {
                const Icon = stage.Icon;
                const isSelected = selectedStage === stage.id;
                const stageDescription = (() => {
                  switch (stage.id) {
                    case 'stage1':
                      return language === 'de' ? 'Optimierte Kennfelder' : 'Optimized maps';
                    case 'stage2':
                      return language === 'de' ? 'Maximale Performance' : 'Maximum performance';
                    case 'eco':
                      return language === 'de' ? 'Verbrauch optimiert' : 'Consumption optimized';
                    case 'gearbox':
                      return language === 'de' ? 'Schaltoptimierung' : 'Shift optimization';
                    default:
                      return language === 'de' ? 'Tuning waehlen' : 'Choose tuning';
                  }
                })();
                const colorMap = {
                  primary: { bg: 'bg-[#18090d] border-[#7a1f2d]', icon: 'text-primary' },
                  orange: { bg: 'bg-[#1d130c] border-orange-500/60', icon: 'text-orange-500' },
                  green: { bg: 'bg-[#0d1610] border-green-500/50', icon: 'text-green-500' },
                  blue: { bg: 'bg-[#0c1320] border-blue-500/50', icon: 'text-blue-500' },
                  muted: { bg: 'bg-card border-border hover:border-muted-foreground/50', icon: 'text-muted-foreground' },
                };
                const colors = isSelected ? colorMap[stage.color] : { bg: 'bg-card border-border hover:border-muted-foreground/50', icon: 'text-muted-foreground' };
                return (
                  <button
                    key={stage.id}
                    type="button"
                    onClick={() => {
                      if (isSelected) {
                        setSelectedStage('');
                        setSelectedGearboxStage('');
                      } else {
                        setSelectedStage(stage.id);
                        setSelectedGearboxStage('');
                      }
                    }}
                    data-testid={`stage-${stage.id}`}
                    className={cn(
                      "relative flex items-center justify-between gap-4 p-5 rounded-sm border transition-all duration-200 cursor-pointer text-left min-h-[96px]",
                      colors.bg
                    )}
                  >
                    <div className="flex items-center gap-4 min-w-0">
                      <div className={cn("w-12 h-12 rounded-sm flex items-center justify-center bg-background/50 shrink-0 border border-border/60", isSelected && "bg-background/80 border-primary/30")}>
                        <Icon weight={isSelected ? "fill" : "regular"} className={cn("w-6 h-6 transition-colors", colors.icon)} />
                      </div>
                      <div className="min-w-0">
                        <span className="block text-base font-bold text-foreground">{stage.name[language] || stage.name.de}</span>
                        <span className="block text-sm text-muted-foreground mt-1">
                          {stageDescription}
                        </span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3 pr-2 shrink-0">
                      {stage.credits > 0 && (
                        <span className="text-xs font-semibold text-muted-foreground whitespace-nowrap">
                          {stage.credits} Credits
                        </span>
                      )}
                      {isSelected && (
                        <CheckCircle weight="fill" className="w-6 h-6 text-green-500" />
                      )}
                    </div>
                  </button>
                );
              })}
            </div>
          </div>

          {/* Gearbox Stages (when Getriebe is selected) */}
          {selectedStage === 'gearbox' && (
            <div>
              <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-4">
                <Gear weight="bold" className="w-3.5 h-3.5" />
                {t('selectGearboxStage')}
              </label>
              {/* Gearbox manufacturer warning */}
              <div className="flex items-start gap-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-sm mb-4">
                <Warning weight="fill" className="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-yellow-400">{t('gearboxNote')}</p>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {gearboxStages.map((gs) => {
                  const isSelected = selectedGearboxStage === gs.id;
                  return (
                    <button
                      key={gs.id}
                      type="button"
                      onClick={() => setSelectedGearboxStage(isSelected ? '' : gs.id)}
                      data-testid={`gearbox-${gs.id}`}
                      className={cn(
                        "relative flex flex-col items-center gap-3 p-5 rounded-sm border-2 transition-all duration-200 cursor-pointer",
                        isSelected
                          ? "bg-blue-500/10 border-blue-500"
                          : "bg-card border-border hover:border-muted-foreground/50"
                      )}
                    >
                      {isSelected && (
                        <div className="absolute top-2 right-2">
                          <CheckCircle weight="fill" className="w-4 h-4 text-green-500" />
                        </div>
                      )}
                      <Gear weight={isSelected ? "fill" : "regular"} className={cn("w-7 h-7", isSelected ? "text-blue-500" : "text-muted-foreground")} />
                      <span className="text-base font-bold text-foreground">{gs.name[language]}</span>
                      <span className={cn(
                        "text-sm font-bold px-4 py-1.5 rounded-full",
                        isSelected ? "bg-blue-500 text-white" : "bg-secondary text-muted-foreground"
                      )}>
                        {gs.credits} Credits
                      </span>
                    </button>
                  );
                })}
              </div>
            </div>
          )}

          {previewEcuOptions.length > 0 && (
            <div data-testid="vehicle-api-ecu-options-card">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                  <Sliders weight="bold" className="w-3.5 h-3.5" />
                  {language === 'de' ? 'Passende ECU Optionen' : 'Matching ECU options'}
                </label>
                <div className="flex flex-wrap gap-2">
                  {previewEcuOptions.map((opt) => (
                    <span
                      key={opt.id}
                      className={cn(
                        "rounded-sm border px-2.5 py-1.5 text-[11px] font-semibold",
                        opt.included
                          ? "border-green-500/30 bg-green-500/8 text-green-300"
                          : "border-border bg-secondary/60 text-foreground"
                      )}
                    >
                      {opt.name?.[language] || opt.name?.de}
                    </span>
                  ))}
                </div>
            </div>
          )}

          {(selectedStage || selectedOptions.length > 0 || selectedGearboxStage) && (
            <div className="rounded-sm border border-primary/30 bg-primary/5 px-4 py-3" data-testid="credits-total-card">
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold text-muted-foreground tracking-widest uppercase">{t('totalCredits')}</span>
                <span className="text-2xl font-bold text-primary">
                  {(() => {
                    let total = 0;
                    if (selectedStage === 'gearbox') {
                      total += gearboxStages.find(gs => gs.id === selectedGearboxStage)?.credits || 0;
                    } else {
                      total += displayTuningStages.find(s => s.id === selectedStage)?.credits || 0;
                      total += selectedOptions.reduce((sum, id) => {
                        const opt = activeSelectableOptions.find(o => o.id === id);
                        return sum + (opt?.credits || 0);
                      }, 0);
                    }
                    return total;
                  })()}{' '}
                  <span className="text-sm font-medium text-muted-foreground">Credits</span>
                </span>
              </div>
            </div>
          )}
          </CardContent>
          </Card>

          {vehicleTypeKey !== 'lkw' && perfData && (
          <Card className="bg-card border-border xl:col-span-6" data-testid="performance-data-card">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Lightning weight="bold" className="w-3.5 h-3.5" />
                  {t('performanceTitle')}
                </label>
                <div className="flex gap-2">
                  <button
                    type="button"
                    onClick={() => setShowPs(p => !p)}
                    data-testid="toggle-ps"
                    className={cn(
                      "flex items-center gap-1.5 px-3 py-1.5 rounded-sm text-xs font-bold border transition-all",
                      showPs
                        ? "bg-[#8B2635]/15 border-[#8B2635]/50 text-[#e74c3c]"
                        : "bg-secondary/50 border-border text-muted-foreground hover:text-foreground"
                    )}
                  >
                    <span className={cn("w-2 h-2 rounded-full", showPs ? "bg-[#e74c3c]" : "bg-muted-foreground/40")} />
                    PS
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowNm(p => !p)}
                    data-testid="toggle-nm"
                    className={cn(
                      "flex items-center gap-1.5 px-3 py-1.5 rounded-sm text-xs font-bold border transition-all",
                      showNm
                        ? "bg-blue-500/15 border-blue-500/50 text-blue-400"
                        : "bg-secondary/50 border-border text-muted-foreground hover:text-foreground"
                    )}
                  >
                    <span className={cn("w-2 h-2 rounded-full", showNm ? "bg-blue-400" : "bg-muted-foreground/40")} />
                    Nm
                  </button>
                </div>
              </div>

              <div className="h-[280px]">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={perfData.curveData} margin={{ top: 5, right: 10, left: -5, bottom: 5 }}>
                    <defs>
                      <linearGradient id="gradSeriePs" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#666" stopOpacity={0.3} />
                        <stop offset="100%" stopColor="#666" stopOpacity={0} />
                      </linearGradient>
                      <linearGradient id="gradS1Ps" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#8B2635" stopOpacity={0.3} />
                        <stop offset="100%" stopColor="#8B2635" stopOpacity={0} />
                      </linearGradient>
                      <linearGradient id="gradS2Ps" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#e74c3c" stopOpacity={0.25} />
                        <stop offset="100%" stopColor="#e74c3c" stopOpacity={0} />
                      </linearGradient>
                      <linearGradient id="gradSerieNm" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#555" stopOpacity={0.2} />
                        <stop offset="100%" stopColor="#555" stopOpacity={0} />
                      </linearGradient>
                      <linearGradient id="gradS1Nm" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#2563eb" stopOpacity={0.2} />
                        <stop offset="100%" stopColor="#2563eb" stopOpacity={0} />
                      </linearGradient>
                      <linearGradient id="gradS2Nm" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#3b82f6" stopOpacity={0.15} />
                        <stop offset="100%" stopColor="#3b82f6" stopOpacity={0} />
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
                    <XAxis dataKey="rpmLabel" tick={{ fill: '#666', fontSize: 11 }} axisLine={{ stroke: 'rgba(255,255,255,0.08)' }} tickLine={false} />
                    <YAxis tick={{ fill: '#666', fontSize: 11 }} axisLine={false} tickLine={false} />
                    <Tooltip content={<DynoTooltip />} />
                    {showPs && <Area type="monotone" dataKey="seriePs" name={`${t('originalPower')} PS`} stroke="#666" strokeWidth={2} fill="url(#gradSeriePs)" strokeDasharray="6 3" dot={false} />}
                    {showPs && <Area type="monotone" dataKey="stage1Ps" name="Stage 1 PS" stroke="#8B2635" strokeWidth={2.5} fill="url(#gradS1Ps)" dot={false} />}
                    {showPs && <Area type="monotone" dataKey="stage2Ps" name="Stage 2 PS" stroke="#e74c3c" strokeWidth={2.5} fill="url(#gradS2Ps)" dot={false} />}
                    {showNm && <Area type="monotone" dataKey="serieNm" name={`${t('originalPower')} Nm`} stroke="#555" strokeWidth={2} fill="url(#gradSerieNm)" strokeDasharray="6 3" dot={false} />}
                    {showNm && <Area type="monotone" dataKey="stage1Nm" name="Stage 1 Nm" stroke="#2563eb" strokeWidth={2.5} fill="url(#gradS1Nm)" dot={false} />}
                    {showNm && <Area type="monotone" dataKey="stage2Nm" name="Stage 2 Nm" stroke="#3b82f6" strokeWidth={2.5} fill="url(#gradS2Nm)" dot={false} />}
                  </AreaChart>
                </ResponsiveContainer>
              </div>

              <div className="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 mt-4">
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                  <span className="w-5 h-0.5 bg-[#666] inline-block" style={{ borderTop: '2px dashed #666' }} />
                  {t('originalPower')}
                </div>
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                  <span className="w-5 h-0.5 bg-[#8B2635] inline-block rounded" />
                  Stage 1 {showPs && <span className="text-[#e74c3c]">PS</span>}{showPs && showNm && ' / '}{showNm && <span className="text-blue-500">Nm</span>}
                </div>
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                  <span className="w-5 h-0.5 bg-[#e74c3c] inline-block rounded" />
                  Stage 2 {showPs && <span className="text-[#e74c3c]">PS</span>}{showPs && showNm && ' / '}{showNm && <span className="text-blue-400">Nm</span>}
                </div>
              </div>

              <div className="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-xs font-bold text-foreground uppercase tracking-wider">{t('power')} (PS)</span>
                    <span className="text-xs text-muted-foreground">max {perfData.stage2Ps} PS</span>
                  </div>
                  <div className="space-y-2">
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-muted-foreground w-16 text-right flex-shrink-0">{t('originalPower')}</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-[#555] rounded-sm transition-all" style={{ width: `${(perfData.originalPs / perfData.stage2Ps) * 100}%` }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.originalPs} PS</span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-foreground w-16 text-right flex-shrink-0 font-semibold">Stage 1</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-gradient-to-r from-[#8B2635] to-[#a52d3a] rounded-sm transition-all" style={{ width: `${(perfData.stage1Ps / perfData.stage2Ps) * 100}%` }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.stage1Ps} PS</span>
                        <span className="absolute inset-0 flex items-center justify-end pr-3 text-[10px] font-bold text-green-400">+{perfData.psGainS1}</span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-foreground w-16 text-right flex-shrink-0 font-semibold">Stage 2</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-gradient-to-r from-[#c0392b] to-[#e74c3c] rounded-sm transition-all" style={{ width: '100%' }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.stage2Ps} PS</span>
                        <span className="absolute inset-0 flex items-center justify-end pr-3 text-[10px] font-bold text-green-400">+{perfData.psGainS2}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-xs font-bold text-foreground uppercase tracking-wider">{t('torque')} (Nm)</span>
                    <span className="text-xs text-muted-foreground">max {perfData.stage2Nm} Nm</span>
                  </div>
                  <div className="space-y-2">
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-muted-foreground w-16 text-right flex-shrink-0">{t('originalPower')}</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-[#555] rounded-sm transition-all" style={{ width: `${(perfData.originalNm / perfData.stage2Nm) * 100}%` }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.originalNm} Nm</span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-foreground w-16 text-right flex-shrink-0 font-semibold">Stage 1</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-gradient-to-r from-[#1d4ed8] to-[#2563eb] rounded-sm transition-all" style={{ width: `${(perfData.stage1Nm / perfData.stage2Nm) * 100}%` }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.stage1Nm} Nm</span>
                        <span className="absolute inset-0 flex items-center justify-end pr-3 text-[10px] font-bold text-green-400">+{perfData.nmGainS1}</span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="text-[11px] text-foreground w-16 text-right flex-shrink-0 font-semibold">Stage 2</span>
                      <div className="flex-1 h-7 bg-secondary/40 rounded-sm overflow-hidden relative">
                        <div className="h-full bg-gradient-to-r from-[#2563eb] to-[#3b82f6] rounded-sm transition-all" style={{ width: '100%' }} />
                        <span className="absolute inset-0 flex items-center pl-3 text-xs font-bold text-white">{perfData.stage2Nm} Nm</span>
                        <span className="absolute inset-0 flex items-center justify-end pr-3 text-[10px] font-bold text-green-400">+{perfData.nmGainS2}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
          )}

          {selectedStage !== 'gearbox' && (
          <Card className="bg-card border-border xl:col-span-12">
            <CardContent className="p-6">
            <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-4">
              <Sliders weight="bold" className="w-3.5 h-3.5" />
              {t('additionalOptions')}
            </label>
            <UiTooltipProvider delayDuration={120}>
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                {activeSelectableOptions.map((opt) => {
                  const isSelected = selectedOptions.includes(opt.id);
                  const optionDescription = opt.desc?.[language] || opt.desc?.de || '';

                  return (
                    <button
                      key={opt.id}
                      type="button"
                      onClick={() => {
                        setSelectedOptions(prev =>
                          prev.includes(opt.id) ? prev.filter(id => id !== opt.id) : [...prev, opt.id]
                        );
                      }}
                      data-testid={`option-${opt.id}`}
                      className={cn(
                        "relative flex flex-col items-center gap-2 p-4 rounded-sm border transition-all duration-200 cursor-pointer",
                        isSelected
                          ? "bg-primary/8 border-primary/40 ring-1 ring-primary/20"
                          : "bg-card border-border hover:border-muted-foreground/40"
                      )}
                    >
                      {isSelected && (
                        <div className="absolute top-1.5 right-1.5">
                          <CheckCircle weight="fill" className="w-4 h-4 text-green-500" />
                        </div>
                      )}
                      <div className={cn("w-10 h-10 rounded-sm flex items-center justify-center bg-background/50", isSelected && "bg-background/80")}>
                        <OptionVisual option={opt} selected={isSelected} className="w-6 h-6 transition-colors" />
                      </div>
                      <span className="text-xs font-bold text-foreground text-center leading-tight">{opt.name[language] || opt.name.de}</span>
                      {optionDescription !== '' && (
                        <UiTooltip>
                          <UiTooltipTrigger asChild>
                            <span className="inline-flex items-center gap-1 text-[10px] text-muted-foreground cursor-help">
                              <BookOpen weight="bold" className="w-3 h-3" />
                              {language === 'de' ? 'Details' : 'Details'}
                            </span>
                          </UiTooltipTrigger>
                          <UiTooltipContent className="max-w-xs whitespace-pre-line leading-relaxed">
                            {optionDescription}
                          </UiTooltipContent>
                        </UiTooltip>
                      )}
                      {opt.credits > 0 ? (
                        <span className="text-[10px] font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-full">{opt.credits} Credits</span>
                      ) : (
                        <span className="text-[10px] font-semibold text-green-500 bg-green-500/10 px-2 py-0.5 rounded-full">0 Credits</span>
                      )}
                    </button>
                  );
                })}
              </div>
            </UiTooltipProvider>
            </CardContent>
          </Card>
          )}
          </div>
          </div>

          <div className="flex items-center justify-between">
            <Button
              variant="outline"
              className="border-border hover:bg-secondary font-semibold px-6 py-3 h-auto"
              onClick={() => setCurrentStep(1)}
              data-testid="wizard-prev-btn-step3"
            >
              <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
              {t('prevStep')}
            </Button>
            <Button
              className="btn-gradient text-white font-semibold px-8 py-3 h-auto"
              data-testid="wizard-next-btn-step3"
              onClick={() => setCurrentStep(3)}
            >
              {t('nextStep4')}
              <ArrowRight weight="bold" className="w-4 h-4 ml-2" />
            </Button>
          </div>
        </>)}

        {/* ====== STEP 4: Übersicht ====== */}
        {currentStep === 3 && (<>
          {/* Section 1: File & Lesegerät */}
          <Card className="bg-card border-border" data-testid="review-file-card">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-4">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Upload weight="bold" className="w-3.5 h-3.5" />
                  {t('reviewFile')}
                </label>
                <button
                  type="button"
                  onClick={() => setCurrentStep(1)}
                  className="flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 transition-colors"
                  data-testid="review-edit-step1"
                >
                  <Pencil weight="bold" className="w-3.5 h-3.5" />
                  {t('editStep')}
                </button>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {/* Uploaded File */}
                <div className="bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{language === 'de' ? 'Datei' : 'File'}</p>
                  {uploadedFiles.length > 0 ? (
                    <div className="flex items-center gap-2">
                      <FileIcon weight="fill" className="w-4 h-4 text-primary flex-shrink-0" />
                      <p className="text-sm font-semibold text-foreground truncate">{uploadedFiles[0].name}</p>
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">{t('noFileUploaded')}</p>
                  )}
                </div>
                {/* Reading Device */}
                <div className="bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('tuningTool')}</p>
                  <p className="text-sm font-semibold text-foreground">{formData.readingDevice || '—'}</p>
                </div>
                {/* Reading Method */}
                <div className="bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('readingMethod')}</p>
                  <p className="text-sm font-semibold text-foreground">{formData.readingMethod}</p>
                </div>
                {/* Reading Type */}
                <div className="bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('readingType')}</p>
                  <p className="text-sm font-semibold text-foreground">{formData.readingType}</p>
                </div>
                {/* Master/Slave */}
                <div className="bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('masterSlave')}</p>
                  <p className="text-sm font-semibold text-foreground">{formData.masterSlave}</p>
                </div>
                {/* Priority */}
                <div className={cn(
                  "border rounded-sm px-4 py-3",
                  formData.priority !== 'Normal' ? "bg-primary/8 border-primary/30" : "bg-secondary/50 border-border"
                )}>
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('priority')}</p>
                  <p className={cn("text-sm font-semibold", formData.priority !== 'Normal' ? "text-primary" : "text-foreground")}>
                    {formData.priority}
                    {formData.priority === 'Express' && <span className="text-xs ml-1 text-muted-foreground">(+49€)</span>}
                    {formData.priority === 'Sofort' && <span className="text-xs ml-1 text-muted-foreground">(+99€)</span>}
                  </p>
                </div>
              </div>
              {/* Comment */}
              {formData.comment && (
                <div className="mt-3 bg-secondary/50 border border-border rounded-sm px-4 py-3">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{t('comment')}</p>
                  <p className="text-sm text-foreground whitespace-pre-wrap">{formData.comment}</p>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Section 2: Vehicle */}
          <Card className="bg-card border-border" data-testid="review-vehicle-card">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-4">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <CarProfile weight="bold" className="w-3.5 h-3.5" />
                  {t('reviewVehicle')}
                </label>
                <button
                  type="button"
                  onClick={() => setCurrentStep(2)}
                  className="flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 transition-colors"
                  data-testid="review-edit-step2"
                >
                  <Pencil weight="bold" className="w-3.5 h-3.5" />
                  {t('editStep')}
                </button>
              </div>
              {formData.manufacturer ? (
                <div className="flex flex-wrap gap-3">
                  {formData.vehicleType && (
                    <div className="flex items-center gap-2 bg-primary/10 border border-primary/30 rounded-sm px-4 py-2.5">
                      <HardDrives weight="bold" className="w-4 h-4 text-primary" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('vehicleType')}</p>
                        <p className="text-sm font-semibold text-foreground">{formData.vehicleType}</p>
                      </div>
                    </div>
                  )}
                  <div className="flex items-center gap-2 bg-primary/10 border border-primary/30 rounded-sm px-4 py-2.5">
                    <Car weight="bold" className="w-4 h-4 text-primary" />
                    <div>
                      <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('manufacturer')}</p>
                      <p className="text-sm font-semibold text-foreground">{formData.manufacturer}</p>
                    </div>
                  </div>
                  {formData.series && (
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-4 py-2.5">
                      <Tag weight="bold" className="w-4 h-4 text-muted-foreground" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('series')}</p>
                        <p className="text-sm font-semibold text-foreground">{formData.series}</p>
                      </div>
                    </div>
                  )}
                  {formData.vehicleModel && (
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-4 py-2.5">
                      <Calendar weight="bold" className="w-4 h-4 text-muted-foreground" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('vehicleModel')}</p>
                        <p className="text-sm font-semibold text-foreground">{formData.vehicleModel}</p>
                      </div>
                    </div>
                  )}
                  {formData.engine && (
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-4 py-2.5">
                      <Engine weight="bold" className="w-4 h-4 text-muted-foreground" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('engine')}</p>
                        <p className="text-sm font-semibold text-foreground">{formData.engine}</p>
                      </div>
                    </div>
                  )}
                  {formData.ecu && (
                    <div className="flex items-center gap-2 bg-primary/10 border border-primary/30 rounded-sm px-4 py-2.5">
                      <GasPump weight="bold" className="w-4 h-4 text-primary" />
                      <div>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('ecu')}</p>
                        <p className="text-sm font-semibold text-foreground">{formData.ecu}</p>
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">{t('noVehicleSelected')}</p>
              )}
            </CardContent>
          </Card>

          {/* Section 3: Tuning & Options */}
          <Card className="bg-card border-border" data-testid="review-tuning-card">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-4">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                  <Lightning weight="bold" className="w-3.5 h-3.5" />
                  {t('reviewTuning')}
                </label>
                <button
                  type="button"
                  onClick={() => setCurrentStep(2)}
                  className="flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 transition-colors"
                  data-testid="review-edit-step3"
                >
                  <Pencil weight="bold" className="w-3.5 h-3.5" />
                  {t('editStep')}
                </button>
              </div>
              {selectedStage ? (
                <div className="space-y-4">
                  {/* Selected Stage */}
                  <div>
                    <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-2">{t('stage')}</p>
                    <div className="flex items-center gap-3">
                      {(() => {
                        const stage = displayTuningStages.find(s => s.id === selectedStage);
                        if (!stage) return null;
                        const Icon = stage.Icon;
                        return (
                          <div className="flex items-center gap-3 bg-primary/8 border border-primary/30 rounded-sm px-4 py-2.5">
                            <Icon weight="fill" className="w-5 h-5 text-primary" />
                            <span className="text-sm font-bold text-foreground">{stage.name[language]}</span>
                            {selectedStage === 'gearbox' && selectedGearboxStage ? (
                              <Badge className="bg-blue-500/20 text-blue-400 border-blue-500/30 text-xs font-bold">
                                {gearboxStages.find(gs => gs.id === selectedGearboxStage)?.name[language]} — {gearboxStages.find(gs => gs.id === selectedGearboxStage)?.credits} Credits
                              </Badge>
                            ) : stage.credits > 0 ? (
                              <Badge className="bg-primary/20 text-primary border-primary/30 text-xs font-bold">{stage.credits} Credits</Badge>
                            ) : null}
                          </div>
                        );
                      })()}
                    </div>
                  </div>
                  {/* Selected Options (not for gearbox) */}
                  {selectedStage !== 'gearbox' && selectedOptions.length > 0 && (
                    <div>
                      <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-2">{t('options')}</p>
                      <UiTooltipProvider delayDuration={120}>
                      <div className="flex flex-wrap gap-2">
                        {selectedOptions.map(optId => {
                          const opt = activeSelectableOptions.find(o => o.id === optId);
                          if (!opt) return null;
                          const optionDescription = opt.desc?.[language] || opt.desc?.de || '';
                          return (
                            <div key={optId} className="flex items-center gap-2 bg-secondary/80 border border-border rounded-sm px-3 py-2">
                              <OptionVisual option={opt} selected className="w-4 h-4" />
                              <span className="text-xs font-semibold text-foreground">{opt.name[language]}</span>
                              {optionDescription !== '' && (
                                <UiTooltip>
                                  <UiTooltipTrigger asChild>
                                    <span className="text-muted-foreground cursor-help">
                                      <BookOpen weight="bold" className="w-3.5 h-3.5" />
                                    </span>
                                  </UiTooltipTrigger>
                                  <UiTooltipContent className="max-w-xs whitespace-pre-line leading-relaxed">
                                    {optionDescription}
                                  </UiTooltipContent>
                                </UiTooltip>
                              )}
                              {opt.credits > 0 ? (
                                <span className="text-[10px] font-bold text-primary">+{opt.credits}</span>
                              ) : (
                                <span className="text-[10px] font-bold text-green-400">0</span>
                              )}
                            </div>
                          );
                        })}
                      </div>
                      </UiTooltipProvider>
                    </div>
                  )}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">{t('noTuningSelected')}</p>
              )}
            </CardContent>
          </Card>

          {/* Section 4: Cost Summary */}
          <Card className="bg-card border-primary/30" data-testid="review-cost-card">
            <CardContent className="p-5">
              <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-4">
                <CreditCard weight="bold" className="w-3.5 h-3.5" />
                {t('reviewCost')}
              </label>
              <div className="space-y-2">
                {/* Stage base cost */}
                {selectedStage && (
                  <div className="flex items-center justify-between py-2 border-b border-border/50">
                    <span className="text-sm text-muted-foreground">{t('baseCredits')}: {
                      selectedStage === 'gearbox'
                        ? `${displayTuningStages.find(s => s.id === 'gearbox')?.name[language]} ${selectedGearboxStage ? '— ' + gearboxStages.find(gs => gs.id === selectedGearboxStage)?.name[language] : ''}`
                        : displayTuningStages.find(s => s.id === selectedStage)?.name[language]
                    }</span>
                    <span className="text-sm font-bold text-foreground">
                      {selectedStage === 'gearbox'
                        ? (gearboxStages.find(gs => gs.id === selectedGearboxStage)?.credits || 0)
                        : (displayTuningStages.find(s => s.id === selectedStage)?.credits || 0)
                      } Credits
                    </span>
                  </div>
                )}
                {/* Options cost */}
                {selectedStage !== 'gearbox' && selectedOptions.length > 0 && (
                  <div className="flex items-center justify-between py-2 border-b border-border/50">
                    <span className="text-sm text-muted-foreground">{t('optionsCredits')} ({selectedOptions.length}x)</span>
                    <span className="text-sm font-bold text-foreground">
                      {selectedOptions.reduce((sum, id) => sum + (activeSelectableOptions.find(o => o.id === id)?.credits || 0), 0)} Credits
                    </span>
                  </div>
                )}
                {/* Priority surcharge */}
                {formData.priority !== 'Normal' && (
                  <div className="flex items-center justify-between py-2 border-b border-border/50">
                    <span className="text-sm text-muted-foreground">{t('prioritySurcharge')}: {formData.priority}</span>
                    <span className="text-sm font-bold text-primary">
                      +{formData.priority === 'Express' ? '49' : '99'}€
                    </span>
                  </div>
                )}
                {/* Total */}
                <div className="flex items-center justify-between pt-3">
                  <span className="text-sm font-bold text-foreground uppercase tracking-wider">{t('totalCredits')}</span>
                  <span className="text-2xl font-bold text-primary font-heading">
                    {(() => {
                      let total = 0;
                      if (selectedStage === 'gearbox') {
                        total += gearboxStages.find(gs => gs.id === selectedGearboxStage)?.credits || 0;
                      } else {
                        total += displayTuningStages.find(s => s.id === selectedStage)?.credits || 0;
                        total += selectedOptions.reduce((sum, id) => sum + (activeSelectableOptions.find(o => o.id === id)?.credits || 0), 0);
                      }
                      return total;
                    })()}{' '}
                    <span className="text-sm font-medium text-muted-foreground">Credits</span>
                  </span>
                </div>
              </div>

              {selectedStage && (
                <div className="mt-5 rounded-sm border border-border bg-secondary/30 px-4 py-3">
                  <div className="flex items-center justify-between gap-4">
                    <span className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                      {language === 'de' ? 'Aktuelle Credits' : 'Current credits'}
                    </span>
                    <span className="text-sm font-bold text-foreground">
                      {(creditQuote?.currentCredits ?? (simulateZeroCredits ? 0 : currentCredits))} Credits
                    </span>
                  </div>
                </div>
              )}

              {(isLoadingCreditQuote || creditQuote?.missingCredits > 0 || purchaseError) && (
                <div
                  className="mt-4 min-h-[252px]"
                  style={{ overflowAnchor: 'none' }}
                  data-testid="order-credit-topup-region"
                >
                {isLoadingCreditQuote ? (
                  <div className="rounded-sm border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-muted-foreground">
                    {language === 'de' ? 'Credits werden serverseitig berechnet...' : 'Calculating credits on the server...'}
                  </div>
                ) : purchaseError && !(creditQuote?.missingCredits > 0) ? (
                  <div className="rounded-sm border border-red-500/30 bg-red-500/10 px-4 py-4">
                    <p className="text-sm font-bold text-red-200">
                      {language === 'de' ? 'Credits konnten für diesen Auftrag nicht geladen werden.' : 'Could not load credits for this order.'}
                    </p>
                    <p className="mt-2 text-sm text-red-100/90">{purchaseError}</p>
                    <Button
                      variant="outline"
                      className="mt-4 border-red-300/30 text-red-100 hover:bg-red-500/10"
                      onClick={() => {
                        setCreditQuote(null);
                        setPurchaseError('');
                        setCurrentStep(2);
                        window.setTimeout(() => setCurrentStep(3), 0);
                      }}
                    >
                      {language === 'de' ? 'Erneut laden' : 'Retry'}
                    </Button>
                  </div>
                ) : (
                <div className="rounded-sm border border-amber-500/30 bg-amber-500/10 px-4 py-4" data-testid="order-credit-topup-box">
                  <div className="flex items-start justify-between gap-4">
                    <div>
                      <p className="text-sm font-bold text-amber-200">
                        {language === 'de'
                          ? `Für diesen Auftrag sind ${creditQuote.requiredCredits} Credits erforderlich.`
                          : `${creditQuote.requiredCredits} credits are required for this order.`}
                      </p>
                      <p className="mt-1 text-xs text-amber-100/80">
                        {language === 'de'
                          ? 'Die Kaufmenge ist exakt auf diesen Auftrag begrenzt.'
                          : 'The purchase quantity is fixed to this order exactly.'}
                      </p>
                    </div>
                    <CreditCard weight="fill" className="h-5 w-5 flex-shrink-0 text-amber-200" />
                  </div>
                  <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div className="rounded-sm border border-white/10 bg-black/10 px-3 py-2.5">
                      <p className="text-[10px] uppercase tracking-wider text-amber-100/70">{language === 'de' ? 'Aktuell' : 'Current'}</p>
                      <p className="text-lg font-bold text-white">{creditQuote.currentCredits}</p>
                    </div>
                    <div className="rounded-sm border border-white/10 bg-black/10 px-3 py-2.5">
                      <p className="text-[10px] uppercase tracking-wider text-amber-100/70">{language === 'de' ? 'Benötigt' : 'Required'}</p>
                      <p className="text-lg font-bold text-white">{creditQuote.requiredCredits}</p>
                    </div>
                    <div className="rounded-sm border border-white/10 bg-black/10 px-3 py-2.5">
                      <p className="text-[10px] uppercase tracking-wider text-amber-100/70">{language === 'de' ? 'Zu kaufen' : 'To buy'}</p>
                      <p className="text-lg font-bold text-white">{creditQuote.missingCredits}</p>
                    </div>
                  </div>
                  {creditQuote?.topupPricing?.priceGross > 0 && (
                    <p className="mt-4 text-sm font-semibold text-white">
                      {language === 'de' ? 'PayPal Gesamtbetrag: ' : 'PayPal total: '}
                      {Number(creditQuote.topupPricing.priceGross).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €
                    </p>
                  )}
                  {simulateZeroCredits && (
                    <p className="mt-3 text-xs font-semibold text-amber-100">
                      {language === 'de'
                        ? '0-Credits-Testmodus aus dem Profil ist aktiv.'
                        : '0-credit test mode from profile is active.'}
                    </p>
                  )}
                  <div className="mt-4">
                    {!isPaypalOpen ? (
                      <Button
                        className="btn-gradient text-white font-semibold"
                        onClick={handleStartOrderTopup}
                        disabled={paypalBusy}
                        data-testid="order-topup-start-btn"
                      >
                        <CreditCard weight="fill" className="mr-2 h-4 w-4" />
                        {language === 'de' ? 'Credits jetzt kaufen' : 'Buy credits now'}
                      </Button>
                    ) : (
                      <div className="space-y-3">
                        <div
                          ref={paypalButtonsRef}
                          className="min-h-[180px]"
                          style={{ overflowAnchor: 'none' }}
                          data-paypal-ready={paypalSdkReady ? '1' : '0'}
                        />
                        <Button
                          variant="outline"
                          className="border-border"
                          onClick={() => setIsPaypalOpen(false)}
                          disabled={paypalBusy}
                        >
                          {language === 'de' ? 'Abbrechen' : 'Cancel'}
                        </Button>
                      </div>
                    )}
                  </div>
                  {purchaseError && creditQuote?.missingCredits > 0 && (
                    <p className="mt-3 text-sm text-red-200">{purchaseError}</p>
                  )}
                </div>
                )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Step 4 Navigation */}
          <div className="flex flex-col gap-3">
            <div className="flex items-center justify-between">
              <Button
                variant="ghost"
                className="border-border hover:bg-secondary font-semibold px-6 py-3 h-auto"
                onClick={() => setCurrentStep(2)}
                data-testid="wizard-prev-btn-step4"
              >
                <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
                {t('prevStep')}
              </Button>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  className="border-border text-muted-foreground font-semibold px-6 py-3 h-auto"
                  onClick={handleParkOrder}
                  disabled={isParkBusy}
                  data-testid="wizard-park-btn"
                >
                  {isParkBusy ? (language === 'de' ? 'Parkt...' : 'Parking...') : (language === 'de' ? 'Auftrag parken' : 'Park order')}
                </Button>
                <Button
                  className="btn-gradient text-white font-semibold px-8 py-3.5 h-auto text-base"
                  data-testid="wizard-submit-btn"
                  onClick={creditQuote?.missingCredits > 0 ? handleStartOrderTopup : submitOrder}
                  disabled={isSubmittingOrder || isLoadingCreditQuote || paypalBusy}
                >
                  {creditQuote?.missingCredits > 0 ? (
                    <>
                      <CreditCard weight="fill" className="w-5 h-5 mr-2" />
                      {language === 'de' ? 'Credits jetzt kaufen' : 'Buy credits now'}
                    </>
                  ) : (
                    <>
                      <PaperPlaneTilt weight="fill" className="w-5 h-5 mr-2" />
                      {isSubmittingOrder ? (language === 'de' ? 'Speichert...' : 'Saving...') : t('submitOrder')}
                    </>
                  )}
                </Button>
              </div>
            </div>
            {(parkMessage || parkError) && (
              <p
                className={cn(
                  'text-sm font-semibold',
                  parkError ? 'text-red-400' : 'text-emerald-400',
                )}
              >
                {parkError || parkMessage}
              </p>
            )}
          </div>
          {submitError && (
            <p className="text-sm text-red-400" data-testid="wizard-submit-error">{submitError}</p>
          )}
        </>)}

      </div>
    </DashboardLayout>
  );
}

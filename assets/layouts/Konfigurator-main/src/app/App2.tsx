import { useState } from 'react';
import { Check, Gauge, Wrench, Fuel, Zap, ChevronRight } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';
import bmwCar from 'figma:asset/93a78ea6a4ebfe48d30cd0f2f45cfdafd28e23a9.png';

export default function App2() {
  const [activeStage, setActiveStage] = useState(1);

  const stages = [
    {
      id: 1,
      name: 'STAGE 1',
      ps: { original: 550, tuned: 650, increase: 100 },
      nm: { original: 650, tuned: 850, increase: 200 }
    },
    {
      id: 2,
      name: 'STAGE 2',
      ps: { original: 550, tuned: 720, increase: 170 },
      nm: { original: 650, tuned: 920, increase: 270 }
    },
    {
      id: 3,
      name: 'STAGE 3',
      ps: { original: 550, tuned: 800, increase: 250 },
      nm: { original: 650, tuned: 1000, increase: 350 }
    }
  ];

  const currentStage = stages.find(s => s.id === activeStage) || stages[0];

  return (
    <div className="min-h-screen bg-black text-white flex">
      {/* Left Sidebar */}
      <div className="w-20 bg-gradient-to-b from-red-600 to-red-800 flex flex-col items-center py-8 gap-8">
        <img src={bmwLogo} alt="BMW" className="w-12 h-12" />
        
        <div className="flex-1 flex flex-col gap-4">
          {stages.map((stage) => (
            <button
              key={stage.id}
              onClick={() => setActiveStage(stage.id)}
              className={`w-12 h-12 flex items-center justify-center font-bold transition-all border-2 ${
                activeStage === stage.id
                  ? 'bg-black text-red-600 border-black'
                  : 'bg-transparent text-white border-white/20 hover:border-white/40'
              }`}
            >
              {stage.id}
            </button>
          ))}
        </div>

        <button className="w-12 h-12 bg-black flex items-center justify-center">
          <Wrench className="w-6 h-6 text-white" />
        </button>
      </div>

      {/* Main Content */}
      <div className="flex-1 relative">
        {/* Background Image */}
        <div className="absolute inset-0">
          <img
            src={bmwCar}
            alt="BMW M3"
            className="w-full h-full object-cover opacity-20"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent"></div>
        </div>

        {/* Content Overlay */}
        <div className="relative z-10 h-full p-8 flex flex-col">
          {/* Header */}
          <div className="mb-8">
            <div className="text-xs tracking-[0.3em] text-red-600 mb-2">CHIPTUNING</div>
            <h1 className="text-4xl font-bold tracking-tight mb-1">TUNINGFILES-DOWNLOAD.COM</h1>
            <div className="h-0.5 w-32 bg-red-600 mt-2"></div>
          </div>

          {/* Vehicle Title */}
          <div className="mb-8">
            <h2 className="text-6xl font-bold mb-2">BMW M3</h2>
            <div className="flex items-center gap-4 text-gray-400">
              <span className="flex items-center gap-2">
                <Fuel className="w-4 h-4 text-red-600" />
                BENZIN
              </span>
              <span className="text-red-600">|</span>
              <span>{currentStage.name}</span>
            </div>
          </div>

          {/* Performance Grid */}
          <div className="grid grid-cols-3 gap-6 mb-8">
            {/* Original Power */}
            <div className="bg-black/60 border-l-4 border-white/20 p-6 backdrop-blur-sm">
              <div className="text-xs text-gray-500 mb-2 tracking-wider">ORIGINAL</div>
              <div className="flex items-baseline gap-2 mb-4">
                <div className="text-5xl font-bold">{currentStage.ps.original}</div>
                <div className="text-xl text-gray-400">PS</div>
              </div>
              <div className="flex items-baseline gap-2">
                <div className="text-3xl font-bold">{currentStage.nm.original}</div>
                <div className="text-sm text-gray-400">NM</div>
              </div>
            </div>

            {/* Arrow */}
            <div className="flex items-center justify-center">
              <div className="relative w-full">
                <div className="h-0.5 bg-gradient-to-r from-white/20 to-red-600"></div>
                <ChevronRight className="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 text-red-600" />
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-black px-3 py-1 text-xs text-red-600 font-bold">
                  +{currentStage.ps.increase} PS / +{currentStage.nm.increase} NM
                </div>
              </div>
            </div>

            {/* Tuned Power */}
            <div className="bg-gradient-to-br from-red-600/20 to-transparent border-l-4 border-red-600 p-6 backdrop-blur-sm">
              <div className="text-xs text-gray-500 mb-2 tracking-wider">NACH TUNING</div>
              <div className="flex items-baseline gap-2 mb-4">
                <div className="text-5xl font-bold text-red-600">{currentStage.ps.tuned}</div>
                <div className="text-xl text-red-600">PS</div>
              </div>
              <div className="flex items-baseline gap-2">
                <div className="text-3xl font-bold text-red-600">{currentStage.nm.tuned}</div>
                <div className="text-sm text-gray-400">NM</div>
              </div>
            </div>
          </div>

          {/* Stats Bars */}
          <div className="grid grid-cols-2 gap-6 mb-8">
            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                  <Gauge className="w-5 h-5 text-red-600" />
                  <span className="text-sm text-gray-400">LEISTUNG</span>
                </div>
                <span className="text-sm text-red-600 font-bold">+{((currentStage.ps.increase / currentStage.ps.original) * 100).toFixed(0)}%</span>
              </div>
              <div className="h-3 bg-white/10 relative overflow-hidden">
                <div className="absolute left-0 top-0 h-full bg-white/20" style={{ width: '50%' }}></div>
                <div 
                  className="absolute left-0 top-0 h-full bg-gradient-to-r from-red-600 to-red-500 transition-all duration-500"
                  style={{ width: `${50 + (currentStage.ps.increase / currentStage.ps.original) * 50}%` }}
                ></div>
              </div>
              <div className="flex justify-between mt-2 text-xs text-gray-500">
                <span>{currentStage.ps.original} PS</span>
                <span className="text-red-600">{currentStage.ps.tuned} PS</span>
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                  <Zap className="w-5 h-5 text-red-600" />
                  <span className="text-sm text-gray-400">DREHMOMENT</span>
                </div>
                <span className="text-sm text-red-600 font-bold">+{((currentStage.nm.increase / currentStage.nm.original) * 100).toFixed(0)}%</span>
              </div>
              <div className="h-3 bg-white/10 relative overflow-hidden">
                <div className="absolute left-0 top-0 h-full bg-white/20" style={{ width: '50%' }}></div>
                <div 
                  className="absolute left-0 top-0 h-full bg-gradient-to-r from-red-600 to-red-500 transition-all duration-500"
                  style={{ width: `${50 + (currentStage.nm.increase / currentStage.nm.original) * 50}%` }}
                ></div>
              </div>
              <div className="flex justify-between mt-2 text-xs text-gray-500">
                <span>{currentStage.nm.original} NM</span>
                <span className="text-red-600">{currentStage.nm.tuned} NM</span>
              </div>
            </div>
          </div>

          {/* Bottom Section */}
          <div className="mt-auto grid grid-cols-2 gap-6">
            <div className="bg-black/60 border border-white/10 p-6 backdrop-blur-sm">
              <div className="text-sm text-gray-400 mb-4 tracking-wider">TUNING-OPTIONEN</div>
              <div className="space-y-3 mb-6">
                <div className="flex items-center justify-between text-sm">
                  <span>VMAX Off</span>
                  <Check className="w-5 h-5 text-red-600" />
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span>Kat. Off</span>
                  <Check className="w-5 h-5 text-red-600" />
                </div>
              </div>
            </div>

            <div className="flex items-end">
              <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-5 transition-all shadow-lg shadow-red-600/30 flex items-center justify-center gap-2">
                <span>JETZT KONTAKT AUFNEHMEN</span>
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

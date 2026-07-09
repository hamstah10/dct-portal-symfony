import { useState } from 'react';
import { ImageWithFallback } from './components/figma/ImageWithFallback';
import { Check, Gauge, Wrench, Fuel, Zap } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';
import bmwCar from 'figma:asset/93a78ea6a4ebfe48d30cd0f2f45cfdafd28e23a9.png';
import App2 from './App2';
import App3 from './App3';
import App4 from './App4';
import App5 from './App5';
import App6 from './App6';

export default function App() {
  const [version, setVersion] = useState(1);

  if (version === 2) {
    return (
      <div className="size-full">
        <div className="fixed top-4 right-4 z-50 flex gap-2">
          <button
            onClick={() => setVersion(1)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V1
          </button>
          <button
            onClick={() => setVersion(2)}
            className="px-4 py-2 bg-red-600 text-white"
          >
            V2
          </button>
          <button
            onClick={() => setVersion(3)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V3
          </button>
          <button
            onClick={() => setVersion(4)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V4
          </button>
          <button
            onClick={() => setVersion(5)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V5
          </button>
          <button
            onClick={() => setVersion(6)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V6
          </button>
        </div>
        <App2 />
      </div>
    );
  }

  if (version === 3) {
    return (
      <div className="size-full">
        <div className="fixed top-4 right-4 z-50 flex gap-2">
          <button
            onClick={() => setVersion(1)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V1
          </button>
          <button
            onClick={() => setVersion(2)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V2
          </button>
          <button
            onClick={() => setVersion(3)}
            className="px-4 py-2 bg-red-600 text-white"
          >
            V3
          </button>
          <button
            onClick={() => setVersion(4)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V4
          </button>
          <button
            onClick={() => setVersion(5)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V5
          </button>
          <button
            onClick={() => setVersion(6)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V6
          </button>
        </div>
        <App3 />
      </div>
    );
  }

  if (version === 4) {
    return (
      <div className="size-full">
        <div className="fixed top-4 right-4 z-50 flex gap-2">
          <button
            onClick={() => setVersion(1)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V1
          </button>
          <button
            onClick={() => setVersion(2)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V2
          </button>
          <button
            onClick={() => setVersion(3)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V3
          </button>
          <button
            onClick={() => setVersion(4)}
            className="px-4 py-2 bg-red-600 text-white"
          >
            V4
          </button>
          <button
            onClick={() => setVersion(5)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V5
          </button>
          <button
            onClick={() => setVersion(6)}
            className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
          >
            V6
          </button>
        </div>
        <App4 />
      </div>
    );
  }

  if (version === 5) {
    return (
      <div className="size-full">
        <div className="fixed top-4 right-4 z-50 flex gap-2">
          <button
            onClick={() => setVersion(1)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V1
          </button>
          <button
            onClick={() => setVersion(2)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V2
          </button>
          <button
            onClick={() => setVersion(3)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V3
          </button>
          <button
            onClick={() => setVersion(4)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V4
          </button>
          <button
            onClick={() => setVersion(5)}
            className="px-4 py-2 bg-black text-white"
          >
            V5
          </button>
          <button
            onClick={() => setVersion(6)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V6
          </button>
        </div>
        <App5 />
      </div>
    );
  }

  if (version === 6) {
    return (
      <div className="size-full">
        <div className="fixed top-4 right-4 z-50 flex gap-2">
          <button
            onClick={() => setVersion(1)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V1
          </button>
          <button
            onClick={() => setVersion(2)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V2
          </button>
          <button
            onClick={() => setVersion(3)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V3
          </button>
          <button
            onClick={() => setVersion(4)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V4
          </button>
          <button
            onClick={() => setVersion(5)}
            className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-300"
          >
            V5
          </button>
          <button
            onClick={() => setVersion(6)}
            className="px-4 py-2 bg-black text-white"
          >
            V6
          </button>
        </div>
        <App6 />
      </div>
    );
  }

  return (
    <>
      <div className="fixed top-4 right-4 z-50 flex gap-2">
        <button
          onClick={() => setVersion(1)}
          className="px-4 py-2 bg-red-600 text-white"
        >
          V1
        </button>
        <button
          onClick={() => setVersion(2)}
          className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
        >
          V2
        </button>
        <button
          onClick={() => setVersion(3)}
          className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
        >
          V3
        </button>
        <button
          onClick={() => setVersion(4)}
          className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
        >
          V4
        </button>
        <button
          onClick={() => setVersion(5)}
          className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
        >
          V5
        </button>
        <button
          onClick={() => setVersion(6)}
          className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20"
        >
          V6
        </button>
      </div>
      <AppVersion1 />
    </>
  );
}

function AppVersion1() {
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
    <div className="min-h-screen bg-[#0a0a0a] text-white">
      {/* Header */}
      <header className="border-b border-white/10">
        <div className="max-w-7xl mx-auto px-6 py-4">
          <div className="text-center">
            <div className="text-sm tracking-[0.3em] text-gray-500 mb-1">CHIPTUNING</div>
            <h1 className="text-3xl font-bold tracking-tight">TUNINGFILES-DOWNLOAD.COM</h1>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Vehicle Info Hero */}
        <div className="grid lg:grid-cols-2 gap-6 mb-8">
          {/* Left: Vehicle Details */}
          <div className="bg-gradient-to-br from-red-600/10 to-transparent border border-red-600/20 p-5">
            <div className="mb-4">
              <div className="flex items-center gap-3">
                <img src={bmwLogo} alt="BMW Logo" className="w-12 h-12" />
                <h2 className="text-3xl font-bold">BMW M3</h2>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3 mt-4">
              <div className="bg-black/40 p-3 border border-white/5">
                <div className="flex items-center gap-2 mb-1">
                  <Gauge className="w-4 h-4 text-red-600" />
                  <div className="text-xs text-gray-400">Leistung</div>
                </div>
                <div className="text-2xl font-bold">{currentStage.ps.original}</div>
                <div className="text-xs text-gray-500">PS</div>
              </div>
              <div className="bg-black/40 p-3 border border-white/5">
                <div className="flex items-center gap-2 mb-1">
                  <Zap className="w-4 h-4 text-red-600" />
                  <div className="text-xs text-gray-400">Drehmoment</div>
                </div>
                <div className="text-2xl font-bold">{currentStage.nm.original}</div>
                <div className="text-xs text-gray-500">NM</div>
              </div>
            </div>

            <div className="mt-3 flex items-center gap-2 text-xs text-gray-400 bg-black/40 p-3 border border-white/5">
              <Fuel className="w-4 h-4 text-red-600" />
              <span>Kraftstoff: <span className="text-white">BENZIN</span></span>
            </div>
          </div>

          {/* Right: Car Image */}
          <div className="relative overflow-hidden bg-gradient-to-br from-gray-900 to-black border border-white/10 h-[280px]">
            <img
              src={bmwCar}
              alt="BMW M3"
              className="w-full h-full object-contain"
            />
          </div>
        </div>

        {/* Stage Selector */}
        <div className="flex flex-wrap gap-3 mb-8">
          {stages.map((stage) => (
            <button
              key={stage.id}
              onClick={() => setActiveStage(stage.id)}
              className={`flex items-center gap-3 px-6 py-4 font-bold transition-all ${
                activeStage === stage.id
                  ? 'bg-red-600 text-white shadow-lg shadow-red-600/30'
                  : 'bg-white/5 text-gray-400 hover:bg-white/10 border border-white/10'
              }`}
            >
              <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
              </svg>
              {stage.name}
            </button>
          ))}
          <button className="flex items-center gap-3 px-6 py-4 font-bold bg-white/5 text-gray-400 hover:bg-white/10 border border-white/10 transition-all">
            <Wrench className="w-5 h-5" />
            TUNING-OPTIONEN
          </button>
        </div>

        {/* Performance Data */}
        <div className="grid lg:grid-cols-3 gap-6 mb-12">
          {/* PS Card */}
          <div className="bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="text-sm text-gray-400">LEISTUNG</div>
              <Gauge className="w-6 h-6 text-red-600" />
            </div>
            <div className="space-y-4">
              <div className="flex items-end justify-between">
                <div>
                  <div className="text-sm text-gray-500 mb-1">Original</div>
                  <div className="text-3xl font-bold">{currentStage.ps.original}</div>
                  <div className="text-sm text-gray-500">PS</div>
                </div>
                <div className="text-right">
                  <div className="text-sm text-gray-500 mb-1">Nach Tuning</div>
                  <div className="text-3xl font-bold text-red-600">{currentStage.ps.tuned}</div>
                  <div className="text-sm text-gray-500">PS</div>
                </div>
              </div>
              <div className="relative pt-2">
                <div className="h-2 bg-white/10 overflow-hidden">
                  <div 
                    className="h-full bg-gradient-to-r from-red-600 to-red-500 transition-all duration-500"
                    style={{ width: `${(currentStage.ps.increase / currentStage.ps.original) * 100}%` }}
                  ></div>
                </div>
                <div className="mt-2 text-right">
                  <span className="text-red-600 font-bold">+ {currentStage.ps.increase} PS</span>
                </div>
              </div>
            </div>
          </div>

          {/* NM Card */}
          <div className="bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="text-sm text-gray-400">DREHMOMENT</div>
              <Zap className="w-6 h-6 text-red-600" />
            </div>
            <div className="space-y-4">
              <div className="flex items-end justify-between">
                <div>
                  <div className="text-sm text-gray-500 mb-1">Original</div>
                  <div className="text-3xl font-bold">{currentStage.nm.original}</div>
                  <div className="text-sm text-gray-500">NM</div>
                </div>
                <div className="text-right">
                  <div className="text-sm text-gray-500 mb-1">Nach Tuning</div>
                  <div className="text-3xl font-bold text-red-600">{currentStage.nm.tuned}</div>
                  <div className="text-sm text-gray-500">NM</div>
                </div>
              </div>
              <div className="relative pt-2">
                <div className="h-2 bg-white/10 overflow-hidden">
                  <div 
                    className="h-full bg-gradient-to-r from-red-600 to-red-500 transition-all duration-500"
                    style={{ width: `${(currentStage.nm.increase / currentStage.nm.original) * 100}%` }}
                  ></div>
                </div>
                <div className="mt-2 text-right">
                  <span className="text-red-600 font-bold">+ {currentStage.nm.increase} NM</span>
                </div>
              </div>
            </div>
          </div>

          {/* Tuning Options Card */}
          <div className="bg-gradient-to-br from-red-600/10 to-transparent border border-red-600/20 p-6">
            <div className="text-sm text-gray-400 mb-4">TUNING-OPTIONEN</div>
            <div className="text-sm text-gray-300 mb-6">
              In unseren Leistungsstufen sind die beliebtesten Tuning-Optionen unserer Kunden bereits inkludiert.
            </div>
            <div className="space-y-3 mb-6">
              <div className="flex items-center justify-between">
                <span className="text-sm">VMAX Off</span>
                <Check className="w-5 h-5 text-red-600" />
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm">Kat. Off</span>
                <Check className="w-5 h-5 text-red-600" />
              </div>
            </div>
            <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 transition-all shadow-lg shadow-red-600/30">
              KONTAKT
            </button>
          </div>
        </div>

        {/* Toggle Switch Visualization */}
        <div className="bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-8">
          <div className="max-w-2xl mx-auto">
            <div className="flex items-center justify-between mb-8">
              <div className="text-center flex-1">
                <div className="text-sm text-gray-500 mb-2">ORIGINAL</div>
                <div className="text-2xl font-bold">{currentStage.ps.original} PS</div>
              </div>
              
              <div className="relative px-8">
                <div className="w-32 h-16 bg-black border-2 border-red-600 p-1 cursor-pointer shadow-lg shadow-red-600/20">
                  <div className="w-14 h-full bg-gradient-to-r from-red-600 to-red-500 ml-auto flex items-center justify-center shadow-lg">
                    <Zap className="w-6 h-6 text-white" />
                  </div>
                </div>
              </div>

              <div className="text-center flex-1">
                <div className="text-sm text-gray-500 mb-2">GETUNT</div>
                <div className="text-2xl font-bold text-red-600">{currentStage.ps.tuned} PS</div>
              </div>
            </div>

            <div className="text-center">
              <div className="inline-flex items-center gap-2 px-6 py-3 bg-red-600/10 border border-red-600/20">
                <div className="w-2 h-2 bg-red-600 animate-pulse"></div>
                <span className="text-sm text-gray-300">
                  Leistungssteigerung von <span className="text-red-600 font-bold">+{currentStage.ps.increase} PS</span> und <span className="text-red-600 font-bold">+{currentStage.nm.increase} NM</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export { AppVersion1 };
import { useState } from 'react';
import { ChevronRight, AlertTriangle, Fuel, CircleDot, Wind, Volume2, Gauge } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';

export default function App6() {
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
    <div className="min-h-screen bg-white text-gray-900">
      {/* Top Bar */}
      <div className="bg-black text-white py-3">
        <div className="max-w-7xl mx-auto px-8">
          <div className="flex items-center justify-between text-xs tracking-[0.2em]">
            <span>TUNINGFILES-DOWNLOAD.COM</span>
            <span>PERFORMANCE CHIPTUNING</span>
          </div>
        </div>
      </div>

      {/* Main Header */}
      <div className="border-b-2 border-black">
        <div className="max-w-7xl mx-auto px-8 py-8">
          <div className="flex items-center gap-4">
            <img src={bmwLogo} alt="BMW Logo" className="w-16 h-16" />
            <div>
              <h1 className="text-4xl font-bold mb-1">BMW M3</h1>
              <div className="text-sm text-gray-500">Benzin | 3.0L | Turbo</div>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-8 py-12">
        {/* Layout: Sidebar + Content */}
        <div className="grid lg:grid-cols-12 gap-8">
          {/* Sidebar - Stage Selection */}
          <div className="lg:col-span-3">
            <div className="sticky top-8">
              <div className="text-xs tracking-[0.2em] text-gray-500 mb-6">TUNING-STUFEN</div>
              <div className="space-y-3">
                {stages.map((stage) => (
                  <button
                    key={stage.id}
                    onClick={() => setActiveStage(stage.id)}
                    className={`w-full text-left p-6 border-2 transition-all ${
                      activeStage === stage.id
                        ? 'bg-black text-white border-black'
                        : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-gray-400'
                    }`}
                  >
                    <div className="text-xs tracking-[0.2em] mb-2 opacity-70">STUFE</div>
                    <div className="text-xl font-bold mb-3">{stage.name.replace('STAGE ', '')}</div>
                    <div className="flex items-center gap-2 text-xs">
                      <span className={activeStage === stage.id ? 'text-red-400' : 'text-red-600'}>
                        +{stage.ps.increase} PS
                      </span>
                      <span className="opacity-50">|</span>
                      <span className={activeStage === stage.id ? 'text-red-400' : 'text-red-600'}>
                        +{stage.nm.increase} NM
                      </span>
                    </div>
                  </button>
                ))}
              </div>

              <div className="mt-8 p-6 bg-red-600 text-white">
                <div className="text-xs tracking-[0.2em] mb-3">INKLUDIERT</div>
                <div className="space-y-2 text-sm mb-6">
                  <div className="flex items-center gap-2">
                    <div className="w-1 h-1 bg-white"></div>
                    <span>VMAX Off</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-1 h-1 bg-white"></div>
                    <span>Kat. Off</span>
                  </div>
                </div>
                <button className="w-full bg-white text-red-600 font-bold py-3 text-sm tracking-[0.1em] hover:bg-gray-100 transition-all">
                  ANFRAGEN
                </button>
              </div>
            </div>
          </div>

          {/* Main Content */}
          <div className="lg:col-span-9">
            {/* Performance Cards */}
            <div className="grid md:grid-cols-2 gap-6 mb-8">
              {/* PS Card */}
              <div className="border-2 border-gray-200 p-8">
                <div className="flex items-center justify-between mb-8">
                  <div className="text-xs tracking-[0.2em] text-gray-500">LEISTUNG</div>
                  <div className="text-xs tracking-[0.2em] text-gray-400">PS</div>
                </div>

                <div className="mb-8">
                  <div className="text-xs text-gray-500 mb-2">Serienmäßig</div>
                  <div className="text-6xl font-bold text-gray-300 mb-6">{currentStage.ps.original}</div>

                  <div className="flex items-center gap-3 mb-4">
                    <ChevronRight className="w-5 h-5 text-red-600" />
                    <div className="h-px bg-red-600 flex-1"></div>
                  </div>

                  <div className="text-xs text-gray-500 mb-2">Nach {currentStage.name}</div>
                  <div className="text-6xl font-bold text-black">{currentStage.ps.tuned}</div>
                </div>

                <div className="border-t-2 border-gray-200 pt-6">
                  <div className="flex items-end justify-between">
                    <div>
                      <div className="text-xs text-gray-500 mb-1">Steigerung</div>
                      <div className="text-3xl font-bold text-red-600">+{currentStage.ps.increase}</div>
                    </div>
                    <div className="text-right">
                      <div className="text-xs text-gray-500 mb-1">Prozent</div>
                      <div className="text-3xl font-bold text-gray-400">
                        +{Math.round((currentStage.ps.increase / currentStage.ps.original) * 100)}%
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* NM Card */}
              <div className="border-2 border-gray-200 p-8">
                <div className="flex items-center justify-between mb-8">
                  <div className="text-xs tracking-[0.2em] text-gray-500">DREHMOMENT</div>
                  <div className="text-xs tracking-[0.2em] text-gray-400">NM</div>
                </div>

                <div className="mb-8">
                  <div className="text-xs text-gray-500 mb-2">Serienmäßig</div>
                  <div className="text-6xl font-bold text-gray-300 mb-6">{currentStage.nm.original}</div>

                  <div className="flex items-center gap-3 mb-4">
                    <ChevronRight className="w-5 h-5 text-red-600" />
                    <div className="h-px bg-red-600 flex-1"></div>
                  </div>

                  <div className="text-xs text-gray-500 mb-2">Nach {currentStage.name}</div>
                  <div className="text-6xl font-bold text-black">{currentStage.nm.tuned}</div>
                </div>

                <div className="border-t-2 border-gray-200 pt-6">
                  <div className="flex items-end justify-between">
                    <div>
                      <div className="text-xs text-gray-500 mb-1">Steigerung</div>
                      <div className="text-3xl font-bold text-red-600">+{currentStage.nm.increase}</div>
                    </div>
                    <div className="text-right">
                      <div className="text-xs text-gray-500 mb-1">Prozent</div>
                      <div className="text-3xl font-bold text-gray-400">
                        +{Math.round((currentStage.nm.increase / currentStage.nm.original) * 100)}%
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Visual Comparison */}
            <div className="border-2 border-gray-200 p-8">
              <div className="text-xs tracking-[0.2em] text-gray-500 mb-8">TUNING-OPTIONEN</div>

              <div className="mb-6">
                <div className="text-xs tracking-[0.2em] text-gray-500 mb-3">ECU HERSTELLER</div>
                <div className="text-sm font-bold">Bosch</div>
              </div>

              <div className="grid grid-cols-2 gap-x-8 gap-y-4">
                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <AlertTriangle className="w-5 h-5 text-gray-900" />
                    <span className="text-sm font-medium">DTC OFF</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <div className="w-5 h-5 flex items-center justify-center">
                      <span className="text-xs font-bold text-gray-900">E85</span>
                    </div>
                    <span className="text-sm font-medium">E85 FLEX-FUEL</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <CircleDot className="w-5 h-5 text-gray-900" />
                    <span className="text-sm font-medium">KALTSTART OFF</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <Wind className="w-5 h-5 text-gray-900" />
                    <span className="text-sm font-medium">KAT OFF</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <Volume2 className="w-5 h-5 text-gray-900" />
                    <span className="text-sm font-medium">Pops & Bangs</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <Gauge className="w-5 h-5 text-gray-900" />
                    <span className="text-sm font-medium">VMAX Off</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>

                <div className="flex items-center justify-between py-3 border-b border-gray-200">
                  <div className="flex items-center gap-3">
                    <div className="w-5 h-5 flex items-center justify-center">
                      <span className="text-xs font-bold text-gray-900">30</span>
                    </div>
                    <span className="text-sm font-medium">Vmax 30</span>
                  </div>
                  <div className="w-5 h-5 border-2 border-red-600 flex items-center justify-center">
                    <div className="w-2 h-2 bg-red-600"></div>
                  </div>
                </div>
              </div>
            </div>

            {/* Bottom Info */}
            <div className="mt-8 border-2 border-gray-200 p-8 bg-gray-50">
              <div className="text-xs tracking-[0.2em] text-gray-500 mb-4">HINWEIS</div>
              <p className="text-sm text-gray-700">
                Alle angegebenen Leistungssteigerungen basieren auf Prüfstandsmessungen unter optimalen Bedingungen.
                Die tatsächlichen Werte können je nach Fahrzeugzustand und Kraftstoffqualität variieren.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

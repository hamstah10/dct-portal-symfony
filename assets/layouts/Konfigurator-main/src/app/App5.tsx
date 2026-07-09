import { useState } from 'react';
import { TrendingUp, ArrowRight } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';

export default function App5() {
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
    <div className="min-h-screen bg-gray-50 text-gray-900">
      {/* Header */}
      <header className="bg-white border-b border-gray-200">
        <div className="max-w-6xl mx-auto px-8 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <img src={bmwLogo} alt="BMW Logo" className="w-10 h-10" />
              <div>
                <div className="text-xs tracking-[0.2em] text-gray-500 mb-1">CHIPTUNING</div>
                <h1 className="text-xl font-bold tracking-tight">BMW M3 TUNING</h1>
              </div>
            </div>
            <div className="text-sm text-gray-500">TUNINGFILES-DOWNLOAD.COM</div>
          </div>
        </div>
      </header>

      <div className="max-w-6xl mx-auto px-8 py-12">
        {/* Stage Selector */}
        <div className="mb-10">
          <div className="text-xs tracking-[0.2em] text-gray-500 mb-4">TUNING-STUFE AUSWÄHLEN</div>
          <div className="flex gap-2">
            {stages.map((stage) => (
              <button
                key={stage.id}
                onClick={() => setActiveStage(stage.id)}
                className={`px-8 py-4 text-sm font-bold border-2 transition-all ${
                  activeStage === stage.id
                    ? 'bg-black text-white border-black'
                    : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400'
                }`}
              >
                {stage.name}
              </button>
            ))}
          </div>
        </div>

        {/* Main Data Grid */}
        <div className="grid lg:grid-cols-2 gap-8 mb-10">
          {/* Leistung */}
          <div className="bg-white border border-gray-200 p-8">
            <div className="text-xs tracking-[0.2em] text-gray-500 mb-6">LEISTUNG</div>

            <div className="space-y-6">
              <div className="flex items-end justify-between pb-6 border-b border-gray-200">
                <div>
                  <div className="text-xs text-gray-500 mb-2">Original</div>
                  <div className="text-5xl font-bold text-gray-900">{currentStage.ps.original}</div>
                  <div className="text-sm text-gray-500 mt-1">PS</div>
                </div>

                <ArrowRight className="w-8 h-8 text-gray-300" />

                <div className="text-right">
                  <div className="text-xs text-gray-500 mb-2">Nach Tuning</div>
                  <div className="text-5xl font-bold text-black">{currentStage.ps.tuned}</div>
                  <div className="text-sm text-gray-500 mt-1">PS</div>
                </div>
              </div>

              <div className="flex items-center justify-between pt-2">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 bg-red-600 flex items-center justify-center">
                    <TrendingUp className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <div className="text-xs text-gray-500">Steigerung</div>
                    <div className="text-2xl font-bold text-red-600">+{currentStage.ps.increase}</div>
                  </div>
                </div>
                <div className="text-right">
                  <div className="text-3xl font-bold text-gray-400">
                    +{Math.round((currentStage.ps.increase / currentStage.ps.original) * 100)}%
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Drehmoment */}
          <div className="bg-white border border-gray-200 p-8">
            <div className="text-xs tracking-[0.2em] text-gray-500 mb-6">DREHMOMENT</div>

            <div className="space-y-6">
              <div className="flex items-end justify-between pb-6 border-b border-gray-200">
                <div>
                  <div className="text-xs text-gray-500 mb-2">Original</div>
                  <div className="text-5xl font-bold text-gray-900">{currentStage.nm.original}</div>
                  <div className="text-sm text-gray-500 mt-1">NM</div>
                </div>

                <ArrowRight className="w-8 h-8 text-gray-300" />

                <div className="text-right">
                  <div className="text-xs text-gray-500 mb-2">Nach Tuning</div>
                  <div className="text-5xl font-bold text-black">{currentStage.nm.tuned}</div>
                  <div className="text-sm text-gray-500 mt-1">NM</div>
                </div>
              </div>

              <div className="flex items-center justify-between pt-2">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 bg-red-600 flex items-center justify-center">
                    <TrendingUp className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <div className="text-xs text-gray-500">Steigerung</div>
                    <div className="text-2xl font-bold text-red-600">+{currentStage.nm.increase}</div>
                  </div>
                </div>
                <div className="text-right">
                  <div className="text-3xl font-bold text-gray-400">
                    +{Math.round((currentStage.nm.increase / currentStage.nm.original) * 100)}%
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Comparison Table */}
        <div className="bg-white border border-gray-200 mb-10">
          <div className="border-b border-gray-200 p-6">
            <div className="text-xs tracking-[0.2em] text-gray-500">LEISTUNGSVERGLEICH</div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-200">
                  <th className="px-6 py-4 text-left text-xs tracking-[0.2em] text-gray-500 font-normal">STUFE</th>
                  <th className="px-6 py-4 text-right text-xs tracking-[0.2em] text-gray-500 font-normal">LEISTUNG (PS)</th>
                  <th className="px-6 py-4 text-right text-xs tracking-[0.2em] text-gray-500 font-normal">STEIGERUNG</th>
                  <th className="px-6 py-4 text-right text-xs tracking-[0.2em] text-gray-500 font-normal">DREHMOMENT (NM)</th>
                  <th className="px-6 py-4 text-right text-xs tracking-[0.2em] text-gray-500 font-normal">STEIGERUNG</th>
                </tr>
              </thead>
              <tbody>
                <tr className="border-b border-gray-100 bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium">Original</td>
                  <td className="px-6 py-4 text-right text-sm font-bold">{currentStage.ps.original}</td>
                  <td className="px-6 py-4 text-right text-sm text-gray-400">-</td>
                  <td className="px-6 py-4 text-right text-sm font-bold">{currentStage.nm.original}</td>
                  <td className="px-6 py-4 text-right text-sm text-gray-400">-</td>
                </tr>
                {stages.map((stage) => (
                  <tr
                    key={stage.id}
                    className={`border-b border-gray-100 hover:bg-gray-50 cursor-pointer ${
                      activeStage === stage.id ? 'bg-black text-white' : ''
                    }`}
                    onClick={() => setActiveStage(stage.id)}
                  >
                    <td className={`px-6 py-4 text-sm font-medium ${activeStage === stage.id ? 'text-white' : 'text-gray-900'}`}>
                      {stage.name}
                    </td>
                    <td className={`px-6 py-4 text-right text-sm font-bold ${activeStage === stage.id ? 'text-white' : 'text-gray-900'}`}>
                      {stage.ps.tuned}
                    </td>
                    <td className={`px-6 py-4 text-right text-sm font-bold ${activeStage === stage.id ? 'text-red-400' : 'text-red-600'}`}>
                      +{stage.ps.increase}
                    </td>
                    <td className={`px-6 py-4 text-right text-sm font-bold ${activeStage === stage.id ? 'text-white' : 'text-gray-900'}`}>
                      {stage.nm.tuned}
                    </td>
                    <td className={`px-6 py-4 text-right text-sm font-bold ${activeStage === stage.id ? 'text-red-400' : 'text-red-600'}`}>
                      +{stage.nm.increase}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Bottom Info */}
        <div className="grid lg:grid-cols-2 gap-8">
          <div className="bg-white border border-gray-200 p-8">
            <div className="text-xs tracking-[0.2em] text-gray-500 mb-4">TUNING-OPTIONEN</div>
            <div className="space-y-3 mb-6">
              <div className="flex items-center justify-between py-2 border-b border-gray-100">
                <span className="text-sm">VMAX Off</span>
                <div className="w-5 h-5 bg-black flex items-center justify-center">
                  <div className="w-2 h-2 bg-white"></div>
                </div>
              </div>
              <div className="flex items-center justify-between py-2 border-b border-gray-100">
                <span className="text-sm">Kat. Off</span>
                <div className="w-5 h-5 bg-black flex items-center justify-center">
                  <div className="w-2 h-2 bg-white"></div>
                </div>
              </div>
            </div>
            <div className="text-xs text-gray-500">
              Alle Standard-Tuning-Optionen sind in den Leistungsstufen bereits enthalten.
            </div>
          </div>

          <div className="bg-white border border-gray-200 p-8 flex flex-col justify-between">
            <div>
              <div className="text-xs tracking-[0.2em] text-gray-500 mb-4">FAHRZEUG</div>
              <div className="space-y-2 mb-6">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-500">Modell</span>
                  <span className="font-bold">BMW M3</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-500">Kraftstoff</span>
                  <span className="font-bold">BENZIN</span>
                </div>
              </div>
            </div>
            <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 transition-all text-sm tracking-[0.1em]">
              KONTAKT AUFNEHMEN
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

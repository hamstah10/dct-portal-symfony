import { useState } from 'react';
import { Check, Gauge, Fuel, Zap, TrendingUp, Cpu } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';
import bmwCar from 'figma:asset/93a78ea6a4ebfe48d30cd0f2f45cfdafd28e23a9.png';

export default function App4() {
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
  const maxPS = Math.max(...stages.map(s => s.ps.tuned));
  const maxNM = Math.max(...stages.map(s => s.nm.tuned));

  return (
    <div className="min-h-screen bg-[#0a0a0a] text-white">
      {/* Top Header */}
      <div className="border-b border-white/10 bg-black">
        <div className="max-w-[1800px] mx-auto px-8 py-4 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <img src={bmwLogo} alt="BMW" className="w-10 h-10" />
            <div>
              <div className="text-xs tracking-[0.3em] text-gray-600">CHIPTUNING</div>
              <div className="text-xl font-bold">TUNINGFILES-DOWNLOAD.COM</div>
            </div>
          </div>
          <div className="flex items-center gap-6">
            <div className="flex items-center gap-2 text-sm text-gray-400">
              <Fuel className="w-4 h-4 text-red-600" />
              <span>BENZIN</span>
            </div>
            <div className="flex items-center gap-2 text-sm text-gray-400">
              <Cpu className="w-4 h-4 text-red-600" />
              <span>ECU TUNING</span>
            </div>
          </div>
        </div>
      </div>

      {/* Vehicle Section */}
      <div className="border-b border-white/10 bg-gradient-to-b from-black to-[#0a0a0a]">
        <div className="max-w-[1800px] mx-auto px-8 py-12">
          <div className="grid lg:grid-cols-5 gap-8 items-center">
            <div className="lg:col-span-2">
              <div className="text-xs tracking-[0.3em] text-gray-600 mb-3">FAHRZEUG</div>
              <h1 className="text-8xl font-bold mb-4 leading-none">BMW</h1>
              <div className="text-6xl font-bold text-red-600 mb-6">M3</div>
              <div className="w-24 h-1 bg-red-600"></div>
            </div>
            <div className="lg:col-span-3 relative">
              <div className="relative h-[300px] bg-gradient-to-r from-transparent via-red-600/5 to-transparent">
                <img
                  src={bmwCar}
                  alt="BMW M3"
                  className="absolute inset-0 w-full h-full object-contain"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Timeline Stage Selector */}
      <div className="border-b border-white/10 bg-black">
        <div className="max-w-[1800px] mx-auto px-8 py-8">
          <div className="text-xs tracking-[0.3em] text-gray-600 mb-6">LEISTUNGSSTUFE WÄHLEN</div>
          <div className="relative">
            {/* Timeline Line */}
            <div className="absolute top-6 left-0 right-0 h-0.5 bg-white/10"></div>
            <div 
              className="absolute top-6 left-0 h-0.5 bg-red-600 transition-all duration-500"
              style={{ width: `${(activeStage / stages.length) * 100}%` }}
            ></div>

            {/* Stage Buttons */}
            <div className="relative grid grid-cols-3 gap-4">
              {stages.map((stage, index) => (
                <button
                  key={stage.id}
                  onClick={() => setActiveStage(stage.id)}
                  className="relative"
                >
                  <div className={`border-2 p-6 transition-all ${
                    activeStage === stage.id
                      ? 'bg-red-600 border-red-600 text-white'
                      : 'bg-black border-white/10 text-gray-500 hover:border-white/30'
                  }`}>
                    {/* Stage Number Circle */}
                    <div className={`absolute -top-3 left-6 w-12 h-12 flex items-center justify-center border-2 ${
                      activeStage === stage.id
                        ? 'bg-red-600 border-red-600 text-white'
                        : 'bg-black border-white/10'
                    }`}>
                      <span className="text-xl font-bold">{stage.id}</span>
                    </div>
                    
                    <div className="mt-4">
                      <div className="text-xl font-bold mb-4">{stage.name}</div>
                      <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                          <div className={activeStage === stage.id ? 'text-white/80' : 'text-gray-600'}>PS</div>
                          <div className="text-2xl font-bold">{stage.ps.tuned}</div>
                        </div>
                        <div>
                          <div className={activeStage === stage.id ? 'text-white/80' : 'text-gray-600'}>NM</div>
                          <div className="text-2xl font-bold">{stage.nm.tuned}</div>
                        </div>
                      </div>
                      <div className={`mt-3 pt-3 border-t text-xs font-bold ${
                        activeStage === stage.id ? 'border-white/20' : 'border-white/10'
                      }`}>
                        +{stage.ps.increase} PS / +{stage.nm.increase} NM
                      </div>
                    </div>
                  </div>
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Performance Visualization */}
      <div className="max-w-[1800px] mx-auto px-8 py-16">
        <div className="grid lg:grid-cols-2 gap-12">
          {/* Power Chart */}
          <div>
            <div className="flex items-center gap-3 mb-8">
              <div className="w-12 h-12 bg-red-600 flex items-center justify-center">
                <Gauge className="w-6 h-6 text-white" />
              </div>
              <div>
                <div className="text-xs tracking-[0.3em] text-gray-600">LEISTUNG</div>
                <div className="text-2xl font-bold">POWER OUTPUT</div>
              </div>
            </div>

            {/* Vertical Bar Chart */}
            <div className="relative h-[400px] bg-black border border-white/10 p-8">
              <div className="h-full flex items-end justify-around gap-8">
                {/* Original Bar */}
                <div className="flex-1 flex flex-col items-center">
                  <div className="text-xs text-gray-600 mb-2 text-center h-8">ORIGINAL</div>
                  <div 
                    className="w-full bg-white/10 relative transition-all duration-500"
                    style={{ height: `${(currentStage.ps.original / maxPS) * 100}%` }}
                  >
                    <div className="absolute top-0 left-0 right-0 h-1 bg-white/30"></div>
                  </div>
                  <div className="mt-4">
                    <div className="text-4xl font-bold">{currentStage.ps.original}</div>
                    <div className="text-xs text-gray-600 text-center">PS</div>
                  </div>
                </div>

                {/* Arrow/Plus */}
                <div className="flex items-center justify-center pb-16">
                  <div className="text-center">
                    <TrendingUp className="w-8 h-8 text-red-600 mx-auto mb-2" />
                    <div className="text-red-600 font-bold text-xl">+{currentStage.ps.increase}</div>
                  </div>
                </div>

                {/* Tuned Bar */}
                <div className="flex-1 flex flex-col items-center">
                  <div className="text-xs text-gray-600 mb-2 text-center h-8">NACH TUNING</div>
                  <div 
                    className="w-full bg-gradient-to-t from-red-600 to-red-500 relative transition-all duration-500"
                    style={{ height: `${(currentStage.ps.tuned / maxPS) * 100}%` }}
                  >
                    <div className="absolute top-0 left-0 right-0 h-1 bg-red-400"></div>
                  </div>
                  <div className="mt-4">
                    <div className="text-4xl font-bold text-red-600">{currentStage.ps.tuned}</div>
                    <div className="text-xs text-gray-600 text-center">PS</div>
                  </div>
                </div>
              </div>

              {/* Percentage Increase */}
              <div className="absolute top-4 right-4 bg-red-600 px-4 py-2">
                <div className="text-xs text-white/80">STEIGERUNG</div>
                <div className="text-2xl font-bold">+{((currentStage.ps.increase / currentStage.ps.original) * 100).toFixed(0)}%</div>
              </div>
            </div>
          </div>

          {/* Torque Chart */}
          <div>
            <div className="flex items-center gap-3 mb-8">
              <div className="w-12 h-12 bg-red-600 flex items-center justify-center">
                <Zap className="w-6 h-6 text-white" />
              </div>
              <div>
                <div className="text-xs tracking-[0.3em] text-gray-600">DREHMOMENT</div>
                <div className="text-2xl font-bold">TORQUE OUTPUT</div>
              </div>
            </div>

            {/* Vertical Bar Chart */}
            <div className="relative h-[400px] bg-black border border-white/10 p-8">
              <div className="h-full flex items-end justify-around gap-8">
                {/* Original Bar */}
                <div className="flex-1 flex flex-col items-center">
                  <div className="text-xs text-gray-600 mb-2 text-center h-8">ORIGINAL</div>
                  <div 
                    className="w-full bg-white/10 relative transition-all duration-500"
                    style={{ height: `${(currentStage.nm.original / maxNM) * 100}%` }}
                  >
                    <div className="absolute top-0 left-0 right-0 h-1 bg-white/30"></div>
                  </div>
                  <div className="mt-4">
                    <div className="text-4xl font-bold">{currentStage.nm.original}</div>
                    <div className="text-xs text-gray-600 text-center">NM</div>
                  </div>
                </div>

                {/* Arrow/Plus */}
                <div className="flex items-center justify-center pb-16">
                  <div className="text-center">
                    <TrendingUp className="w-8 h-8 text-red-600 mx-auto mb-2" />
                    <div className="text-red-600 font-bold text-xl">+{currentStage.nm.increase}</div>
                  </div>
                </div>

                {/* Tuned Bar */}
                <div className="flex-1 flex flex-col items-center">
                  <div className="text-xs text-gray-600 mb-2 text-center h-8">NACH TUNING</div>
                  <div 
                    className="w-full bg-gradient-to-t from-red-600 to-red-500 relative transition-all duration-500"
                    style={{ height: `${(currentStage.nm.tuned / maxNM) * 100}%` }}
                  >
                    <div className="absolute top-0 left-0 right-0 h-1 bg-red-400"></div>
                  </div>
                  <div className="mt-4">
                    <div className="text-4xl font-bold text-red-600">{currentStage.nm.tuned}</div>
                    <div className="text-xs text-gray-600 text-center">NM</div>
                  </div>
                </div>
              </div>

              {/* Percentage Increase */}
              <div className="absolute top-4 right-4 bg-red-600 px-4 py-2">
                <div className="text-xs text-white/80">STEIGERUNG</div>
                <div className="text-2xl font-bold">+{((currentStage.nm.increase / currentStage.nm.original) * 100).toFixed(0)}%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Section */}
      <div className="border-t border-white/10 bg-black">
        <div className="max-w-[1800px] mx-auto px-8 py-12">
          <div className="grid lg:grid-cols-3 gap-8">
            {/* Tuning Options */}
            <div className="lg:col-span-2">
              <div className="text-xs tracking-[0.3em] text-gray-600 mb-6">INKLUDIERTE TUNING-OPTIONEN</div>
              <div className="grid grid-cols-2 gap-4">
                <div className="flex items-center gap-3 bg-white/5 p-4 border border-white/10">
                  <div className="w-10 h-10 bg-red-600 flex items-center justify-center">
                    <Check className="w-5 h-5" />
                  </div>
                  <div>
                    <div className="font-bold">VMAX Off</div>
                    <div className="text-xs text-gray-600">Geschwindigkeitsbegrenzung entfernt</div>
                  </div>
                </div>
                <div className="flex items-center gap-3 bg-white/5 p-4 border border-white/10">
                  <div className="w-10 h-10 bg-red-600 flex items-center justify-center">
                    <Check className="w-5 h-5" />
                  </div>
                  <div>
                    <div className="font-bold">Kat. Off</div>
                    <div className="text-xs text-gray-600">Katalysator-Optimierung</div>
                  </div>
                </div>
              </div>
            </div>

            {/* CTA */}
            <div className="flex flex-col justify-end">
              <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-6 transition-all border-4 border-red-600 hover:border-red-700">
                <div className="text-xs tracking-[0.3em] mb-1">JETZT</div>
                <div className="text-xl">KONTAKT AUFNEHMEN</div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

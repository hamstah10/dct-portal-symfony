import { useState } from 'react';
import { Check, Gauge, Wrench, Fuel, Zap, ArrowRight } from 'lucide-react';
import bmwLogo from 'figma:asset/6b52949a68d726720eda8b7b738e5f1184568965.png';
import bmwCar from 'figma:asset/93a78ea6a4ebfe48d30cd0f2f45cfdafd28e23a9.png';

export default function App3() {
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
    <div className="min-h-screen bg-black text-white grid lg:grid-cols-2">
      {/* Left Side - Image */}
      <div className="relative bg-gradient-to-br from-gray-900 to-black flex items-center justify-center overflow-hidden">
        <img
          src={bmwCar}
          alt="BMW M3"
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-black/40"></div>
        
        {/* Logo Overlay */}
        <div className="absolute top-8 left-8">
          <img src={bmwLogo} alt="BMW" className="w-16 h-16" />
        </div>

        {/* Website Title Overlay */}
        <div className="absolute bottom-8 left-8 right-8">
          <div className="text-xs tracking-[0.3em] text-red-600 mb-2">CHIPTUNING</div>
          <div className="text-2xl font-bold">TUNINGFILES-DOWNLOAD.COM</div>
        </div>
      </div>

      {/* Right Side - Data */}
      <div className="bg-black p-12 flex flex-col">
        {/* Vehicle Title */}
        <div className="mb-12">
          <div className="text-xs tracking-[0.3em] text-gray-600 mb-3">MODELL</div>
          <h1 className="text-7xl font-bold mb-4">BMW M3</h1>
          <div className="h-1 w-20 bg-red-600"></div>
        </div>

        {/* Stage Selector */}
        <div className="mb-12">
          <div className="text-xs tracking-[0.3em] text-gray-600 mb-4">LEISTUNGSSTUFE</div>
          <div className="grid grid-cols-3 gap-3">
            {stages.map((stage) => (
              <button
                key={stage.id}
                onClick={() => setActiveStage(stage.id)}
                className={`py-4 font-bold border-2 transition-all ${
                  activeStage === stage.id
                    ? 'bg-red-600 border-red-600 text-white'
                    : 'bg-transparent border-white/10 text-gray-500 hover:border-white/30'
                }`}
              >
                {stage.name}
              </button>
            ))}
          </div>
        </div>

        {/* Performance Data */}
        <div className="flex-1 space-y-10">
          {/* PS */}
          <div>
            <div className="flex items-center gap-2 mb-4">
              <Gauge className="w-5 h-5 text-red-600" />
              <div className="text-xs tracking-[0.3em] text-gray-600">LEISTUNG</div>
            </div>
            <div className="grid grid-cols-3 items-end">
              <div>
                <div className="text-xs text-gray-600 mb-1">ORIGINAL</div>
                <div className="text-5xl font-bold">{currentStage.ps.original}</div>
                <div className="text-sm text-gray-600">PS</div>
              </div>
              <div className="flex justify-center items-center pb-4">
                <div className="text-center">
                  <ArrowRight className="w-8 h-8 text-red-600 mx-auto mb-1" />
                  <div className="text-xs text-red-600 font-bold">+{currentStage.ps.increase}</div>
                </div>
              </div>
              <div className="text-right">
                <div className="text-xs text-gray-600 mb-1">GETUNT</div>
                <div className="text-5xl font-bold text-red-600">{currentStage.ps.tuned}</div>
                <div className="text-sm text-gray-600">PS</div>
              </div>
            </div>
            <div className="mt-4 h-px bg-gradient-to-r from-white/20 via-red-600 to-white/20"></div>
          </div>

          {/* NM */}
          <div>
            <div className="flex items-center gap-2 mb-4">
              <Zap className="w-5 h-5 text-red-600" />
              <div className="text-xs tracking-[0.3em] text-gray-600">DREHMOMENT</div>
            </div>
            <div className="grid grid-cols-3 items-end">
              <div>
                <div className="text-xs text-gray-600 mb-1">ORIGINAL</div>
                <div className="text-5xl font-bold">{currentStage.nm.original}</div>
                <div className="text-sm text-gray-600">NM</div>
              </div>
              <div className="flex justify-center items-center pb-4">
                <div className="text-center">
                  <ArrowRight className="w-8 h-8 text-red-600 mx-auto mb-1" />
                  <div className="text-xs text-red-600 font-bold">+{currentStage.nm.increase}</div>
                </div>
              </div>
              <div className="text-right">
                <div className="text-xs text-gray-600 mb-1">GETUNT</div>
                <div className="text-5xl font-bold text-red-600">{currentStage.nm.tuned}</div>
                <div className="text-sm text-gray-600">NM</div>
              </div>
            </div>
            <div className="mt-4 h-px bg-gradient-to-r from-white/20 via-red-600 to-white/20"></div>
          </div>

          {/* Options */}
          <div>
            <div className="text-xs tracking-[0.3em] text-gray-600 mb-4">INKLUDIERTE OPTIONEN</div>
            <div className="grid grid-cols-2 gap-4">
              <div className="flex items-center gap-2 text-sm">
                <div className="w-6 h-6 bg-red-600 flex items-center justify-center">
                  <Check className="w-4 h-4" />
                </div>
                <span>VMAX Off</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <div className="w-6 h-6 bg-red-600 flex items-center justify-center">
                  <Check className="w-4 h-4" />
                </div>
                <span>Kat. Off</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <div className="w-6 h-6 bg-white/10 flex items-center justify-center">
                  <Fuel className="w-4 h-4 text-red-600" />
                </div>
                <span className="text-gray-600">BENZIN</span>
              </div>
            </div>
          </div>
        </div>

        {/* CTA */}
        <div className="mt-auto pt-8">
          <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-5 transition-all flex items-center justify-between px-6">
            <span className="tracking-wider">KONTAKT</span>
            <ArrowRight className="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  );
}

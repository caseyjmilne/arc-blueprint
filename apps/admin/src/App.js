import { HashRouter, Routes as RouterRoutes, Route } from 'react-router-dom';
import Header from './components/Header';
import Dashboard from './pages/Dashboard';
import Schemas from './pages/Schemas';

export default function App() {
  return (
    <HashRouter>
      <div className="-ml-[22px] -mt-[10px] -mr-[20px] w-[calc(100%+40px)] bg-zinc-200 min-h-screen text-gray-400">
        <Header />

        {/* Main Content */}
        <div className="px-8 py-6">
          <RouterRoutes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/schemas" element={<Schemas />} />
          </RouterRoutes>
        </div>
      </div>
    </HashRouter>
  );
}

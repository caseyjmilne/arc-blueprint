import { NavLink } from 'react-router-dom';
import { __ } from '@wordpress/i18n';

export default function Header() {
    return (
        <header className="bg-zinc-300 px-3 py-1">
            <div className="flex items-center">
                <h1 className="font-playfair !text-lg !font-black !text-gray-800 !m-0 !p-0">
                    ARC\Blueprint
                </h1>
                <nav className="flex gap-6 ml-8">
                    <NavLink
                        to="/"
                        className="text-sm font-medium !text-gray-700 hover:!text-gray-800 hover:!border-none"
                        end
                    >
                        {__('Dashboard', 'arc-blueprint')}
                    </NavLink>
                    <NavLink
                        to="/schemas"
                        className="text-sm font-medium !text-gray-700 hover:!text-gray-800 hover:!border-none"
                    >
                        {__('Schemas', 'arc-blueprint')}
                    </NavLink>
                </nav>
            </div>
        </header>
    );
}

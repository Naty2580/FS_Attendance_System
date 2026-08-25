import '../css/app.css';

import './lib/i18n';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { AttendanceProvider } from './Contexts/AttendanceContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Force the page component resolution explicitly
        const page = resolvePageComponent(
            `./Pages/${name}.jsx`, // or .tsx if using TypeScript
            import.meta.glob('./Pages/**/*.jsx')
        );
        
        if (!page) {
            console.error(`Page component not found: ./Pages/${name}.jsx`);
        }
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <AttendanceProvider>
                <App {...props} />
             </AttendanceProvider>
        );
    },
    progress: { color: '#4B5563' }, 
});
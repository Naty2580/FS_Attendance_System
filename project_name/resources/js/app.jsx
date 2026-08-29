import './lib/i18n';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { AttendanceProvider } from './Contexts/AttendanceContext';
// 🌟 ADD THIS IMPORT
import { Toaster } from 'sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Sunday School';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <AttendanceProvider>
                <App {...props} />
                {/* 🌟 ADD THIS COMPONENT (Rich Colors make it look highly professional) */}
                <Toaster position="bottom-center" richColors />
            </AttendanceProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
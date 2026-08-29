import React, { useEffect } from 'react'; // Add useEffect
import { usePage, Link } from '@inertiajs/react';
import { WifiOff, CloudUpload, User, Globe } from 'lucide-react';
import { useAttendance } from '../Contexts/AttendanceContext';
import { useTranslation } from 'react-i18next';
// 🌟 ADD THIS IMPORT
import { toast } from 'sonner';

export default function MobileLayout({ children, header }) {
    const { auth, flash } = usePage().props; // Extract flash from props
    const { isOnline, isSyncing, pendingCount } = useAttendance();
    const { t, i18n } = useTranslation();

    // 🌟 ADD THIS EFFECT: Watch for backend messages
    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
        if (flash.warning) {
            toast.warning(flash.warning);
        }
    }, [flash]);

    
    const toggleLanguage = () => {
        const newLang = i18n.language === 'en' ? 'am' : 'en';
        i18n.changeLanguage(newLang);
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* Top Navigation Bar */}
            <header className="bg-primary-900 text-white shadow-md sticky top-0 z-50">
                <div className="max-w-md mx-auto px-4 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <span className="font-bold text-lg tracking-tight">{t('sunday_school')}</span>
                    </div>

                    <div className="flex items-center gap-4">

                        <button onClick={toggleLanguage} className="flex items-center gap-1 text-xs font-bold bg-white/20 px-2 py-1 rounded-full hover:bg-white/30 transition">
                            <Globe size={14} /> {i18n.language === 'en' ? 'አማ' : 'EN'}
                        </button>
                        
                        {/* Offline / Sync Indicator */}
                        {!isOnline ? (
                            <div className="flex items-center text-warning gap-1 text-xs font-medium bg-white/10 px-2 py-1 rounded-full">
                                <WifiOff size={14} /> Offline
                            </div>
                        ) : pendingCount > 0 ? (
                            <button className="flex items-center text-secondary-400 gap-1 text-xs font-medium bg-white/10 px-2 py-1 rounded-full animate-pulse">
                                <CloudUpload size={14} /> {isSyncing ? 'Syncing...' : `${pendingCount} Pending`}
                            </button>
                        ) : null}

                        {/* Profile Link */}
                        <Link href={route('profile.edit')} className="p-2 bg-white/10 rounded-full hover:bg-white/20 transition">
                            <User size={18} />
                        </Link>
                    </div>
                </div>
            </header>

            {/* Page Header (Optional) */}
            {header && (
                <div className="bg-white border-b shadow-sm">
                    <div className="max-w-md mx-auto px-4 py-3">
                        {header}
                    </div>
                </div>
            )}

            {/* Main Content Area - Constrained to Mobile Width */}
            <main className="flex-1 w-full max-w-md mx-auto p-4">
                {children}
            </main>
        </div>
    );
}
import React from 'react';
import { Head, router } from '@inertiajs/react';
import MobileLayout from '../../Layouts/MobileLayout';
import { Card, CardContent } from '../../Components/ui/card';
import { Badge } from '../../Components/ui/badge';
import { Clock, PlayCircle, ChevronRight, Lock } from 'lucide-react';
import { motion } from 'framer-motion';
import { useTranslation } from 'react-i18next';

export default function Dashboard({ todaySessions }) {
    const { t } = useTranslation();

    const getStatusDisplay = (window, status, isStartable, expectedStart) => {
        // If it exists, show its current state
        if (status === 'created') {
            switch (window) {
                case 'present': return { label: t('open_present'), color: 'bg-green-600', icon: null };
                case 'late': return { label: t('open_late'), color: 'bg-yellow-500', icon: null };
                case 'closed': return { label: t('closed'), color: 'bg-red-600', icon: <Lock size={14}/> };
                default: return { label: 'Unknown', color: 'bg-gray-500', icon: null };
            }
        }
        
        // If it hasn't been created yet, evaluate the boundaries
        if (isStartable) return { label: 'Tap to Start', color: 'bg-blue-600', icon: <PlayCircle size={14}/> };
        if (window === 'expired') return { label: 'Expired', color: 'bg-red-600', icon: <Lock size={14}/> };
        
        // Default: Too early
        return { label: `Starts at ${expectedStart || 'TBD'}`, color: 'bg-gray-400', icon: <Lock size={14}/> };
    };

    const handleCardClick = (session) => {
        if (session.status === 'created') {
            // Already started, go to Roster
            router.get(route('attendance.roster', session.session_id));
        } else if (session.is_startable) {
            // Safe to start
            router.post(route('dashboard.start'), {
                schedule_id: session.schedule_id,
                class_id: session.class_id
            });
        }
        // If too early or expired, do nothing on click
    };

    return (
        <MobileLayout header={<h2 className="font-semibold text-xl text-gray-800">{t('todays_classes')}</h2>}>
            <Head title="Dashboard" />
            <div className="space-y-4">
                {(!todaySessions || todaySessions.length === 0) ? (
                    <div className="text-center py-12 text-gray-500">
                        <p>{t('no_classes')}</p>
                    </div>
                ) : (
                    todaySessions.map((session, index) => {
                        const display = getStatusDisplay(session.current_window, session.status, session.is_startable, session.expected_start);
                        const isClickable = session.status === 'created' || session.is_startable;
                        
                        return (
                            <motion.div key={`${session.schedule_id}-${session.class_id}`} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.1 }}>
                                <Card 
                                    onClick={() => handleCardClick(session)} 
                                    className={`transition-transform border-gray-200 ${isClickable ? 'active:scale-[0.98] cursor-pointer shadow-sm hover:shadow-md' : 'opacity-70 bg-gray-50'}`}
                                >
                                    <CardContent className="p-4 flex items-center justify-between">
                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">
                                                <h3 className={`font-bold text-lg ${isClickable ? 'text-blue-900' : 'text-gray-700'}`}>{session.class_name}</h3>
                                                <Badge variant="outline" className="text-xs">{session.type}</Badge>
                                            </div>
                                            
                                            <div className="flex items-center text-sm text-gray-600 gap-1.5">
                                                <Clock size={16} />
                                                <span>Expected: {session.expected_start || 'Anytime'}</span>
                                            </div>

                                            <Badge className={`${display.color} text-white hover:${display.color} border-none flex gap-1 items-center w-fit`}>
                                                {display.icon}
                                                {display.label}
                                            </Badge>
                                        </div>
                                        {isClickable && (
                                            <div className="text-gray-400">
                                                <ChevronRight size={24} />
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </motion.div>
                        );
                    })
                )}
            </div>
        </MobileLayout>
    );
}
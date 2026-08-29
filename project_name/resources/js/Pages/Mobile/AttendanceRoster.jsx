import React, { useState, useMemo } from 'react';
import MobileLayout from '../../Layouts/MobileLayout';
import { useAttendance } from '../../Contexts/AttendanceContext';
import { ArrowLeft, Search } from 'lucide-react';
import { useTranslation } from 'react-i18next'; 
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react'; // Add an icon
import { motion, AnimatePresence } from 'framer-motion';

export default function AttendanceRoster({ sessionClass, students, existingRecords }) {
    const { markAttendance } = useAttendance();
    const { t } = useTranslation(); // <-- ADDED THIS
    const [searchQuery, setSearchQuery] = useState('');
    const [records, setRecords] = useState(existingRecords || {});

    const totalStudents = students.length;
    const markedCount = Object.keys(records).length;
    const unmarkedCount = totalStudents - markedCount;
    const pendingCount = Object.values(records).filter(status => status === 'pending').length;
    const isSyncing = pendingCount > 0; // Assuming syncing is true if there are pending records

    const filteredStudents = useMemo(() => {
        return students.filter(s => 
            s.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
            s.student_number.includes(searchQuery)
        );
    }, [students, searchQuery]);

    const handleMark = (studentId, statusCode) => {
        setRecords(prev => ({ ...prev, [studentId]: statusCode }));
        markAttendance(sessionClass.id, studentId, statusCode);
    };

    const handleEndSession = () => {
        if (confirm("Are you sure you want to end this session? You will not be able to change attendance after this.")) {
            router.post(route('attendance.end', sessionClass.id));
        }
    };

    const isClosed = sessionClass.current_window === 'closed';
    const isLateWindow = sessionClass.current_window === 'late';
    const isNotStarted = sessionClass.current_window === 'not_started';

    return (
        <MobileLayout>
            <Head title={`Attendance - ${sessionClass.class_name}`} />

            <div className="mb-6 space-y-4">
                <div className="flex items-center gap-3">
                    <Link href={route('dashboard')} className="p-2 bg-gray-200 rounded-full">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <h2 className="font-bold text-xl leading-tight">{sessionClass.class_name}</h2>
                        <p className="text-sm text-gray-500">{sessionClass.type}</p>
                    </div>
                </div>

                <div className="flex bg-white rounded-xl shadow-sm border p-3 justify-around text-center">
                    <div>
                        <p className="text-2xl font-bold text-blue-600">{totalStudents}</p>
                        <p className="text-xs text-gray-500 uppercase">{t('total')}</p>
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-green-600">{markedCount}</p>
                        <p className="text-xs text-gray-500 uppercase">{t('marked')}</p>
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-red-500">{unmarkedCount}</p>
                        <p className="text-xs text-gray-500 uppercase">{t('unmarked')}</p>
                    </div>
                </div>

                <div className="relative">
                    <Search className="absolute left-3 top-3 text-gray-400" size={20} />
                    <input 
                        type="text" 
                        placeholder={t('search_students')} 
                        className="w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>
                {/* BULK PRESENT BUTTON */}
                <AnimatePresence>
                    {!isClosed && !isLateWindow && unmarkedCount > 0 && pendingCount === 0 && !isSyncing && (
                        <motion.div
                            initial={{ opacity: 0, height: 0, marginTop: 0 }}
                            animate={{ opacity: 1, height: 'auto', marginTop: 8 }}
                            exit={{ opacity: 0, height: 0, marginTop: 0 }}
                            className="overflow-hidden"
                        >
                            <button 
                                onClick={() => {
                                    if(confirm(t('mark_rest_present') + "?")) {
                                        router.post(route('attendance.bulk-present', sessionClass.id));
                                    }
                                }}
                                className="w-full py-3 bg-green-50 text-green-700 font-bold rounded-xl border border-green-200 hover:bg-green-100 active:bg-green-200 active:scale-[0.98] transition-all"
                            >
                                ✓ {t('mark_rest_present')}
                            </button>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            <div className="space-y-3 pb-20">
                {isNotStarted && (
                    <div className="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-center font-medium">
                        {t('not_started_warning')}
                    </div>
                )}

                {isClosed && (
                    <div className="bg-red-100 text-red-800 p-4 rounded-lg text-center font-medium">
                        {t('closed_warning')}
                    </div>
                )}

               <AnimatePresence>
                    {!isNotStarted && filteredStudents.map((student, index) => {
                        const currentStatus = records[student.id];

                        return (
                            <motion.div 
                                key={student.id} 
                                // 🌟 Cascading entrance animation
                                initial={{ opacity: 0, y: 15 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, scale: 0.95 }}
                                transition={{ duration: 0.2, delay: index * 0.05 }}
                                className="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3"
                            >
                                <div className="flex justify-between items-center">
                                    <h3 className="font-semibold text-lg text-gray-800">{student.name}</h3>
                                    <span className="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full border">
                                        {student.student_number}
                                    </span>
                                </div>

                                {/* 🌟 Buttons with active:scale-95 for haptic feel and smooth color transitions */}
                                <div className="grid grid-cols-4 gap-2 h-14">
                                    <button 
                                        disabled={isClosed || isLateWindow} 
                                        onClick={() => handleMark(student.id, 'present')}
                                        className={`rounded-lg font-bold text-sm transition-all duration-200 active:scale-95 ${
                                            currentStatus === 'present' 
                                                ? 'bg-green-500 text-white shadow-[inset_0px_4px_4px_rgba(0,0,0,0.15)] scale-[0.98]' 
                                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200'
                                        }`}>
                                        {t('present_btn')}
                                    </button>

                                    <button 
                                        disabled={isClosed || !isLateWindow} 
                                        onClick={() => handleMark(student.id, 'late')}
                                        className={`rounded-lg font-bold text-sm transition-all duration-200 active:scale-95 ${
                                            currentStatus === 'late' 
                                                ? 'bg-yellow-500 text-white shadow-[inset_0px_4px_4px_rgba(0,0,0,0.15)] scale-[0.98]' 
                                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200'
                                        }`}>
                                        {t('late_btn')}
                                    </button>

                                    <button 
                                        disabled={isClosed} 
                                        onClick={() => handleMark(student.id, 'permission')}
                                        className={`rounded-lg font-bold text-sm transition-all duration-200 active:scale-95 ${
                                            currentStatus === 'permission' 
                                                ? 'bg-blue-500 text-white shadow-[inset_0px_4px_4px_rgba(0,0,0,0.15)] scale-[0.98]' 
                                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200'
                                        }`}>
                                        {t('permission_btn')}
                                    </button>

                                    <button 
                                        disabled={isClosed} 
                                        onClick={() => handleMark(student.id, 'absent')}
                                        className={`rounded-lg font-bold text-sm transition-all duration-200 active:scale-95 ${
                                            currentStatus === 'absent' 
                                                ? 'bg-red-500 text-white shadow-[inset_0px_4px_4px_rgba(0,0,0,0.15)] scale-[0.98]' 
                                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed border border-gray-200'
                                        }`}>
                                        {t('absent_btn')}
                                    </button>
                                </div>
                            </motion.div>
                        );
                    })}
                </AnimatePresence>

                  {!isClosed && (markedCount === totalStudents) && totalStudents > 0 && (
                <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50">
                    <div className="max-w-md mx-auto">
                        <button 
                            onClick={handleEndSession}
                            // 🛑 SAFETY GATE: Disable button if offline queue has data or is currently uploading
                            disabled={pendingCount > 0 || isSyncing}
                            className={`w-full flex items-center justify-center gap-2 font-bold py-4 rounded-xl shadow-lg transition-all ${
                                pendingCount > 0 || isSyncing 
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                    : 'bg-blue-600 hover:bg-blue-700 text-white active:scale-[0.98]'
                            }`}
                        >
                            {pendingCount > 0 || isSyncing ? (
                                // Show loading spinner and explanation if syncing
                                <>
                                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Syncing... Please wait
                                </>
                            ) : (
                                // Normal button state
                                <>
                                    <CheckCircle2 size={20} />
                                    {t('end_session')}
                                </>
                            )}
                        </button>
                    </div>
                </div>
            )}
            </div>
        </MobileLayout>
    );
}
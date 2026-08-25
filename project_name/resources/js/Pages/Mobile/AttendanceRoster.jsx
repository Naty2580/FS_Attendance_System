import React, { useState, useMemo } from 'react';
import MobileLayout from '../../Layouts/MobileLayout';
import { useAttendance } from '../../Contexts/AttendanceContext';
import { ArrowLeft, Search } from 'lucide-react';
import { useTranslation } from 'react-i18next'; 
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react'; // Add an icon

export default function AttendanceRoster({ sessionClass, students, existingRecords }) {
    const { markAttendance } = useAttendance();
    const { t } = useTranslation(); // <-- ADDED THIS
    const [searchQuery, setSearchQuery] = useState('');
    const [records, setRecords] = useState(existingRecords || {});

    const totalStudents = students.length;
    const markedCount = Object.keys(records).length;
    const unmarkedCount = totalStudents - markedCount;

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

                {!isNotStarted && filteredStudents.map(student => {
                    const currentStatus = records[student.id];

                    return (
                        <div key={student.id} className="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3">
                            <div className="flex justify-between items-center">
                                <h3 className="font-semibold text-lg">{student.name}</h3>
                                <span className="text-xs text-gray-400">{student.student_number}</span>
                            </div>

                           <div className="grid grid-cols-4 gap-2 h-14">
                                {/* PRESENT BUTTON */}
                                <button 
                                    disabled={isClosed || isLateWindow} 
                                    onClick={() => handleMark(student.id, 'present')}
                                    className={`rounded-lg font-bold text-sm transition-colors ${
                                        currentStatus === 'present' 
                                            ? 'bg-green-500 text-white shadow-inner' 
                                            : 'bg-gray-100 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed'
                                    }`}>
                                    {t('present_btn')}
                                </button>

                                {/* LATE BUTTON - Now disabled if Closed AND disabled if in Present window! */}
                                <button 
                                    disabled={isClosed || !isLateWindow} 
                                    onClick={() => handleMark(student.id, 'late')}
                                    className={`rounded-lg font-bold text-sm transition-colors ${
                                        currentStatus === 'late' 
                                            ? 'bg-yellow-500 text-white shadow-inner' 
                                            : 'bg-gray-100 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed'
                                    }`}>
                                    {t('late_btn')}
                                </button>

                                {/* PERMISSION BUTTON */}
                                <button 
                                    disabled={isClosed} 
                                    onClick={() => handleMark(student.id, 'permission')}
                                    className={`rounded-lg font-bold text-sm transition-colors ${
                                        currentStatus === 'permission' 
                                            ? 'bg-blue-500 text-white shadow-inner' 
                                            : 'bg-gray-100 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed'
                                    }`}>
                                    {t('permission_btn')}
                                </button>

                                {/* ABSENT BUTTON */}
                                <button 
                                    disabled={isClosed || !isLateWindow} 
                                    onClick={() => handleMark(student.id, 'absent')}
                                    className={`rounded-lg font-bold text-sm transition-colors ${
                                        currentStatus === 'absent' 
                                            ? 'bg-red-500 text-white shadow-inner' 
                                            : 'bg-gray-100 text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed'
                                    }`}>
                                    {t('absent_btn')}
                                </button>
                            </div>
                        </div>
                    );
                })}

                 {!isClosed && (markedCount === totalStudents) && totalStudents > 0 && (
                <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50">
                    <div className="max-w-md mx-auto">
                        <button 
                            onClick={handleEndSession}
                            className="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all active:scale-[0.98]"
                        >
                            <CheckCircle2 size={20} />
                            {t('end_session')}
                        </button>
                    </div>
                </div>
            )}
            </div>
        </MobileLayout>
    );
}
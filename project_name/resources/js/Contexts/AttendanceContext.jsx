import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { syncQueue } from '../lib/offlineDb';
import { processSyncQueue } from '../lib/syncService';
import { useNetworkStatus } from '../lib/useNetworkStatus';
import { ulid } from 'ulidx'; // We need a ULID generator for the client

const AttendanceContext = createContext();

export const AttendanceProvider = ({ children }) => {
    const isOnline = useNetworkStatus();
    const [isSyncing, setIsSyncing] = useState(false);
    const [pendingCount, setPendingCount] = useState(0);

    // Update pending count from IndexedDB
    const refreshPendingCount = useCallback(async () => {
        const records = await syncQueue.getAll();
        setPendingCount(records.length);
    }, []);

    // Attempt sync
    const triggerSync = useCallback(async () => {
        if (!isOnline || isSyncing) return;
        
        setIsSyncing(true);
        const result = await processSyncQueue();
        await refreshPendingCount();
        setIsSyncing(false);
        
        return result;
    }, [isOnline, isSyncing, refreshPendingCount]);

    // Auto-sync when coming back online
    useEffect(() => {
        if (isOnline) {
            triggerSync();
        }
    }, [isOnline, triggerSync]);

    // Initial load
    useEffect(() => {
        refreshPendingCount();
    }, [refreshPendingCount]);

    /**
     * The main function the UI will call when a user taps "Present", "Late", etc.
     */
    const markAttendance = async (sessionId, studentId, statusCode) => {
        const record = {
            sync_id: ulid(), // Client-side generated ULID for idempotency
            attendance_session_id: sessionId,
            student_id: studentId,
            status_code: statusCode,
            recorded_at: new Date().toISOString(),
        };

        // 1. Save to Offline DB immediately
        await syncQueue.add(record);
        await refreshPendingCount();

        // 2. Try to sync in the background if online
        if (isOnline) {
            triggerSync();
        }
    };

    return (
        <AttendanceContext.Provider 
            value={{ 
                isOnline, 
                isSyncing, 
                pendingCount, 
                markAttendance, 
                triggerSync 
            }}
        >
            {children}
        </AttendanceContext.Provider>
    );
};

export const useAttendance = () => useContext(AttendanceContext);
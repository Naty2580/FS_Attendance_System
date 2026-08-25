import axios from 'axios';
import { syncQueue } from './offlineDb';

export const processSyncQueue = async () => {
    if (!navigator.onLine) return { status: 'offline' };

    const pendingRecords = await syncQueue.getAll();
    if (pendingRecords.length === 0) return { status: 'empty' };

    try {
        const response = await axios.post('/sync/attendance', { records: pendingRecords });
        const { synced, failed } = response.data.data;

        if (synced && synced.length > 0) {
            await syncQueue.removeMultiple(synced);
        }

        if (failed && failed.length > 0) {
            console.warn('Server explicitly rejected these records:', failed);
            await syncQueue.removeMultiple(failed.map(f => f.sync_id));
        }

        return { status: 'success', syncedCount: synced?.length || 0, failedCount: failed?.length || 0 };
    } catch (error) {
        // If the server throws a 500 or 422 error, Axios jumps here!
        console.error('CRITICAL BACKEND ERROR:', error.response?.data || error.message);
        
        // Safety Valve: We don't want to get stuck in an infinite loop due to a bad payload.
        // We will increment a retry count, and if it fails too many times, we drop it.
        for (const record of pendingRecords) {
            record.retries = (record.retries || 0) + 1;
            if (record.retries > 3) {
                console.error(`Dropping record ${record.sync_id} after 3 failed server attempts.`);
                await syncQueue.remove(record.sync_id);
            } else {
                await syncQueue.add(record); // Update retry count locally
            }
        }

        return { status: 'error', error };
    }
};
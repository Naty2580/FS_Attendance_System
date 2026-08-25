import { openDB } from 'idb';

const DB_NAME = 'sunday_school_db';
const DB_VERSION = 1;
const QUEUE_STORE = 'attendance_sync_queue';

export const initDB = async () => {
    return openDB(DB_NAME, DB_VERSION, {
        upgrade(db) {
            if (!db.objectStoreNames.contains(QUEUE_STORE)) {
                // sync_id is the primary key (client-generated ULID)
                db.createObjectStore(QUEUE_STORE, { keyPath: 'sync_id' });
            }
            // Future offline stores (Students, Sessions) can be added here
        },
    });
};

export const syncQueue = {
    async add(record) {
        const db = await initDB();
        await db.put(QUEUE_STORE, record);
    },

    async getAll() {
        const db = await initDB();
        return db.getAll(QUEUE_STORE);
    },

    async remove(sync_id) {
        const db = await initDB();
        await db.delete(QUEUE_STORE, sync_id);
    },

    async removeMultiple(sync_ids) {
        const db = await initDB();
        const tx = db.transaction(QUEUE_STORE, 'readwrite');
        await Promise.all(sync_ids.map(id => tx.store.delete(id)));
        await tx.done;
    },

    async clear() {
        const db = await initDB();
        await db.clear(QUEUE_STORE);
    }
};
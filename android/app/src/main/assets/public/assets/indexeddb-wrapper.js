/**
 * Tharimpepe FSMS - IndexedDB Wrapper
 *
 * Provides a robust, promise-based IndexedDB layer for offline-first storage.
 * Uses structured storage for attendance, stock, beneficiaries, and sync queue.
 */

const DB_NAME = 'fsms_offline';
const DB_VERSION = 1;

const objectStores = {
  syncQueue: { keyPath: 'id', autoIncrement: true },
  cachedData: { keyPath: 'key' },
  pendingMutations: { keyPath: 'id', autoIncrement: true }
};

class IndexedDBWrapper {
  constructor() {
    this.db = null;
    this._ready = this._init();
  }

  async _init() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = (event) => {
        const db = event.target.result;

        for (const [storeName, config] of Object.entries(objectStores)) {
          if (!db.objectStoreNames.contains(storeName)) {
            const store = db.createObjectStore(storeName, config);
            // Create indexes for common queries
            if (storeName === 'syncQueue') {
              store.createIndex('status', 'status', { unique: false });
              store.createIndex('timestamp', 'timestamp', { unique: false });
              store.createIndex('endpoint', 'endpoint', { unique: false });
            }
            if (storeName === 'pendingMutations') {
              store.createIndex('status', 'status', { unique: false });
              store.createIndex('createdAt', 'createdAt', { unique: false });
            }
          }
        }
      };

      request.onsuccess = (event) => {
        this.db = event.target.result;
        resolve(this.db);
      };

      request.onerror = (event) => {
        reject(new Error('Failed to open IndexedDB: ' + event.target.error));
      };
    });
  }

  async ready() {
    await this._ready;
    if (!this.db) {
      throw new Error('Database not initialized');
    }
    return this.db;
  }

  async _transaction(storeName, mode = 'readonly') {
    const db = await this.ready();
    const tx = db.transaction(storeName, mode);
    return tx.objectStore(storeName);
  }

  // ===== Generic CRUD =====

  async add(storeName, data) {
    const store = await this._transaction(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
      const request = store.add(data);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async put(storeName, data) {
    const store = await this._transaction(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
      const request = store.put(data);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async get(storeName, key) {
    const store = await this._transaction(storeName);
    return new Promise((resolve, reject) => {
      const request = store.get(key);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async getAll(storeName) {
    const store = await this._transaction(storeName);
    return new Promise((resolve, reject) => {
      const request = store.getAll();
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async delete(storeName, key) {
    const store = await this._transaction(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
      const request = store.delete(key);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  }

  async clear(storeName) {
    const store = await this._transaction(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
      const request = store.clear();
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  }

  async count(storeName) {
    const store = await this._transaction(storeName);
    return new Promise((resolve, reject) => {
      const request = store.count();
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  // ===== Sync Queue (replaces localStorage OfflineQueue) =====

  async enqueueSyncItem(item) {
    const entry = {
      id: undefined, // auto-incremented
      method: item.method || 'POST',
      endpoint: item.endpoint || item.url,
      body: item.body || null,
      headers: item.headers || { 'Content-Type': 'application/json' },
      status: 'pending',
      attempts: 0,
      timestamp: new Date().toISOString(),
      created_at: Date.now()
    };
    return this.add('syncQueue', entry);
  }

  async getPendingSyncItems() {
    const all = await this.getAll('syncQueue');
    return all.filter(item => item.status === 'pending' || item.status === 'failed');
  }

  async removeSyncItem(id) {
    await this.delete('syncQueue', id);
  }

  async updateSyncItem(id, updates) {
    const item = await this.get('syncQueue', id);
    if (!item) throw new Error('Sync item not found');
    const updated = { ...item, ...updates };
    await this.put('syncQueue', updated);
    return updated;
  }

  async getSyncCount() {
    return this.count('syncQueue');
  }

  async clearSyncQueue() {
    await this.clear('syncQueue');
  }
}

const DB = new IndexedDBWrapper();

function getDB() {
  return DB;
}

export { DB, IndexedDBWrapper, getDB };
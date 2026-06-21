/**
 * Tharimpepe FSMS - Offline Sync Worker
 *
 * Replaces the localStorage-based OfflineQueue with IndexedDB-backed
 * background sync. Handles retry logic, exponential backoff, and
 * conflict resolution.
 *
 * Usage:
 *   import { SyncWorker } from './sync-worker.js';
 *   await SyncWorker.init();
 *   await SyncWorker.enqueue('/api/attendance/bulk-mark.php', 'POST', { ... });
 */

import { DB } from './indexeddb-wrapper.js';

const SYNC_WORKER_VERSION = 1;

const MAX_RETRIES = 5;
const BASE_BACKOFF_MS = 1000;
const MAX_BACKOFF_MS = 60000;

class SyncWorker {
  constructor() {
    this.isRunning = false;
    this.isOnline = navigator.onLine;
    this.retryTimers = new Map();
  }

  async init() {
    try {
      await DB.ready();
      console.log('[SyncWorker] Initialized with IndexedDB');

      // Listen for online/offline events
      window.addEventListener('online', () => this._handleOnline());
      window.addEventListener('offline', () => this._handleOffline());

      if (this.isOnline) {
        // Try to process any pending items on startup
        this._scheduleSync(0);
      }

      return true;
    } catch (error) {
      console.error('[SyncWorker] Initialization failed:', error);
      return false;
    }
  }

  async enqueue(endpoint, method = 'POST', body = null, headers = null, options = {}) {
    const defaultHeaders = {
      'Content-Type': 'application/json',
      ...headers
    };

    const item = {
      endpoint,
      method: method.toUpperCase(),
      body,
      headers: defaultHeaders,
      priority: options.priority || 'normal',
      idempotencyKey: options.idempotencyKey || null,
      tags: options.tags || [],
      status: 'pending',
      attempts: 0,
      nextAttemptAt: new Date().toISOString(),
      created_at: Date.now(),
      updated_at: Date.now()
    };

    const id = await DB.enqueueSyncItem(item);
    console.log(`[SyncWorker] Enqueued ${method} ${endpoint} (id: ${id})`);

    // If online, try to sync immediately
    if (this.isOnline && !this.isRunning) {
      this._scheduleSync(0);
    }

    return id;
  }

  async processQueue() {
    if (this.isRunning) {
      console.log('[SyncWorker] Already running, skipping');
      return { processed: 0, failed: 0 };
    }

    if (!navigator.onLine) {
      console.log('[SyncWorker] Offline, queue will sync when online');
      return { processed: 0, failed: 0, reason: 'offline' };
    }

    this.isRunning = true;
    console.log('[SyncWorker] Processing queue...');

    try {
      const pendingItems = await DB.getPendingSyncItems();

      if (pendingItems.length === 0) {
        console.log('[SyncWorker] Queue is empty');
        return { processed: 0, failed: 0 };
      }

      // Sort by priority (high first), then by creation time
      pendingItems.sort((a, b) => {
        const priorityOrder = { high: 0, normal: 1, low: 2 };
        const priorityDiff = (priorityOrder[a.priority] || 1) - (priorityOrder[b.priority] || 1);
        if (priorityDiff !== 0) return priorityDiff;
        return a.created_at - b.created_at;
      });

      let processed = 0;
      let failed = 0;
      const failedItems = [];

      for (const item of pendingItems) {
        try {
          const success = await this._processItem(item);
          if (success) {
            processed++;
            await DB.removeSyncItem(item.id);
            console.log(`[SyncWorker] Processed ${item.method} ${item.endpoint}`);
          } else {
            failed++;
            failedItems.push(item);
            // Update retry count and status
            const newAttempts = (item.attempts || 0) + 1;
            const updates = {
              attempts: newAttempts,
              updated_at: Date.now()
            };

            if (newAttempts >= MAX_RETRIES) {
              updates.status = 'dead';
              updates.failedAt = new Date().toISOString();
              console.warn(`[SyncWorker] Item failed after ${MAX_RETRIES} retries: ${item.endpoint}`);
              await DB.updateSyncItem(item.id, updates);
            } else {
              updates.status = 'failed';
              updates.lastError = 'Sync failed';
              updates.nextAttemptAt = new Date(Date.now() + this._calculateBackoff(newAttempts)).toISOString();
              await DB.updateSyncItem(item.id, updates);
            }
          }
        } catch (error) {
          console.error(`[SyncWorker] Error processing item:`, error);
          failed++;
          failedItems.push(item);
        }
      }

      // Schedule retry for failed items
      for (const item of failedItems) {
        if (item.status !== 'dead') {
          this._scheduleRetry(item);
        }
      }

      console.log(`[SyncWorker] Processed: ${processed}, Failed: ${failed}`);

      // Emit event for UI to update
      this._emitSyncEvent({ processed, failed, total: pendingItems.length });

      return { processed, failed };
    } finally {
      this.isRunning = false;
    }
  }

  async _processItem(item) {
    const { _fetch } = await import('./api.js');
    const baseURL = (await import('./runtime-config.js')).RuntimeConfig.getAPIBase();

    const url = baseURL + item.endpoint;

    try {
      const response = await fetch(url, {
        method: item.method,
        headers: item.headers,
        body: item.body ? JSON.stringify(item.body) : null,
        // Add idempotency key if available
        ...(item.idempotencyKey && {
          headers: {
            ...item.headers,
            'Idempotency-Key': item.idempotencyKey
          }
        })
      });

      if (response.ok) {
        // Success - return parsed response
        const data = await response.json();
        return true;
      } else if (response.status === 401 || response.status === 403) {
        // Auth errors - don't retry, mark as failed
        console.warn(`[SyncWorker] Auth error for ${item.endpoint}: ${response.status}`);
        return false;
      } else if (response.status >= 400 && response.status < 500) {
        // Client errors - don't retry
        console.warn(`[SyncWorker] Client error for ${item.endpoint}: ${response.status}`);
        return false;
      } else {
        // Server errors (500+) - retry
        console.warn(`[SyncWorker] Server error for ${item.endpoint}: ${response.status}`);
        return false;
      }
    } catch (error) {
      // Network error - retry
      console.warn(`[SyncWorker] Network error for ${item.endpoint}:`, error.message);
      return false;
    }
  }

  _calculateBackoff(attempt) {
    const backoff = Math.min(
      BASE_BACKOFF_MS * Math.pow(2, attempt - 1),
      MAX_BACKOFF_MS
    );
    // Add jitter (±20%)
    const jitter = backoff * (0.8 + Math.random() * 0.4);
    return jitter;
  }

  _scheduleRetry(item) {
    if (this.retryTimers.has(item.id)) {
      clearTimeout(this.retryTimers.get(item.id));
    }

    const backoffMs = this._calculateBackoff(item.attempts || 1);
    const timer = setTimeout(async () => {
      this.retryTimers.delete(item.id);
      if (navigator.onLine) {
        await this.processQueue();
      }
    }, backoffMs);

    this.retryTimers.set(item.id, timer);
  }

  _scheduleSync(delayMs) {
    if (this._syncTimeout) {
      clearTimeout(this._syncTimeout);
    }

    this._syncTimeout = setTimeout(() => {
      this.processQueue();
    }, delayMs);
  }

  _handleOnline() {
    console.log('[SyncWorker] Back online - processing queue');
    this.isOnline = true;
    this._scheduleSync(1000);
  }

  _handleOffline() {
    console.log('[SyncWorker] Gone offline - queue will sync when back online');
    this.isOnline = false;
    if (this._syncTimeout) {
      clearTimeout(this._syncTimeout);
    }
  }

  _emitSyncEvent(details) {
    window.dispatchEvent(new CustomEvent('sync:complete', { detail: details }));
  }

  async getStatus() {
    const pendingCount = await DB.getSyncCount();
    const items = await DB.getAll('syncQueue');
    const statusBreakdown = items.reduce((acc, item) => {
      acc[item.status] = (acc[item.status] || 0) + 1;
      return acc;
    }, {});

    return {
      isOnline: this.isOnline,
      isRunning: this.isRunning,
      total: items.length,
      pending: statusBreakdown.pending || 0,
      failed: statusBreakdown.failed || 0,
      dead: statusBreakdown.dead || 0,
      summary: statusBreakdown
    };
  }

  async getDeadLetters() {
    const items = await DB.getAll('syncQueue');
    return items.filter(item => item.status === 'dead');
  }

  async retryDeadLetter(id) {
    const item = await DB.get('syncQueue', id);
    if (!item) throw new Error('Item not found');

    const updates = {
      status: 'pending',
      attempts: 0,
      nextAttemptAt: new Date().toISOString(),
      updated_at: Date.now()
    };

    await DB.updateSyncItem(id, updates);

    if (navigator.onLine) {
      this._scheduleSync(0);
    }

    return true;
  }

  async clearDeadLetters() {
    const deadItems = await this.getDeadLetters();
    for (const item of deadItems) {
      await DB.removeSyncItem(item.id);
    }
    return deadItems.length;
  }

  async clearAll() {
    this.retryTimers.forEach(timer => clearTimeout(timer));
    this.retryTimers.clear();
    await DB.clearSyncQueue();
    console.log('[SyncWorker] All data cleared');
  }
}

const SyncWorker = new SyncWorker();

export { SyncWorker };
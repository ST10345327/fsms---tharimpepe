/**
 * Tharimpepe FSMS - Offline Queue Module
 *
 * Provides an offline-first request queue backed by localStorage.
 * Queued requests are replayed automatically when the app detects
 * it is back online again.
 */

const OfflineQueue = (() => {
  const QUEUE_KEY = 'offline_queue';
  const META_KEY = 'offline_queue_meta';

  let queue = [];
  let meta = { failed: 0, lastSync: null };

  function load() {
    try {
      const raw = localStorage.getItem(QUEUE_KEY);
      queue = raw ? JSON.parse(raw) : [];
      const rawMeta = localStorage.getItem(META_KEY);
      meta = rawMeta ? JSON.parse(rawMeta) : { failed: 0, lastSync: null };
    } catch (e) {
      queue = [];
      meta = { failed: 0, lastSync: null };
    }
  }

  function save() {
    try {
      localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
      localStorage.setItem(META_KEY, JSON.stringify(meta));
    } catch (e) {
      console.error('Failed to save offline queue:', e);
      clear();
    }
  }

  function clear() {
    queue = [];
    meta = { failed: 0, lastSync: null };
    save();
  }

  function enqueue(request) {
    const item = {
      id: String(Date.now()) + Math.random().toString(36).slice(2, 8),
      timestamp: new Date().toISOString(),
      ...request
    };
    queue.push(item);
    save();
    return item;
  }

  function remove(id) {
    queue = queue.filter(item => item.id !== id);
    save();
  }

  function getAll() {
    return [...queue];
  }

  function getMeta() {
    return { ...meta };
  }

  async function process() {
    if (!navigator.onLine) return { processed: 0, failed: 0 };

    load();
    if (queue.length === 0) return { processed: 0, failed: 0 };

    const results = { processed: 0, failed: 0 };
    const remaining = [];

    for (const item of queue) {
      try {
        const options = {
          method: item.method || 'GET',
          headers: item.headers || { 'Content-Type': 'application/json' }
        };

        if (item.body) {
          options.body = typeof item.body === 'string' ? item.body : JSON.stringify(item.body);
        }

        const response = await fetch(item.url, options);
        if (response.ok) {
          results.processed += 1;
        } else {
          remaining.push(item);
          results.failed += 1;
        }
      } catch (e) {
        remaining.push(item);
        results.failed += 1;
      }
    }

    queue = remaining;
    meta.lastSync = new Date().toISOString();
    if (results.failed > 0) {
      meta.failed = meta.failed + results.failed;
    }
    save();

    if (queue.length === 0) {
      meta.failed = 0;
      save();
    }

    return results;
  }

  load();

  return {
    enqueue,
    remove,
    getAll,
    getMeta,
    process,
    clear,
    load
  };
})();

window.OfflineQueue = OfflineQueue;
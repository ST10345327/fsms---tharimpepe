/**
 * Server Discovery Module
 *
 * Scans the local network for a reachable FSMS backend.
 * Falls back to the stored/default API base if no server is found.
 *
 * Limitations:
 *  - Phone and backend must be on the same LAN.
 *  - Firewall must allow inbound TCP on port 8000.
 *  - Subnet scanning can take ~10-20 seconds on a cold start.
 */

const COMMON_SUBNETS = [
  '192.168.0.',
  '192.168.1.',
  '192.168.18.',
  '10.0.0.',
  '192.168.4.',
  '192.168.5.',
  // Add more subnets your environment uses
];

const HEALTH_PATH = '/api/system/health.php';
const PORT = 8000;
const PER_HOST_TIMEOUT_MS = 800;  // Fail fast on each candidate
const MAX_CONCURRENT = 6;          // Limit parallel probes

async function discoverServer() {
  const candidates = buildCandidateHosts();

  return scanCandidates(candidates);
}

async function discoverServerFast(knownBase) {
  // If the stored/default URL looks like LAN, just verify it first.
  if (typeof knownBase === 'string' && isLanBase(knownBase)) {
    const ok = await probeHost(stripHost(knownBase));
    if (ok) return knownBase;
  }
  return discoverServer();
}

function buildCandidateHosts() {
  const hosts = [];
  for (const subnet of COMMON_SUBNETS) {
    for (let i = 1; i <= 50; i++) {
      hosts.push(`${subnet}${i}`);
    }
  }
  return hosts;
}

async function scanCandidates(hosts) {
  // Process in small batches so we don't timeout the JS event loop.
  for (let i = 0; i < hosts.length; i += MAX_CONCURRENT) {
    const batch = hosts.slice(i, i + MAX_CONCURRENT);
    const results = await Promise.allSettled(
      batch.map(host => probeHost(host))
    );

    for (const result of results) {
      if (result.status === 'fulfilled' && result.value) {
        return result.value;
      }
    }
  }
  return null;
}

async function probeHost(host) {
  const url = `http://${host}:${PORT}${HEALTH_PATH}`;

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), PER_HOST_TIMEOUT_MS);

  try {
    const response = await fetch(url, {
      method: 'GET',
      signal: controller.signal,
      // Credentials mode same-origin avoids CORS preflight noise in Capacitor.
      mode: 'no-cors',
    });

    // In no-cors mode, the browser opaque-blocks the body, but we can still
    // detect "reachable" by the absence of a network-level failure.
    if (response.type === 'opaque') {
      // Treat any opaque success as a likely match; we validate on first use.
      return `http://${host}:${PORT}`;
    }

    if (!response.ok) return null;

    const data = await response.json();
    return data && data.status === 'ok'
      ? `http://${host}:${PORT}`
      : null;
  } catch {
    return null;
  } finally {
    clearTimeout(timer);
  }
}

function stripHost(baseUrl) {
  try {
    const u = new URL(baseUrl);
    return u.hostname;
  } catch {
    return '';
  }
}

function isLanBase(baseUrl) {
  try {
    const u = new URL(baseUrl);
    if (u.hostname === 'localhost') return true;
    return u.hostname.startsWith('192.168.') ||
           u.hostname.startsWith('10.') ||
           /^10\.\d+\.\d+\.\d+$/.test(u.hostname);
  } catch {
    return false;
  }
}

// Make globally accessible
window.discoverServer = discoverServer;
window.discoverServerFast = discoverServerFast;

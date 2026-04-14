/**
 * Mesmo domínio (InfinityFree com backend em /backend/): use caminho relativo —
 * evita erro se o site abrir em http e a URL estiver em https (ou o contrário).
 */
const API_BASE = '/backend/api';

/**
 * Se um dia o HTML estiver na Vercel e o PHP em outro host, troque por URL completa, ex.:
 * const API_BASE = 'https://seudominio.infinityfree.me/backend/api';
 */
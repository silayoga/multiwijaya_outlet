<svg viewBox="0 0 600 240" class="w-full h-auto" role="img" aria-label="Diagram: router, camera, POS terminal, and dashboard connected in one continuous loop">
  <defs>
    <linearGradient id="heroTraceGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1830C4" />
      <stop offset="100%" stop-color="#3DD6E8" />
    </linearGradient>
  </defs>

  {{-- Static faint base path — always visible, gives the loop shape even without motion --}}
  <path
    d="M300,120 C300,60 220,40 150,60 C80,80 60,140 90,170 C120,200 200,190 240,150 C260,130 280,120 300,120 C320,120 340,130 360,150 C400,190 480,200 510,170 C540,140 520,80 450,60 C380,40 300,60 300,120 Z"
    fill="none" stroke="#E4E9F5" stroke-width="2.5" stroke-linecap="round"
  />

  {{-- Animated trace — quiet, continuous draw/erase; disabled entirely under prefers-reduced-motion --}}
  <path
    class="infinity-trace-path"
    pathLength="1"
    d="M300,120 C300,60 220,40 150,60 C80,80 60,140 90,170 C120,200 200,190 240,150 C260,130 280,120 300,120 C320,120 340,130 360,150 C400,190 480,200 510,170 C540,140 520,80 450,60 C380,40 300,60 300,120 Z"
    fill="none" stroke="url(#heroTraceGradient)" stroke-width="2.5" stroke-linecap="round"
  />

  {{-- Router --}}
  <g transform="translate(90,170)">
    <circle r="20" fill="white" stroke="#E4E9F5" stroke-width="1.5" />
    <g transform="translate(-9,-9)" stroke="#1830C4" stroke-width="1.6" fill="none">
      <rect x="3" y="8" width="18" height="6" rx="1.5"/><circle cx="8" cy="17" r="1.5"/><circle cx="16" cy="17" r="1.5"/>
    </g>
    <text x="0" y="38" text-anchor="middle" class="fill-slate-500" style="font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.04em;">ROUTER</text>
  </g>

  {{-- CCTV camera --}}
  <g transform="translate(150,60)">
    <circle r="20" fill="white" stroke="#E4E9F5" stroke-width="1.5" />
    <g transform="translate(-9,-9)" stroke="#1830C4" stroke-width="1.6" fill="none">
      <circle cx="12" cy="12" r="4"/><path d="M4 8V6a2 2 0 0 1 2-2h2M20 8V6a2 2 0 0 0-2-2h-2M4 16v2a2 2 0 0 0 2 2h2M20 16v2a2 2 0 0 1-2 2h-2"/>
    </g>
    <text x="0" y="-28" text-anchor="middle" class="fill-slate-500" style="font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.04em;">CCTV</text>
  </g>

  {{-- POS terminal --}}
  <g transform="translate(450,60)">
    <circle r="20" fill="white" stroke="#E4E9F5" stroke-width="1.5" />
    <g transform="translate(-9,-9)" stroke="#3DD6E8" stroke-width="1.6" fill="none">
      <rect x="4" y="3" width="16" height="14" rx="1.5"/><path d="M8 21h8M12 17v4"/>
    </g>
    <text x="0" y="-28" text-anchor="middle" class="fill-slate-500" style="font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.04em;">POS</text>
  </g>

  {{-- Dashboard --}}
  <g transform="translate(510,170)">
    <circle r="20" fill="white" stroke="#E4E9F5" stroke-width="1.5" />
    <g transform="translate(-9,-9)" stroke="#3DD6E8" stroke-width="1.6" fill="none">
      <path d="M3 17l4-8 4 4 4-10 6 14"/>
    </g>
    <text x="0" y="38" text-anchor="middle" class="fill-slate-500" style="font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:0.04em;">DASHBOARD</text>
  </g>
</svg>

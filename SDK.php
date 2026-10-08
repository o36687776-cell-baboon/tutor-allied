<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Pi SDK Hub — AI Academy</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand-bg:#0b0b0b; /* deep black for AI Academy / Pi Academy */
      --brand-accent:#6c757d; /* grey */
      --card-bg:#111111;
      --muted:#cfcfcf;
    }
    body{background:linear-gradient(180deg,var(--brand-bg),#0f0f0f);color:var(--muted);font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial}
    .brand{color:white}
    .repo-card{background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(255,255,255,0.01));border:1px solid rgba(255,255,255,0.04)}
    .muted-small{color:var(--brand-accent);font-size:0.9rem}
    .mono{font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}
    .btn-accent{background:#222;border-color:#333;color:#fff}
    .header-hero{border-bottom:1px solid rgba(255,255,255,0.03);padding-bottom:1.25rem;margin-bottom:1rem}
    .nerd-view{font-size:0.88rem;background:#070707;color:#e9e9e9;padding:0.75rem;border-radius:6px}
  </style>
</head>
<body>
  <div class="container py-4">
    <header class="d-flex align-items-center justify-content-between mb-3 header-hero">
      <div>
        <h1 class="h3 brand">AI Academy — Pi SDK Hub</h1>
        <p class="muted-small mb-0">Developer "nerd view": curated Pi SDK repositories. Mobile-first • Bootstrap • Add new listings locally.</p>
      </div>
      <div class="text-end">
        <small class="muted-small">Theme: black • grey • white</small>
      </div>
    </header>

    <section class="mb-4">
      <div class="row g-3">
        <div class="col-md-8">
          <div class="card repo-card p-3">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <h2 class="h5 mb-1">Official & community Pi SDKs</h2>
                <p class="muted-small mb-2">Click a repo to open it — use the "Add repo" form to create a new listing saved to your browser.</p>
              </div>
              <div class="text-end">
                <span class="badge bg-secondary">Developers</span>
              </div>
            </div>

            <div id="repoList" class="mt-3 row g-2"></div>
          </div>
        </div>

        <aside class="col-md-4">
          <div class="card p-3 repo-card">
            <h3 class="h6 mb-2">Add a new listing</h3>
            <form id="addRepoForm" class="mb-2">
              <div class="mb-2">
                <label class="form-label small">Repository name</label>
                <input id="repoName" class="form-control form-control-sm bg-transparent text-white" placeholder="e.g. pi-apps/pi-python" required>
              </div>
              <div class="mb-2">
                <label class="form-label small">URL</label>
                <input id="repoUrl" type="url" class="form-control form-control-sm bg-transparent text-white" placeholder="https://github.com/..." required>
              </div>
              <div class="mb-2">
                <label class="form-label small">Short description</label>
                <input id="repoDesc" class="form-control form-control-sm bg-transparent text-white" placeholder="One-line summary">
              </div>
              <div class="d-grid">
                <button class="btn btn-accent btn-sm" type="submit">Add to list</button>
              </div>
            </form>

            <div class="muted-small">
              <p class="mb-1">Local-only storage: listings are saved to your browser (localStorage). To export, use the "Export JSON" button.</p>
              <div class="d-flex gap-2">
                <button id="exportBtn" class="btn btn-outline-secondary btn-sm">Export JSON</button>
                <button id="clearBtn" class="btn btn-outline-danger btn-sm">Clear all</button>
              </div>
            </div>
          </div>

          <div class="card p-3 repo-card mt-3">
            <h4 class="h6 mb-2">Nerd view</h4>
            <pre id="nerd" class="nerd-view mono">{
  "repos": []
}</pre>
          </div>
        </aside>
      </div>
    </section>

    <footer class="text-center mt-4 muted-small">
      <small>Built for AI Academy • Pi SDK Hub • Local-only demo • © AI Academy</small>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- Starter repo data (curated) ---
    const starterRepos = [
      {name: 'pi-apps/pi-sdk-integration-guide', url: 'https://github.com/pi-apps/pi-sdk-integration-guide', desc: 'Integration guide and examples for building Pi SDKs.'},
      {name: 'pi-apps/pi-python', url: 'https://github.com/pi-apps/pi-python', desc: 'Community Python backend package for Pi apps.'},
      {name: 'pi-apps/pi-blazor', url: 'https://github.com/pi-apps/pi-blazor', desc: 'Blazor / .NET SDK integration for Pi.'},
      {name: 'pi-apps/pi-rust', url: 'https://github.com/pi-apps/pi-rust', desc: 'Rust backend SDK for Pi App-to-User (A2U) flows.'},
      {name: 'pi-apps/pi-platform-docs', url: 'https://github.com/pi-apps/pi-platform-docs', desc: 'Docs and frontend SDK guidance for Pi Browser apps.'},
      {name: 'KOSASIH/sdk (unofficial)', url: 'https://github.com/KOSASIH/sdk', desc: 'Community TypeScript/JS SDK examples.'}
    ];

    // --- Helpers ---
    const repoListEl = document.getElementById('repoList');
    const nerdEl = document.getElementById('nerd');
    const STORAGE_KEY = 'piSdkHub.repos.v1';

    function loadRepos(){
      const raw = localStorage.getItem(STORAGE_KEY);
      if(!raw){
        localStorage.setItem(STORAGE_KEY, JSON.stringify(starterRepos, null, 2));
        return starterRepos.slice();
      }
      try{return JSON.parse(raw);}catch(e){return starterRepos.slice();}
    }

    function saveRepos(list){
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list, null, 2));
      render(list);
    }

    function safeCopyToClipboard(text){
      // Try modern Clipboard API first. If blocked or unavailable, fall back to textarea + execCommand.
      if(navigator.clipboard && typeof navigator.clipboard.writeText === 'function'){
        return navigator.clipboard.writeText(text);
      }
      return new Promise((resolve, reject) => {
        try{
          const ta = document.createElement('textarea');
          ta.value = text;
          // Move off-screen
          ta.style.position = 'fixed'; ta.style.left = '-9999px';
          document.body.appendChild(ta);
          ta.focus(); ta.select();
          const ok = document.execCommand('copy');
          document.body.removeChild(ta);
          if(ok) resolve(); else reject(new Error('execCommand copy failed'));
        }catch(err){
          reject(err);
        }
      });
    }

    function render(list){
      repoListEl.innerHTML = '';
      list.forEach((r, idx) => {
        const col = document.createElement('div'); col.className = 'col-12';
        col.innerHTML = `
          <div class="p-2 repo-card rounded d-flex align-items-start justify-content-between">
            <div>
              <a href="${r.url}" target="_blank" rel="noopener noreferrer" class="h6 mb-0 d-block text-white text-decoration-none mono">${r.name}</a>
              <div class="muted-small">${r.desc || ''}</div>
            </div>
            <div class="text-end">
              <button data-idx="${idx}" class="btn btn-sm btn-outline-secondary btn-copy">Copy</button>
            </div>
          </div>`;
        repoListEl.appendChild(col);
      });
      nerdEl.textContent = JSON.stringify({repos: list}, null, 2);

      // wire copy buttons with robust fallback handling
      document.querySelectorAll('.btn-copy').forEach(btn => {
        btn.onclick = async (e) => {
          const i = Number(btn.getAttribute('data-idx'));
          const r = list[i];
          const original = btn.textContent;
          try{
            btn.disabled = true;
            await safeCopyToClipboard(r.url);
            btn.textContent = 'Copied';
            setTimeout(()=>{btn.textContent = original; btn.disabled = false;}, 900);
          }catch(err){
            // If clipboard APIs are blocked by the environment (sandbox), fallback to prompt so the user can copy manually.
            console.warn('Clipboard copy failed, falling back to prompt:', err);
            btn.disabled = false;
            // show a prompt as last resort — user can Ctrl+C from here
            window.prompt('Copy the repository URL (Ctrl+C / Cmd+C):', r.url);
            // restore label after a short delay
            setTimeout(()=>{btn.textContent = original;}, 900);
          }
        };
      });
    }

    // --- init ---
    let repos = loadRepos();
    render(repos);

    // --- form handlers ---
    document.getElementById('addRepoForm').addEventListener('submit', (e)=>{
      e.preventDefault();
      const name = document.getElementById('repoName').value.trim();
      const url = document.getElementById('repoUrl').value.trim();
      const desc = document.getElementById('repoDesc').value.trim();
      if(!name || !url) return;
      repos.unshift({name,url,desc});
      saveRepos(repos);
      e.target.reset();
    });

    document.getElementById('exportBtn').addEventListener('click', ()=>{
      const data = localStorage.getItem(STORAGE_KEY) || JSON.stringify(repos, null, 2);
      const blob = new Blob([data], {type: 'application/json'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url; a.download = 'pi-sdk-hub-repos.json';
      // Use a user-triggered click to avoid popup blockers
      a.click();
      URL.revokeObjectURL(url);
    });

    document.getElementById('clearBtn').addEventListener('click', ()=>{
      if(!confirm('Clear all local listings?')) return; localStorage.removeItem(STORAGE_KEY); repos = []; render(repos);
    });
  </script>
</body>
</html>

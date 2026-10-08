// minimal hash router
const routes = {
  '/': ()=>renderHome(),
  '/directory': ()=>renderDirectory(),
  '/ide': ()=>renderIDE(),
  '/badges': ()=>renderBadges(),
  '/stakes': ()=>renderStakes(),
  '/selection': ()=>showSelection()
};

function renderHome(){
  const view = document.getElementById('view');
  view.innerHTML = `<h3 class="mono">Welcome to PI AI-Academy Bootstrap</h3>
    <p class="muted-small">This is the incubation island for developer-friendly projects. Explore the directory, use the IDE, earn badges, and contribute to the community.</p>
    <div class="mt-3">
      <h5 class="mono">Quick Actions</h5>
      <div class="d-flex gap-2 flex-wrap">
        <a href="#/directory" class="btn btn-outline-primary btn-sm">Browse Projects</a>
        <a href="#/ide" class="btn btn-outline-success btn-sm">Try the IDE</a>
        <a href="#/badges" class="btn btn-outline-warning btn-sm">View Badges</a>
        <a href="#/stakes" class="btn btn-outline-info btn-sm">Stake Projects</a>
      </div>
    </div>`;
}

function updateActiveNav(){ document.querySelectorAll('[data-link]').forEach(a=> a.classList.remove('active')); const path = location.hash.replace('#','')||'/'; const el = document.querySelector(`[href="#${path}"]`); if(el) el.classList.add('active'); }

window.addEventListener('hashchange', ()=>{ route(); updateActiveNav(); });

function route(){ const path = location.hash.replace('#','')||'/'; const fn = routes[path] || routes['/']; fn(); }
// render home page (welcome message)
function renderHome(){
  const view = document.getElementById('view');
  view.innerHTML = `
    <h3 class="mono">Welcome to PI Bootstrap</h3>
    <p class="muted-small">Explore projects in the Directory, experiment in the IDE, earn badges through contributions, or check out staking opportunities.</p>
    <div class="row mt-3">
      <div class="col-md-6">
        <div class="p-3" style="background:rgba(255,255,255,0.02);border-radius:8px;">
          <h5 class="mono">Quick Start</h5>
          <p class="muted-small">Browse the project directory to find community, enterprise, or individual projects to contribute to.</p>
          <a href="#/directory" class="btn btn-sm btn-outline-primary">View Directory</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3" style="background:rgba(255,255,255,0.02);border-radius:8px;">
          <h5 class="mono">Try the IDE</h5>
          <p class="muted-small">Experiment with HTML, CSS, and JavaScript in our live code playground.</p>
          <a href="#/ide" class="btn btn-sm btn-success">Open IDE</a>
        </div>
      </div>
    </div>
  `;
}

// fetch projects.json and render cards
async function loadProjects(){
  try{
    const res = await fetch('data/projects.json');
    const data = await res.json();
    return data.projects;
  }catch(e){
    // fallback if offline
    return storage.get('pi_projects', []);
  }
}

async function renderDirectory(){
  const view = document.getElementById('view');
  const selectedType = storage.get('selected_product_type', null);
  const typeTitle = selectedType ? selectedType.charAt(0).toUpperCase() + selectedType.slice(1) : 'All';
  view.innerHTML = `<h3 class="mono">${typeTitle} Project Directory</h3><div id="projectList" class="row g-3 mt-2"></div>`;
  const listEl = document.getElementById('projectList');
  const projects = await loadProjects();
  const filteredProjects = selectedType ? projects.filter(p => p.type === selectedType) : projects;
  if(filteredProjects.length===0){ listEl.innerHTML='<div class="muted-small">No projects available for this category.</div>'; return }
  filteredProjects.forEach(p=>{
    const col = document.createElement('div'); col.className='col-md-6';
    col.innerHTML = `
      <div class="p-3" style="background:rgba(255,255,255,0.02);border-radius:8px;">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="mono">${p.title}</h5>
            <div class="muted-small">${p.summary}</div>
            <div class="mt-2 mono">Type: ${p.type} • Owner: ${p.owner}</div>
          </div>
          <div class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="#" onclick="openProject('${p.id}')">Open</a>
          </div>
        </div>
      </div>`;
    listEl.appendChild(col);
  });
}

function openProject(id){
  const view = document.getElementById('view');
  // find the project
  loadProjects().then(projects=>{
    const p = projects.find(x=>x.id===id);
    if(!p){ view.innerHTML = `<div class=\"muted-small\">Project not found</div>`; return }
    view.innerHTML = `
      <h3 class=\"mono\">${p.title}</h3>
      <p class=\"muted-small\">${p.long}</p>
      <p class=\"mono\">Repo: <a href=\"${p.repo}\" target=\"_blank\">${p.repo}</a></p>
      <div class=\"d-flex gap-2 mt-2\">` +
      `<button class=\"btn btn-sm btn-success\" onclick=\"simulateContribute('${p.id}')\">Contribute (simulate)</button>` +
      `<button class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.hash='#/directory'\">Back</button></div>`;
  });
}

function simulateContribute(projectId){
  // create a fake contribution entry in localStorage
  const contribs = storage.get('contributions', []);
  const id = 'c_'+Date.now();
  contribs.push({id, projectId, status:'submitted', ts:Date.now(), message:'Simulated contribution via portal'});
  storage.set('contributions', contribs);
  // auto-approve small probability
  setTimeout(()=>{ // simulate review
    if(Math.random()>0.4){
      // approved
      const c = contribs.find(x=>x.id===id);
      c.status='approved'; storage.set('contributions', contribs);
      // award small badge
      awardBadgeOnApproval('CONTRIB_01');
      alert('Contribution approved (simulated). Badge progress updated.');
    } else alert('Contribution submitted. Awaiting review (simulated).');
  }, 800);
}
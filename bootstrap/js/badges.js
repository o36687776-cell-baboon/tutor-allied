// simple badge awarding and rendering
const BADGES = [
  {code:"CONTRIB_01", name:"Contributor", description:">=1 approved contribution"},
  {code:"TRUSTED_01", name:"Trusted Contributor", description:"approval rate >= 0.75 & approved >=3"}
];

function awardBadgeOnApproval(code){
  const userBadges = storage.get("user_badges", []);
  if(!userBadges.includes(code)){
    userBadges.push(code); storage.set("user_badges", userBadges);
  }
}

function renderBadges(){
  const view = document.getElementById("view");
  const userBadges = storage.get("user_badges", []);
  view.innerHTML = `<h3 class=\"mono\">Badges</h3><div class=\"mt-2\">` +
    BADGES.map(b=>`<div class=\"p-2\" style=\"background:rgba(255,255,255,0.02);border-radius:6px;margin-bottom:8px;\">`+
    `<strong class=\"mono\">${b.name}</strong><div class=\"muted-small\">${b.description}</div>`+
    `<div class=\"mt-1\">Status: ${userBadges.includes(b.code)?"<span style=\"color:var(--bash-green)\">AWARDED</span>":"<span class=\"muted-small\">Not yet</span>"}</div></div>`).join("") + `</div>`;
}

function renderStakes(){
  const view = document.getElementById("view");
  view.innerHTML = `<h3 class=\"mono\">Stakes (mock)</h3><p class=\"muted-small\">This is a mock. Buy stake to support projects and gain voting rights (simulation).</p>` +
    `<div class=\"d-flex gap-2 mt-2\"><button class=\"btn btn-sm btn-outline-primary\" onclick=\"alert(\"Stake purchase simulated\")\">Buy stake (simulate)</button></div>`;
}

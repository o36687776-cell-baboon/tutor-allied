// app init and route wiring
(function(){
  // initial terminal intro
  typewriter(document.getElementById("terminalIntro"), "> initializing Pi Bootstrap environment...\n> welcome to AI-Academy Incubation Hub [v1.0]\n> click Enter Bootstrap to continue", 18);

  // if user previously consented, auto-enter SPA or selection
  const consent = storage.get("pi_bootstrap_consent", {});
  const selectedType = storage.get("selected_product_type", null);
  if(consent && consent.agreed && selectedType){
    showSPA();
    if(!location.hash) location.hash = "#/";
    route(); updateActiveNav();
  }
  else if(consent && consent.agreed){
    showSelection();
  }

  // wire agree/disagree in popup handled in popup.js
  // router wiring
  window.initApp = function(){
    showSPA();
    if(!location.hash) location.hash = "#/";
    route(); updateActiveNav();
  }

  // expose helpers used by directory and selection
  window.simulateContribute = simulateContribute;
  window.openProject = openProject;
  window.selectProductType = selectProductType;
  window.showSelection = showSelection;
})();

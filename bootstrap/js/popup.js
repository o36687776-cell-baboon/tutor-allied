// popup control and consent flow
const consentModalEl = document.getElementById('consentModal');
const consentModal = new bootstrap.Modal(consentModalEl, {keyboard:true});
const enterBtn = document.getElementById('enterBtn');
const agreeBtn = document.getElementById('agreeBtn');
const disagreeBtn = document.getElementById('disagreeBtn');

enterBtn.addEventListener('click', ()=>{
  // typewriter text
  const txt = `You are now opting into the AI-ACADEMY bootstrap network.\nHere you can propose, join product teams, contribute to community products, and access development tooling on the PI network.\nRead the terms of service correctly. Click AGREE to continue or DISAGREE to return to the main domain.`;
  typewriter(document.getElementById('popupTypewriter'), txt, 20);
  consentModal.show();
});

agreeBtn.addEventListener('click', ()=>{
  consentModal.hide();
  // mark consent in localStorage
  storage.set('pi_bootstrap_consent', {agreed:true, ts:Date.now()});
  // redirect to selection page
  location.hash = '#/selection';
});

// if user clicks disagree, anchor handles redirect

// auto-show for visitors who click Enter only
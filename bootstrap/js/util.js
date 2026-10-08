// small utilities used by SPA
function el(sel){return document.querySelector(sel)}
function htmlEscape(s){ if(!s) return ''; return s.replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>') }

function typewriter(targetEl, text, speed=24, callback){
  targetEl.innerHTML=''; let i=0;
  (function tick(){
    if(i<=text.length){ targetEl.innerHTML = htmlEscape(text.slice(0,i)) + (i%2?'<span class=\"blink\">_</span>':''); i++; setTimeout(tick, speed); }
    else if(callback) callback();
  })();
}

function showSPA(){ document.getElementById('hero').classList.add('d-none'); document.getElementById('spa').classList.remove('d-none'); }

// simple local storage helpers
const storage = {
  get(k, fallback){ try{ const v = localStorage.getItem(k); return v?JSON.parse(v):fallback; }catch(e){return fallback} },
  set(k,v){ try{ localStorage.setItem(k, JSON.stringify(v)); }catch(e){} }
};
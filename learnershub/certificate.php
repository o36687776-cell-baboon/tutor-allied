<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PI AI Academy — Certificate Generator (Fixed QR)</title>
  <style>
    :root {
      --black: #000000;
      --white: #ffffff;
      --grey: #6C757D;
      --light-grey: #F5F5F5;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', Arial, sans-serif;
      background-color: var(--light-grey);
      color: var(--black);
      padding: 1rem;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .container {
      width: 100%;
      max-width: 700px;
      background: var(--white);
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      padding: 1.5rem;
    }

    h1 {
      text-align: center;
      color: var(--black);
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    label {
      display: block;
      font-size: 0.9rem;
      color: var(--grey);
      margin-bottom: 0.3rem;
    }

    input[type=text], input[type=date], input[type=file] {
      width: 100%;
      padding: 0.7rem;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      justify-content: center;
    }

    button {
      background: var(--black);
      color: var(--white);
      border: none;
      border-radius: 0.5rem;
      padding: 0.7rem 1.2rem;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      background: var(--grey);
    }

    .preview {
      background: var(--white);
      border: 1px solid #ddd;
      border-radius: 0.75rem;
      margin-top: 1rem;
      padding: 1rem;
      text-align: center;
      position: relative;
    }

    /* QR canvas placed inside preview; QR generated using QRious (local canvas) */
    #qrCode {
      position: absolute;
      bottom: 10px;
      right: 10px;
      width: 80px;
      height: 80px;
    }

    .recipient {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--black);
      margin: 0.5rem 0;
    }

    .meta {
      font-size: 0.9rem;
      color: var(--grey);
    }

    .footer {
      margin-top: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      font-size: 0.8rem;
      color: var(--grey);
    }

    @media (max-width: 600px) {
      .container { padding: 1rem; }
      h1 { font-size: 1.25rem; }
      .recipient { font-size: 1.2rem; }
      #qrCode { width: 60px; height: 60px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>PI AI Academy Certificate Generator</h1>

    <label for="name">Recipient Name</label>
    <input type="text" id="name" placeholder="Enter recipient name" />

    <label for="course">Course / Achievement</label>
    <input type="text" id="course" placeholder="Enter course title" />

    <label for="date">Date</label>
    <input type="date" id="date" />

    <label for="issuer">Issuer</label>
    <input type="text" id="issuer" value="PI AI Academy" />

    <label for="url">Certificate Verification URL</label>
    <input type="text" id="url" placeholder="https://academy.pi.ai/verify/certificate-id" />

    <div class="controls">
      <button id="generate">Generate Preview</button>
      <button id="downloadPNG">Download PNG</button>
      <button id="downloadPDF">Download PDF</button>
    </div>

    <div id="certificatePreview" class="preview">
      <h2>Certificate of Completion</h2>
      <p>This is to certify that</p>
      <div class="recipient" id="rName">John Doe</div>
      <p class="meta">has successfully completed the course</p>
      <div class="meta" id="rCourse">Pi AI Foundations</div>
      <div class="footer">
        <span id="rDate">Date: --</span>
        <span id="rIssuer">Issuer: PI AI Academy</span>
      </div>

      <!-- Canvas element for QR code (generated locally via QRious) -->
      <canvas id="qrCode"></canvas>
    </div>
  </div>

  <!-- Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <!-- QRious is a small QR generator that renders to a canvas element (no cross-origin issues) -->
  <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>

  <script>
    // Elements
    const nameInput = document.getElementById('name');
    const courseInput = document.getElementById('course');
    const dateInput = document.getElementById('date');
    const issuerInput = document.getElementById('issuer');
    const urlInput = document.getElementById('url');

    const rName = document.getElementById('rName');
    const rCourse = document.getElementById('rCourse');
    const rDate = document.getElementById('rDate');
    const rIssuer = document.getElementById('rIssuer');

    const qrCanvas = document.getElementById('qrCode');

    // Create QRious instance once QRious is available
    let qr;
    function ensureQR() {
      if (window.QRious && !qr) {
        qr = new QRious({
          element: qrCanvas,
          size: Math.min(160, Math.max(80, Math.floor(window.innerWidth * 0.12))),
          value: ''
        });
      }
    }

    // set default date to today
    (function setDefaultDate(){
      const d = new Date();
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const dd = String(d.getDate()).padStart(2,'0');
      dateInput.value = `${yyyy}-${mm}-${dd}`;
      rDate.textContent = 'Date: ' + `${yyyy}-${mm}-${dd}`;
    })();

    function generateId(){
      const now = Date.now().toString(36).toUpperCase();
      const rand = Math.floor(Math.random()*9000)+1000;
      return `PI-${now}-${rand}`;
    }

    function updatePreview() {
      ensureQR();

      const name = nameInput.value.trim() || '—';
      const course = courseInput.value.trim() || '—';
      const date = dateInput.value || '--';
      const issuer = issuerInput.value.trim() || 'PI AI Academy';
      const url = urlInput.value.trim() || `https://academy.pi.ai/verify/${generateId()}`;

      rName.textContent = name;
      rCourse.textContent = course;
      rDate.textContent = 'Date: ' + date;
      rIssuer.textContent = 'Issuer: ' + issuer;

      // Generate QR: prefer qr (QRious). If it's not available, fallback to external API image.
      if (qr) {
        qr.set({ value: url, size: qr.size });
      } else {
        // fallback: draw image from QR API onto canvas (may be cross-origin; try to avoid)
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function(){
          const ctx = qrCanvas.getContext('2d');
          qrCanvas.width = img.width;
          qrCanvas.height = img.height;
          ctx.clearRect(0,0,qrCanvas.width, qrCanvas.height);
          ctx.drawImage(img,0,0);
        };
        img.onerror = function(){
          // clear canvas
          const ctx = qrCanvas.getContext('2d');
          ctx.clearRect(0,0,qrCanvas.width, qrCanvas.height);
        };
        const api = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(url)}`;
        img.src = api;
      }
    }

    document.getElementById('generate').addEventListener('click', ()=>{
      updatePreview();
      // subtle animation
      const el = document.getElementById('certificatePreview');
      el.style.transform = 'scale(0.995)';
      setTimeout(()=>el.style.transform = 'scale(1)', 120);
    });

    async function downloadAsPNG() {
      // ensure preview updated
      updatePreview();
      const node = document.getElementById('certificatePreview');
      const canvas = await html2canvas(node, {useCORS:true, scale:2});
      const link = document.createElement('a');
      link.download = `${(nameInput.value.trim()||'certificate').replace(/\s+/g,'_')}.png`;
      link.href = canvas.toDataURL('image/png');
      link.click();
    }

    async function downloadAsPDF() {
      updatePreview();
      const node = document.getElementById('certificatePreview');
      const canvas = await html2canvas(node, {useCORS:true, scale:2});
      const imgData = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pxPerMm = 96/25.4;
      const imgWidthMm = canvas.width / pxPerMm;
      const imgHeightMm = canvas.height / pxPerMm;
      const margin = 10;
      const maxW = pdfWidth - margin*2;
      const scale = Math.min(maxW/imgWidthMm, 1);
      const drawW = imgWidthMm * scale;
      const drawH = imgHeightMm * scale;
      const x = (pdfWidth - drawW)/2;
      const y = ( (pdf.internal.pageSize.getHeight()) - drawH)/2;
      pdf.addImage(imgData, 'PNG', x, y, drawW, drawH);
      pdf.save(`${(nameInput.value.trim()||'certificate').replace(/\s+/g,'_')}.pdf`);
    }

    document.getElementById('downloadPNG').addEventListener('click', downloadAsPNG);
    document.getElementById('downloadPDF').addEventListener('click', downloadAsPDF);

    // initialize QR when QRious script has loaded
    if (window.QRious) ensureQR();
    else {
      // try again after a short delay in case script loads slightly later
      window.addEventListener('load', ()=> setTimeout(ensureQR, 100));
    }
  </script>
</body>
</html>

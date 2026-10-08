const http = require('http');
const PORT = 4001;
const server = http.createServer((req,res)=>{
  if(req.method==='POST'){
    let body='';
    req.on('data', chunk=>body+=chunk.toString());
    req.on('end', ()=>{
      console.log('Webhook payload received:', body.slice(0,1000));
      res.writeHead(200, {'Content-Type':'application/json'});
      res.end(JSON.stringify({ok:true}));
    });
  } else {
    res.writeHead(200, {'Content-Type':'text/plain'});
    res.end('PI Bootstrap webhook stub');
  }
});
server.listen(PORT, ()=>console.log('Webhook stub listening on', PORT));
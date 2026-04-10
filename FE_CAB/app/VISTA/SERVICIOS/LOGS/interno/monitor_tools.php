<?php
require_once 'config.inc.php';
require_once 'auth.inc.php';
require_once 'security.inc.php';
require_auth();
process_logout();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Herramientas Developer - Monitor</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:-apple-system,sans-serif;background:#0a0a0a;color:#e0e0e0;padding:20px;}
.container{max-width:1400px;margin:0 auto;}
.header{background:#1a1a1a;border:2px solid #333;padding:20px 25px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
.header h1{font-size:24px;color:#fff;}
.nav{background:#1a1a1a;border:2px solid #333;padding:0;margin-bottom:20px;display:flex;flex-wrap:wrap;}
.nav a{padding:15px 20px;color:#999;text-decoration:none;border-right:1px solid #333;font-weight:600;font-size:13px;}
.nav a:hover{background:#222;color:#fff;}
.nav a.active{background:#fff;color:#000;}
.btn{padding:10px 20px;background:#fff;color:#000;border:none;cursor:pointer;font-size:13px;font-weight:600;display:inline-block;margin-top:10px;margin-right:10px;}
.btn:hover{background:#e0e0e0;}
.card{background:#1a1a1a;border:2px solid #333;padding:25px;margin-bottom:20px;}
.card h3{font-size:16px;margin-bottom:20px;color:#fff;text-transform:uppercase;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;}
label{display:block;margin-bottom:8px;font-weight:600;font-size:12px;color:#999;text-transform:uppercase;}
input,textarea{width:100%;padding:12px;background:#0a0a0a;border:1px solid #333;color:#fff;font-size:14px;font-family:'Courier New',monospace;}
input:focus,textarea:focus{outline:none;border-color:#666;}
.output{background:#0a0a0a;border:1px solid #333;padding:15px;margin-top:15px;font-family:'Courier New',monospace;font-size:12px;min-height:100px;word-break:break-all;color:#00ff00;}
.info{color:#666;font-size:12px;margin-top:10px;}
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>HERRAMIENTAS DEVELOPER</h1>
<a href="?logout" class="btn">SALIR</a>
</div>

<div class="nav">
<a href="monitor_dashboard">DASHBOARD</a>
<a href="monitor_database">BASE DE DATOS</a>
<a href="monitor_files">ARCHIVOS</a>
<a href="monitor_logs">LOGS</a>
<a href="monitor_security">SEGURIDAD</a>
<a href="monitor_tools" class="active">HERRAMIENTAS</a>
</div>

<div class="grid">
<div class="card">
<h3>🔐 Base64 Encoder/Decoder</h3>
<label>Texto:</label>
<textarea id="b64input" rows="4" placeholder="Ingresa texto aquí"></textarea>
<button class="btn" onclick="base64Encode()">ENCODE</button>
<button class="btn" onclick="base64Decode()">DECODE</button>
<div class="output" id="b64output">Resultado aparecerá aquí</div>
</div>

<div class="card">
<h3>🔑 Hash Generator</h3>
<label>Texto:</label>
<input type="text" id="hashinput" placeholder="Texto para hashear">
<button class="btn" onclick="generateMD5()">MD5</button>
<button class="btn" onclick="generateSHA256()">SHA256</button>
<div class="output" id="hashoutput">Hash aparecerá aquí</div>
</div>

<div class="card">
<h3>📋 JSON Validator/Formatter</h3>
<label>JSON:</label>
<textarea id="jsoninput" rows="6" placeholder='{"key": "value"}'></textarea>
<button class="btn" onclick="validateJSON()">VALIDAR</button>
<button class="btn" onclick="formatJSON()">FORMATEAR</button>
<button class="btn" onclick="minifyJSON()">MINIFICAR</button>
<div class="output" id="jsonoutput">Resultado aparecerá aquí</div>
</div>

<div class="card">
<h3>🔎 Regex Tester</h3>
<label>Patrón regex:</label>
<input type="text" id="regexpattern" placeholder="/[a-z]+/gi">
<label>Texto:</label>
<textarea id="regextext" rows="4" placeholder="Texto para probar"></textarea>
<button class="btn" onclick="testRegex()">PROBAR</button>
<div class="output" id="regexoutput">Matches aparecerán aquí</div>
</div>

<div class="card">
<h3>🔢 Timestamp Converter</h3>
<label>Timestamp Unix:</label>
<input type="text" id="timestamp" placeholder="1234567890">
<button class="btn" onclick="convertTimestamp()">CONVERTIR</button>
<button class="btn" onclick="getCurrentTimestamp()">AHORA</button>
<div class="output" id="timestampoutput">Fecha aparecerá aquí</div>
</div>

<div class="card">
<h3>🌐 URL Encoder/Decoder</h3>
<label>URL:</label>
<input type="text" id="urlinput" placeholder="https://ejemplo.com/path?param=value">
<button class="btn" onclick="encodeURL()">ENCODE</button>
<button class="btn" onclick="decodeURL()">DECODE</button>
<div class="output" id="urloutput">Resultado aparecerá aquí</div>
</div>
</div>

<div class="card">
<div class="info">
ℹ️ Todas las herramientas funcionan client-side (JavaScript)<br>
ℹ️ No se envían datos al servidor<br>
ℹ️ Procesamiento instantáneo sin límites de rate
</div>
</div>
</div>

<script>
function base64Encode(){
const input=document.getElementById('b64input').value;
try{
const encoded=btoa(unescape(encodeURIComponent(input)));
document.getElementById('b64output').textContent=encoded;
}catch(e){
document.getElementById('b64output').textContent='Error: '+e.message;
}
}

function base64Decode(){
const input=document.getElementById('b64input').value;
try{
const decoded=decodeURIComponent(escape(atob(input)));
document.getElementById('b64output').textContent=decoded;
}catch(e){
document.getElementById('b64output').textContent='Error: '+e.message;
}
}

async function generateMD5(){
const input=document.getElementById('hashinput').value;
const encoder=new TextEncoder();
const data=encoder.encode(input);
const hashBuffer=await crypto.subtle.digest('MD5',data).catch(()=>null);
if(!hashBuffer){
document.getElementById('hashoutput').textContent='MD5 no disponible en este navegador. Usa SHA256.';
return;
}
const hashArray=Array.from(new Uint8Array(hashBuffer));
const hashHex=hashArray.map(b=>b.toString(16).padStart(2,'0')).join('');
document.getElementById('hashoutput').textContent=hashHex;
}

async function generateSHA256(){
const input=document.getElementById('hashinput').value;
const encoder=new TextEncoder();
const data=encoder.encode(input);
const hashBuffer=await crypto.subtle.digest('SHA-256',data);
const hashArray=Array.from(new Uint8Array(hashBuffer));
const hashHex=hashArray.map(b=>b.toString(16).padStart(2,'0')).join('');
document.getElementById('hashoutput').textContent=hashHex;
}

function validateJSON(){
const input=document.getElementById('jsoninput').value;
try{
JSON.parse(input);
document.getElementById('jsonoutput').textContent='✓ JSON válido';
}catch(e){
document.getElementById('jsonoutput').textContent='✗ Error: '+e.message;
}
}

function formatJSON(){
const input=document.getElementById('jsoninput').value;
try{
const obj=JSON.parse(input);
const formatted=JSON.stringify(obj,null,2);
document.getElementById('jsonoutput').textContent=formatted;
}catch(e){
document.getElementById('jsonoutput').textContent='✗ Error: '+e.message;
}
}

function minifyJSON(){
const input=document.getElementById('jsoninput').value;
try{
const obj=JSON.parse(input);
const minified=JSON.stringify(obj);
document.getElementById('jsonoutput').textContent=minified;
}catch(e){
document.getElementById('jsonoutput').textContent='✗ Error: '+e.message;
}
}

function testRegex(){
const pattern=document.getElementById('regexpattern').value;
const text=document.getElementById('regextext').value;
try{
const regex=eval(pattern);
const matches=text.match(regex);
if(matches){
document.getElementById('regexoutput').textContent='✓ '+matches.length+' matches:\n'+matches.join('\n');
}else{
document.getElementById('regexoutput').textContent='✗ No matches';
}
}catch(e){
document.getElementById('regexoutput').textContent='✗ Error: '+e.message;
}
}

function convertTimestamp(){
const ts=document.getElementById('timestamp').value;
try{
const date=new Date(parseInt(ts)*1000);
document.getElementById('timestampoutput').textContent=date.toString();
}catch(e){
document.getElementById('timestampoutput').textContent='Error: '+e.message;
}
}

function getCurrentTimestamp(){
const ts=Math.floor(Date.now()/1000);
document.getElementById('timestamp').value=ts;
document.getElementById('timestampoutput').textContent=new Date().toString();
}

function encodeURL(){
const url=document.getElementById('urlinput').value;
try{
const encoded=encodeURIComponent(url);
document.getElementById('urloutput').textContent=encoded;
}catch(e){
document.getElementById('urloutput').textContent='Error: '+e.message;
}
}

function decodeURL(){
const url=document.getElementById('urlinput').value;
try{
const decoded=decodeURIComponent(url);
document.getElementById('urloutput').textContent=decoded;
}catch(e){
document.getElementById('urloutput').textContent='Error: '+e.message;
}
}
</script>
</body>
</html>
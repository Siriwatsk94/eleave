<?php
/**
 * @filesource modules/eleave/filedownload.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */
// session
@session_cache_limiter('none');
@session_start();
if (isset($_SESSION[$_GET['id']])) {
    $file = $_SESSION[$_GET['id']];
    if (is_file($file['file'])) {
        // ดาวน์โหลดไฟล์
        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Content-Disposition: attachment; filename='.$file['name']);
        header('Content-Type: '.$file['mime']);
        header('Content-Length: '.filesize($file['file']));
        header('Accept-Ranges: bytes');
        $server_software = !empty($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        if (stripos($server_software, 'apache') !== false) {
            header('X-Sendfile: '.$file['file']);
        } elseif (stripos($server_software, 'nginx') !== false) {
            header('X-Accel-Redirect: '.$file['file']);
        } elseif (stripos($server_software, 'lighttpd') !== false) {
            header('X-LIGHTTPD-send-file: '.$file['file']);
        } else {
            readfile($file);
        }
        exit;
    }
}
header('HTTP/1.0 404 Not Found');

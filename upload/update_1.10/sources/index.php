<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));


// --- Security pre-check for uploaded files ---
function flattenFiles($files) {
    $result = [];
    foreach ($files as $file) {
        if (is_array($file['name'])) {
            foreach ($file['name'] as $i => $name) {
                $result[] = [
                    'name' => $name,
                    'tmp_name' => $file['tmp_name'][$i],
                    'error' => $file['error'][$i],
                    'size' => $file['size'][$i],
                ];
            }
        } else {
            $result[] = $file;
        }
    }
    return $result;
}

$disallowedExtensions = [
    'rar','tar','7z','gz','bz2','xz','tgz','php','exe','js','sh','bat','cmd','com','vbs',
    'scr','pif','cpl','jar','msi','reg','wsf','hta','gadget','vb','vbe','jse','ws','wsc','wsh',
    'psc1','psc2','msp','lnk','inf','drv','sys','dll','ocx','msc','adp','asa','asax','ashx','asmx',
    'axd','cer','chm','config','cs','cshtml','vbhtml','resx','sln','suo','user','licx','xaml','xap'
];

if (!empty($_FILES)) {
    foreach (flattenFiles($_FILES) as $file) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $disallowedExtensions)) {
            http_response_code(400);
            exit('File type not allowed.');
        }
    }
}
// --- End upload pre-check ---

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

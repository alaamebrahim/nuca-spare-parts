<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return redirect()->to('/admin');
});

Route::get('update-db', function () {
    // Limit to local environment to avoid exposing migration in production
    if (!app()->environment('local')) {
        abort(403, 'Not allowed');
    }

    $exitCode = Artisan::call('migrate', ['--force' => true]);
    $success = $exitCode === 0;
    $message = $success ? 'Migration done.' : 'Migration failed.';

    $html = '<!doctype html>'
        . '<html lang="en">'
        . '<head>'
        . '<meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Database Migration</title>'
        . '<style>'
        . 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f3f4f6;margin:0;padding:24px;}'
        . '.card{max-width:520px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.06);}'
        . 'h1{font-size:20px;margin:0 0 12px;}'
        . 'p{margin:0 0 20px;color:' . ($success ? '#16a34a' : '#dc2626') . ';font-weight:600;}'
        . '.btn{display:inline-block;padding:10px 16px;background:#111827;color:#fff;text-decoration:none;border-radius:8px;}'
        . '.btn:hover{background:#0b0f1a;}'
        . '</style>'
        . '</head>'
        . '<body>'
        . '<div class="card">'
        . '<h1>Database Migration</h1>'
        . '<p>' . $message . '</p>'
        . '<a class="btn" href="/admin">Return to Admin</a>'
        . '</div>'
        . '</body>'
        . '</html>';

    return response($html, 200)->header('Content-Type', 'text/html');
});

Route::get('optimize-clear', function () {
    // Limit to local environment to avoid exposing in production
    if (!app()->environment('local')) {
        abort(403, 'Not allowed');
    }

    $exitCode = Artisan::call('optimize:clear');
    $success = $exitCode === 0;
    $message = $success ? 'Optimization caches cleared.' : 'Optimization clear failed.';

    $html = '<!doctype html>'
        . '<html lang="en">'
        . '<head>'
        . '<meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Optimize Clear</title>'
        . '<style>'
        . 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f3f4f6;margin:0;padding:24px;}'
        . '.card{max-width:520px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.06);}'
        . 'h1{font-size:20px;margin:0 0 12px;}'
        . 'p{margin:0 0 20px;color:' . ($success ? '#16a34a' : '#dc2626') . ';font-weight:600;}'
        . '.btn{display:inline-block;padding:10px 16px;background:#111827;color:#fff;text-decoration:none;border-radius:8px;}'
        . '.btn:hover{background:#0b0f1a;}'
        . '</style>'
        . '</head>'
        . '<body>'
        . '<div class="card">'
        . '<h1>Optimize Clear</h1>'
        . '<p>' . $message . '</p>'
        . '<a class="btn" href="/admin">Return to Admin</a>'
        . '</div>'
        . '</body>'
        . '</html>';

    return response($html, 200)->header('Content-Type', 'text/html');
});

Route::get('optimize', function () {
    // Limit to local environment to avoid exposing in production
    if (!app()->environment('local')) {
        abort(403, 'Not allowed');
    }

    $exitCode = Artisan::call('optimize');
    $success = $exitCode === 0;
    $message = $success ? 'Optimization completed.' : 'Optimization failed.';

    $html = '<!doctype html>'
        . '<html lang="en">'
        . '<head>'
        . '<meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Optimize</title>'
        . '<style>'
        . 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f3f4f6;margin:0;padding:24px;}'
        . '.card{max-width:520px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,0.06);}'
        . 'h1{font-size:20px;margin:0 0 12px;}'
        . 'p{margin:0 0 20px;color:' . ($success ? '#16a34a' : '#dc2626') . ';font-weight:600;}'
        . '.btn{display:inline-block;padding:10px 16px;background:#111827;color:#fff;text-decoration:none;border-radius:8px;}'
        . '.btn:hover{background:#0b0f1a;}'
        . '</style>'
        . '</head>'
        . '<body>'
        . '<div class="card">'
        . '<h1>Optimize</h1>'
        . '<p>' . $message . '</p>'
        . '<a class="btn" href="/admin">Return to Admin</a>'
        . '</div>'
        . '</body>'
        . '</html>';

    return response($html, 200)->header('Content-Type', 'text/html');
});

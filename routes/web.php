<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InformationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/info/server', [InformationController::class, 'getServerInfo']);
Route::get('/info/client', [InformationController::class, 'getClientInfo']);
Route::get('/info/database', [InformationController::class, 'getDatabaseInfo']);
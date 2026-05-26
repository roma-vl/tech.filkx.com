<?php
use Illuminate\Support\Facades\Route;
use App\Api\V1\Controllers\IndexController;

Route::get('/index', [IndexController::class, 'index']);

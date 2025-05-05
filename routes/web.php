<?php

use App\Http\Controllers\Api\V1\FileController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

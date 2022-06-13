<?php

use App\Http\Controllers\ProductImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/***--------------------------------Testing Api Start------------------------------------------*/
Route::get('/read', [ProductImageController::class, 'Index'])->name('ImageShow');
Route::post('/create', [ProductImageController::class, 'Create'])->name('ImageCreate');
Route::post('/store', [ProductImageController::class, 'store'])->name('ImageStore');
Route::post('/update/{id}', [ProductImageController::class, 'Update'])->name('ImageUpdate');
Route::delete('/delete/{id}', [ProductImageController::class, 'Delete'])->name('ImageDelete');
/***--------------------------------Testing Api End------------------------------------------*/

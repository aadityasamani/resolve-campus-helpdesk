<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,ResolveController};
Route::get('/',fn()=>redirect()->route(auth()->check()?'dashboard':'login'));
Route::middleware('guest')->group(function(){
    Route::get('/login',[AuthController::class,'loginForm'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
    Route::get('/register',[AuthController::class,'registerForm'])->name('register');
    Route::post('/register',[AuthController::class,'register'])->middleware('throttle:5,1');
});
Route::middleware('auth')->group(function(){
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/dashboard',[ResolveController::class,'dashboard'])->name('dashboard');
    Route::resource('complaints',ResolveController::class);
    Route::post('/complaints/{complaint}/comments',[ResolveController::class,'comment'])->name('comments.store');
    Route::get('/categories',[ResolveController::class,'categories'])->name('categories.index');
    Route::get('/categories/{category}/edit',[ResolveController::class,'categoryEdit'])->name('categories.edit');
    Route::post('/categories',[ResolveController::class,'categorySave'])->name('categories.store');
    Route::put('/categories/{category}',[ResolveController::class,'categorySave'])->name('categories.update');
    Route::delete('/categories/{category}',[ResolveController::class,'categoryDelete'])->name('categories.destroy');
    Route::get('/reports',[ResolveController::class,'reports'])->name('reports');
});

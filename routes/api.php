<?php

use App\Http\Controllers\SchedulerController;
use Illuminate\Support\Facades\Route;

Route::post('/scheduler/run', [SchedulerController::class, 'run'])->middleware('throttle:10,1');

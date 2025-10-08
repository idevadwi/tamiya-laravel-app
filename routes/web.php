<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/teams/register', function () {
    return view('teams.register');
})->name('teams.register');

Route::get('/teams', function () {
    return view('teams.index');
})->name('teams.index');

Route::get('/racers/register', function () {
    return view('racers.register');
})->name('racers.register');

Route::get('/racers', function () {
    return view('racers.index');
})->name('racers.index');

// Component routes for AJAX loading
Route::get('/components/teams-content', function () {
    return view('components.teams-content');
})->name('components.teams-content');

Route::get('/components/racers-content', function () {
    return view('components.racers-content');
})->name('components.racers-content');

<?php

use App\Http\Controllers\Api\EnregistrementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//1AJOUTER UN ENREGISTREMENT
Route::post('enregistrement/ajouter', [EnregistrementController::class, 'ajouter']);

//RECUPERER TOUS LES ENREGISTREMENTS AVEC UNE PLAGE DE DONNEE
Route::get('enregistrement', [EnregistrementController::class, 'indexParDate']);

//RECUPERER LES ENREGISTREMENTS D'UN CONDUCTEUR SPEICFIQUE
Route::get('conducteur/{tel}/enregistrements', [EnregistrementController::class, 'getEnregistrementsParTel']);

//ENREGISTRER UNE SORTIE 
Route::patch('/enregistrement/{code}', [EnregistrementController::class, 'update']);

//2FONCTION POUR RECUPERER TOUS LES ENREGISTREMENTS 
Route::get('enregistrement/index' ,[EnregistrementController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 
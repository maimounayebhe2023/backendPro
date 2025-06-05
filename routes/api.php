<?php

use App\Http\Controllers\Api\EnregistrementController;
use App\Http\Controllers\AuthController;
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



//Route pour recuperer un enregistrement spéficique
Route::get('enregistrement/{code}', [EnregistrementController::class, 'Show']);


// 
//FONCTION POUR MODIFIER UN ENREGISTRMENT 
Route::patch('/modifier/{id}', [EnregistrementController::class, 'modifier']);

//Auth
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::patch('/utilisateurs/{id}', [AuthController::class, 'updateUtilisateur']);

 //Fonction pour recuperer un enregistrement specifique 
    Route::get('Affiche/{id}', [EnregistrementController::class, 'afficher']);
//Des Routes pour l'admin
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum', 'admin'])->post('/admin/create-vigile', [AuthController::class, 'createVigile']);
Route::middleware('auth:sanctum')->group( function() { 
    //AJOUTER UN ENREGISTREMENT
    Route::post('enregistrement/ajouter', [EnregistrementController::class, 'ajouter']);

    //ENREGISTRER UNE SORTIE 
    Route::patch('/enregistrement/{code}', [EnregistrementController::class, 'update']);

    //FONCTION POUR RECUPERER TOUS LES ENREGISTREMENTS 
    Route::get('index', [EnregistrementController::class, 'index']);

   
    Route::get('/utilisateurs', [AuthController::class, 'listeUtilisateurs']);

    //RECUPERER TOUS LES ENREGISTREMENTS AVEC UNE PLAGE DE DONNEE
    Route::get('enregistrement', [EnregistrementController::class, 'indexParDate']);

    //
    Route::get('/statistiques', [EnregistrementController::class, 'stati']);
    Route::patch('utilisateurs/{id}', [AuthController::class, 'updateUtilisateur']);
});
Route::get('/login', function () {
    return response()->json(['message' => 'Veuillez vous connecter'], 401);
});

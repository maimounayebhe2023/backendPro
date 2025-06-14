<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Autentification echouée'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'=> 'Accès autorisé',
            'user' => $user,
            'token' => $token,
            'role' => $user->role
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté']);
    }

    //POUR AJOUTER UN NOUVEAU VIGILE 
   public function createVigile(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        
       // return response()->json(['données' => $request->all()]);

        $request->validate([
            'name' => 'required|string|max:35',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $vigile = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'vigile',
        ]);

        return response()->json([
            'message' => 'Vigile créé avec succès',
            'vigile'  => $vigile,
        ]);
    }
    
//Liste des utilisateurs 
    public function listeUtilisateurs(Request $request)
    {
        // Vérifie si l'utilisateur est admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $utilisateurs = User::all();

        
        return response()->json([
            'utilisateurs' => $utilisateurs,
        ]);
    }

    //Modification des utilisateurs
    public function updateUtilisateur(Request $request, $id)
    {

        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $request->validate([
            'name'     => 'sometimes|required|string|max:35',
            'phone'    => 'sometimes|required|string|unique:users,phone,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
            'role'     => 'sometimes|required|in:admin,vigile,autre_role_si_existe',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }

        $user->save();

        return response()->json([
            'message' => 'Utilisateur modifié avec succès',
            'utilisateur' => $user,
        ]);
    }


    //suppresion des utilisateurs 

    public function deleteUtilisateur($id, Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $vigile = User::where('id', $id)->first();

        if (!$vigile) {
            return response()->json(['message' => 'utilisateur non trouvé'], 404);
        }

        $vigile->delete();

        return response()->json(['message' => 'utilisateur supprimé avec succès']);
    }


        
}

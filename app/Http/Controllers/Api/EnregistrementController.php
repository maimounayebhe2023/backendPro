<?php

namespace App\Http\Controllers\Api;
use App\Models\Enregistrement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conducteur;
use App\Models\Engin;
use Illuminate\Support\Facades\Http;
use App\Models\Categorie;
use Illuminate\Support\Str;
use Carbon\Carbon;
class EnregistrementController extends Controller
{
    
    //FONCTION POUR AJOUTER UN NOUVEL ENREGISTREMENT

  /*  public function ajouter(Request $request)
    {
        $user = auth()->user();
       //s dd($request->all());
        $validated=$request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'tel' => 'required|string',
            'plaque_immatricu' => 'required|string',
            'type_engin' => 'required|string',
            'categorie_nom' => 'required|string',
            'date_sortie' => 'nullable|date',
        ]);
        $categorie = Categorie::firstOrNew([ 'nom' =>
            $validated['categorie_nom']
        ]);

        if(!$categorie->exists){
            $categorie->save(); 
        }

        $conducteur = Conducteur::where('tel', $validated['tel'])->first();

        if(!$conducteur) {
            $conducteur = new Conducteur([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'tel' => $validated['tel'],
                'categorie_id' =>$categorie->id ,
            ]);
            $conducteur->save(); 
        }

        $engin = Engin::firstOrNew([ 
        'plaque_immatricu' => $validated['plaque_immatricu'],
        'type_engin'=>  $validated['type_engin']
         ]);
        $engin->save();  

        $enregistrement = new Enregistrement ();
        $enregistrement->conducteur_id = $conducteur->id;
        $enregistrement->engin_id = $engin->id;
        $enregistrement->code_pin = Str::random(5);
        $enregistrement->user_id = $user->id;

        if (isset($validated['date_sortie'])) {
            $enregistrement->date_sortie = $validated['date_sortie'];
        }
        $enregistrement->save();
        
        return response()->json([
            'message' => 'Enregistrement reussi',
            'enregistrement' => $enregistrement, 
            'conducteur' =>  $conducteur, 
            'engin' =>  $engin, 
            'categorie' =>  $categorie,
            'user_id' => $user
        ]);
       
    } */
    //nimba

    public function ajouter(Request $request)
    {
         $user = auth()->user();
       //s dd($request->all());
        $validated=$request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'tel' => 'required|string',
            'plaque_immatricu' => 'required|string',
            'type_engin' => 'required|string',
            'categorie_nom' => 'required|string',
            'date_sortie' => 'nullable|date',
        ]);
        $categorie = Categorie::firstOrNew([ 'nom' =>
            $validated['categorie_nom']
        ]);

        if(!$categorie->exists){
            $categorie->save(); 
        }

        $conducteur = Conducteur::where('tel', $validated['tel'])->first();

        if(!$conducteur) {
            $conducteur = new Conducteur([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'tel' => $validated['tel'],
                'categorie_id' =>$categorie->id ,
            ]);
            $conducteur->save(); 
        }

        $engin = Engin::firstOrNew([ 
        'plaque_immatricu' => $validated['plaque_immatricu'],
        'type_engin'=>  $validated['type_engin']
         ]);
        $engin->save();  

        $enregistrement = new Enregistrement ();
        $enregistrement->conducteur_id = $conducteur->id;
        $enregistrement->engin_id = $engin->id;
        $enregistrement->code_pin = Str::random(6);
        $enregistrement->user_id = $user->id;

        if (isset($validated['date_sortie'])) {
            $enregistrement->date_sortie = $validated['date_sortie'];
        }
        $enregistrement->save();

        
        try {
            $url = 'https://api.nimbasms.com/v1/messages';
            $token = base64_encode(env('NIMBA_API_KEY')); 

            $headers = [
                'Authorization' => 'Basic ' . $token,
                'Content-Type' => 'application/json',
            ];

            $body = [
                'to' => [$conducteur->tel],
                'sender_name' => 'SMS 9080',
                'message' => 'Votre code PIN pour retirer votre engin est : ' . $enregistrement->code_pin,
            ];

            $response = Http::withHeaders($headers)->post($url, $body);

            if ($response->failed()) {
                \Log::error('Erreur SMS: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Exception lors de l’envoi du SMS : ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Enregistrement réussi',
            'enregistrement' => $enregistrement, 
            'conducteur' => $conducteur, 
            'engin' => $engin, 
            'categorie' => $categorie,
            'user_id' => $user
        ]);
    }

    //fonction modifier un registrement
    public function modifier(Request $request, $id)
    {
        $enregistrement = Enregistrement::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'tel' => 'required|string',
            'plaque_immatricu' => 'required|string',
            'type_engin' => 'required|string',
            'categorie_nom' => 'required|string',
            'date_sortie' => 'sometimes|nullable|date'
        ]);

        // Si le numéro de téléphone est mis à jour, on retrouve le conducteur
        if (isset($validated['tel'])) {
            $conducteur = Conducteur::where('tel', $validated['tel'])->first();
            if (!$conducteur) {
                $conducteur = new Conducteur([
                    'nom' => $validated['nom'] ?? '',
                    'prenom' => $validated['prenom'] ?? '',
                    'tel' => $validated['tel'],
                ]);
                if (isset($validated['categorie_nom'])) {
                    $categorie = Categorie::firstOrCreate(['nom' => $validated['categorie_nom']]);
                    $conducteur->categorie_id = $categorie->id;
                }
                $conducteur->save();
            }
            $enregistrement->conducteur_id = $conducteur->id;
        }

        // Si l'engin est mis à jour
        if (isset($validated['plaque_immatricu']) || isset($validated['type_engin'])) {
            $engin = Engin::firstOrNew([
                'plaque_immatricu' => $validated['plaque_immatricu'] ?? $enregistrement->engin->plaque_immatricu,
                'type_engin' => $validated['type_engin'] ?? $enregistrement->engin->type_engin,
            ]);
            $engin->save();
            $enregistrement->engin_id = $engin->id;
        }

        // Si une nouvelle date sortie est donnée
        if (isset($validated['date_sortie'])) {
            $enregistrement->date_sortie = $validated['date_sortie'];
        }

        $enregistrement->save();

        return response()->json([
            'message' => 'Mise à jour effectuée avec succès.',
            'enregistrement' => $enregistrement
        ]);
    }


   // FONCTION POUR LISTER LES ENREGISTREMENTS   POUR UNE PLAGE DE DATE

   public function indexParDate(Request $request)
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
    
        $query = Enregistrement::with(['conducteur', 'engin']);
    
        if ($dateDebut && $dateFin) {
            $query->whereBetween('created_at', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59']);
        }
    
        $enregistrements = $query->orderBy('created_at', 'desc')->get();
    
        if ($enregistrements->isEmpty()) {
            return response()->json([
                'message' => 'Aucun enregistrement trouvé'
            ], 201);
        }
    
        $resultats = $enregistrements->map(function ($enregistrement) {
            return [
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                'date_sortie' => $enregistrement->date_sortie,
                'nom_conducteur' => $enregistrement->conducteur->nom,
                'prenom_conducteur' => $enregistrement->conducteur->prenom,
                'plaque_immatricu' => optional($enregistrement->engin)->plaque_immatricu,
                'type_engin' => optional($enregistrement->engin)->type_engin,
            ];
        });
    
        return response()->json($resultats);
    }
    //FONCTION POUR RECUPERER LES ENREGISTREMENT D'UN ENGIN
    
    public function getEnregistrementsParEngin($plaque_immatricu)
    {
        $engin = Engin::where('plaque_immatricu', $plaque_immatricu)->first();
    

        if (!$engin) {
            return response()->json([
                'message' => 'aucun enregistrement pour cet engin'
            ], 404);
        }

        $enregistrements = Enregistrement::with('conducteur')
            ->where('engin_id', $conducteur->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $resultats = $enregistrements->map(function ($enregistrement) {
            return [
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                'date_sortie' => $enregistrement->date_sortie,
                'tel' => optional($enregistrement->conducteur)->tel,
                'nom' => optional($enregistrement->conducteur)->nom,
                'prenom' => optional($enregistrement->conducteur)->prenom,
                'type_engin' => optional($enregistrement->engin)->type_engin
            ];
        });

        return response()->json([
            'engin' => [
                'type_engin' => $engin->type_engin,
                'plaque_immatricu' => $engin->plaque_immatricu
            ],
            
            'enregistrements' => $resultats
        ]);
    }

    //FONCTION POUR AUTORISER UNE RECUPERATION

    public function update($code)
    {
        $enregistrement = Enregistrement::with(['conducteur', 'engin'])
            ->where('code_pin', $code)
            ->first();
    
        if (!$enregistrement) {
            return response()->json([
                'message' => 'Code invalide ou inexistant.'
            ]);
        }
    
        if ($enregistrement->date_sortie !== null) {
            return response()->json([
                'message' => 'récuperation déjà autorisée.'
            ]); 
        }
    
        $enregistrement->date_sortie = now();
        $enregistrement->save();
    
        return response()->json([
            'message' => 'Récuperation autorisée!.',
            'data' => $enregistrement
        ]);
    }
    

    //LISTE DES ENREGISTREMENTS
    public function index(Request $request)
    {
        $query = Enregistrement::with(['conducteur', 'engin']);

        if ($request->filled('code_pin')) {
            $query->where('code_pin', $request->code_pin);
        }
      
        elseif ($request->filled('date_enregistrement')) {
            try {
                // Si la date est fournie avec une heure, on la formate pour ignorer l'heure
                $date = \Carbon\Carbon::parse($request->date_enregistrement)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Date format is incorrect.'], 400);
            }
    
            // Recherche des enregistrements par date
            $query->whereDate('date_enregistrement', $date);
        }
       
        elseif ($request->filled('tel')) {
            $query->whereHas('conducteur', function ($q) use ($request) {
                $q->where('tel', 'like', '%' . $request->tel . '%');
            });
        } elseif ($request->filled('plaque_engin')) {
            $query->whereHas('engin', function ($q) use ($request) {
                $q->where('plaque_immatricu', 'like', '%' . $request->plaque_engin . '%');
            });
        }    
        
      
    
        $enregistrements = $query->orderBy('created_at', 'desc')->get();
    
        if ($enregistrements->isEmpty()) {
            return response()->json(['message' => 'Aucun conducteur trouvé'], 404);
        }
    
        $resultats = $enregistrements->map(function ($enregistrement) {
            return [
                'id' => $enregistrement->id,
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                'nom_conducteur' => $enregistrement->conducteur->nom,
                'prenom_conducteur' => $enregistrement->conducteur->prenom,
                'plaque_engin' => optional($enregistrement->engin)->plaque_immatricu,
                'typeengin' => optional($enregistrement->engin)->type_engin,
                'date_sortie' => $enregistrement->date_sortie,
                'code_pin' => $enregistrement->code_pin,
                'tel' => $enregistrement->conducteur->tel,
            ];
        });
    
        return response()->json($resultats);
    }
    
 //fonction pour recuperer un enregistrement specifique
    public function Show($code_pin)
    {
        $enregistrement = Enregistrement::with(['conducteur.categorie', 'engin'])
            ->where('code_pin', $code_pin)
            ->first();

        if (!$enregistrement) {
            return response()->json([
                'message' => 'Aucun enregistrement trouvé pour ce code PIN.',
            ], 404);
        }

        return response()->json([
            'enregistrement' => $enregistrement,
            'conducteur' => $enregistrement->conducteur,
            'plaque_engin' => optional($enregistrement->engin)->plaque_immatricu,
            'engin' => $enregistrement->engin,
            'categorie' => $enregistrement->conducteur->categorie ?? null,
        ]);
    }

    //Pour afficher un enregistrement specifique 

    public function afficher($id)
    {
        $enregistrement = Enregistrement::with(['conducteur.categorie', 'engin', 'user'])
            ->find($id);

        if (!$enregistrement) {
            return response()->json(['message' => 'Enregistrement non trouvé'], 404);
        }

        return response()->json([
            'message' => 'Détails de l\'enregistrement',
            'enregistrement' => $enregistrement,
        ]);
    }


    //Statistiques 

    public function stati (){
        $aujourdhui = Carbon::today();

        $total = Enregistrement::whereDate('created_at', $aujourdhui)->count();

        $active = Enregistrement::whereDate('created_at', $aujourdhui)
        ->whereNull('date_sortie')
        ->count();

        $sorti = Enregistrement::whereDate('created_at', $aujourdhui)
        ->whereNotNull('date_sortie')
        ->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'Sorti' => $sorti,
        ]);
    }
};

    


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
use GuzzleHttp\Client;
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

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'tel' => 'required|string|regex:/^\d{8,15}$/',
            'plaque_immatricu' => 'required|string',
            'type_engin' => 'required|string',
            'categorie_nom' => 'required|string',
            'date_sortie' => 'nullable|date',
        ]);

        $categorie = Categorie::firstOrCreate(['nom' => $validated['categorie_nom']]);

        $conducteur = Conducteur::firstOrCreate(
            ['tel' => $validated['tel']],
            [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'categorie_id' => $categorie->id,
            ]
        );

         $engin = Engin::firstOrNew([ 
            'plaque_immatricu' => $validated['plaque_immatricu'],
            'type_engin'=>  $validated['type_engin']
         ]);
        $engin->save(); 
        $codePin = Str::upper(Str::random(5));

        $enregistrement = new Enregistrement();
        $enregistrement->conducteur_id = $conducteur->id;
        $enregistrement->engin_id = $engin->id;
        $enregistrement->user_id = $user->id;
        $enregistrement->code_pin = $codePin;
        $enregistrement->date_sortie = $validated['date_sortie'] ?? null;
        $enregistrement->save();

        try {
            $client = new Client();

            $numero = $validated['tel'];
            if (Str::startsWith($numero, '6')) {
                $numero = '224' . $numero; 
            }

            $message ="  Lanala vie : 
                    Bonjour {$conducteur->prenom},\n"
                 . "Votre enregistrement a été effectué avec succès.\n"
                 . "Engin : {$engin->plaque_immatricu}\n"
                 . "Code PIN : {$codePin}\n"
                 . "Veuillez conserver ce code : il vous sera demandé pour récupérer votre engin.";


            $response = Http::get('https://apisms.dbafrica.net/apisms/api/sms/send/status?sender=LANALA VIE&source=testSoutenance&msisdn=' . $conducteur->tel . '&message=' . urlencode($message));

            $smsResponse = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $smsResponse = ['error' => 'Échec de l\'envoi du SMS', 'exception' => $e->getMessage()];
        }

        return response()->json([
            'message' => 'Enregistrement réussi',
            'code_pin' => $codePin,
            'enregistrement' => $enregistrement,
            'conducteur' => $conducteur,
            'engin' => $engin,
            'categorie' => $categorie,
            'user' => $user,
            'sms' => $smsResponse,
        ]);
    }


   public function modifier(Request $request, $id)
{
    $enregistrement = Enregistrement::findOrFail($id);

    $validated = $request->validate([
        'nom' => 'sometimes|string',
        'prenom' => 'sometimes|string',
        'tel' => 'sometimes|string',
        'plaque_immatricu' => 'sometimes|string',
        'type_engin' => 'sometimes|string',
        'categorie_nom' => 'sometimes|string',
    ]);

    // ✅ Création ou récupération de la catégorie si présente dans la requête
    if ($request->has('categorie_nom')) {
        $categorie = Categorie::firstOrCreate(['nom' => $validated['categorie_nom']]);
    }

    // ✅ Mise à jour du conducteur
    if ($enregistrement->conducteur) {
        $conducteur = $enregistrement->conducteur;

        if ($request->has('nom')) {
            $conducteur->nom = $request->nom;
        }
        if ($request->has('prenom')) {
            $conducteur->prenom = $request->prenom;
        }
        if ($request->has('tel')) {
            $conducteur->tel = $request->tel;
        }
        if (isset($categorie)) {
            $conducteur->categorie_id = $categorie->id;
        }

        $conducteur->save();
    }

    // ✅ Mise à jour de l'engin
    if ($enregistrement->engin) {
        $engin = $enregistrement->engin;

        if ($request->has('plaque_immatricu')) {
            $engin->plaque_immatricu = $request->plaque_immatricu;
        }
        if ($request->has('type_engin')) {
            $engin->type_engin = $request->type_engin;
        }

        $engin->save();
    }

    return response()->json([
        'message' => 'Mise à jour effectuée avec succès',
        'enregistrement' => $enregistrement->load('conducteur.categorie', 'engin'),
    ]);
}


// Plage de date
  /* public function indexParDate(Request $request)
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
                'id' => $enregistrement->id,
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d'),
                'nom_conducteur' => $enregistrement->conducteur->nom,
                'prenom_conducteur' => $enregistrement->conducteur->prenom,
                'plaque_engin' => optional($enregistrement->engin)->plaque_immatricu,
                'typeengin' => optional($enregistrement->engin)->type_engin,
                'date_sortie' => $enregistrement->date_sortie,
                'code_pin' => $enregistrement->code_pin,
                'tel' => $enregistrement->conducteur->telephone,
            ];
        });

        //return response()->json($resultats);

        dd($resultats);
    }*/

        public function indexParDate(Request $request)
        {
            $dateDebut = $request->input('date_debut');
            $dateFin = $request->input('date_fin');

            // Si une des deux dates est manquante, retourner un message
            if (!$dateDebut || !$dateFin) {
                return response()->json([
                    'message' => 'Les deux dates (date_debut et date_fin) sont obligatoires.'
                ], 400);
            }

            $enregistrements = Enregistrement::with(['conducteur', 'engin'])
                ->whereBetween('created_at', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($enregistrements->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun enregistrement trouvé'
                ]); 
            }

            $resultats = $enregistrements->map(function ($enregistrement) {
                return [
                    'id' => $enregistrement->id,
                    'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                    'nom_conducteur' => $enregistrement->conducteur->nom ?? '',
                    'prenom_conducteur' => $enregistrement->conducteur->prenom ?? '',
                    'plaque_engin' => optional($enregistrement->engin)->plaque_immatricu ?? '',
                    'typeengin' => optional($enregistrement->engin)->type_engin ?? '',
                    'date_sortie' => $enregistrement->date_sortie,
                    'code_pin' => $enregistrement->code_pin,
                    'tel' => $enregistrement->conducteur->telephone ?? '',
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

    


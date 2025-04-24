<?php

namespace App\Http\Controllers\Api;
use App\Models\Enregistrement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conducteur;
use App\Models\Engin;
use App\Models\Categorie;
use Illuminate\Support\Str;

class EnregistrementController extends Controller
{
    
    //FONCTION POUR AJOUTER UN NOUVEL ENREGISTREMENT

    public function ajouter(Request $request)
    {
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
        $enregistrement->code_pin = Str::random(10);

        if (isset($validated['date_sortie'])) {
            $enregistrement->date_sortie = $validated['date_sortie'];
        }
        $enregistrement->save();
        
        return response()->json([
            'message' => 'Enregistrement reussi',
            'enregistrement' => $enregistrement, 
            'conducteur' =>  $conducteur, 
            'engin' =>  $engin, 
            'categorie' =>  $categorie
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
    //FONCTION POUR RECUPERER LES ENREGISTREMENT D'UN CONDUCTEUR
    
    public function getEnregistrementsParTel($tel)
    {
        $conducteur = Conducteur::where('tel', $tel)->first();
    

        if (!$conducteur) {
            return response()->json([
                'message' => 'Conducteur introuvable'
            ], 404);
        }

        $enregistrements = Enregistrement::with('engin')
            ->where('conducteur_id', $conducteur->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $resultats = $enregistrements->map(function ($enregistrement) {
            return [
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                'date_sortie' => $enregistrement->date_sortie,
                'plaque_immatriculee' => optional($enregistrement->engin)->plaque_immatricu,
                'type_engin' => optional($enregistrement->engin)->type_engin
            ];
        });

        return response()->json([
            'conducteur' => [
                'nom' => $conducteur->nom,
                'prenom' => $conducteur->prenom,
                'tel' => $conducteur->tel
            ],
            
            'enregistrements' => $resultats
        ]);
    }

    //FONCTION POUR ENREGISTRER UNE SORTIE

    public function update($code)
    {
         $enregistrement = Enregistrement::with(['conducteur', 'engin'])
            ->where('code_pin', $code)
            ->first();
    
        if (!$enregistrement) {
            return response()->json([
                'message' => 'Code invalide ou inexistant.'
            ], 404);
        }
    
        if ($enregistrement->date_sortie !== null) {
            return response()->json([
                'message' => 'La sortie a déjà été enregistrée. Modification non autorisée.'
            ], 403); 
        }
    
        $enregistrement->date_sortie = now();
        $enregistrement->save();
    
        return response()->json([
            'message' => 'Mise à jour réussie.',
            'data' => $enregistrement
        ]);
    }
    

    //LISTE lES ENREGISTREMENTS
    public function index()
    {
        
        $enregistrements = Enregistrement::with(['conducteur', 'engin'])
        ->orderBy('created_at', 'desc') 
        ->get();

        if(!$enregistrements ){
            return response()->json([
                'Aucun conducteur trouvé'
            ], 404);
        }
            

        $resultats = $enregistrements->map(function ($enregistrement) {
            return [
                'date_enregistrement' => $enregistrement->created_at->format('Y-m-d H:i'),
                'nom_conducteur' => $enregistrement->conducteur->nom,
                'prenom_conducteur' => $enregistrement->conducteur->prenom,
                'plaque_engin' => optional($enregistrement->engin)->plaque_immatricu,
                'typeengin' => optional($enregistrement->engin)->type_engin,
                'date_sortie' =>$enregistrement->date_sortie
            ];
        });

        return response()->json($resultats);  

    }
    
};

    


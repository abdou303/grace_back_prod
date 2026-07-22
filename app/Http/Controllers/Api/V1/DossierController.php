<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Traits\HasServerSideRowModel;
use App\Exports\DossiersTribunalExport;
use App\Exports\AllReceivedDossiersTrExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAntecedentDossierRequest;
use App\Http\Requests\StoreDossierRequest;
use App\Http\Requests\UpdateDossierGreffeRequest;
use App\Http\Requests\UpdateDossierRequest;
use App\Http\Resources\DossierResource;
use App\Jobs\UploadDossierPJsJob;
use App\Services\OpenBeeService;
use App\Models\Affaire;
use App\Models\Detenu;
use App\Models\Dossier;
use App\Models\Pj;
use App\Models\Prison;
use App\Models\Requette;
use App\Models\TypeRequette;
use App\Models\StatutRequette;
use App\Models\TypePj;
use App\Services\OperationService;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DossiersTrExport;
use Illuminate\Support\Facades\Cache;

class DossierController extends Controller
{



    use HasServerSideRowModel;

    public function dossiersTribunalServerSide(Request $request)
    {
        $query = $this->buildDossiersTribunalQuery($request->input('filters', []));

        return $this->serverSideRowsWithResource(
            $request,
            $query,
            DossierResource::class,
            ['numero', 'created_at', 'id'],
            ['id', 'desc'],
        );
    }

    public function exportDossiersTribunal(Request $request)
    {
        $filters  = $request->input('filters', []);
        $dossiers = $this->buildDossiersTribunalQuery($filters)
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(
            new DossiersTribunalExport($dossiers),
            'liste-dossiers-tribunal.xlsx',
        );
    }

    private function buildDossiersTribunalQuery(array $f): Builder
    {
        // ⚠️ tribunal_id vient du frontend (tribunal de l'utilisateur connecté),
        // contrainte de sécurité, pas un filtre optionnel du formulaire.
        $trId = $f['tribunal_id'] ?? null;

        $query = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])
            ->where('user_tribunal_id', $trId)
            ->where('categorie', 'CAT-1')
            ->where('originedossier', '!=', 'DAPG-ENCOURS')
            // Reprise des 2 conditions qui étaient en JS
            ->where(function ($q) {
                $q->whereNull('has_antecedent')->orWhere('has_antecedent', '!=', 'OUI');
            })
            ->where(function ($q) {
                $q->whereNull('tr_tribunal')->orWhere('tr_tribunal', '!=', 'OK');
            });

        // Filtre d'onglet (remplace le split client-side nonTraites/envoyeGreffe/traiteGreffe)
        if (!empty($f['etat_greffe'])) {
            $query->where('etat_greffe', $f['etat_greffe']);
        }

        if (!empty($f['numero'])) {
            $query->where('numero', 'like', '%' . $f['numero'] . '%');
        }

        if (!empty($f['numeromp'])) {
            $query->where('numeromp', 'like', '%' . $f['numeromp'] . '%');
        }

        if (!empty($f['typedossier_id'])) {
            $query->where('typedossier_id', $f['typedossier_id']);
        }

        if (!empty($f['naturedossier_id'])) {
            $query->where('naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }

        if (!empty($f['cin'])) {
            $query->whereHas('detenu', function ($q) use ($f) {
                $q->where('cin', 'like', '%' . $f['cin'] . '%');
            });
        }

        if (!empty($f['nom'])) {
            $query->whereHas('detenu', function ($q) use ($f) {
                $q->where('nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('prenom', 'like', '%' . $f['nom'] . '%');
            });
        }

        if (!empty($f['dateDebut'])) {
            $query->whereDate('created_at', '>=', $f['dateDebut']);
        }

        if (!empty($f['dateFin'])) {
            $query->whereDate('created_at', '<=', $f['dateFin']);
        }

        return $query;
    }
    /**
     * Display a listing of the resource.
     */


    public function dossierApresReponseRequette(Request $request, Requette $requette, Dossier $dossier)
    {
        $request->validate([
            'statutRequette' => 'required|exists:statut_requettes,code',
            'numeromp' => 'required',
            'copie_decision' => 'nullable|file|mimes:pdf|max:153600',
            'copie_cin' => 'nullable|file|mimes:pdf|max:153600',
            'copie_mp' => 'nullable|file|mimes:pdf|max:153600',
            'copie_non_recours' => 'nullable|file|mimes:pdf|max:153600',
            'copie_social' => 'nullable|file|mimes:pdf|max:153600',
        ]);

        $dossier->numeromp = $request->numeromp;
        $dossier->copie_decision = $request->copie_decision;
        $dossier->copie_cin = $request->copie_cin;
        $dossier->copie_mp = $request->copie_mp;
        $dossier->copie_non_recours = $request->copie_non_recours;
        $dossier->copie_social = $request->copie_social;

        $dossier->save();


        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        $requette->statutrequettes()->attach([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
    }


    public function dossierByTr($tr_id)
    {
        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->where('user_tribunal_id', $tr_id)->where('categorie', 'CAT-1')->where('originedossier', '!=', 'DAPG-ENCOURS')->orderBy('id', 'desc')->get();

        return new DossierResource($dossiers);
    }

    public function dossierAntecedantByTr($tr_id)
    {
        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->where('user_tribunal_id', $tr_id)->where('categorie', 'CAT-1')->orderBy('id', 'desc')->get();

        return new DossierResource($dossiers);
    }
    public function index()
    {

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'prison',
            'objetdemande',
        ])->get();

        return new DossierResource($dossiers);
    }

    /*public function dossiersTr()
    {
        $dossiers = Dossier::with([
            'detenu',
            'affaires',
            'typedossier',
            'naturedossier',
            'objetdemande',
            'sourcedemande',
            'LibelleTribunalUtilisateur',
        ])->whereNotNull('user_tribunal_id')
            ->orderBy('id', 'desc')
            ->get();

        return new DossierResource($dossiers);
    }*/






    public function dossiersDapg()
    {

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'prison',
            'objetdemande',
        ])->whereNull('user_tribunal_id')->orderBy('id', 'desc')->get();

        return new DossierResource($dossiers);
    }

    /**
     * Store a newly created resource in storage.
     */



    public function store(StoreDossierRequest $request, OpenBeeService $openBee)
    {
        try {
            return DB::transaction(function () use ($request, $openBee) {

                // 1. Création du Détenu
                $detenu = new Detenu();
                $detenu->fill([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'datenaissance' => $request->datenaissance,
                    'nompere' => $request->nompere,
                    'nommere' => $request->nommere,
                    'cin' => $request->cin,
                    'genre' => $request->genre,
                    'nationalite_id' => $request->nationalite,
                    'adresse' => $request->adresse ?? null,
                ]);
                $detenu->save();

                // 2. Génération du Numéro de Dossier (Logique Robuste)
                $currentYear = now()->format('Y');
                $prefix = 'D-' . $currentYear;

                // On cherche le dernier numéro commençant par "D-2026"
                $lastRecord = Dossier::where('numero', 'like', $prefix . '%')
                    ->orderBy('numero', 'desc')
                    ->lockForUpdate() // Verrouille la ligne pour éviter les doublons en cas d'accès simultanés
                    ->first();

                if ($lastRecord) {
                    // On extrait la partie numérique après "D-2026" (index 6)
                    $lastSequence = substr($lastRecord->numero, 6);
                    $lastNumber = intval($lastSequence);
                } else {
                    $lastNumber = 0;
                }

                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
                $numero_dossier = $prefix . $newNumber;

                // 3. Création du Dossier
                $dossier = new Dossier();
                $dossier->numero = $numero_dossier;
                $dossier->typedossier_id = $request->typedossier;
                $dossier->naturedossiers_id = $request->naturedossier;
                $dossier->sourcedemande_id = $request->sourcedemande;
                $dossier->autre_source = $request->autre_source;
                $dossier->etat = 'NT';
                $dossier->originedossier = 'D';
                $dossier->objetdemande_id = is_numeric($request->objetdemande) ? (int) $request->objetdemande : null;
                $dossier->user_id = $request->user_id;
                $dossier->user_tribunal_id = $request->tribunal_user_id;
                $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
                $dossier->numeromp = $request->numeromp;
                $dossier->detenu_id = $detenu->id;
                $dossier->prison_id = is_numeric($request->prison) ? (int) $request->prison : null;
                $dossier->numero_detention = $request->numerolocal;
                $dossier->etat_greffe = "KO";
                $dossier->etat_parquet = "KO";
                $dossier->date_envoi_greffe = now();

                $dossier->save();

                // 4. Gestion des Pièces Jointes (PJ)
                $fileMappings = [
                    'copie_decision' => 5,
                    'copie_cin' => 4,
                    'copie_mp' => 3,
                    'copie_non_recours' => 2,
                    'copie_social' => 1,
                ];

                $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();

                foreach ($fileMappings as $fieldName => $typepjId) {
                    if ($request->hasFile($fieldName)) {
                        $file = $request->file($fieldName);
                        $filename = $numero_dossier . '_' . $fieldName . '.' . $file->getClientOriginalExtension();
                        $path = "OPENBEE/" . $filename;

                        $openbeeUrl = null;
                        try {
                            $result = $openBee->upload($file, $filename, [
                                'title'       => $filename,
                                'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج',
                                'path'        => config('openbee.path'),
                            ]);
                            $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
                        } catch (\Exception $e) {
                            Log::error("Erreur OpenBee ($fieldName): " . $e->getMessage());
                        }

                        $pj = new Pj();
                        $pj->contenu = $path;
                        $pj->openbee_url = $openbeeUrl;
                        $pj->dossier_id = $dossier->id;
                        $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->typepj_id = $typepjId;
                        $pj->save();
                    }
                }

                // 5. Gestion des Affaires
                if ($request->has('affaires')) {
                    foreach ($request->affaires as $affaireData) {
                        $affaire = new Affaire();
                        $affaire->numero = $affaireData['numero'];
                        $affaire->code = $affaireData['code'];
                        $affaire->annee = $affaireData['annee'];
                        $affaire->numeroaffaire = $affaireData['numero'] . '/' . $affaireData['code'] . '/' . $affaireData['annee'];
                        $affaire->tribunal_id = $affaireData['tribunal'];
                        $affaire->datejujement = $affaireData['datejujement'];
                        $affaire->conenujugement = $affaireData['conenujugement'];
                        $affaire->save();

                        $dossier->affaires()->attach($affaire->id);
                    }
                }

                return response()->json([
                    'message' => 'تم تسجيل الطلب بنجاح',
                    'data' => $dossier,
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error("Erreur globale store dossier: " . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /*   public function store(StoreDossierRequest $request, OpenBeeService $openBee)
    {





        $detenu = new Detenu();
        $detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;

        $detenu->save();

        $dossier = new Dossier();
        $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        //$lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0;

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = 'D-' . $currentYear . $newNumber;

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->autre_source = $request->autre_source;


        $dossier->numero = $numero_dossier;
        $dossier->etat = 'NT';
        $dossier->originedossier = 'D';

        //$dossier->objetdemande_id = $request->objetdemande ?? null;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;


        $dossier->detenu_id = $detenu->id;
        //$dossier->prison_id =  $request->prison;
        $dossier->prison_id = isset($request->prison) && is_numeric($request->prison)  ? (int) $request->prison : null;
        $dossier->numero_detention =  $request->numerolocal;
        $dossier->etat_greffe =  "NT";
        $dossier->etat_parquet =  "KO";
        $dossier->date_envoi_greffe =  now()->format('Y-m-d H:i:s.v');







        $dossier->save();
        $dossier_id = $dossier->id;



        // Define file fields and corresponding typepj_id values
        $fileMappings = [
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];
        // Fetch all TypePj records and create an associative array of id => label
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();
        // Handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {




            if ($request->hasFile($fieldName)) {
                $insertedObservation = "";
                $file = $request->file($fieldName);
                $filename = $numero_dossier . $fieldName . '.' . $file->getClientOriginalExtension();
                $path = "OPENBEE/" . $filename;


                try {

                    $result = $openBee->upload($file, $filename, [
                        'title'       => $filename,
                        'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                        'path'        => config('openbee.path'),
                    ]);
                    $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
                } catch (\Exception $e) {
                    \Log::error("Erreur d'upload Open Bee (sans affaire): " . $e->getMessage());
                    $openbeeUrl = null;
                }
                $pj = new Pj();
                //$pj->contenu =  $file->storeAs('public/uploads', $filename);
                $pj->contenu = $path;
                $pj->openbee_url = $openbeeUrl;
                $pj->dossier_id = $dossier_id;
                $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                $pj->typepj_id = $typepjId;
                $pj->save();
            }
        }








        if ($request->has('affaires')) {
            $affaires = $request->affaires;
            foreach ($affaires as $affaireData) {
                $affaire = new Affaire();


                //$affaire->numeromp = $affaireData['numeromp'];
                $affaire->numero = $affaireData['numero'];
                $affaire->code = $affaireData['code'];
                $affaire->annee = $affaireData['annee'];
                $affaire->numeroaffaire = $affaireData['numero'] . '/' . $affaireData['code'] . '/' . $affaireData['annee'];
                $affaire->tribunal_id = $affaireData['tribunal'];
                $affaire->datejujement = $affaireData['datejujement'];
                $affaire->conenujugement = $affaireData['conenujugement'];
                $affaire->save();
                $dossier->affaires()->attach($affaire->id);

                // Handle file uploads for this affaire
                $fileMappings = [
                    'copie_decision' => 5,
                    'copie_cin' => 4,
                    'copie_mp' => 3,
                    'copie_non_recours' => 2,
                    'copie_social' => 1,
                ];
                // Fetch all TypePj records and create an associative array of id => label
                $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();
            }
        }
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }
*/

    public function forwardParquetDossier(Request $request, Dossier $dossier)
    {
        $data = $request->validate([


            'parquet_user_id' => 'required|int',

        ]);


        $dossier->date_envoi_parquet = now()->format('Y-m-d H:i:s.v');
        $dossier->etat_parquet = "NT";
        $dossier->user_parquet = $request->parquet_user_id;
        $dossier->save();

        return new DossierResource($dossier);
    }
    public function storeAntecedent(StoreAntecedentDossierRequest $request)
    {


        $dossier = new Dossier();
        $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        //$lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0;

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = 'D-' . $currentYear . $newNumber;

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->numero = $numero_dossier;
        $dossier->etat = 'NT';
        $dossier->has_antecedent = $request->has_antecedent;
        $dossier->antecedant_id = $request->antecedant_id;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;
        $dossier->detenu_id = $request->detenu_id;

        $dossier->save();



        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }


    /******************************************************** */


    public function terminerDossierTr(UpdateDossierRequest $request, $dossier_id, OpenBeeService $openBee)
    {
        Log::debug('terminerDossierTr: Requête reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        // --- VERROU + GARDE ---
        $lockKey = "terminer_dossier_tr_lock_{$dossier_id}";
        $lock = Cache::lock($lockKey, 30);
        if (!$lock->get()) {
            return response()->json(['message' => 'Traitement déjà en cours.'], 409);
        }
        if ($dossier->etat === 'OK') {
            $lock->release();
            return response()->json(['message' => 'Dossier déjà traité.', 'data' => $dossier], 200);
        }
        try {
            $detenu = $dossier->detenu;

            if (!$detenu) {
                return response()->json(['message' => 'Detenu not found'], 404);
            }

            // 1. Mise à jour et sauvegarde IMMÉDIATE du Détenu
            $detenu->nom = $request->nom;
            $detenu->prenom = $request->prenom;
            $detenu->datenaissance = $request->datenaissance;
            $detenu->nompere = $request->nompere;
            $detenu->nommere = $request->nommere;
            $detenu->cin = $request->cin;
            $detenu->genre = $request->genre;
            $detenu->nationalite_id = $request->nationalite;
            $detenu->adresse = $request->adresse ?? null;
            $detenu->save();

            // 2. Mise à jour et sauvegarde IMMÉDIATE du Dossier
            $dossier->typedossier_id = $request->typedossier;
            $dossier->naturedossiers_id = $request->naturedossier;
            $dossier->sourcedemande_id = $request->sourcedemande;

            //$dossier->etat = 'OK'; // Mise à jour immédiate de l'état
            $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande) ? (int) $request->objetdemande : null;
            $dossier->user_id = $request->user_id;
            $dossier->user_tribunal_id = $request->tribunal_user_id;
            $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
            $dossier->numeromp = $request->numeromp;
            $dossier->prison_id = isset($request->prison) && is_numeric($request->prison) ? (int) $request->prison : null;
            $dossier->numero_detention = $request->numerolocal;
            $dossier->save();

            // 2-bis. Mise à jour des AFFAIRES (non recours / cassation)
            if ($request->has('has_non_recours')) {
                foreach ($request->has_non_recours as $affaireId => $hasNonRecours) {

                    $affaire = $dossier->affaires()
                        ->where('affaires.id', $affaireId)
                        ->first();

                    if (!$affaire) {
                        continue;
                    }

                    $hasNonRecoursBool = filter_var($hasNonRecours, FILTER_VALIDATE_BOOLEAN);

                    $affaire->has_non_recours = $hasNonRecoursBool;

                    if (!$hasNonRecoursBool) {
                        $affaire->numero_cassation = $request->numero_cassation[$affaireId] ?? null;
                        $affaire->numero_envoi_cassation = $request->numero_envoi_cassation[$affaireId] ?? null;
                        $affaire->date_envoi_cassation = $request->date_envoi_cassation[$affaireId] ?? null;
                    } else {
                        $affaire->numero_cassation = null;
                        $affaire->numero_envoi_cassation = null;
                        $affaire->date_envoi_cassation = null;
                    }

                    $affaire->save();
                }
            }



            // 3. Préparation et Stockage TEMPORAIRE des fichiers
            $filesToProcess = [];
            $fileMappings = [
                'copie_dgapr' => 8,
                'copie_demande' => 7,
                'copie_decision' => 5,
                'copie_cin' => 4,
                'copie_mp' => 3,
                'copie_non_recours' => 2,
                'copie_social' => 1,
            ];

            foreach ($fileMappings as $fieldName => $typepjId) {
                if ($request->hasFile($fieldName)) {
                    $files = $request->file($fieldName);

                    // Gérer les fichiers multiples (si affaireId est la clé) ou unique
                    $filesArray = is_array($files) ? $files : [null => $files];

                    foreach ($filesArray as $affaireIdKey => $file) {
                        if ($file) {
                            // Stocker le fichier dans un emplacement temporaire de Laravel
                            $path = $file->store('temp/openbee_uploads');
                            $filesToProcess[] = [
                                'path' => $path, // Chemin d'accès temporaire
                                'typepjId' => $typepjId,
                                'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                                'fieldName' => $fieldName,
                                'originalName' => $file->getClientOriginalName(),
                            ];
                        }
                    }
                }
            }

            /*************GENERIQUE JOB 30/03/2026******************* */
            $postActions = [[
                'model' => Dossier::class,
                'id'    => $dossier->id,
                'data'  => ['etat' => 'OK', 'date_etat_ok' => now()->format('Y-m-d H:i:s.v'), 'tr_tribunal' => 'OK', 'date_tr_tribunal' => now()->format('Y-m-d H:i:s.v')]
            ]];

            // 4. Dispatch du Job pour le traitement en arrière-plan
            if (!empty($filesToProcess)) {
                Log::info("## DEBUG: Envoi au Job pour Dossier ID: {$dossier->id}", [
                    'files_count' => count($filesToProcess),
                    'post_actions' => $postActions
                ]);
                // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
                UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess, $postActions)->onQueue('openbee_uploads');
            } else {
                $dossier->etat = 'OK';
                $dossier->date_etat_ok = now()->format('Y-m-d H:i:s.v');
                $dossier->tr_tribunal = 'OK';
                $dossier->date_tr_tribunal = now()->format('Y-m-d H:i:s.v');

                $dossier->save();
            }

            // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
            return response()->json([
                'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
                'data' => $dossier,
            ], 201);
        } finally {
            $lock->release();
        }
    }


    public function terminerGreffeDossierTr(UpdateDossierGreffeRequest $request, $dossier_id, OpenBeeService $openBee)
    {
        Log::debug('terminerGreffeDossierTr-Requête reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        $lockKey = "terminer_greffe_dossier_lock_{$dossier_id}";
        $lock = Cache::lock($lockKey, 30);
        if (!$lock->get()) {
            return response()->json(['message' => 'Traitement déjà en cours.'], 409);
        }
        if ($dossier->etat_greffe === 'TR') {
            $lock->release();
            return response()->json(['message' => 'Dossier déjà traité.', 'data' => $dossier], 200);
        }
        try {
            $detenu = $dossier->detenu;

            if (!$detenu) {
                return response()->json(['message' => 'Detenu not found'], 404);
            }


            // 2. Mise à jour et sauvegarde IMMÉDIATE du Dossier

            /*$dossier->etat_greffe = 'TR'; // Mise à jour immédiate de l'état
        $dossier->user_id = $request->user_id;
        $dossier->date_etat_greffe = now()->format('Y-m-d H:i:s.v');

        $dossier->save();*/

            // 3. Préparation et Stockage TEMPORAIRE des fichiers
            $filesToProcess = [];
            $fileMappings = [
                'copie_decision' => 5,
                'copie_cin' => 4,
                'copie_mp' => 3,
                'copie_non_recours' => 2,
                'copie_social' => 1,
            ];

            foreach ($fileMappings as $fieldName => $typepjId) {
                if ($request->hasFile($fieldName)) {
                    $files = $request->file($fieldName);

                    // Gérer les fichiers multiples (si affaireId est la clé) ou unique
                    $filesArray = is_array($files) ? $files : [null => $files];

                    foreach ($filesArray as $affaireIdKey => $file) {
                        if ($file) {
                            // Stocker le fichier dans un emplacement temporaire de Laravel
                            $path = $file->store('temp/openbee_uploads');
                            $filesToProcess[] = [
                                'path' => $path, // Chemin d'accès temporaire
                                'typepjId' => $typepjId,
                                'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                                'fieldName' => $fieldName,
                                'originalName' => $file->getClientOriginalName(),
                            ];
                        }
                    }
                }
            }
            // 2-bis. Mise à jour des AFFAIRES (non recours / cassation)
            if ($request->has('has_non_recours')) {
                foreach ($request->has_non_recours as $affaireId => $hasNonRecours) {

                    $affaire = $dossier->affaires()
                        ->where('affaires.id', $affaireId)
                        ->first();

                    if (!$affaire) {
                        continue;
                    }

                    $hasNonRecoursBool = filter_var($hasNonRecours, FILTER_VALIDATE_BOOLEAN);

                    $affaire->has_non_recours = $hasNonRecoursBool;

                    if (!$hasNonRecoursBool) {
                        $affaire->numero_cassation = $request->numero_cassation[$affaireId] ?? null;
                        $affaire->numero_envoi_cassation = $request->numero_envoi_cassation[$affaireId] ?? null;
                        $affaire->date_envoi_cassation = $request->date_envoi_cassation[$affaireId] ?? null;
                    } else {
                        $affaire->numero_cassation = null;
                        $affaire->numero_envoi_cassation = null;
                        $affaire->date_envoi_cassation = null;
                    }

                    $affaire->save();
                }
            }
            /*************GENERIQUE JOB 30/03/2026******************* */
            $postActions = [[
                'model' => Dossier::class,
                'id'    => $dossier->id,
                'data'  => [
                    'etat_greffe'      => 'TR',
                    'user_id'          => $request->user_id,
                    'date_etat_greffe' => now()->format('Y-m-d H:i:s.v')
                ]
            ]];
            // 4. Dispatch du Job pour le traitement en arrière-plan
            if (!empty($filesToProcess)) {
                // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
                UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess, $postActions)->onQueue('openbee_uploads');
            } else {
                $dossier->etat_greffe = 'TR'; // Mise à jour immédiate de l'état
                $dossier->user_id = $request->user_id;
                $dossier->date_etat_greffe = now()->format('Y-m-d H:i:s.v');

                $dossier->save();
            }

            // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
            return response()->json([
                'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
                'data' => $dossier,
            ], 201);
        } finally {
            $lock->release();
        }
    }



    public function terminerParquetDossierTr(Request $request, $dossier_id, OpenBeeService $openBee)

    {

        $request->validate([

            'tribunal_user_libelle' => 'nullable',
            'user_id' => 'required|exists:users,id',
            'tribunal_user_id' => 'required',
            'avis' => 'nullable',
            'observations_parquet' => 'nullable',


        ]);
        Log::debug('Requête(terminerParquetDossierTr) reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        $lockKey = "terminer_parquet_dossier_lock_{$dossier_id}";
        $lock = Cache::lock($lockKey, 30);
        if (!$lock->get()) {
            return response()->json(['message' => 'Traitement déjà en cours.'], 409);
        }
        if ($dossier->etat_parquet === 'TR') {
            $lock->release();
            return response()->json(['message' => 'Dossier déjà traité.', 'data' => $dossier], 200);
        }
        try {
            $detenu = $dossier->detenu;

            if (!$detenu) {
                return response()->json(['message' => 'Detenu not found'], 404);
            }

            // 1. Mise à jour et sauvegarde IMMÉDIATE du Détenu
            /*$detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;
        $detenu->save();*/

            // 2. Mise à jour et sauvegarde IMMÉDIATE du Dossier

            /* $dossier->etat_parquet = 'TR'; // Mise à jour immédiate de l'état
        $dossier->user_id = $request->user_id;
        $dossier->date_etat_parquet = now()->format('Y-m-d H:i:s.v');*/
            $dossier->has_file_mp = $request->has_file_mp;
            //if ($request->has_file_mp == '0') {}
            $dossier->avis_id = $request->avis;
            $dossier->observations_parquet = $request->observations_parquet;
            /*$dossier->etat_parquet      = 'TR';
            $dossier->user_parquet = $request->user_id;
            $dossier->date_etat_parquet = now()->format('Y-m-d H:i:s.v');*/
            $dossier->save();





            // 3. Préparation et Stockage TEMPORAIRE des fichiers
            $filesToProcess = [];
            $fileMappings = [

                'copie_mp' => 3,

            ];

            foreach ($fileMappings as $fieldName => $typepjId) {
                if ($request->hasFile($fieldName)) {
                    $files = $request->file($fieldName);

                    // Gérer les fichiers multiples (si affaireId est la clé) ou unique
                    $filesArray = is_array($files) ? $files : [null => $files];

                    foreach ($filesArray as $affaireIdKey => $file) {
                        if ($file) {
                            // Stocker le fichier dans un emplacement temporaire de Laravel
                            $path = $file->store('temp/openbee_uploads');
                            $filesToProcess[] = [
                                'path' => $path, // Chemin d'accès temporaire
                                'typepjId' => $typepjId,
                                'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                                'fieldName' => $fieldName,
                                'originalName' => $file->getClientOriginalName(),
                            ];
                        }
                    }
                }
            }
            // 2-bis. Mise à jour des AFFAIRES (non recours / cassation)
            if ($request->has('has_non_recours')) {
                foreach ($request->has_non_recours as $affaireId => $hasNonRecours) {

                    $affaire = $dossier->affaires()
                        ->where('affaires.id', $affaireId)
                        ->first();

                    if (!$affaire) {
                        continue;
                    }

                    $hasNonRecoursBool = filter_var($hasNonRecours, FILTER_VALIDATE_BOOLEAN);

                    $affaire->has_non_recours = $hasNonRecoursBool;

                    if (!$hasNonRecoursBool) {
                        $affaire->numero_cassation = $request->numero_cassation[$affaireId] ?? null;
                        $affaire->numero_envoi_cassation = $request->numero_envoi_cassation[$affaireId] ?? null;
                        $affaire->date_envoi_cassation = $request->date_envoi_cassation[$affaireId] ?? null;
                    } else {
                        $affaire->numero_cassation = null;
                        $affaire->numero_envoi_cassation = null;
                        $affaire->date_envoi_cassation = null;
                    }

                    $affaire->save();
                }
            }
            /*************GENERIQUE JOB 30/03/2026******************* */
            $postActions = [[
                'model' => Dossier::class,
                'id'    => $dossier->id,
                'data'  => [
                    'etat_parquet'      => 'TR',
                    'user_parquet'           => $request->user_id,
                    'date_etat_parquet' => now()->format('Y-m-d H:i:s.v')
                ]
            ]];
            // 4. Dispatch du Job pour le traitement en arrière-plan
            if (!empty($filesToProcess)) {
                // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
                UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess, $postActions)->onQueue('openbee_uploads');
            } else {
                $dossier->etat_parquet = 'TR'; // Mise à jour immédiate de l'état
                $dossier->user_id = $request->user_id;
                $dossier->date_etat_parquet = now()->format('Y-m-d H:i:s.v');
                $dossier->save();
            }

            // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
            return response()->json([
                'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
                'data' => $dossier,
            ], 201);
        } finally {
            $lock->release();
        }
    }
    /******************************************************** */
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dossier = Dossier::with([
            'detenu',
            'detenu.nationalite',
            'affaires',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'typedossier',
            'naturedossier',
            'objetdemande',
            'sourcedemande',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'requettes',
            'requettes.typerequette',
            'prison',
            'LibelleTribunalUtilisateur',
        ])->findOrFail($id);

        return new DossierResource($dossier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, OperationService $operationService)
    {
        Log::debug('Requête reçue UPDATE DOSSIER:', $request->all());

        $dossier = Dossier::with(['detenu', 'affaires', 'prison'])->findOrFail($id);
        $detenu = $dossier->detenu;
        // Validate incoming request (add rules as needed)
        $validated = $request->validate([
            //'operation_code' => 'nullable|string', // <-- Validation du code d'opération
            // Validation dynamique : requis si origin est 'D' + unique dans la table dossiers
            /*  'numero_dapg' => [
                $request->origindossier === 'D' ? 'required' : 'nullable',
                'string',
                'unique:dossiers,numero_dapg,' . $id // Ignore l'enregistrement actuel
            ],*/
            // Validation dynamique et unique par type de dossier
            /* 'numero_dapg' => [
                $request->origindossier === 'D' ? 'required' : 'nullable',
                'string',
                Rule::unique('dossiers', 'numero_dapg')
                    ->ignore($id) // Ignore le dossier actuel pour éviter les faux positifs lors de la modification
                    ->where(function ($query) use ($dossier) {
                        // Force l'unicité SEULEMENT au sein du même type de dossier
                        return $query->where('typedossier_id', $dossier->typedossier_id);
                    })
            ],*/
            'numero_dapg' => [
                $request->origindossier === 'D' ? 'required' : 'nullable',
                'string',
                Rule::unique('dossiers', 'numero_dapg')
                    ->ignore($id) // Ignore le dossier actuel pour éviter les faux positifs lors de la modification
                    ->where(function ($query) use ($dossier) {
                        // Force l'unicité au sein du même type de dossier ET du même originedossier
                        return $query->where('typedossier_id', $dossier->typedossier_id)
                            ->where('originedossier', $dossier->originedossier); // ou 'originedossier' selon le nom exact de ta colonne en BDD
                    })
            ],
            'numero_detention' => 'nullable|string',
            'detenu.nom' => 'nullable|string',
            'detenu.prenom' => 'nullable|string',
            'detenu.nompere' => 'nullable|string',
            'detenu.nommere' => 'nullable|string',
            'detenu.adresse' => 'nullable|string',
            'detenu.cin' => 'nullable|string',
            'detenu.genre' => 'nullable|string',
            'detenu.datenaissance' => 'nullable|string',
            'detenu.nationalite_id' => 'nullable|int',

            'tr_tribunal' => 'nullable|string',
            'tr_dapg' => 'nullable|string',
            'date_tr_tribunal' => 'nullable|string',
            'date_tr_dapg' => 'nullable|string',
            'user_id' => 'required|int',
            'prison' => 'nullable',
            'date_sortie' => 'nullable|string',






        ]);


        $detenu->nom = $validated['detenu.nom'] ?? $detenu->nom;
        $detenu->prenom = $validated['detenu.prenom'] ?? $detenu->prenom;
        $detenu->datenaissance = $validated['detenu.datenaissance'] ?? $detenu->datenaissance;
        $detenu->nompere = $validated['detenu.nompere'] ?? $detenu->nompere;
        $detenu->nommere = $validated['detenu.nommere'] ?? $detenu->nommere;
        $detenu->cin = $validated['detenu.cin'] ?? $detenu->cin;
        $detenu->genre = $validated['detenu.genre'] ?? $detenu->genre;
        $detenu->nationalite_id = $validated['detenu.nationalite_id'] ?? $detenu->nationalite_id;
        $detenu->adresse = $validated['detenu.adresse'] ?? $detenu->adresse;
        $detenu->save();
        // Update main dossier fields

        $dossier->numero_detention = $validated['numero_detention'] ?? $dossier->numero_detention;
        $dossier->numero_dapg = $validated['numero_dapg'] ?? $dossier->numero_dapg;

        $dossier->tr_tribunal = $validated['tr_tribunal'] ?? $dossier->tr_tribunal;
        $dossier->date_tr_tribunal = $validated['date_tr_tribunal'] ?? $dossier->date_tr_tribunal;
        $dossier->tr_dapg = $validated['tr_dapg'] ?? $dossier->tr_dapg;
        $dossier->date_tr_dapg = now()->format('Y-m-d H:i:s.v') ?? $dossier->date_tr_dapg;
        $dossier->date_sortie = $validated['date_sortie'] ?? $dossier->date_sortie;
        $dossier->user_id = $validated['user_id'] ?? $dossier->user_id;
        $dossier->save();


        // Update detenu fields
        if (isset($validated['detenu'])) {
            $dossier->detenu->update($validated['detenu']);
        }
        /************************************************************ */
        if ($dossier->originedossier == "R") {

            $requette = $dossier->requettes()
                ->whereHas('typerequette', fn($q) => $q->where('cat', 'CAT-1'))
                ->first();
            /*  \Log::alert("requette est :" . $requette->numero);
            \Log::alert("validated[\'tr_dapg\'] :" . $validated['tr_dapg']);*/


            $requette->tr_dapg = $validated['tr_dapg'] ?? $requette->tr_dapg;
            $requette->date_tr_dapg = now()->format('Y-m-d H:i:s.v') ?? $requette->date_tr_dapg;
            $requette->save();
        }
        /*if ($validated['operation_code'] !== null) {
            $userId = (int) Auth::id();
            $operationService->logOperation(
                $dossier->id,
                $validated['operation_code'] ?? null, // Mettez l'ID correspondant à "Ajout requête"
                $requette->id ?? null,
                $userId
            );
        }*/

        return response()->json([
            'message' => 'Dossier updated successfully',
            'data' => $dossier->load(['detenu', 'affaires', 'prison']),
        ]);
    }



    public function getPjs($dossierId)
    {
        $dossier = Dossier::with('pjs')->findOrFail($dossierId);
        return response()->json($dossier->pjs);
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy($id)
    {
        $user = Auth::user();

        // 1. Vérification des permissions
        if (!$user || $user->role_id != 3 || $user->groupe_id != 1) {
            return response()->json([
                'message' => 'غير مسموح لك بالقيام بهذا الإجراء'
            ], 403);
        }

        // 2. Récupération du dossier
        $dossier = Dossier::find($id);

        if (!$dossier) {
            return response()->json(['message' => 'الملف غير موجود'], 404);
        }

        try {
            return DB::transaction(function () use ($dossier) {


                // --- NOUVEAU : Nettoyage des historiques liés ---
                // 1. Supprimer l'historique lié aux requêtes de ce dossier
                if (method_exists($dossier, 'requettes')) {
                    $requetteIds = $dossier->requettes()->pluck('id');
                    DB::table('historiques_operations')->whereIn('requette_id', $requetteIds)->delete();

                    // Supprimer ensuite les requêtes elles-mêmes
                    $dossier->requettes()->delete();
                }

                // 2. Supprimer l'historique directement lié au dossier
                DB::table('historiques_operations')->where('dossier_id', $dossier->id)->delete();
                // ------------------------------------------------

                // 3. Nettoyage des relations qui bloquent la suppression

                // Supprimer les Pièces Jointes (PJs) liées
                // Si vous avez une colonne requette_id dans PJs qui pointe vers ce dossier/requete
                $dossier->pjs()->delete();

                // Détacher les affaires (si c'est une relation Many-to-Many via table pivot)
                if (method_exists($dossier, 'affaires')) {
                    $dossier->affaires()->detach();
                }

                // Supprimer les requêtes associées au dossier (si applicable)
                if (method_exists($dossier, 'requettes')) {
                    $dossier->requettes()->delete();
                }

                // 4. Suppression finale du dossier
                $dossier->delete();

                return response()->json(['message' => 'تم الحذف بنجاح'], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء الحذف',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getRegistreTribunal($id_tr)
    {
        $dossiers = Dossier::with([
            'detenu',
            'garants',
            'affaires',
            'requettes' => function ($query) {
                $query
                    ->whereNotNull('etat_greffe')
                    // Changement ici : (etat_greffe != 'KO' OR etat_parquet != 'KO')
                    ->where(function ($q) {
                        $q->where('etat_greffe', '!=', 'KO')
                            ->orWhere('etat_parquet', '!=', 'KO');
                    })
                    ->whereHas('typerequette', fn($q) => $q->where('cat', 'CAT-1'))
                    ->latest('id')
                    ->limit(1)
                    ->with('typerequette');
            }
        ])
            ->where('user_tribunal_id', $id_tr)
            ->where('categorie', 'CAT-1')

            ->where(function ($query) {
                // 1. DOSSIERS AYANT AU MOINS UNE REQUETTE CAT-1 VALIDE
                $query->whereHas('requettes', function ($q) {
                    $q->whereNotNull('etat_greffe')
                        ->where(function ($sub) {
                            $sub->where('etat_greffe', '!=', 'KO')
                                ->orWhere('etat_parquet', '!=', 'KO');
                        })
                        ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                })

                    // 2. OU DOSSIERS SANS AUCUNE REQUETTE CAT-1 VALIDE
                    ->orWhere(function ($q) {
                        $q->whereDoesntHave('requettes', function ($q2) {
                            $q2->whereNotNull('etat_greffe')
                                ->where(function ($sub) {
                                    $sub->where('etat_greffe', '!=', 'KO')
                                        ->orWhere('etat_parquet', '!=', 'KO');
                                })
                                ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                        })
                            ->whereNotNull('etat_greffe')
                            // Changement ici aussi pour la cohérence sur le Dossier lui-même si nécessaire
                            ->where(function ($sub) {
                                $sub->where('etat_greffe', '!=', 'KO')
                                    ->orWhere('etat_parquet', '!=', 'KO');
                            });
                    });
            })
            ->orderByDesc('id')
            ->get();

        return DossierResource::collection($dossiers);
    }

    public function getRegistreTribunalParquetUser($id_tr, $id_user_parquet)
    {
        $dossiers = Dossier::with([
            'detenu',
            'garants',
            'affaires',
            'requettes' => function ($query) use ($id_user_parquet) {
                // On filtre pour n'afficher que SES requêtes dans le dossier
                $query->where('user_parquet', $id_user_parquet)
                    ->whereNotNull('etat_greffe')
                    ->where(function ($q) {
                        $q->where('etat_greffe', '!=', 'KO')
                            ->orWhere('etat_parquet', '!=', 'KO');
                    })
                    ->whereHas('typerequette', fn($q) => $q->where('cat', 'CAT-1'))
                    ->latest('id')
                    ->limit(1)
                    ->with('typerequette');
            }
        ])
            ->where('user_tribunal_id', $id_tr)
            ->where('categorie', 'CAT-1')
            ->where(function ($mainQuery) use ($id_user_parquet) {
                // CONDITION : Le dossier appartient à l'user OU il contient une requête appartenant à l'user
                $mainQuery->where('user_parquet', $id_user_parquet)
                    ->orWhereHas('requettes', function ($q) use ($id_user_parquet) {
                        $q->where('user_parquet', $id_user_parquet);
                    });
            })
            ->where(function ($query) {
                // 1. DOSSIERS AYANT AU MOINS UNE REQUETTE CAT-1 VALIDE
                $query->whereHas('requettes', function ($q) {
                    $q->whereNotNull('etat_greffe')
                        ->where(function ($sub) {
                            $sub->where('etat_greffe', '!=', 'KO')
                                ->orWhere('etat_parquet', '!=', 'KO');
                        })
                        ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                })
                    // 2. OU DOSSIERS SANS AUCUNE REQUETTE CAT-1 VALIDE (mais remplissant les conditions d'état)
                    ->orWhere(function ($q) {
                        $q->whereDoesntHave('requettes', function ($q2) {
                            $q2->whereNotNull('etat_greffe')
                                ->where(function ($sub) {
                                    $sub->where('etat_greffe', '!=', 'KO')
                                        ->orWhere('etat_parquet', '!=', 'KO');
                                })
                                ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                        })
                            ->whereNotNull('etat_greffe')
                            ->where(function ($sub) {
                                $sub->where('etat_greffe', '!=', 'KO')
                                    ->orWhere('etat_parquet', '!=', 'KO');
                            });
                    });
            })
            ->orderByDesc('id')
            ->get();

        return DossierResource::collection($dossiers);
    }

    /*
    public function getRegistreTribunal($id_tr)
    {


        $dossiers = Dossier::with([
            'detenu',
            'garants',
            'affaires',

            'requettes' => function ($query) {
                $query
                    ->whereNotNull('etat_greffe')
                    ->where('etat_greffe', '!=', 'KO')
                    ->whereHas('typerequette', fn($q) => $q->where('cat', 'CAT-1'))
                    ->latest('id')
                    ->limit(1)
                    ->with('typerequette');
            }
        ])
            ->where('user_tribunal_id', $id_tr)
            ->where('categorie', 'CAT-1')

            //LOGIQUE CORRECTE 
            ->where(function ($query) {

                // DOSSIERS AYANT AU MOINS UNE REQUETTE CAT-1 VALIDE
                $query->whereHas('requettes', function ($q) {
                    $q->whereNotNull('etat_greffe')
                        ->where('etat_greffe', '!=', 'KO')
                        ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                })

                    // OU DOSSIERS SANS AUCUNE REQUETTE CAT-1 VALIDE
                    ->orWhere(function ($q) {
                        $q->whereDoesntHave('requettes', function ($q2) {
                            $q2->whereNotNull('etat_greffe')
                                ->where('etat_greffe', '!=', 'KO')
                                ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                        })
                            ->whereNotNull('etat_greffe')
                            ->where('etat_greffe', '!=', 'KO');
                    });
            })

            ->orderByDesc('id')
            ->get();

        return DossierResource::collection($dossiers);
    }
*/

    // app/Http/Controllers/DossierController.php

    /*public function searchMultiple(Request $request)
    {
        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $cin = $request->input('cin');
        $num_det = $request->input('numero_detention');

        // Paramètres de l'affaire
        $aff_num = $request->input('affaire_numero');
        $aff_code = $request->input('affaire_code');
        $aff_annee = $request->input('affaire_annee');

        $query = Dossier::with(['detenu', 'affaires.tribunal']);

        $query->where(function ($q) use ($nom, $prenom, $cin, $num_det, $aff_num, $aff_code, $aff_annee) {

            // 1. Logique Nom + Prénom (Combinaisons)
            if ($nom && $prenom) {
                $q->orWhereHas('detenu', function ($sub) use ($nom, $prenom) {
                    $sub->where(function ($sq) use ($nom, $prenom) {
                        // Cherche "Nom Prénom" OU "Prénom Nom"
                        $sq->whereRaw("CONCAT(nom, ' ', prenom) LIKE ?", ["%{$nom} {$prenom}%"])
                            ->orWhereRaw("CONCAT(prenom, ' ', nom) LIKE ?", ["%{$nom} {$prenom}%"]);
                    });
                });
            }

            // 2. Logique CIN (Exacte)
            if ($cin) {
                $q->orWhereHas('detenu', function ($sub) use ($cin) {
                    $sub->where('cin', $cin);
                });
            }

            // 3. Logique Numéro d'écrou
            if ($num_det) {
                $q->orWhere('numero_detention', $num_det);
            }

            // 4. Logique Affaire (Somme des 3 critères)
            // Ici on cherche un dossier qui possède UNE affaire ayant ces 3 valeurs
            if ($aff_num && $aff_code && $aff_annee) {
                $q->orWhereHas('affaires', function ($sub) use ($aff_num, $aff_code, $aff_annee) {
                    $sub->where('numero', $aff_num)
                        ->where('code', $aff_code)
                        ->where('annee', $aff_annee);
                });
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $query->limit(10)->get()
        ]);
    }*/
    public function searchMultiple(Request $request)
    {
        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $cin = $request->input('cin');
        $num_det = $request->input('numero_detention');
        $user_tribunal = $request->input('user_tribunal');



        // Paramètres de l'affaire
        $aff_num = $request->input('affaire_numero');
        $aff_code = $request->input('affaire_code');
        $aff_annee = $request->input('affaire_annee');

        $query = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'requettes.typerequette',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.affaire',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->where('categorie', 'CAT-1')->where('user_tribunal_id', $user_tribunal)->where(function ($q) {
            $q->where('has_antecedent', '!=', 'OUI')
                ->orWhereNull('has_antecedent');
        });

        $query->where(function ($q) use ($nom, $prenom, $cin, $num_det, $aff_num, $aff_code, $aff_annee) {

            // 1. Logique Nom + Prénom (Flexible : "karim ahmadi" trouve "mahmadi karima")
            if ($nom || $prenom) {
                $q->orWhereHas('detenu', function ($sub) use ($nom, $prenom) {
                    $search = trim("$nom $prenom"); // On combine les entrées
                    $sub->where(function ($sq) use ($search) {
                        $sq->whereRaw("CONCAT(nom, ' ', prenom) LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("CONCAT(prenom, ' ', nom) LIKE ?", ["%{$search}%"]);
                    });
                });
            }

            // 2. Logique CIN
            if ($cin) {
                $q->orWhereHas('detenu', function ($sub) use ($cin) {
                    $sub->where('cin', 'LIKE', "%{$cin}%");
                });
            }

            // 3. Logique Numéro d'écrou
            if ($num_det) {
                $q->orWhere('numero_detention', 'LIKE', "%{$num_det}%");
            }

            // 4. Logique Affaire (Format Flexible : 136/2601/2024 ou 2024/2601/136)
            if ($aff_num && $aff_code && $aff_annee) {
                $q->orWhereHas('affaires', function ($sub) use ($aff_num, $aff_code, $aff_annee) {
                    $sub->where(function ($sq) use ($aff_num, $aff_code, $aff_annee) {
                        // On teste les deux formats principaux de concaténation
                        $format1 = "{$aff_num}/{$aff_code}/{$aff_annee}";
                        $format2 = "{$aff_annee}/{$aff_code}/{$aff_num}";

                        $sq->whereRaw("CONCAT(numero, '/', code, '/', annee) LIKE ?", ["%{$format1}%"])
                            ->orWhereRaw("CONCAT(annee, '/', code, '/', numero) LIKE ?", ["%{$format2}%"]);
                    });
                });
            }
        });

        return response()->json([
            'status' => 'success',
            'count' => $query->count(),
            'data' => $query->limit(10)->get()
        ]);
    }

    public function forwardDossier(Request $request, Dossier $dossier, OperationService $operationService)
    {
        $dossier->date_envoi_greffe = now()->format('Y-m-d H:i:s.v');
        $dossier->etat_greffe = "NT";
        $dossier->save();
        $userId = (int) Auth::id();
        $operationService->logOperation(
            $dossier->id,
            'TR-ENVOI-TO-GREFFE', // Mettez l'ID correspondant à "Ajout requête"
            $requette->id ?? null,
            $userId
        );
        return new DossierResource($dossier);
    }

    public function reForwardDossierToGreffe(Request $request,  Dossier $dossier, OperationService $operationService)
    {
        // $dossier = Dossier::findOrFail($dossier_id);

        $dossier->date_envoi_greffe = now()->format('Y-m-d H:i:s.v');
        $dossier->etat_greffe = "NT";
        $dossier->nbr_redirection += 1; // Ou $dossier->nbr_redirection++
        $dossier->observation_redirection = $request->observation_redirection;
        $dossier->save();

        $userId = (int) Auth::id();
        $operationService->logOperation(
            $dossier->id,
            'TR-ENVOI-TO-GREFFE', // Mettez l'ID correspondant à "Ajout requête"
            $requette->id ?? null,
            $userId
        );
        return new DossierResource($dossier);
    }

    public function uploadOnePj(Request $request, $dossier_id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:153600',
            'type' => 'required|string'
        ]);

        $file = $request->file('file');
        $path = $file->store('temp/openbee_uploads');

        // Mappage du type vers l'ID OpenBee (comme dans votre controller actuel)
        $fileMappings = [
            'copie_dgapr' => 8,
            'copie_demande' => 7,
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];

        $fileData = [[
            'path' => $path,
            'typepjId' => $fileMappings[$request->type],
            'affaireId' => $request->affaire_id,
            'fieldName' => $request->type,
            'originalName' => $file->getClientOriginalName(),
        ]];

        // Lancer le job immédiatement pour ce fichier seul
        UploadDossierPJsJob::dispatch($dossier_id, $fileData, [])->onQueue('openbee_uploads');

        return response()->json(['message' => 'Fichier en cours de traitement']);
    }

    public function updateInfosOnly(Request $request, $id)

    {

        \Log::debug('***********Modification Personelle des Dossier **************** :', $request->all());


        //$dossier = $requette->dossier;
        $dossier = Dossier::findOrFail($id);
        $detenu = $dossier->detenu;

        // Mise à jour Détenu
        $detenu->update($request->only([
            'nom',
            'prenom',
            'datenaissance',
            'nompere',
            'nommere',
            'cin',
            'adresse',
            'genre',
            'nationalite_id'
        ]));

        // Mise à jour Dossier
        $dossier->update($request->only([
            'numeromp',
            'prison_id'
        ]));

        return response()->json(['message' => 'Mise à jour réussie']);
    }


    // AJOUTEZ ou MODIFIEZ la méthode de traitement des PJ dans votre DossierController.php :
    public function addPjsFromDetails(Request $request, $id)
    {
        $dossier = Dossier::findOrFail($id);
        $filesToProcess = [];
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();

        foreach ($request->input('pjs', []) as $index => $pjData) {
            // Juste après la ligne 1570 (début du foreach) — temporaire pour debug :
            Log::info('PJ DATA REÇU', [
                'typepj_id'         => $pjData['typepj_id'],
                'autre_observation' => $pjData['autre_observation'] ?? 'VIDE',
                'fieldname'         => $pjData['fieldname'] ?? 'VIDE',
            ]);
            // Préréquis 2 & 4 : On récupère le nom dynamique généré par le Front
            $fieldName = $pjData['fieldname'];

            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);

                // Validation (Règle 8)
                if ($file->getClientOriginalExtension() !== 'pdf' || $file->getSize() > (100 * 1024 * 1024)) {
                    continue;
                }

                $tempPath = $file->store('temp/openbee_uploads');

                /*$filesToProcess[] = [
                    'path' => $tempPath,
                    'typepjId' => $pjData['typepj_id'],
                    'fieldName' => $fieldName,
                    'originalName' => $file->getClientOriginalName(),
                    'observation' => !empty($pjData['autre_observation'])
                        ? $pjData['autre_observation']
                        : ($typepjLabels[$pjData['typepj_id']] ?? 'PJ'),
                ];*/

                // APRÈS (avec les clés manquantes) :
                $filesToProcess[] = [
                    'path'                => $tempPath,
                    'typepjId'            => $pjData['typepj_id'],
                    'fieldName'           => $fieldName,
                    'originalName'        => $file->getClientOriginalName(),
                    'affaireId'           => $pjData['affaire_id'] ?? null,        // ← AJOUT
                    'context_requette_id' => $pjData['requette_id'] ?? null,       // ← AJOUT
                    'observation'         => !empty($pjData['autre_observation'])
                        ? $pjData['autre_observation']
                        : ($typepjLabels[$pjData['typepj_id']] ?? 'PJ'),
                ];
            }
        }

        if (!empty($filesToProcess)) {
            // Préréquis 1 & 10 : Insertion standard via Job
            UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess, [])
                ->onQueue('openbee_uploads');
        }

        return response()->json(['message' => 'Traitement lancé'], 200);
    }


    // ========================================================================
    // ÉCRAN 1 : demandes.component (liste "toujours en attente")
    // ========================================================================

    public function dossiersTrServerSide(Request $request)
    {
        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $perPage  = max(1, $endRow - $startRow);
        $page     = (int) floor($startRow / $perPage) + 1;

        $query = $this->buildDossiersTrQuery($request->input('filters', []));
        $this->applyDossiersTrSorting($query, $request->input('sortModel', []));

        $dossiers = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'rows'    => DossierResource::collection($dossiers->items()),
            'lastRow' => $dossiers->total(),
        ]);
    }

    public function exportDossiersTr(Request $request)
    {
        $filters  = $request->input('filters', []);
        $dossiers = $this->buildDossiersTrQuery($filters)->get();

        return Excel::download(new DossiersTrExport($dossiers), 'liste-demandes.xlsx');
    }

    /** Conservée pour compatibilité descendante si un autre écran l'appelle encore. */
    public function dossiersTr(Request $request)
    {
        $dossiers = $this->buildDossiersTrQuery($request->input('filters', []))
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 50));

        return new DossierResource($dossiers);
    }

    private function buildDossiersTrQuery(array $f): Builder
    {
        $query = Dossier::with([
            'detenu',
            'affaires',
            'typedossier',
            'naturedossier',
            'objetdemande',
            'sourcedemande',
            'LibelleTribunalUtilisateur',
        ])
            ->whereNotNull('user_tribunal_id')
            ->where(function ($q) {
                $q->whereNull('has_antecedent')->orWhere('has_antecedent', '!=', 'OUI');
            })
            // Spécifique à cet écran : dossiers PAS encore reçus par la DAPG
            // et provenant uniquement d'une demande (D) ou d'une requête (R)
            ->where(function ($q) {
                $q->whereNull('tr_dapg')->orWhere('tr_dapg', '!=', 'OK');
            })
            ->whereIn('originedossier', ['D', 'R']);

        $this->applyCommonDossierTrFilters($query, $f);

        return $query;
    }

    // ========================================================================
    // ÉCRAN 2 : all-received-dossiers-from-tr.component (liste complète,
    // avec switch "dossiers reçus")
    // ========================================================================

    public function allReceivedDossiersTrServerSide(Request $request)
    {
        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $perPage  = max(1, $endRow - $startRow);
        $page     = (int) floor($startRow / $perPage) + 1;

        $query = $this->buildAllReceivedDossiersTrQuery($request->input('filters', []));
        $this->applyDossiersTrSorting($query, $request->input('sortModel', []));

        $dossiers = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'rows'    => DossierResource::collection($dossiers->items()),
            'lastRow' => $dossiers->total(),
        ]);
    }

    public function exportAllReceivedDossiersTr(Request $request)
    {
        $filters  = $request->input('filters', []);
        $dossiers = $this->buildAllReceivedDossiersTrQuery($filters)->get();

        return Excel::download(
            new AllReceivedDossiersTrExport($dossiers),
            'liste-dossiers-recus-tribunaux.xlsx',
        );
    }

    private function buildAllReceivedDossiersTrQuery(array $f): Builder
    {
        $query = Dossier::with([
            'detenu',
            'affaires',
            'affaires.tribunal',
            'typedossier',
            'naturedossier',
            'objetdemande',
            'sourcedemande',
            'LibelleTribunalUtilisateur',
        ])
            ->whereNotNull('user_tribunal_id')
            ->where(function ($q) {
                $q->whereNull('has_antecedent')->orWhere('has_antecedent', '!=', 'OUI');
            })
            ->whereIn('originedossier', ['R', 'D']); // exclut null et toute autre valeur

        $this->applyCommonDossierTrFilters($query, $f);

        if (!empty($f['dossiers_recus'])) {
            $query->where('tr_dapg', 'OK');
        }

        return $query;
    }

    // ========================================================================
    // Logique de filtrage PARTAGÉE entre les deux écrans
    // (reprend exactement le formulaire filterForm des deux composants Angular)
    // ========================================================================

    private function applyCommonDossierTrFilters(Builder $query, array $f): void
    {
        if (!empty($f['numero'])) {
            $query->where('numero', 'like', '%' . $f['numero'] . '%');
        }

        if (!empty($f['numero_dapg'])) {
            $query->where('numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }

        if (!empty($f['numero_affaire'])) {
            $query->whereHas('affaires', function ($q) use ($f) {
                $q->where('numeroaffaire', 'like', '%' . $f['numero_affaire'] . '%');
            });
        }

        if (!empty($f['nom_detenu'])) {
            $query->whereHas('detenu', function ($q) use ($f) {
                $q->where('nom', 'like', '%' . $f['nom_detenu'] . '%')
                    ->orWhere('prenom', 'like', '%' . $f['nom_detenu'] . '%');
            });
        }

        if (!empty($f['user_tribunal_libelle'])) {
            $query->where('user_tribunal_id', $f['user_tribunal_libelle']);
        }

        if (!empty($f['typedossier'])) {
            $query->where('typedossier_id', $f['typedossier']);
        }

        if (!empty($f['naturedossier'])) {
            $query->where('naturedossiers_id', $f['naturedossier']);
        }

        if (!empty($f['etat'])) {
            if ($f['etat'] === 'NT') {
                $query->where('etat', 'NT');
            } elseif ($f['etat'] === 'OK_not_ready') {
                $query->where('etat', 'OK')
                    ->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->whereNull('tr_tribunal');
                        })->orWhere(function ($q2) {
                            $q2->where('tr_tribunal', '!=', 'OK');
                        });
                    });
            } elseif ($f['etat'] === 'OK_ready') {
                $query->where('etat', 'OK')->where('tr_tribunal', 'OK');
            }
        }

        if (!empty($f['date_debut'])) {
            $query->whereDate('created_at', '>=', $f['date_debut']);
        }

        if (!empty($f['date_fin'])) {
            $query->whereDate('created_at', '<=', $f['date_fin']);
        }

        if (!empty($f['date_sortie'])) {
            $query->whereNotNull('date_sortie')
                ->where('date_sortie', '!=', '')
                ->whereDate('date_sortie', '>', $f['date_sortie']);
        }
    }

    private function applyDossiersTrSorting($query, array $sortModel): void
    {
        $allowedColumns = ['numero', 'numero_dapg', 'etat', 'created_at', 'id'];

        if (empty($sortModel)) {
            $query->orderBy('id', 'desc');
            return;
        }

        foreach ($sortModel as $sort) {
            $colId     = $sort['colId'] ?? null;
            $direction = ($sort['sort'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

            if (in_array($colId, $allowedColumns, true)) {
                $query->orderBy($colId, $direction);
            }
        }
    }

    // ========================================================================
    // Suppression : conservée telle quelle, ne dépend pas du chargement
    // complet en mémoire — aucun changement nécessaire ici.
    // (déjà présente dans ton controller, non dupliquée dans ce fichier)
    // ========================================================================



    /**
     * ============================================================================
     * À AJOUTER dans App\Http\Controllers\Api\V1\DossierController
     * ============================================================================
     *
     * ⚠️ CE ENDPOINT N'UTILISE PAS le trait HasServerSideRowModel — il ne
     * s'applique pas ici. Le trait suppose UNE SEULE requête Eloquent paginable ;
     * cet écran combine deux tables (dossiers ET requettes), donc la pagination
     * doit être faite manuellement via une UNION SQL.
     *
     * PRINCIPE :
     * 1. On construit 2 requêtes légères (juste id + type + date), une par table,
     *    avec TOUS les filtres déjà appliqués en SQL.
     * 2. On les combine avec un UNION ALL, on trie et on découpe UNIQUEMENT ce
     *    UNION (id/type/date = quelques octets par ligne, pas les relations).
     * 3. Seulement pour les ~20 lignes de la page demandée, on va chercher les
     *    objets complets (avec relations) dans Dossier et Requette séparément.
     * 4. On réassemble dans le bon ordre.
     *
     * ⚠️ Colonnes vérifiées dans ton code existant :
     * - dossiers.detenu_id (FK vers detenus.id)
     * - requettes.dossier_id (FK vers dossiers.id)
     * - naturedossiers_id (avec le "s")
     *
     * ⚠️ Limite assumée : le tri ne se fait que sur une colonne "date" commune
     * (created_at pour les dossiers, date pour les requettes), pas sur les
     * colonnes cliquables de la grille — trier une UNION de 2 tables
     * différentes par une colonne arbitraire n'a pas de sens structurel.
     * L'ancien code ne triait déjà pas explicitement (juste une concaténation
     * de tableaux), donc ce n'est pas une régression.
     */

    public function dossiersRequettesGreffeServerSide(Request $request)
    {
        $f          = $request->input('filters', []);
        $trId       = $f['tribunal_id'] ?? null;
        $etatGreffe = $f['etat_greffe'] ?? 'NT';

        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $limit    = max(1, $endRow - $startRow);
        $offset   = $startRow;

        $dossierIdsQuery  = $this->buildDossierGreffeIdsQuery($trId, $etatGreffe, $f);
        $requetteIdsQuery = $this->buildRequetteGreffeIdsQuery($trId, $etatGreffe, $f);

        // Totaux (comptage léger, chaque requête ne compte que des ids)
        $total = (clone $dossierIdsQuery)->count() + (clone $requetteIdsQuery)->count();

        // UNION triée + paginée — seule cette étape touche potentiellement
        // beaucoup de lignes, mais avec seulement 3 colonnes chacune.
        $unionQuery = $dossierIdsQuery->unionAll($requetteIdsQuery);

        $page = DB::query()
            ->fromSub($unionQuery, 'combined')
            ->orderBy('sort_date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $dossierIds  = $page->where('row_type', 'dossier')->pluck('id')->all();
        $requetteIds = $page->where('row_type', 'requette')->pluck('id')->all();

        // Hydratation complète UNIQUEMENT pour les ids de cette page
        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->whereIn('id', $dossierIds)->get()->keyBy('id');

        $requettes = Requette::with([
            'dossier',
            'dossier.pjs',
            'dossier.avis',
            'dossier.pjs.affaire',
            'dossier.detenu',
            'dossier.affaires',
            'userParquetObjet:id,name',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($q) {
                $q->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.objetdemande',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette',
        ])->whereIn('id', $requetteIds)->get()->keyBy('id');

        // Réassemblage dans l'ordre exact renvoyé par l'UNION triée
        $rows = $page->map(function ($row) use ($dossiers, $requettes) {
            if ($row->row_type === 'dossier') {
                $model = $dossiers->get($row->id);
                if (!$model) return null;
                $arr = $model->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            }

            $model = $requettes->get($row->id);
            if (!$model) return null;
            $arr = $model->toArray();
            $arr['rowType'] = 'requette';
            return $arr;
        })->filter()->values();

        return response()->json([
            'rows'    => $rows,
            'lastRow' => $total,
        ]);
    }

    private function buildDossierGreffeIdsQuery($trId, string $etatGreffe, array $f)
    {
        $query = DB::table('dossiers')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'dossiers.id as id',
                DB::raw("'dossier' as row_type"),
                'dossiers.created_at as sort_date',
            ])
            ->where('dossiers.user_tribunal_id', $trId)
            ->where('dossiers.categorie', 'CAT-1')
            ->where('dossiers.originedossier', '!=', 'DAPG-ENCOURS')
            ->where(function ($q) {
                $q->whereNull('dossiers.has_antecedent')->orWhere('dossiers.has_antecedent', '!=', 'OUI');
            })
            ->where(function ($q) {
                $q->whereNull('dossiers.tr_tribunal')->orWhere('dossiers.tr_tribunal', '!=', 'OK');
            })
            ->where('dossiers.etat_greffe', $etatGreffe);

        if (!empty($f['numero'])) {
            $query->where('dossiers.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('dossiers.created_at', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('dossiers.created_at', '<=', $f['dateFin']);
        }

        return $query;
    }

    private function buildRequetteGreffeIdsQuery($trId, string $etatGreffe, array $f)
    {
        $query = DB::table('requettes')
            ->join('dossiers', 'requettes.dossier_id', '=', 'dossiers.id')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'requettes.id as id',
                DB::raw("'requette' as row_type"),
                'requettes.date as sort_date',
            ])
            ->where('requettes.tribunal_id', $trId)
            ->where('requettes.etat', 'TR')
            ->where(function ($q) {
                $q->where('requettes.etat_tribunal', '!=', 'TR')->orWhereNull('requettes.etat_tribunal');
            })
            ->where('requettes.etat_greffe', $etatGreffe);

        if (!empty($f['numero'])) {
            $query->where('requettes.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('requettes.date', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('requettes.date', '<=', $f['dateFin']);
        }

        return $query;
    }

    /**
     * ============================================================================
     * À AJOUTER dans App\Http\Controllers\Api\V1\DossierController
     * (même pattern que dossiersRequettesGreffeServerSide de la Tâche 3)
     * ============================================================================
     *
     * Différences avec la version "greffe" :
     * - Contrainte supplémentaire : user_parquet == utilisateur courant (colonne
     *   directe vérifiée sur dossiers ET requettes)
     * - dossiers.originedossier = 'D' strictement (pas 'D' ou 'R'), repris du
     *   filtre JS existant (item.originedossier === 'D')
     * - Le pivot d'onglet est etat_parquet (au lieu de etat_greffe), avec 2
     *   valeurs mutuellement exclusives :
     *     - 'EN_COURS' -> etat_parquet != 'OK'
     *     - 'OK'       -> etat_parquet == 'OK'
     */

    public function dossiersRequettesParquetServerSide(Request $request)
    {
        $f       = $request->input('filters', []);
        $trId    = $f['tribunal_id'] ?? null;
        $userId  = $f['user_parquet'] ?? null;
        $tabKey  = $f['etat_parquet_tab'] ?? 'EN_COURS'; // 'EN_COURS' | 'OK'

        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $limit    = max(1, $endRow - $startRow);
        $offset   = $startRow;

        $dossierIdsQuery  = $this->buildDossierParquetIdsQuery($trId, $userId, $tabKey, $f);
        $requetteIdsQuery = $this->buildRequetteParquetIdsQuery($trId, $userId, $tabKey, $f);

        $total = (clone $dossierIdsQuery)->count() + (clone $requetteIdsQuery)->count();

        $unionQuery = $dossierIdsQuery->unionAll($requetteIdsQuery);

        $page = DB::query()
            ->fromSub($unionQuery, 'combined')
            ->orderBy('sort_date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $dossierIds  = $page->where('row_type', 'dossier')->pluck('id')->all();
        $requetteIds = $page->where('row_type', 'requette')->pluck('id')->all();

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->whereIn('id', $dossierIds)->get()->keyBy('id');

        $requettes = Requette::with([
            'dossier',
            'dossier.pjs',
            'dossier.avis',
            'dossier.pjs.affaire',
            'dossier.detenu',
            'dossier.affaires',
            'userParquetObjet:id,name',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($q) {
                $q->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.objetdemande',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette',
        ])->whereIn('id', $requetteIds)->get()->keyBy('id');

        $rows = $page->map(function ($row) use ($dossiers, $requettes) {
            if ($row->row_type === 'dossier') {
                $model = $dossiers->get($row->id);
                if (!$model) return null;
                $arr = $model->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            }

            $model = $requettes->get($row->id);
            if (!$model) return null;
            $arr = $model->toArray();
            $arr['rowType'] = 'requette';
            return $arr;
        })->filter()->values();

        return response()->json([
            'rows'    => $rows,
            'lastRow' => $total,
        ]);
    }

    private function buildDossierParquetIdsQuery($trId, $userId, string $tabKey, array $f)
    {
        $query = DB::table('dossiers')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'dossiers.id as id',
                DB::raw("'dossier' as row_type"),
                'dossiers.created_at as sort_date',
            ])
            ->where('dossiers.user_tribunal_id', $trId)
            ->where('dossiers.categorie', 'CAT-1')
            ->where('dossiers.originedossier', 'D') // repris du filtre JS : origineDossier === 'D'
            ->where('dossiers.user_parquet', $userId)
            ->where(function ($q) {
                $q->whereNull('dossiers.has_antecedent')->orWhere('dossiers.has_antecedent', '!=', 'OUI');
            })
            ->where(function ($q) {
                $q->whereNull('dossiers.tr_tribunal')->orWhere('dossiers.tr_tribunal', '!=', 'OK');
            });

        if ($tabKey === 'OK') {
            $query->where('dossiers.etat_parquet', 'TR'); // ⚠️ corrigé : 'TR' est la vraie valeur "traité", pas 'OK'
        } else {
            $query->where(function ($q) {
                $q->whereNull('dossiers.etat_parquet')->orWhere('dossiers.etat_parquet', '!=', 'TR');
            });
        }

        if (!empty($f['numero'])) {
            $query->where('dossiers.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('dossiers.created_at', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('dossiers.created_at', '<=', $f['dateFin']);
        }

        return $query;
    }

    private function buildRequetteParquetIdsQuery($trId, $userId, string $tabKey, array $f)
    {
        $query = DB::table('requettes')
            ->join('dossiers', 'requettes.dossier_id', '=', 'dossiers.id')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'requettes.id as id',
                DB::raw("'requette' as row_type"),
                'requettes.date as sort_date',
            ])
            ->where('requettes.tribunal_id', $trId)
            ->where('requettes.etat', 'TR')
            ->where(function ($q) {
                $q->where('requettes.etat_tribunal', '!=', 'TR')->orWhereNull('requettes.etat_tribunal');
            })
            ->where('requettes.user_parquet', $userId);

        if ($tabKey === 'OK') {
            $query->where('requettes.etat_parquet', 'TR'); // ⚠️ corrigé : 'TR' est la vraie valeur "traité", pas 'OK'
        } else {
            $query->where(function ($q) {
                $q->whereNull('requettes.etat_parquet')->orWhere('requettes.etat_parquet', '!=', 'TR');
            });
        }

        if (!empty($f['numero'])) {
            $query->where('requettes.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('requettes.date', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('requettes.date', '<=', $f['dateFin']);
        }

        return $query;
    }

    // ========================================================================
    // À AJOUTER dans App\Http\Controllers\Api\V1\DossierController
    // (juste après dossiersRequettesParquetServerSide / buildRequetteParquetIdsQuery,
    // avant la fermeture de la classe)
    // ========================================================================
    //
    // Fusion pour le rôle TRIBUNAL :
    // - Dossiers : reprend exactement les conditions de dossierByTr()
    //   (user_tribunal_id = trId, categorie = CAT-1), + originedossier = 'D'
    //   comme demandé (au lieu de "!= DAPG-ENCOURS" qui laissait passer 'D' et 'R').
    //   PAS d'exclusion sur has_antecedent / tr_tribunal : ces dossiers restent
    //   affichés, seulement badgés dans la colonne "تفاصيل" (comme all-tr-dossiers).
    // - Requêtes : reprend exactement les conditions de requetteByTr()
    //   (tribunal_id = trId, etat = 'TR'), + typerequette.cat = 'CAT-1' comme
    //   demandé (le champ "cat" vit sur typerequette, pas sur typedossier —
    //   voir getTRRequettes() qui utilise déjà whereHas('typerequette', ...)).
    // ========================================================================

    public function dossiersRequettesTribunalServerSide(Request $request)
    {
        $f    = $request->input('filters', []);
        $trId = $f['tribunal_id'] ?? null;

        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $limit    = max(1, $endRow - $startRow);
        $offset   = $startRow;

        $dossierIdsQuery  = $this->buildDossierTribunalCat1IdsQuery($trId, $f);
        $requetteIdsQuery = $this->buildRequetteTribunalCat1IdsQuery($trId, $f);

        $total = (clone $dossierIdsQuery)->count() + (clone $requetteIdsQuery)->count();

        $unionQuery = $dossierIdsQuery->unionAll($requetteIdsQuery);

        $page = DB::query()
            ->fromSub($unionQuery, 'combined')
            ->orderBy('sort_date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $dossierIds  = $page->where('row_type', 'dossier')->pluck('id')->all();
        $requetteIds = $page->where('row_type', 'requette')->pluck('id')->all();

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->whereIn('id', $dossierIds)->get()->keyBy('id');

        $requettes = Requette::with([
            'dossier',
            'dossier.pjs',
            'dossier.avis',
            'dossier.pjs.affaire',
            'dossier.detenu',
            'dossier.affaires',
            'userParquetObjet:id,name',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($q) {
                $q->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.objetdemande',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette',
        ])->whereIn('id', $requetteIds)->get()->keyBy('id');

        $rows = $page->map(function ($row) use ($dossiers, $requettes) {
            if ($row->row_type === 'dossier') {
                $model = $dossiers->get($row->id);
                if (!$model) return null;
                $arr = $model->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            }

            $model = $requettes->get($row->id);
            if (!$model) return null;
            $arr = $model->toArray();
            $arr['rowType'] = 'requette';
            return $arr;
        })->filter()->values();

        return response()->json([
            'rows'    => $rows,
            'lastRow' => $total,
        ]);
    }

    /**
     * Export Excel : mêmes filtres que la grille, mais SANS pagination
     * (toutes les lignes qui matchent). On renvoie du JSON hydraté (pas de
     * Excel::download ici, car il n'existe pas encore de classe Export dédiée
     * à cette liste fusionnée) — c'est le front qui génère le .xlsx avec
     * ExcelJS, comme c'est déjà fait dans list-requette-parquet.component.ts.
     */
    public function exportDossiersRequettesTribunal(Request $request)
    {
        $f    = $request->input('filters', []);
        $trId = $f['tribunal_id'] ?? null;

        $dossierIds  = $this->buildDossierTribunalCat1IdsQuery($trId, $f)->pluck('dossiers.id')->all();
        $requetteIds = $this->buildRequetteTribunalCat1IdsQuery($trId, $f)->pluck('requettes.id')->all();

        $dossiers = Dossier::with([
            'detenu',
            'affaires',
            'affaires.tribunal',
            'typedossier',
            'naturedossier',
        ])->whereIn('id', $dossierIds)->orderBy('created_at', 'desc')->get()
            ->map(function ($d) {
                $arr = $d->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            });

        $requettes = Requette::with([
            'dossier',
            'dossier.detenu',
            'dossier.affaires',
            'dossier.affaires.tribunal',
            'dossier.typedossier',
            'dossier.naturedossier',
            'typerequette',
        ])->whereIn('id', $requetteIds)->orderBy('date', 'desc')->get()
            ->map(function ($r) {
                $arr = $r->toArray();
                $arr['rowType'] = 'requette';
                return $arr;
            });

        return response()->json([
            'rows' => $dossiers->concat($requettes)->values(),
        ]);
    }

    private function buildDossierTribunalCat1IdsQuery($trId, array $f)
    {
        $query = DB::table('dossiers')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'dossiers.id as id',
                DB::raw("'dossier' as row_type"),
                'dossiers.created_at as sort_date',
            ])
            ->where('dossiers.user_tribunal_id', $trId)
            ->where('dossiers.categorie', 'CAT-1')
            ->where('dossiers.originedossier', 'D');

        if (!empty($f['numero'])) {
            $query->where('dossiers.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('dossiers.created_at', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('dossiers.created_at', '<=', $f['dateFin']);
        }

        return $query;
    }

    private function buildRequetteTribunalCat1IdsQuery($trId, array $f)
    {
        // ⚠️ CORRECTIF : le nom réel de la table (SQL Server) n'est pas
        // "typerequettes" (erreur "Invalid object name 'typerequettes'").
        // On résout le nom exact via le modèle Eloquent plutôt que de le
        // deviner — TypeRequette::all() fonctionne déjà ailleurs, donc
        // ->getTable() donne le nom correct à coup sûr.
        $typeRequetteTable = (new TypeRequette())->getTable();

        $query = DB::table('requettes')
            ->join('dossiers', 'requettes.dossier_id', '=', 'dossiers.id')
            ->join($typeRequetteTable, 'requettes.typerequette_id', '=', $typeRequetteTable . '.id')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'requettes.id as id',
                DB::raw("'requette' as row_type"),
                'requettes.date as sort_date',
            ])
            ->where('requettes.tribunal_id', $trId)
            ->where('requettes.etat', 'TR')
            ->where($typeRequetteTable . '.cat', 'CAT-1');

        // ... reste des filtres inchangé (numero, numeromp, numero_dapg,
        // typedossier_id, naturedossier_id, cin, nom, dateDebut, dateFin)

        if (!empty($f['numero'])) {
            $query->where('requettes.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('requettes.date', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('requettes.date', '<=', $f['dateFin']);
        }

        return $query;
    }

    // ========================================================================
    // À AJOUTER dans App\Http\Controllers\Api\V1\DossierController
    // (même pattern que dossiersRequettesTribunalServerSide, mais reprend les
    // conditions d'affichage de all-parquet-dossiers.component +
    // all-parquet-requettes.component — écran SANS onglets etat_parquet,
    // contrairement à dossiersRequettesParquetServerSide qui reste inchangé.)
    //
    // - Dossiers : reprend exactement all-parquet-dossiers.component.ts
    //   (user_tribunal_id = trId, categorie = CAT-1 [via dossierByTr],
    //   has_antecedent != OUI, originedossier = 'D', user_parquet = userId).
    //   PAS d'exclusion sur tr_tribunal (commentée dans le composant d'origine).
    // - Requêtes : reprend exactement all-parquet-requettes.component.ts
    //   (tribunal_id = trId, etat = 'TR' [via requetteByTr], user_parquet =
    //   userId), + typerequette.cat = 'CAT-1' comme demandé.
    //   PAS d'exclusion sur etat_tribunal.
    // ========================================================================

    public function dossiersRequettesAllParquetServerSide(Request $request)
    {
        $f      = $request->input('filters', []);
        $trId   = $f['tribunal_id'] ?? null;
        $userId = $f['user_parquet'] ?? null;

        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $limit    = max(1, $endRow - $startRow);
        $offset   = $startRow;

        $dossierIdsQuery  = $this->buildDossierAllParquetIdsQuery($trId, $userId, $f);
        $requetteIdsQuery = $this->buildRequetteAllParquetIdsQuery($trId, $userId, $f);

        $total = (clone $dossierIdsQuery)->count() + (clone $requetteIdsQuery)->count();

        $unionQuery = $dossierIdsQuery->unionAll($requetteIdsQuery);

        $page = DB::query()
            ->fromSub($unionQuery, 'combined')
            ->orderBy('sort_date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $dossierIds  = $page->where('row_type', 'dossier')->pluck('id')->all();
        $requetteIds = $page->where('row_type', 'requette')->pluck('id')->all();

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'userParquetObjet:id,name',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'avis',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->whereIn('id', $dossierIds)->get()->keyBy('id');

        $requettes = Requette::with([
            'dossier',
            'dossier.pjs',
            'dossier.avis',
            'dossier.pjs.affaire',
            'dossier.detenu',
            'dossier.affaires',
            'userParquetObjet:id,name',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($q) {
                $q->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.objetdemande',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette',
        ])->whereIn('id', $requetteIds)->get()->keyBy('id');

        $rows = $page->map(function ($row) use ($dossiers, $requettes) {
            if ($row->row_type === 'dossier') {
                $model = $dossiers->get($row->id);
                if (!$model) return null;
                $arr = $model->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            }

            $model = $requettes->get($row->id);
            if (!$model) return null;
            $arr = $model->toArray();
            $arr['rowType'] = 'requette';
            return $arr;
        })->filter()->values();

        return response()->json([
            'rows'    => $rows,
            'lastRow' => $total,
        ]);
    }

    /**
     * Export Excel : mêmes filtres que la grille, sans pagination.
     * Comme pour le tribunal, on renvoie du JSON hydraté et c'est le front
     * qui génère le .xlsx avec ExcelJS (pattern déjà utilisé dans
     * all-parquet-requettes.component.ts).
     */
    public function exportDossiersRequettesAllParquet(Request $request)
    {
        $f      = $request->input('filters', []);
        $trId   = $f['tribunal_id'] ?? null;
        $userId = $f['user_parquet'] ?? null;

        $dossierIds  = $this->buildDossierAllParquetIdsQuery($trId, $userId, $f)->pluck('dossiers.id')->all();
        $requetteIds = $this->buildRequetteAllParquetIdsQuery($trId, $userId, $f)->pluck('requettes.id')->all();

        $dossiers = Dossier::with([
            'detenu',
            'affaires',
            'affaires.tribunal',
            'typedossier',
            'naturedossier',
        ])->whereIn('id', $dossierIds)->orderBy('created_at', 'desc')->get()
            ->map(function ($d) {
                $arr = $d->toArray();
                $arr['rowType'] = 'dossier';
                return $arr;
            });

        $requettes = Requette::with([
            'dossier',
            'dossier.detenu',
            'dossier.affaires',
            'dossier.affaires.tribunal',
            'dossier.typedossier',
            'dossier.naturedossier',
            'typerequette',
        ])->whereIn('id', $requetteIds)->orderBy('date', 'desc')->get()
            ->map(function ($r) {
                $arr = $r->toArray();
                $arr['rowType'] = 'requette';
                return $arr;
            });

        return response()->json([
            'rows' => $dossiers->concat($requettes)->values(),
        ]);
    }

    private function buildDossierAllParquetIdsQuery($trId, $userId, array $f)
    {
        $query = DB::table('dossiers')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'dossiers.id as id',
                DB::raw("'dossier' as row_type"),
                'dossiers.created_at as sort_date',
            ])
            ->where('dossiers.user_tribunal_id', $trId)
            ->where('dossiers.categorie', 'CAT-1')
            ->where('dossiers.originedossier', 'D')
            ->where('dossiers.user_parquet', $userId)
            ->where(function ($q) {
                $q->whereNull('dossiers.has_antecedent')->orWhere('dossiers.has_antecedent', '!=', 'OUI');
            });
        // ⚠️ PAS d'exclusion sur tr_tribunal : all-parquet-dossiers.component
        // l'a explicitement commentée (// trTribunal !== 'OK').

        if (!empty($f['numero'])) {
            $query->where('dossiers.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('dossiers.created_at', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('dossiers.created_at', '<=', $f['dateFin']);
        }

        return $query;
    }

    private function buildRequetteAllParquetIdsQuery($trId, $userId, array $f)
    {
        $typeRequetteTable = (new TypeRequette())->getTable();

        $query = DB::table('requettes')
            ->join('dossiers', 'requettes.dossier_id', '=', 'dossiers.id')
            ->join($typeRequetteTable, 'requettes.typerequette_id', '=', $typeRequetteTable . '.id')
            ->leftJoin('detenus', 'dossiers.detenu_id', '=', 'detenus.id')
            ->select([
                'requettes.id as id',
                DB::raw("'requette' as row_type"),
                'requettes.date as sort_date',
            ])
            ->where('requettes.tribunal_id', $trId)
            ->where('requettes.etat', 'TR')
            ->where('requettes.user_parquet', $userId)
            ->where($typeRequetteTable . '.cat', 'CAT-1');
        // ⚠️ PAS d'exclusion sur etat_tribunal : all-parquet-requettes.component
        // utilise getRequettesByTr() -> requetteByTr() qui ne filtre que etat='TR'.

        if (!empty($f['numero'])) {
            $query->where('requettes.numero', 'like', '%' . $f['numero'] . '%');
        }
        if (!empty($f['numeromp'])) {
            $query->where('dossiers.numeromp', 'like', '%' . $f['numeromp'] . '%');
        }
        if (!empty($f['numero_dapg'])) {
            $query->where('dossiers.numero_dapg', 'like', '%' . $f['numero_dapg'] . '%');
        }
        if (!empty($f['typedossier_id'])) {
            $query->where('dossiers.typedossier_id', $f['typedossier_id']);
        }
        if (!empty($f['naturedossier_id'])) {
            $query->where('dossiers.naturedossiers_id', $f['naturedossier_id']); // ⚠️ avec le "s"
        }
        if (!empty($f['cin'])) {
            $query->where('detenus.cin', 'like', '%' . $f['cin'] . '%');
        }
        if (!empty($f['nom'])) {
            $query->where(function ($q) use ($f) {
                $q->where('detenus.nom', 'like', '%' . $f['nom'] . '%')
                    ->orWhere('detenus.prenom', 'like', '%' . $f['nom'] . '%');
            });
        }
        if (!empty($f['dateDebut'])) {
            $query->whereDate('requettes.date', '>=', $f['dateDebut']);
        }
        if (!empty($f['dateFin'])) {
            $query->whereDate('requettes.date', '<=', $f['dateFin']);
        }

        return $query;
    }

    public function updateNumeroDapgSortie(Request $request, $id)
    {
        $dossier = Dossier::findOrFail($id);

        $validated = $request->validate([
            'numero_dapg' => [
                'required',
                'string',
                Rule::unique('dossiers', 'numero_dapg')->ignore($dossier->id),
            ],
            'date_sortie' => ['required', 'date'],
        ], [
            'numero_dapg.unique' => 'رقم الملف بالوزارة موجود مسبقاً',
        ]);

        $dossier->update([
            'numero_dapg' => $validated['numero_dapg'],
            'date_sortie' => $validated['date_sortie'],
        ]);

        return response()->json([
            'message' => 'تم التحديث بنجاح',
            'data' => $dossier->fresh(),
        ]);
    }
}

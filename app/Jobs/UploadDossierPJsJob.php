<?php

namespace App\Jobs;

use App\Models\Dossier;
use App\Models\Pj;
use App\Models\Requette;
use App\Models\TypePj;
use App\Services\OpenBeeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadDossierPJsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // On augmente à 5 tentatives pour être résilient face aux Deadlocks OpenBee
    public $tries = 5;

    // Délais progressifs entre les tentatives (en secondes)
    public function backoff(): array
    {
        return [5, 15, 30, 60];
    }

    protected $dossierId;
    protected $filesToProcess;
    protected $postUploadActions;

    public function __construct(int $dossierId, array $filesToProcess, array $postUploadActions = [])
    {
        $this->dossierId = $dossierId;
        $this->filesToProcess = $filesToProcess;
        $this->postUploadActions = $postUploadActions; // <--- Initialiser ceci
    }

    public function handle(OpenBeeService $openBee)
    {
        $dossier = Dossier::findOrFail($this->dossierId);
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();

        foreach ($this->filesToProcess as $fileData) {
            $tempStoragePath = $fileData['path'];

            // Vérifier si le fichier existe encore (évite les erreurs au retry)
            if (!Storage::exists($tempStoragePath)) {
                Log::warning("Fichier source absent pour le Job Dossier ID: {$this->dossierId}. Le fichier a peut-être déjà été traité.");
                continue;
            }

            $tempFilePath = null;

            try {
                // 1. Reconstitution du fichier
                $fileContent = Storage::get($tempStoragePath);
                $tempFilePath = tempnam(sys_get_temp_dir(), 'openbee');
                file_put_contents($tempFilePath, $fileContent);

                $uploadedFile = new UploadedFile(
                    $tempFilePath,
                    $fileData['originalName'],
                    mime_content_type($tempFilePath) ?: 'application/octet-stream',
                    null,
                    true
                );

                // 2. Logique de contexte (Requête ou Dossier)
                $contextRequetteId = $fileData['context_requette_id'] ?? null;
                $requette = $contextRequetteId ? Requette::find($contextRequetteId) : null;
                $typepjId = $fileData['typepjId'];

                if ($requette) {
                    $insertedObservation = ($requette->typerequette->cat == "CAT-1")
                        ? ($typepjLabels[$typepjId] ?? 'أخرى')
                        : ($requette->typerequette->libelle ?? 'أخرى');
                    $baseNumero = $requette->numero;
                } else {
                    $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';
                    $baseNumero = $dossier->numero;
                }

                // 3. Préparation du nom de fichier
                $extension = pathinfo($fileData['originalName'], PATHINFO_EXTENSION);
                $affairePart = $fileData['affaireId'] ? "_" . $fileData['affaireId'] : "";
                $filename = $baseNumero . "_" . $dossier->id . $affairePart . "_" . $fileData['fieldName'] . '.' . $extension;
                $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);

                // 4. Action OpenBee (La méthode upload() du service gère déjà son propre retry interne)
                $openBee->deleteIfExists($filenameSansExtension);

                $result = $openBee->upload($uploadedFile, $filename, [
                    'title'       => $filename,
                    'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                    'path'        => config('openbee.path'),
                ]);

                $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;

                // 5. Enregistrement en base de données (firstOrNew pour éviter les doublons au retry)
                $pj = Pj::firstOrNew([
                    'dossier_id' => $dossier->id,
                    'affaire_id' => $fileData['affaireId'],
                    'typepj_id'  => $typepjId,
                    'requette_id' => $contextRequetteId,
                ]);

                $pj->contenu = "OPENBEE/" . $filename;
                $pj->openbee_url = $openbeeUrl;
                $pj->observation = $insertedObservation;
                $pj->save();

                // 6. Succès : On peut supprimer le fichier source du Storage Laravel
                Storage::delete($tempStoragePath);
            } catch (\Exception $e) {
                Log::error("Échec dans UploadJob (Dossier: {$this->dossierId}): " . $e->getMessage());

                // On jette l'exception pour que Laravel déclenche le système de retry/backoff
                throw $e;
            } finally {
                // Toujours supprimer le fichier système temporaire (php tempnam)
                if ($tempFilePath && file_exists($tempFilePath)) {
                    @unlink($tempFilePath);
                }
            }
        }
        if (!empty($this->postUploadActions)) {
            DB::transaction(function () {
                foreach ($this->postUploadActions as $action) {
                    $modelClass = $action['model'];
                    $modelId    = $action['id'];
                    $data       = $action['data'] ?? [];

                    $model = $modelClass::find($modelId);
                    if ($model) {
                        // Mise à jour des colonnes (etat, user_id, etc.)
                        if (!empty($data)) {
                            $model->update($data);
                        }

                        // Gestion des relations Many-to-Many (ex: attach de statutrequettes)
                        if (isset($action['attach']) && isset($action['relation'])) {
                            $model->{$action['relation']}()->attach($action['attach']);
                        }
                    }
                }
            });
        }
    }
}
/*

// App/Jobs/UploadDossierPJsJob.php

namespace App\Jobs;

use App\Models\Dossier;
use App\Models\Pj;
use App\Models\Requette;
use App\Models\TypePj;
use App\Services\OpenBeeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// Importez UploadedFile pour créer le faux objet
use Illuminate\Http\UploadedFile;
// Retirez 'use Illuminate\Http\File;' si vous l'aviez

// Fichier : app/Jobs/UploadDossierPJsJob.php

class UploadDossierPJsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $dossierId;
    protected $filesToProcess;

    public function __construct(int $dossierId, array $filesToProcess)
    {
        $this->dossierId = $dossierId;
        $this->filesToProcess = $filesToProcess;
    }

    public function handle(OpenBeeService $openBee)
    {
        $dossier = Dossier::findOrFail($this->dossierId);
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();

        foreach ($this->filesToProcess as $fileData) {
            $tempStoragePath = $fileData['path'];
            $typepjId = $fileData['typepjId'];
            $affaireId = $fileData['affaireId'];
            $fieldName = $fileData['fieldName'];

            // 💡 NOUVELLE LOGIQUE CLÉ : Vérifier le contexte
            $contextRequetteId = $fileData['context_requette_id'] ?? null;
            $requette = null;

            if ($contextRequetteId) {
                $requette = Requette::find($contextRequetteId);
            }

            if (!Storage::exists($tempStoragePath)) {
                Log::warning("Fichier temporaire non trouvé dans le Job: " . $tempStoragePath);
                continue;
            }

            $tempFilePath = null;
            $openbeeUrl = null;

            try {
                // 1. Reconstitution du fichier (inchangé)
                $fileContent = Storage::get($tempStoragePath);
                $tempFilePath = tempnam(sys_get_temp_dir(), 'openbee');
                file_put_contents($tempFilePath, $fileContent);

                $uploadedFile = new UploadedFile(
                    $tempFilePath,
                    $fileData['originalName'],
                    mime_content_type($tempFilePath) ?: 'application/octet-stream',
                    null,
                    true
                );

                $extension = $uploadedFile->getClientOriginalExtension() ?: pathinfo($fileData['originalName'], PATHINFO_EXTENSION);
                $affairePart = $affaireId ? "_" . $affaireId : "";

                // 2. Détermination de l'observation
                if ($requette) {
                    // Logique spécifique à addReponseRequette
                    if ($requette->typerequette->cat == "CAT-1") {
                        $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';
                    } else {
                        $insertedObservation = $requette->typerequette->libelle ?? 'أخرى';
                    }
                    // 💡 Nom de fichier pour addReponseRequette : utilise $requette->numero
                    $baseNumero = $requette->numero;
                } else {
                    // Logique pour terminerDossierTr (pas de Requette)
                    $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';
                    // 💡 Nom de fichier pour terminerDossierTr : utilise $dossier->numero
                    $baseNumero = $dossier->numero;
                }

                // 3. Création du nom de fichier unique (UNIFIÉ)
                $filename = $baseNumero . "_" . $dossier->id . $affairePart . "_" . $fieldName . '.' . $extension;
                $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);
                $dbPath = "OPENBEE/" . $filename;

                // 4. Logique d'upload OpenBee
                $openBee->deleteIfExists($filenameSansExtension);
                $result = $openBee->upload($uploadedFile, $filename, [
                    'title'       => $filename,
                    'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                    'path'        => config('openbee.path'),
                ]);
                $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;

                // 5. Enregistrer l'objet Pj
                $pj = Pj::firstOrNew([
                    'dossier_id' => $dossier->id,
                    'affaire_id' => $affaireId,
                    'typepj_id'  => $typepjId,
                ]);

                // 💡 Ajout de l'ID de la requette si elle existe
                $pj->requette_id = $contextRequetteId;

                $pj->contenu = $dbPath;
                $pj->openbee_url = $openbeeUrl;
                $pj->observation = $insertedObservation;
                $pj->save();
            } catch (\Exception $e) {
                Log::error("Erreur d'upload Open Bee (Job Dossier ID: {$dossier->id}): " . $e->getMessage());
                throw $e;
            } finally {
                if ($tempFilePath && file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
                Storage::delete($tempStoragePath);
            }
        }
    }
}
*/

<?php

namespace App\Jobs;

use App\Models\Pj;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadDossierPJsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $dossier;
    public $files;
    public $fileMappings;
    public $typepjLabels;

    public function __construct($dossier, $files, $fileMappings, $typepjLabels)
    {
        $this->dossier = $dossier;
        $this->files = $files;
        $this->fileMappings = $fileMappings;
        $this->typepjLabels = $typepjLabels;
    }

    public function handle()
    {
        // Récupérer le service OpenBee
        $openBee = app('openbee');

        foreach ($this->fileMappings as $fieldName => $typepjId) {

            if (!isset($this->files[$fieldName])) {
                continue;
            }

            $input = $this->files[$fieldName];
            $insertedObservation = $this->typepjLabels[$typepjId] ?? 'أخرى';

            // Cas 1 : fichiers multiples par affaire
            if (is_array($input)) {
                foreach ($input as $affaireId => $uploadedFile) {
                    if (!$uploadedFile instanceof \Illuminate\Http\UploadedFile) continue;
                    $this->uploadAndSave($uploadedFile, $fieldName, $typepjId, $insertedObservation, $affaireId, $openBee);
                }
                continue;
            }

            // Cas 2 : fichier unique
            if ($input instanceof \Illuminate\Http\UploadedFile) {
                $this->uploadAndSave($input, $fieldName, $typepjId, $insertedObservation, null, $openBee);
            }
        }
    }

    private function uploadAndSave($file, $fieldName, $typepjId, $insertedObservation, $affaireId, $openBee)
    {
        $filename = $this->dossier->numero . "_" . $this->dossier->id
            . ($affaireId ? "_$affaireId" : "") . "_" . $fieldName . "."
            . $file->getClientOriginalExtension();

        $filenameSansExt = pathinfo($filename, PATHINFO_FILENAME);
        $path = "OPENBEE/" . $filename;

        try {
            $openBee->deleteIfExists($filenameSansExt);

            $result = $openBee->upload($file, $filename, [
                'title'       => $filename,
                'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                'path'        => config('openbee.path'),
            ]);

            $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
        } catch (\Exception $e) {
            Log::error("Erreur Upload OpenBee (affaire:$affaireId) : " . $e->getMessage());
            $openbeeUrl = null;
        }

        $pj = Pj::firstOrNew([
            'dossier_id' => $this->dossier->id,
            'affaire_id' => $affaireId,
            'typepj_id'  => $typepjId,
        ]);

        $pj->contenu = $path;
        $pj->openbee_url = $openbeeUrl;
        $pj->observation = $insertedObservation;
        $pj->save();
    }
}

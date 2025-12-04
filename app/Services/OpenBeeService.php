<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;

class OpenBeeService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $getDocument;


    public function __construct()
    {
        $this->baseUrl = config('services.openbee.base_url');
        $this->username = config('services.openbee.username');
        $this->password = config('services.openbee.password');
        $this->getDocument = config('services.openbee.get_document');
    }

    /*   public function upload(UploadedFile $file, string $filename, array $options = []): array
{
    $checksum = hash('sha256', file_get_contents($file->getRealPath()));

    $metadata = [
        'title'       => $options['title'] ?? $filename,
        'description' => $options['description'] ?? '',
        'filename'    => $filename,
        'checksum'    => $checksum,
        'overwrite'   => 'true',
    ];

    if (!empty($options['path'])) {
        $metadata['path'] = $options['path'];
    }

    \Log::debug('OpenBee metadata sent:', $metadata);

    $response = Http::withBasicAuth($this->username, $this->password)
        ->attach('file', fopen($file->getRealPath(), 'r'), $filename)
        ->post("{$this->baseUrl}/ws/v2/document", $metadata);

    \Log::debug('⬇️ OpenBee response:', [
        'status' => $response->status(),
        'body'   => $response->body(),
        'headers' => $response->headers(),
    ]);

    if ($response->successful()) {
        $json = $response->json();

        if (is_array($json) && !empty($json)) {
            return $json;
        }

        // 🔁 Fallback : si pas de JSON, prendre l’en-tête Location
        $location = $response->header('Document-Ids');

        if ($location) {
            return [
                //'document_link' => $this->baseUrl.$this->getDocument.$location,
                'document_link' => $location,

            ];
        }

        return [
            'message' => 'Upload succeeded but no data returned',
        ];
    }

    \Log::error("Open Bee upload failed", [
        'status' => $response->status(),
        'body'   => $response->body(),
    ]);

    throw new \Exception("Erreur Open Bee: " . $response->body());
}
*/


    public function upload(UploadedFile $file, string $filename, array $options = []): array

    {
        // Le reste du code reste inchangé car getRealPath() est disponible sur File
        $checksum = hash('sha256', file_get_contents($file->getRealPath()));

        $metadata = [
            'title'       => $options['title'] ?? $filename,
            'description' => $options['description'] ?? '',
            'filename'    => $filename,
            'checksum'    => $checksum,
            'overwrite'   => 'true',
        ];

        if (!empty($options['path'])) {
            $metadata['path'] = $options['path'];
        }

        Log::debug('OpenBee metadata sent:', $metadata);

        $response = Http::withBasicAuth($this->username, $this->password)
            ->attach('file', fopen($file->getRealPath(), 'r'), $filename)
            ->post("{$this->baseUrl}/ws/v2/document", $metadata);

        Log::debug('⬇️ OpenBee response:', [
            'status' => $response->status(),
            'body'   => $response->body(),
            'headers' => $response->headers(),
        ]);

        if ($response->successful()) {
            $json = $response->json();

            if (is_array($json) && !empty($json)) {
                return $json;
            }

            // 🔁 Fallback : si pas de JSON, prendre l’en-tête Location
            $location = $response->header('Document-Ids');

            if ($location) {
                return [
                    'document_link' => $location,
                ];
            }

            return [
                'message' => 'Upload succeeded but no data returned',
            ];
        }

        Log::error("Open Bee upload failed", [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        throw new \Exception("Erreur Open Bee: " . $response->body());
    }

    /*
public function deleteIfExists(string $filename): void
{
    \Log::error("------deleteIfExists--------:Entrer".$filename);

    $response = Http::withBasicAuth($this->username, $this->password)
        ->get("{$this->baseUrl}/ws/v2/search", [
            'name' => $filename,
        ]);

    if ($response->successful()) {
        $results = $response->json()['documents'] ?? [];

        foreach ($results as $document) {
            if (($document['name'] ?? '') === $filename) {
                //$documentId = $document['id'];
                $documentId = $document['idDocument']; 
                \Log::info("***********id doc ***********: {$documentId}");

                $deleteResponse = Http::withBasicAuth($this->username, $this->password)
                    ->delete("{$this->baseUrl}/ws/v2/document/{$documentId}");

                if (!$deleteResponse->successful()) {
                    \Log::warning("⚠️ Impossible de supprimer le document Open Bee avec ID {$documentId}: " . $deleteResponse->body());
                } else {
                    \Log::info("✅ Document supprimé de Open Bee: {$filename}");
                }
            }
        }
    } else {
        \Log::warning("⚠️ Recherche de document échouée dans Open Bee: " . $response->body());
    }
}*/

    public function deleteIfExists(string $filename): void
    {
        \Log::error("------deleteIfExists--------:Entrer " . $filename);

        $response = Http::withBasicAuth($this->username, $this->password)
            ->get("{$this->baseUrl}/ws/v2/search", [
                'name' => $filename,
            ]);

        if ($response->successful()) {
            $results = $response->json()['documents'] ?? [];

            foreach ($results as $item) {
                $document = $item['document'] ?? null;

                if ($document && ($document['name'] ?? '') === $filename) {
                    $documentId = $document['idDocument']; // 

                    $deleteResponse = Http::withBasicAuth($this->username, $this->password)
                        ->delete("{$this->baseUrl}/ws/v2/document/{$documentId}");

                    if (!$deleteResponse->successful()) {
                        \Log::warning("⚠️ Impossible de supprimer le document Open Bee avec ID {$documentId}: " . $deleteResponse->body());
                    } else {
                        \Log::info("✅ Document supprimé de Open Bee: {$filename}");
                    }
                }
            }
        } else {
            \Log::warning("⚠️ Recherche de document échouée dans Open Bee: " . $response->body());
        }
    }
}

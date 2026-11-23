<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    // Lecture limitée aux N derniers octets (évite de charger un laravel.log de 500 Mo)
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const PATTERN = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}:?\d{2})?)\]\s(\w+)\.(\w+):\s?(.*)$/';

    public function files()
    {
        $files = collect(File::glob(storage_path('logs/*.log')))
            ->map(fn($p) => [
                'name'     => basename($p),
                'size'     => filesize($p),
                'modified' => date('Y-m-d H:i:s', filemtime($p)),
            ])
            ->sortByDesc('modified')
            ->values();

        return response()->json($files);
    }

    public function entries(Request $request)
    {
        $path   = $this->resolve($request->query('file', 'laravel.log'));
        $level  = strtolower((string) $request->query('level', ''));
        $search = (string) $request->query('search', '');
        $limit  = min(max((int) $request->query('limit', 500), 1), 5000);

        $entries = array_reverse($this->parse($this->tail($path))); // plus récent d'abord

        if ($search !== '') {
            $entries = array_filter($entries, fn($e) =>
            mb_stripos($e['message'] . ' ' . $e['stack'], $search) !== false);
        }

        $stats = array_count_values(array_column($entries, 'level'));

        if ($level !== '') {
            $entries = array_filter($entries, fn($e) => $e['level'] === $level);
        }

        $entries = array_slice(array_values($entries), 0, $limit);
        foreach ($entries as $i => &$e) {
            $e['id'] = $i;
        }

        return response()->json([
            'entries'   => $entries,
            'stats'     => $stats,
            'size'      => filesize($path),
            'truncated' => filesize($path) > self::MAX_BYTES,
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
    }

    public function download(Request $request)
    {
        $path = $this->resolve($request->query('file', 'laravel.log'));
        return response()->download($path, basename($path));
    }

    public function clear(Request $request)
    {
        $path = $this->resolve($request->query('file', 'laravel.log'));
        file_put_contents($path, '');
        return response()->json(['success' => true]);
    }

    private function resolve(string $file): string
    {
        $path = storage_path('logs/' . basename($file)); // anti path traversal
        abort_unless(str_ends_with($path, '.log') && is_file($path), 404, 'Fichier introuvable');
        return $path;
    }

    private function tail(string $path): string
    {
        $size   = filesize($path);
        $offset = max(0, $size - self::MAX_BYTES);

        $h = fopen($path, 'rb');
        fseek($h, $offset);
        $data = stream_get_contents($h);
        fclose($h);

        // Supprimer la première ligne partielle si on a coupé au milieu
        if ($offset > 0 && ($pos = strpos($data, "\n")) !== false) {
            $data = substr($data, $pos + 1);
        }
        return $data;
    }

    private function parse(string $content): array
    {
        $entries = [];
        $current = null;

        foreach (preg_split('/\r\n|\n/', $content) as $line) {
            if (preg_match(self::PATTERN, $line, $m)) {
                if ($current) $entries[] = $current;
                $current = [
                    'date'    => $m[1],
                    'env'     => $m[2],
                    'level'   => strtolower($m[3]),
                    'message' => $m[4],
                    'stack'   => '',
                ];
            } elseif ($current && $line !== '') {
                $current['stack'] .= $line . "\n";
            }
        }
        if ($current) $entries[] = $current;

        return $entries;
    }
}

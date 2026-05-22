<?php

namespace App\Services;

use App\Models\Course;
use App\Models\VaultFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class VaultIntelligenceService
{
    private const TEXT_EXTENSIONS = [
        'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'yaml', 'yml', 'ini',
        'log', 'sql', 'html', 'css',
    ];

    private const CODE_EXTENSIONS = [
        'php', 'js', 'ts', 'jsx', 'tsx', 'vue', 'py', 'java', 'c', 'cpp', 'cs',
        'go', 'rb', 'rs', 'swift', 'kt', 'kts', 'dart', 'sh', 'bat', 'ps1',
    ];

    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function analyzeUpload(UploadedFile $file, Collection $courses): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $content = $this->extractTextFromPath($file->getRealPath(), $extension);
        $corpus = $this->normalizeText($originalName . "\n" . $content);

        [$course, $courseScore] = $this->detectCourse($corpus, $courses);
        $week = $this->detectWeek($corpus);
        $topic = $this->detectTopic($originalName, $content, $course, $week);

        $confidence = $courseScore;
        $confidence += $week ? 12 : 0;
        $confidence += $topic ? 8 : 0;
        $confidence += trim($content) !== '' ? 6 : 0;
        $confidence = min(98, max(35, $confidence));

        return [
            'course_id' => $course?->id,
            'course_name' => $course?->name,
            'course_code' => $course?->code,
            'course_color' => $course?->color,
            'week' => $week,
            'topic' => $topic,
            'title' => $this->titleFromOriginalName($originalName),
            'confidence' => $confidence,
            'source' => trim($content) !== '' ? 'nama dan isi file' : 'nama file',
            'content_excerpt' => Str::limit($this->cleanPreviewText($content), 180),
        ];
    }

    public function preview(VaultFile $file): array
    {
        return $this->previewStoredFile(
            'public',
            $file->file_path,
            $file->file_name,
            route('vault.files.inline', $file),
            route('vault.files.download', $file)
        );
    }

    public function previewStoredFile(
        string $diskName,
        ?string $filePath,
        ?string $fileName,
        ?string $inlineUrl = null,
        ?string $downloadUrl = null
    ): array {
        $filePath = (string) $filePath;
        $fileName = (string) $fileName;
        $disk = Storage::disk($diskName);
        $exists = $filePath !== '' && $disk->exists($filePath);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $preview = [
            'exists' => $exists,
            'type' => 'unsupported',
            'extension' => $extension,
            'inline_url' => $inlineUrl,
            'download_url' => $downloadUrl,
            'message' => null,
        ];

        if ($filePath === '' || $fileName === '') {
            return array_merge($preview, [
                'type' => 'none',
                'message' => 'Tidak ada file yang dilampirkan.',
            ]);
        }

        if (!$exists) {
            return array_merge($preview, [
                'type' => 'missing',
                'message' => 'File fisik belum tersedia di storage. Data file-nya ada, tapi berkasnya belum ditemukan.',
            ]);
        }

        return $this->previewExistingPath($disk->path($filePath), $extension, $preview);
    }

    private function previewExistingPath(string $absolutePath, string $extension, array $preview): array
    {
        if ($extension === 'pdf') {
            return array_merge($preview, ['type' => 'pdf']);
        }

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return array_merge($preview, ['type' => 'image']);
        }

        if ($extension === 'zip') {
            return array_merge($preview, [
                'type' => 'zip',
                'entries' => $this->zipEntries($absolutePath),
                'inline_previews' => $this->zipInlinePreviews($absolutePath),
                'text_previews' => $this->zipTextPreviews($absolutePath),
            ]);
        }

        if ($extension === 'docx') {
            return array_merge($preview, [
                'type' => 'document',
                'label' => 'Word Document',
                'content' => $this->cleanPreviewText($this->extractDocxText($absolutePath)),
            ]);
        }

        if ($extension === 'pptx') {
            return array_merge($preview, [
                'type' => 'document',
                'label' => 'PowerPoint',
                'content' => $this->cleanPreviewText($this->extractPptxText($absolutePath)),
            ]);
        }

        if ($extension === 'doc') {
            return array_merge($preview, [
                'type' => 'document',
                'label' => 'Word Document',
                'content' => $this->cleanPreviewText($this->extractPrintableText($absolutePath)),
            ]);
        }

        if ($this->isTextLikeExtension($extension)) {
            return array_merge($preview, [
                'type' => in_array($extension, self::CODE_EXTENSIONS, true) ? 'code' : 'text',
                'language' => $this->languageLabel($extension),
                'content' => $this->readTextFile($absolutePath, 120000),
            ]);
        }

        return array_merge($preview, [
            'message' => 'Format ini belum punya preview khusus. File tetap bisa dibuka lewat tombol unduh.',
        ]);
    }

    public function storageDirectory(Course $course, ?int $week, ?string $topic): string
    {
        $courseFolder = Str::slug($course->code ?: $course->name) ?: 'course-' . $course->id;
        $weekFolder = $week ? 'minggu-' . str_pad((string) $week, 2, '0', STR_PAD_LEFT) : 'tanpa-minggu';

        if ($topic) {
            $weekFolder .= '-' . Str::slug($topic);
        }

        return 'vault/' . $courseFolder . '/' . $weekFolder;
    }

    public function safeStoredFileName(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = Str::slug($baseName) ?: 'materi';
        $suffix = now()->format('YmdHis') . '-' . Str::lower(Str::random(6));

        return $safeBaseName . '-' . $suffix . ($extension ? '.' . $extension : '');
    }

    private function detectCourse(string $corpus, Collection $courses): array
    {
        if ($courses->isEmpty()) {
            return [null, 0];
        }

        $bestCourse = null;
        $bestScore = 0;

        foreach ($courses as $course) {
            $score = 0;
            $code = $this->normalizeText($course->code);
            $alphaCode = preg_replace('/\d+/', '', $code);
            $name = $this->normalizeText($course->name);
            $description = $this->normalizeText((string) $course->description);

            if ($code && str_contains($corpus, $code)) {
                $score += 60;
            }

            if ($alphaCode && strlen($alphaCode) >= 3 && str_contains($corpus, $alphaCode)) {
                $score += 35;
            }

            if ($name && str_contains($corpus, $name)) {
                $score += 50;
            }

            foreach ($this->meaningfulWords($name . ' ' . $description) as $word) {
                if (str_contains($corpus, $word)) {
                    $score += strlen($word) >= 7 ? 10 : 6;
                }
            }

            foreach ($this->domainHints($course) as $hint) {
                if (str_contains($corpus, $hint)) {
                    $score += 18;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCourse = $course;
            }
        }

        if (!$bestCourse) {
            return [$courses->first(), 20];
        }

        return [$bestCourse, min(78, max(35, $bestScore))];
    }

    private function detectWeek(string $corpus): ?int
    {
        $patterns = [
            '/\b(?:minggu|week|pertemuan|chapter|bab|modul|materi|sesi)\s*(?:ke)?\s*[-_: #.]?\s*(\d{1,2})\b/i',
            '/\b(?:w|wk|p)\s*[-_]?(\d{1,2})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $corpus, $match)) {
                $week = (int) $match[1];
                return $week >= 1 && $week <= 30 ? $week : null;
            }
        }

        if (preg_match('/\b(?:minggu|week|pertemuan|chapter|bab|modul|sesi)\s+(i{1,3}|iv|v|vi{0,3}|ix|x|xi|xii)\b/i', $corpus, $match)) {
            return $this->romanToInt($match[1]);
        }

        return null;
    }

    private function detectTopic(string $originalName, string $content, ?Course $course, ?int $week): ?string
    {
        if (preg_match('/(?:topik|topic|judul|title)\s*[:\-]\s*([^\r\n]{3,100})/i', $content, $match)) {
            return $this->cleanTopic($match[1], $course, $week);
        }

        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $parts = preg_split('/\s+[-–—]\s+|[_]+/', $baseName) ?: [];
        $candidate = count($parts) > 1 ? end($parts) : $baseName;
        $topic = $this->cleanTopic((string) $candidate, $course, $week);

        if ($topic) {
            return $topic;
        }

        foreach (preg_split('/\R+/', $content) ?: [] as $line) {
            $topic = $this->cleanTopic($line, $course, $week);
            if ($topic) {
                return $topic;
            }
        }

        return null;
    }

    private function cleanTopic(string $candidate, ?Course $course, ?int $week): ?string
    {
        $candidate = pathinfo(trim($candidate), PATHINFO_FILENAME);
        $candidate = preg_replace('/\b(?:minggu|week|pertemuan|chapter|bab|modul|materi|sesi)\s*(?:ke)?\s*[-_: #.]?\s*\d{1,2}\b/i', ' ', $candidate);
        $candidate = preg_replace('/\b(?:slide|slides|materi|kuliah|lecture|course|file|dokumen|document)\b/i', ' ', $candidate);

        if ($week) {
            $candidate = preg_replace('/\b' . preg_quote((string) $week, '/') . '\b/', ' ', $candidate);
        }

        if ($course) {
            $candidate = str_ireplace([$course->code, $course->name], ' ', $candidate);
            foreach ($this->meaningfulWords($course->name) as $word) {
                $candidate = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', ' ', $candidate);
            }
        }

        $candidate = preg_replace('/[^[:alnum:]\s&+.#-]/u', ' ', $candidate);
        $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? '');

        if (strlen($candidate) < 3 || preg_match('/^\d+$/', $candidate)) {
            return null;
        }

        return Str::headline(Str::limit($candidate, 80, ''));
    }

    private function extractTextFromPath(?string $path, string $extension): string
    {
        if (!$path || !is_readable($path)) {
            return '';
        }

        return match ($extension) {
            'pdf' => $this->extractPdfText($path),
            'docx' => $this->extractDocxText($path),
            'pptx' => $this->extractPptxText($path),
            'doc' => $this->extractPrintableText($path),
            'zip' => $this->zipCorpus($path),
            default => $this->isTextLikeExtension($extension) ? $this->readTextFile($path, 80000) : '',
        };
    }

    private function extractDocxText(string $path): string
    {
        return $this->extractOfficeXmlText($path, '/^word\/(document|header\d+|footer\d+)\.xml$/');
    }

    private function extractPptxText(string $path): string
    {
        return $this->extractOfficeXmlText($path, '/^ppt\/slides\/slide\d+\.xml$/');
    }

    private function extractOfficeXmlText(string $path, string $pattern): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $text = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$name || !preg_match($pattern, $name)) {
                continue;
            }

            $xml = $zip->getFromIndex($i);
            if ($xml !== false) {
                $text .= "\n" . $this->textFromXml($xml);
            }
        }

        $zip->close();

        return $text;
    }

    private function extractPdfText(string $path): string
    {
        $raw = file_get_contents($path, false, null, 0, 600000);
        if ($raw === false) {
            return '';
        }

        preg_match_all('/\((?:\\\\.|[^\\\\)]){2,}\)/s', $raw, $matches);
        $strings = array_map(function (string $value): string {
            $value = trim($value, '()');
            $value = str_replace(['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t'], ['(', ')', '\\', ' ', ' ', ' '], $value);
            return $value;
        }, $matches[0] ?? []);

        $text = implode(' ', $strings);
        if (strlen($text) < 40) {
            $text = $this->extractPrintableText($path);
        }

        return $text;
    }

    private function extractPrintableText(string $path): string
    {
        $raw = file_get_contents($path, false, null, 0, 300000);
        if ($raw === false) {
            return '';
        }

        preg_match_all('/[\x20-\x7E]{4,}/', $raw, $matches);

        return implode(' ', array_slice($matches[0] ?? [], 0, 400));
    }

    private function zipCorpus(string $path): string
    {
        $entries = $this->zipEntries($path, 80);
        $previews = $this->zipTextPreviews($path, 3);

        return implode("\n", array_merge(
            array_column($entries, 'name'),
            array_map(fn(array $item) => $item['content'], $previews)
        ));
    }

    private function zipEntries(string $path, int $limit = 150): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $entries = [];
        $count = min($zip->numFiles, $limit);

        for ($i = 0; $i < $count; $i++) {
            $stat = $zip->statIndex($i);
            $name = $zip->getNameIndex($i) ?: '';
            $trimmedName = trim($name, '/');
            $isDir = str_ends_with($name, '/');

            $entries[] = [
                'name' => $trimmedName ?: $name,
                'basename' => basename($trimmedName ?: $name),
                'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION)),
                'is_dir' => $isDir,
                'depth' => $trimmedName === '' ? 0 : substr_count($trimmedName, '/'),
                'size' => $stat['size'] ?? 0,
                'human_size' => $this->humanSize($stat['size'] ?? 0),
            ];
        }

        $zip->close();

        return $entries;
    }

    private function zipTextPreviews(string $path, int $limit = 4): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $previews = [];

        for ($i = 0; $i < $zip->numFiles && count($previews) < $limit; $i++) {
            $name = $zip->getNameIndex($i) ?: '';
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $stat = $zip->statIndex($i);

            if (str_ends_with($name, '/') || !$this->isTextLikeExtension($extension) || ($stat['size'] ?? 0) > 160000) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            $previews[] = [
                'name' => $name,
                'language' => $this->languageLabel($extension),
                'content' => Str::limit($this->normalizeVisibleText($content), 5000),
            ];
        }

        $zip->close();

        return $previews;
    }

    private function zipInlinePreviews(string $path, int $limit = 3): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $previews = [];

        for ($i = 0; $i < $zip->numFiles && count($previews) < $limit; $i++) {
            $name = $zip->getNameIndex($i) ?: '';
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $stat = $zip->statIndex($i);
            $size = (int) ($stat['size'] ?? 0);

            if (str_ends_with($name, '/') || $size <= 0 || $size > 2500000) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            if ($extension === 'pdf') {
                $previews[] = [
                    'type' => 'pdf',
                    'name' => $name,
                    'data_uri' => 'data:application/pdf;base64,' . base64_encode($content),
                ];
                continue;
            }

            if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $mime = match ($extension) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'application/octet-stream',
                };

                $previews[] = [
                    'type' => 'image',
                    'name' => $name,
                    'data_uri' => 'data:' . $mime . ';base64,' . base64_encode($content),
                ];
                continue;
            }

            if ($this->isTextLikeExtension($extension)) {
                $previews[] = [
                    'type' => 'code',
                    'name' => $name,
                    'language' => $this->languageLabel($extension),
                    'content' => Str::limit($this->normalizeVisibleText($content), 9000),
                ];
            }
        }

        $zip->close();

        return $previews;
    }

    private function readTextFile(string $path, int $limit): string
    {
        $content = file_get_contents($path, false, null, 0, $limit + 1);
        if ($content === false) {
            return '';
        }

        $content = $this->normalizeVisibleText($content);

        return strlen($content) > $limit
            ? substr($content, 0, $limit) . "\n\n[Preview dipotong...]"
            : $content;
    }

    private function textFromXml(string $xml): string
    {
        $xml = preg_replace('/<[^>]+>/', ' ', $xml) ?? $xml;

        return html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function cleanPreviewText(string $text): string
    {
        return Str::limit($this->normalizeVisibleText($text), 120000);
    }

    private function normalizeText(string $text): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', $text) ?? ''));
    }

    private function normalizeVisibleText(string $text): string
    {
        $text = str_replace("\0", '', $text);
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        $text = preg_replace('/[^\P{C}\t\r\n]+/u', '', $text) ?? $text;
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function meaningfulWords(string $text): array
    {
        $stopWords = ['dan', 'yang', 'untuk', 'dengan', 'mata', 'kuliah', 'course', 'the', 'and'];
        preg_match_all('/[a-z0-9]{3,}/i', $this->normalizeText($text), $matches);

        return array_values(array_unique(array_filter(
            $matches[0] ?? [],
            fn(string $word) => !in_array($word, $stopWords, true)
        )));
    }

    private function domainHints(Course $course): array
    {
        $name = $this->normalizeText($course->name . ' ' . $course->code);

        if (str_contains($name, 'rekayasa') || str_contains($name, 'rpl')) {
            return ['rpl', 'uml', 'sdlc', 'srs', 'requirement', 'requirements', 'software', 'use case', 'sequence diagram'];
        }

        if (str_contains($name, 'numerik') || str_contains($name, 'metode')) {
            return ['numerik', 'metnum', 'newton', 'raphson', 'bisection', 'galat', 'error', 'interpolasi'];
        }

        if (str_contains($name, 'basis') || str_contains($name, 'data') || str_contains($name, 'basdat')) {
            return ['basdat', 'erd', 'sql', 'normalisasi', 'database', 'relasi', 'query'];
        }

        return [];
    }

    private function titleFromOriginalName(string $originalName): string
    {
        return Str::headline(pathinfo($originalName, PATHINFO_FILENAME));
    }

    private function isTextLikeExtension(string $extension): bool
    {
        return in_array($extension, array_merge(self::TEXT_EXTENSIONS, self::CODE_EXTENSIONS), true);
    }

    private function languageLabel(string $extension): string
    {
        return match ($extension) {
            'php' => 'PHP',
            'js', 'jsx' => 'JavaScript',
            'ts', 'tsx' => 'TypeScript',
            'py' => 'Python',
            'java' => 'Java',
            'md', 'markdown' => 'Markdown',
            'json' => 'JSON',
            'html' => 'HTML',
            'css' => 'CSS',
            'sql' => 'SQL',
            default => strtoupper($extension ?: 'TXT'),
        };
    }

    private function humanSize(int|float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function romanToInt(string $roman): ?int
    {
        $map = [
            'i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5, 'vi' => 6,
            'vii' => 7, 'viii' => 8, 'ix' => 9, 'x' => 10, 'xi' => 11, 'xii' => 12,
        ];

        return $map[strtolower($roman)] ?? null;
    }
}

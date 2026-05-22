<?php

namespace App\Services;

/**
 * Mock AI similarity detector untuk Non-Blocking AI Suggestions.
 *
 * Di production, ini diganti dengan call ke Python service (Scikit-Learn TF-IDF + Cosine).
 * Untuk UX demo, kita pakai deterministic mock yang menghasilkan score realistis.
 */
class AiSimilarityService
{
    /**
     * Hasilkan similarity score (0-100) untuk submission.
     * Mock: deterministic berdasarkan content hash, biar consistent per submission.
     */
    public function detect(string $content, ?int $seed = null): array
    {
        // Deterministic seed dari content
        $seed = $seed ?? crc32(substr($content, 0, 100));
        mt_srand($seed);

        // 60% normal (< 30%), 25% suspicious (30-70%), 15% high (> 70%)
        $roll = mt_rand(1, 100);

        if ($roll <= 60) {
            $score = mt_rand(5, 29);
            $level = 'normal';
        } elseif ($roll <= 85) {
            $score = mt_rand(30, 69);
            $level = 'suspicious';
        } else {
            $score = mt_rand(70, 92);
            $level = 'high';
        }

        mt_srand(); // reset

        return [
            'similarity_score' => $score,
            'confidence_level' => $level, // 'normal' | 'suspicious' | 'high'
            'sources' => $level !== 'normal' ? $this->mockSources($score) : [],
            'highlighted_excerpt' => $level !== 'normal' ? $this->mockExcerpt($content) : null,
        ];
    }

    protected function mockSources(int $score): array
    {
        $sources = [
            ['title' => 'Stack Overflow — "How to implement UML in software design"', 'match' => max(20, $score - 10)],
            ['title' => 'GitHub repository — student-portfolio/uml-template', 'match' => max(10, $score - 25)],
            ['title' => 'Medium article — "UML Diagrams Explained"', 'match' => max(8, $score - 30)],
        ];

        return array_slice($sources, 0, $score >= 70 ? 3 : 2);
    }

    protected function mockExcerpt(string $content): string
    {
        $words = explode(' ', $content);
        $startIdx = min(5, max(0, count($words) - 15));
        return implode(' ', array_slice($words, $startIdx, 15));
    }
}

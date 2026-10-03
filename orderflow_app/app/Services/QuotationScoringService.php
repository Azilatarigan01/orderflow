<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;

class QuotationScoringService
{
    /**
     * Weight definitions (total 100%)
     */
    public const WEIGHT_PRICE = 45;
    public const WEIGHT_DELIVERY = 25;
    public const WEIGHT_WARRANTY = 15;
    public const WEIGHT_RATING = 15;

    /**
     * Calculate and return scored metrics for all quotations of a PR
     *
     * @param PurchaseRequest $pr
     * @return array [
     *    'scored_quotations' => Collection,
     *    'best_score_id' => int|null,
     *    'min_price' => float,
     *    'min_days' => int,
     *    'max_warranty' => int,
     *    'max_rating' => float,
     * ]
     */
    public function evaluateQuotations(PurchaseRequest $pr): array
    {
        $quotations = $pr->quotations()->with('vendor')->get();

        if ($quotations->isEmpty()) {
            return [
                'scored_quotations' => collect(),
                'best_score_id' => null,
                'min_price' => 0,
                'min_days' => 0,
                'max_warranty' => 0,
                'max_rating' => 0,
            ];
        }

        $minPrice = (float) $quotations->min('grand_total');
        $minDays = (int) $quotations->min('estimated_delivery_days');
        if ($minDays <= 0) {
            $minDays = 1;
        }

        $maxWarranty = (int) $quotations->max('warranty_months');
        $maxRating = (float) $quotations->max(fn ($q) => (float) ($q->vendor?->rating ?? 5.0));

        $highestScore = -1.0;
        $bestQuotationId = null;

        foreach ($quotations as $quotation) {
            // 1. Price Score (45%)
            $priceScore = 0.0;
            if ($quotation->grand_total > 0 && $minPrice > 0) {
                $priceScore = ($minPrice / (float) $quotation->grand_total) * self::WEIGHT_PRICE;
            }

            // 2. Delivery Time Score (25%)
            $deliveryScore = 0.0;
            $days = max(1, (int) $quotation->estimated_delivery_days);
            if ($minDays > 0) {
                $deliveryScore = ($minDays / $days) * self::WEIGHT_DELIVERY;
            }

            // 3. Warranty Score (15%)
            $warrantyScore = 0.0;
            if ($maxWarranty > 0) {
                $warrantyScore = ((int) $quotation->warranty_months / $maxWarranty) * self::WEIGHT_WARRANTY;
            } else {
                // If no vendor offers warranty, give neutral baseline
                $warrantyScore = self::WEIGHT_WARRANTY;
            }

            // 4. Vendor Performance / Rating (15%)
            $vendorRating = (float) ($quotation->vendor?->rating ?? 5.0);
            $ratingScore = ($vendorRating / 5.0) * self::WEIGHT_RATING;

            $totalScore = round($priceScore + $deliveryScore + $warrantyScore + $ratingScore, 2);

            // Update database score if different
            if ($quotation->score != $totalScore) {
                $quotation->update(['score' => $totalScore]);
            }

            // Attach dynamic breakdown attributes
            $quotation->score_breakdown = [
                'price' => round($priceScore, 1),
                'delivery' => round($deliveryScore, 1),
                'warranty' => round($warrantyScore, 1),
                'rating' => round($ratingScore, 1),
                'total' => $totalScore,
            ];

            $quotation->is_lowest_price = ($minPrice > 0 && (float) $quotation->grand_total == $minPrice);
            $quotation->is_fastest_delivery = ($minDays > 0 && (int) $quotation->estimated_delivery_days == $minDays);
            $quotation->is_longest_warranty = ($maxWarranty > 0 && (int) $quotation->warranty_months == $maxWarranty);

            if ($totalScore > $highestScore) {
                $highestScore = $totalScore;
            }
        }

        // Deterministic Multi-Tier Tie-Breaker Rule for Corporate Audit Governance:
        // Priority 1: Highest Total Score
        // Priority 2: Lowest Grand Total Price
        // Priority 3: Fastest Lead Time (Delivery Days)
        // Priority 4: Highest Vendor Historical Rating
        // Priority 5: Earliest Submission Timestamp
        $sortedCandidates = $quotations->sort(function ($a, $b) {
            if ($a->score != $b->score) {
                return $a->score < $b->score ? 1 : -1;
            }
            if ($a->grand_total != $b->grand_total) {
                return $a->grand_total > $b->grand_total ? 1 : -1;
            }
            if ($a->estimated_delivery_days != $b->estimated_delivery_days) {
                return $a->estimated_delivery_days > $b->estimated_delivery_days ? 1 : -1;
            }
            $ratingA = (float) ($a->vendor?->rating ?? 5.0);
            $ratingB = (float) ($b->vendor?->rating ?? 5.0);
            if ($ratingA != $ratingB) {
                return $ratingA < $ratingB ? 1 : -1;
            }
            return $a->id > $b->id ? 1 : -1;
        });

        $bestQuotationId = $sortedCandidates->first()?->id;

        // Set best recommendation flag
        foreach ($quotations as $quotation) {
            $quotation->is_recommended = ($quotation->id === $bestQuotationId && $quotations->count() > 1);
        }

        return [
            'scored_quotations' => $quotations,
            'best_score_id' => $bestQuotationId,
            'min_price' => $minPrice,
            'min_days' => $minDays,
            'max_warranty' => $maxWarranty,
            'max_rating' => $maxRating,
            'methodology' => self::getMethodologyDocumentation(),
        ];
    }

    /**
     * Corporate Evaluation Methodology and Mathematical Formula Matrix
     * Used for audit transparency, policy compliance, and UI explanation.
     */
    public static function getMethodologyDocumentation(): array
    {
        return [
            'scale' => '0 - 100 Poin',
            'components' => [
                [
                    'name' => 'Efisiensi Harga (Price Competitiveness)',
                    'weight' => self::WEIGHT_PRICE . '%',
                    'formula' => '(Harga Terendah / Harga Penawaran) × 45 Poin',
                    'principle' => 'Nilai berbanding terbalik: Vendor dengan harga paling kompetitif mendapat poin penuh (45.0), sedangkan harga yang lebih tinggi terdepresiasi proporsional.',
                ],
                [
                    'name' => 'Kecepatan Pengiriman (Lead Time)',
                    'weight' => self::WEIGHT_DELIVERY . '%',
                    'formula' => '(Hari Tercepat / Hari Penawaran) × 25 Poin',
                    'principle' => 'Vendor yang mampu memenuhi barang paling cepat mendapat poin penuh (25.0). Waktu pengiriman lebih lama mendapat skor terdepresiasi.',
                ],
                [
                    'name' => 'Proteksi Garansi & Purna Jual',
                    'weight' => self::WEIGHT_WARRANTY . '%',
                    'formula' => '(Bulan Garansi / Garansi Terpanjang) × 15 Poin',
                    'principle' => 'Vendor dengan komitmen durasi garansi terpanjang mendapat 15.0 poin. Jika seluruh vendor tidak menyertakan garansi, diberikan skor netral baseline.',
                ],
                [
                    'name' => 'Kredibilitas Rekanan (Vendor Rating)',
                    'weight' => self::WEIGHT_RATING . '%',
                    'formula' => '(Rating Vendor / 5.0) × 15 Poin',
                    'principle' => 'Skor performa historis rekanan berdasarkan track record transaksi dan audit QC sebelumnya.',
                ],
            ],
            'tie_breaker_hierarchy' => [
                '1. Total Skor Gabungan Tertinggi',
                '2. Penawaran Harga Terendah (Lowest Price Preference)',
                '3. Waktu Pengiriman Tercepat (Shortest Lead Time)',
                '4. Rating Historis Vendor Tertinggi',
                '5. Waktu Registrasi Penawaran Lebih Awal (First-come First-served Timestamp)',
            ],
        ];
    }
}

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
                $bestQuotationId = $quotation->id;
            }
        }

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
        ];
    }
}

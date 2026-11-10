<?php

namespace App\Services;

use App\Models\PlanSectionDiscount;
use App\Models\Section;
use App\Models\User;

class SectionPricingService
{
    public function __construct(private PricingService $pricing) {}

    /**
     * Calculate the member discount for using a section (applied per use, not yearly).
     *
     * @return array{allowed:bool, reason:?string, plan_id:?int, original_amount:int, discount_amount:int, final_amount:int, discount_type:?string, discount_value:int}
     */
    public function quote(User $user, Section $section, int $baseAmountPence): array
    {
        $baseAmountPence = max(0, $baseAmountPence);

        if (! $section->active) {
            return $this->denied('This section is currently paused.', $baseAmountPence);
        }

        if ($section->isOpenToPublic() && ! $user->hasMemberAccess()) {
            return [
                'allowed' => true,
                'reason' => null,
                'plan_id' => null,
                'original_amount' => $baseAmountPence,
                'discount_amount' => 0,
                'final_amount' => $baseAmountPence,
                'discount_type' => null,
                'discount_value' => 0,
            ];
        }

        if ($section->isMembersOnly() && ! $user->hasMemberAccess()) {
            return $this->denied('This section is available to active members only.', $baseAmountPence);
        }

        $membership = $user->membership;
        $planId = $membership?->plan_id ?: $membership?->membership_category_id;
        if (! $planId) {
            return $this->denied('No active membership plan was found.', $baseAmountPence);
        }

        // Monthly members have access but not member discounts; only yearly members save.
        if (! $user->isAnnualMember()) {
            return [
                'allowed' => true,
                'reason' => null,
                'plan_id' => (int) $planId,
                'original_amount' => $baseAmountPence,
                'discount_amount' => 0,
                'final_amount' => $baseAmountPence,
                'discount_type' => null,
                'discount_value' => 0,
            ];
        }

        $rule = PlanSectionDiscount::query()
            ->where('plan_id', $planId)
            ->where('section_id', $section->id)
            ->where('is_active', true)
            ->first();

        if (! $rule) {
            return [
                'allowed' => true,
                'reason' => null,
                'plan_id' => (int) $planId,
                'original_amount' => $baseAmountPence,
                'discount_amount' => 0,
                'final_amount' => $baseAmountPence,
                'discount_type' => null,
                'discount_value' => 0,
            ];
        }

        $type = $this->pricing->normalizeDiscountType($rule->type);
        $value = (int) $rule->value;
        $final = $this->pricing->applyDiscount($baseAmountPence, $type, $value);
        $discount = max(0, $baseAmountPence - $final);

        return [
            'allowed' => true,
            'reason' => null,
            'plan_id' => (int) $planId,
            'original_amount' => $baseAmountPence,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'discount_type' => $discount > 0 ? $type : null,
            'discount_value' => $discount > 0 ? $value : 0,
        ];
    }

    private function denied(string $reason, int $amount): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
            'plan_id' => null,
            'original_amount' => $amount,
            'discount_amount' => 0,
            'final_amount' => $amount,
            'discount_type' => null,
            'discount_value' => 0,
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| Claim rate matrix – single source of truth
|--------------------------------------------------------------------------
| Read by App\Services\ClaimCalculator (server), the Alpine claimCalculator()
| component (window.CLAIM_RATES) and the admin review pop-up.
|
| type:
|   per_unit        amount = input * rate
|   fixed_threshold flat `amount` when input > `threshold`
|   direct          amount = input (RM on receipt)
|
| layout:
|   standard  title | input | unit | rate | rule | RM | result
|   mileage   title | tier name | rate | input + km | (blank) | RM | result
|
| title / sub: text shown in the first column (null title = blank cell,
|              used for the continuation rows of a group).
| group / label: saved with each claim item (category + description).
*/

$receipt = fn (string $key, string $title, ?string $sub = null) => [
    'key' => $key, 'group' => $title, 'label' => $title,
    'title' => $title, 'sub' => $sub,
    'type' => 'direct', 'layout' => 'standard',
    'unit' => 'RM', 'rate_text' => 'At cost', 'rule' => 'Receipt required',
    'max' => 9999999,
];

$hardship = fn (string $key, string $group, ?string $title, float $rate, int $hours) => [
    'key' => $key, 'group' => $group, 'label' => "> {$hours} hours",
    'title' => $title, 'sub' => null,
    'type' => 'per_unit', 'layout' => 'standard', 'rate' => $rate,
    'unit' => 'day x', 'rate_text' => 'RM '.number_format($rate, 2), 'rule' => "> {$hours} hours per day",
    'max' => 31,
];

// One part of the Travelling claim (Fuel / Toll / Parking / Other). Saved under the "Travelling" category.
$travel = fn (string $key, string $part, ?string $title, bool $text = false) => array_merge(
    $receipt($key, 'Travelling'),
    ['label' => 'Travelling – '.$part, 'title' => $title, 'part' => $part, 'text' => $text]
);

$mileage = fn (string $key, string $group, ?string $title, string $tier, float $rate) => [
    'key' => $key, 'group' => $group, 'label' => $tier,
    'title' => $title, 'sub' => null,
    'type' => 'per_unit', 'layout' => 'mileage', 'rate' => $rate, 'tier' => $tier,
    'unit' => 'km', 'rate_text' => '@ RM '.number_format($rate, 2).' sen/km', 'rule' => '',
    'max' => 1000000,
];

return [

    'timezone' => 'Asia/Kuala_Lumpur',

    'allowed_mimes' => ['pdf', 'jpg', 'jpeg'],
    'max_file_kb' => 10240, // 10 MB per file; no limit on the number of files

    'rows' => [
        // Meal
        [
            'key' => 'meal_days', 'group' => 'Meal Allowance', 'label' => '> 100 km radius',
            'title' => 'Meal Allowance', 'sub' => '> 100 km radius',
            'type' => 'per_unit', 'layout' => 'standard', 'rate' => 45,
            'unit' => 'day x', 'rate_text' => 'RM 45.00', 'rule' => 'per day', 'max' => 31,
        ],

        // Lodging (4 rows)
        [
            'key' => 'lodging_tier1_days', 'group' => 'Lodging Allowance', 'label' => 'Lodging Allowance *1 (without receipts)',
            'title' => 'Lodging Allowance *1', 'sub' => '(without receipts)',
            'type' => 'per_unit', 'layout' => 'standard', 'rate' => 100,
            'unit' => 'day x', 'rate_text' => 'RM 100.00', 'rule' => 'per day', 'max' => 31,
        ],
        [
            'key' => 'lodging_tier2_days', 'group' => 'Lodging Allowance', 'label' => 'Lodging Allowance *2 (without receipts)',
            'title' => 'Lodging Allowance *2', 'sub' => '(without receipts)',
            'type' => 'per_unit', 'layout' => 'standard', 'rate' => 80,
            'unit' => 'day x', 'rate_text' => 'RM 80.00', 'rule' => 'per day', 'max' => 31,
        ],
        [
            'key' => 'lodging_receipt_nights', 'group' => 'Lodging Allowance', 'label' => 'Lodging with receipts',
            'title' => 'Lodging with receipts', 'sub' => null,
            'type' => 'per_unit', 'layout' => 'standard', 'rate' => 200,
            'unit' => 'nights', 'rate_text' => 'Max RM 200.00', 'rule' => 'per night', 'max' => 31,
        ],
        [
            'key' => 'site_work_days', 'group' => 'Lodging Allowance', 'label' => 'Lodging Fixed Allowance',
            'title' => 'Lodging Fixed Allowance', 'sub' => null,
            'type' => 'fixed_threshold', 'layout' => 'standard', 'threshold' => 25, 'amount' => 2800,
            'unit' => 'days', 'rate_text' => 'RM 2,800.00', 'rule' => '> 25 days', 'max' => 31,
        ],

        // Hardship – office
        $hardship('hardship_office_3', 'Hardship (Office Work)', 'Hardship (Office Work)', 20, 3),
        $hardship('hardship_office_5', 'Hardship (Office Work)', null, 35, 5),
        $hardship('hardship_office_8', 'Hardship (Office Work)', null, 70, 8),
        $hardship('hardship_office_12', 'Hardship (Office Work)', null, 100, 12),

        // Hardship – site
        $hardship('hardship_site_3', 'Hardship (Site Work)', 'Hardship (Site Work)', 35, 3),
        $hardship('hardship_site_5', 'Hardship (Site Work)', null, 70, 5),
        $hardship('hardship_site_10', 'Hardship (Site Work)', null, 100, 10),

        // Car mileage
        $mileage('car_first', 'Car Mileage', 'Car', 'First 500 km', 0.75),
        $mileage('car_next', 'Car Mileage', null, 'Next 500 km', 0.65),
        $mileage('car_subsequent', 'Car Mileage', null, 'Subsequent km', 0.55),

        // Motorcycle mileage
        $mileage('moto_first', 'Motorcycle Mileage', 'Motorcycle', '1st 1000 km', 0.55),
        $mileage('moto_subsequent', 'Motorcycle Mileage', null, 'Subsequent km', 0.45),

        // Receipt-backed claims
        $travel('travelling_fuel', 'Fuel', null),
        $travel('travelling_toll', 'Toll', null),
        $travel('travelling_parking', 'Parking', null),
        $travel('travelling_other', 'Other', null, true),
        $receipt('entertainment', 'Entertainment'),
        $receipt('project_purchases', 'Project Purchases'),
        $receipt('accommodation', 'Accommodation'),
        $receipt('insurance_roadtax', 'Insurance & Roadtax'),
        $receipt('vehicle_upkeep', 'Vehicle Upkeep'),
    ],

    // Claims submitted before Travelling was split into parts keep their single Travelling amount.
    // Shown (read-only) in the review pop-ups only; never used by the form or the calculator.
    'legacy_rows' => [
        array_merge($receipt('travelling', 'Travelling'), ['title' => null, 'part' => 'Earlier entry', 'legacy' => true]),
    ],
];

<?php

namespace App\Faker;

use Faker\Provider\Base as BaseProvider;

class UruguayanIdProvider extends BaseProvider
{
    public function uruguayanId()
    {
        $number = $this->generator->numberBetween(1000000, 9999999);
        return $this->generateCedula($number);
    }

    private function generateCedula($number)
    {
        $multipliers = [2, 9, 8, 7, 6, 3, 4];
        $numberArray = array_reverse(str_split($number));
        $sum = 0;

        foreach ($numberArray as $index => $digit) {
            if (isset($multipliers[$index])) {
                $sum += $digit * $multipliers[$index];
            }
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $number . $checkDigit;
    }
}

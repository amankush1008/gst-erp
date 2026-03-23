<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    public static function convert(float $number, string $currency = 'Rupees'): string
    {
        if ($number == 0) return "Zero {$currency} Only";

        $rupees = (int) $number;
        $paise  = round(($number - $rupees) * 100);

        $result = self::inWords($rupees) . " {$currency}";
        if ($paise > 0) {
            $result .= " and " . self::inWords($paise) . " Paise";
        }
        return $result . " Only";
    }

    private static function inWords(int $number): string
    {
        if ($number < 0) return "Minus " . self::inWords(-$number);
        if ($number === 0) return '';
        if ($number < 20) return self::$ones[$number];
        if ($number < 100) {
            return self::$tens[(int)($number / 10)] .
                ($number % 10 ? ' ' . self::$ones[$number % 10] : '');
        }
        if ($number < 1000) {
            return self::$ones[(int)($number / 100)] . ' Hundred' .
                ($number % 100 ? ' ' . self::inWords($number % 100) : '');
        }
        if ($number < 100000) {
            return self::inWords((int)($number / 1000)) . ' Thousand' .
                ($number % 1000 ? ' ' . self::inWords($number % 1000) : '');
        }
        if ($number < 10000000) {
            return self::inWords((int)($number / 100000)) . ' Lakh' .
                ($number % 100000 ? ' ' . self::inWords($number % 100000) : '');
        }
        return self::inWords((int)($number / 10000000)) . ' Crore' .
            ($number % 10000000 ? ' ' . self::inWords($number % 10000000) : '');
    }
}

<?php
namespace App\Helpers;
class Number {
    public static function exp2dec($number) {
        return number_format($number, 8, '.', ',');
    }
    /**
     * calculate line formation from 2 points
     * y = mx + b
     *
     * how to calculate: http://mathworld.wolfram.com/Two-PointForm.html
     */
    public static function lineFormationFromTwoPoints($x1, $y1, $x2, $y2) {
        if (($x2 - $x1) == 0) {
            return null;
        }
        $result['m'] = ($y2 - $y1) / ($x2 - $x1);
        $result['b'] = $x1 * $result['m'] - $y1;
        return $result;
    }
    /**
     * calculate intersection point from 2 lines
     * y = m1x + b1
     * y = m2x + b2
     *
     * how to calculate: http://www.ambrsoft.com/MathCalc/Line/TwoLinesIntersection/TwoLinesIntersection.htm
     */
    public static function calculateCrossPoint($m1, $b1, $m2, $b2) {
        if (($m2 - $m1) == 0) {
            return null;
        }
        $result['x'] = ($b1 - $b2) / ($m2 - $m1);
        $result['y'] = ($m2 * $b1 - $m1 * $b2) / ($m2 - $m1);
        return $result;
    }
    public static function floorDec($value, $decimals=2){
        if ($decimals < 0) { $decimals = 0; }
        $numPointPosition = intval(strpos($value, '.'));
        if ($numPointPosition === 0) { //$value is an integer
            return $value;
        }
        return floatval(substr($value, 0, $numPointPosition + $decimals + 1));
    }
}
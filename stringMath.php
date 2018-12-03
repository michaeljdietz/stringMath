<?php

class stringMath
{
    const OPERATORS = ['+', '-', '*', '/', '^'];

    public static function calculate($text) {
        self::parse($text, $operators, $numbers);
        return self::calculateWithQueue($operators, $numbers);
    }

    protected static function parse($text, &$operators, &$numbers) {
        $operators = $numbers = array();
        $number = null;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            switch ($char) {
                case self::isNumber($char):
                    $number = self::appendNumber($number, $char);
                    break;
                case self::isOperator($char):
                    $numbers[] = $number;
                    $operators[] = $char;
                    $number = null;
                    break;
            }
        }

        if (!is_null($number)) {
            $numbers[] = $number;
        }
    }

    protected static function calculateWithQueue(&$operators, &$numbers) {
        $operatorsBacklog = $numbersBacklog = array();

        if (!count($numbers)) {
            return 0;
        }

        $number1 = $number2 = null;

        while (count($numbers) || count($numbersBacklog)) {
            $number2 = $number1;
            $number1 = end($numbers);

            if (is_null($number2) && count($numbers)) {
                array_pop($numbers);
                continue;
            }

            $operator = array_pop($operators);
            $compare = self::compareOperator($operator, end($operatorsBacklog));

            if ($compare < 0) {
                if (!is_null($operator)) {
                    $operators[] = $operator;
                }

                $operators[] = array_pop($operatorsBacklog);
                $numbers[] = $number2;
                $number1 = array_pop($numbersBacklog);
                continue;
            }

            $compare = self::compareOperator($operator, end($operators));

            if ($compare < 0) {
                $numbersBacklog[] = $number2;
                $operatorsBacklog[] = $operator;
            }

            if ($compare >= 0) {
                $number1 = self::operate($operator, $number1, $number2);
            }

            array_pop($numbers);
        }

        return $number1;
    }

    protected static function calculateWithRecursion(&$operators, &$numbers) {
        if (!count($numbers)) {
            return 0;
        }

        $number1 = $number2 = null;

        while (count($numbers)) {
            $number2 = $number1;
            $number1 = end($numbers);

            if (is_null($number2)) {
                array_pop($numbers);
                continue;
            }

            $operator = array_pop($operators);
            $compare = self::compareOperator($operator, end($operators));

            if ($compare < 0) {
                $number1 = self::calculateWithRecursion($operators, $numbers);
            }

            if ($compare >= 0) {
                array_pop($numbers);
            }

            $number1 = self::operate($operator, $number1, $number2);
        }

        return $number1;
    }

    protected static function operate($operator, $number1, $number2) {
        switch ($operator) {
            case '+':
                return $number1 + $number2;
                break;
            case '-':
                return $number1 - $number2;
                break;
            case '*':
                return $number1 * $number2;
                break;
            case '/':
                return $number1 / $number2;
                break;
            case '^':
                return pow($number1, $number2);
            default:
                return 0;
        }
    }

    protected static function isNumber($char) {
        return ord($char) >= ord('0') && ord($char) <= ord('9');
    }

    protected static function isOperator($char) {
        return in_array($char, self::OPERATORS);
    }

    protected static function appendNumber($number, $char) {
        if (!$number) {
            return $char;
        }

        return $number.$char;
    }

    protected static function compareOperator($operator1, $operator2) {
        if (!self::isOperator($operator1) && !self::isOperator($operator2)) {
            return 0;
        }

        if (!self::isOperator($operator2)) {
            return 1;
        }

        if (!self::isOperator($operator1)) {
            return -1;
        }

        return array_search($operator1, self::OPERATORS) - array_search($operator2, self::OPERATORS);
    }
}

echo stringMath::calculate("1+2").PHP_EOL;
echo stringMath::calculate("1 + 2 + 3").PHP_EOL;
echo stringMath::calculate("3 * 4").PHP_EOL;
echo stringMath::calculate("1 + 2 + 3 * 4").PHP_EOL;
echo stringMath::calculate("-2 * 3 + 4 + 5 * 6 ^2 + 5").PHP_EOL;
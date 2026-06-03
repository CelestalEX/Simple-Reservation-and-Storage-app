<?php

class InputHelper {

    public static function readInt(string $label): int {
        echo $label;
        $input = trim(fgets(STDIN));

        if (!ctype_digit($input)) {
            echo "Wartość musi być liczbą dodatnią.\n";
            return self::readInt($label);
        }

        return (int) $input;
    }

    public static function readString(string $label, bool $allowEmpty = false): ?string {
        echo $label;
        $input = trim(fgets(STDIN));

        if ($input === "" && !$allowEmpty) {
            echo "Pole nie może być puste.\n";
            return self::readString($label, $allowEmpty);
        }

        return $input === "" ? null : $input;
    }
}

?>
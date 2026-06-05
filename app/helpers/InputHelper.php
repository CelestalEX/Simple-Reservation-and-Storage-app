<?php

class InputHelper {

    public static function readInt(string $label, bool $allowEmpty = false): ?int {
      echo $label;
      $input =trim(fgets(STDIN));

      if ($allowEmpty && $input === "") {
        return null;
      }

      if (!is_numeric($input)) {
        echo "Wartość musi być liczbą!\n";
        return self::readInt($label, $allowEmpty);
      }

      return (int) $input;

    }

    public static function readFloat(string $label, bool $allowEmpty = false): ?float {
        echo $label;
        $input = trim(fgets(STDIN));

        if ($allowEmpty && $input === "") {
          return null;
        }

        if (!is_numeric($input) && !$allowEmpty ) {
          echo "Wartość musi być liczbą!\n";
          return self::readFloat($label, $allowEmpty);
        }

        return $input === "" ? null : (float) $input;
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
<?php

class TableRenderer {

    public static function render(array $headers, array $rows): void {

        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], strlen((string)$cell));
            }
        }

        $drawLine = function() use ($widths) {
            echo "+";
            foreach ($widths as $w) {
                echo str_repeat("-", $w + 2) . "+";
            }
            echo "\n";
        };

        $drawLine();
        echo "|";
        foreach ($headers as $i => $header) {
            echo " " . str_pad($header, $widths[$i]) . " |";
        }
        echo "\n";
        $drawLine();

        foreach ($rows as $row) {
            echo "|";
            foreach ($row as $i => $cell) {
                echo " " . str_pad($cell, $widths[$i]) . " |";
            }
            echo "\n";
        }

        $drawLine();
    }
}

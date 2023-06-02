<?php

require_once 'code2Mermaid.php';
function testMermaidStringElementDecor() {
    $testCases = [
        ['state_1 --> func_1', 'state', '-->', '((', '))', 'state_1((state_1)) --> func_1'],
        ['state_1 --> func_1', 'func', '-->', '((', '))', 'state_1 --> func_1'],
        ['state_1 --> state_2 --> state_3', 'state', '-->', '((', '))', 'state_1((state_1)) --> state_2((state_2)) --> state_3((state_3))'],
        ['state_1', 'state', '-->', '((', '))', 'state_1((state_1))'],
        ['state_1', 'func', '-->', '((', '))', 'state_1'],
    ];

    foreach ($testCases as $testCase) {
        $string = $testCase[0];
        $word = $testCase[1];
        $separator = $testCase[2];
        $left = $testCase[3];
        $right = $testCase[4];
        $expectedResult = $testCase[5];

        $result = mermaidStringElementDecor($string, $word, $separator, $left, $right);

        echo "Input: $string, Word: $word, Separator: $separator, Left: $left, Right: $right\n";
        echo "Expected Result: $expectedResult\n";
        echo "Actual Result: $result\n";

        if ($result === $expectedResult) {
            echo "Test Passed!\n";
        } else {
            echo "Test Failed!\n";
        }

        echo "------------------------\n";
    }
}

// Запуск теста
testMermaidStringElementDecor();

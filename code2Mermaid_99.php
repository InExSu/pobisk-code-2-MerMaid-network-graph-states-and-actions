<?php

/**
 * Код php в код диаграммы MerMaid
 * Начиная с $functionName строит диаграмму связей вызовов функций
 * имена функций, начинающиеся с state - помещать в узлы графа
 * имена функций, не начинающиеся с state - помещать на рёбра графа
 *
 * @param string $phpCode = <<<PHP
 * function state_a() {b();}
 * function b() {state_c();}
 * function state_c() {// ...}
 * PHP;
 * @param string $functionName = 'state_a';
 * @return string 'state_a -- b --> state_c'
 */
function codePHP_2_Mermaid(string $phpCode, string $functionName): string
{
    // Извлечь имена функций из кода
    preg_match_all('/function\s+(\w+)\s*\(/', $phpCode, $matches);
    $functionNames = $matches[1];

    // Проверить, существует ли указанная функция
    if (!in_array($functionName, $functionNames)) {
        return '';
    }

    // Фильтровать имена функций в зависимости от префикса
    $nodes = [];
    $edges = [];
    foreach ($functionNames as $name) {
        if (strpos($name, 'state') === 0) {
            $nodes[] = $name;
        } else {
            $edges[] = $name;
        }
    }

    // Построить строку диаграммы Mermaid
    $mermaidDiagram = "{$functionName} -- ";

    // Получить индекс начальной функции
    $startIndex = array_search($functionName, $nodes);

    // Добавить рёбра в диаграмму
    for ($i = $startIndex; $i < count($nodes) - 1; $i++) {
        $mermaidDiagram .= "{$edges[$i]} --> {$nodes[$i + 1]}";
    }

    // Вернуть окончательную строку диаграммы Mermaid
    return $mermaidDiagram;
}

function codePHP_2_Mermaid_Test()
{

    $functionName = 'state_a';
    // Тестовый код
    $phpCode = <<<PHP
function state_a() {b(); d()}
function b() {state_c();}
function state_c() {// ...}
function d() {state_c();}
PHP;

    $result =  codePHP_2_Mermaid($phpCode, $functionName);
    assert($result == 'state_a -- b --> state_c');

    $phpCode = <<<PHP
function state_a() {b(z);}
function b(z) {state_c();}
function state_c() {// ...}
PHP;

    $result =  codePHP_2_Mermaid($phpCode, $functionName);
    echo $result;
    assert($result == 'state_a -- b --> state_c');

}

codePHP_2_Mermaid_Test();
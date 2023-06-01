<?php

/**
 * Собирает содержимое файлов, в том числе по require.
 * Берёт содержимое файла $filePHP.
 * Если в файле есть выражения include, include_once, require, require_once,
 * то собирает содержимое этих файлов.
 * Избегает зацикливания - один и тот же файл, второй раз пропускает
 *
 * @param string $filePHP имя файла, с которого начать собирать содержимое файлов
 * @return string содержимое файлов
 */
function fileName2Code(string $filePHP): string
{
    //TODO  продолжи тестирование

    // Проверяем, существует ли файл
    if (!file_exists($filePHP)) {
        return '';
    }

    // Хранит уже включенные файлы
    static $includedFiles = [];

    // Проверяем, был ли файл уже включен ранее
    if (in_array($filePHP, $includedFiles)) {
        return '';
    }

    // Добавляем текущий файл в список уже включенных
    $includedFiles[] = $filePHP;

    // Читаем содержимое исходного файла
    $code = file_get_contents($filePHP);

    // Поиск выражений include, include_once, require, require_once в исходном файле
    $pattern = '/^(?:require_once|include|require|include_once)\s+["\']([^"\']+)["\'];/m';
    preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

    // Обрабатываем найденные выражения
    foreach ($matches as $match) {
        $includeFile = $matches[0][1];

        // Рекурсивно вызываем функцию fileName2Code для собирания содержимого включаемых файлов
        $includedCode = fileName2Code($includeFile);

        // Заменяем выражение в исходном коде на собранное содержимое
        $code = str_replace($match[0][0], $includedCode, $code);
    }

    return $code;
}

function fileName2Code_Test()
{
    $code = fileName2Code('c2p_Run.php');
}

function regTest()
{
    $code = <<<CODE
require_once 'O:/reqonce.php';
include 'incl.php';
require "req.php";
include_once "z:/inclonce.php";
echo \$mermaidCode;
CODE;

//    $pattern = '/^(?:require_once|include|require|include_once)\s+\['"]([^'"]+)['"];\//m';
    $pattern = '/^(?:require_once|include|require|include_once)\s+["\']([^"\']+)["\'];/m';

    preg_match_all($pattern, $code, $matches);

    $fileNames = $matches[1];
    print_r($fileNames);
}

//regTest();
fileName2Code_Test();
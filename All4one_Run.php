<?php

require_once 'All4One.php';

// Пример использования
$projectPath = '/Users/michaelpopov/PhpstormProjects/Bitrix24-entity-operations';
$outputFile = 'project_Files_All.code';

$exclude = ['.idea', '.git','vendor'];

$phpFiles = getAllPhpFiles($projectPath, $exclude);
combinePhpFiles($phpFiles, $outputFile);

echo "Файлы успешно объединены в {$outputFile}." . PHP_EOL;

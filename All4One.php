<?php

function getAllPhpFiles($dir)
{
    $phpFiles = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
    );

    foreach ($iterator as $path => $fileInfo) {
        if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
            $phpFiles[] = $path;
        }
    }

    return $phpFiles;
}

function combinePhpFiles($files, $outputFile)
{
    $combinedCode = '';

    foreach ($files as $file) {
        $combinedCode .= file_get_contents($file) . PHP_EOL;
    }

    file_put_contents($outputFile, $combinedCode);
}

// Пример использования
$projectPath = 'path/to/your/php/project';
$outputFile = 'path/to/output/combined-file.php';

$phpFiles = getAllPhpFiles($projectPath);
combinePhpFiles($phpFiles, $outputFile);

echo "Файлы успешно объединены в {$outputFile}." . PHP_EOL;

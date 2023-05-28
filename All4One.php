<?php

//function getAllPhpFiles(string $dir): array
//{
//    $phpFiles = [];
//
//    $iterator = new RecursiveIteratorIterator(
//        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
//        RecursiveIteratorIterator::SELF_FIRST,
//        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
//    );
//
//    foreach ($iterator as $path => $fileInfo) {
//        if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
//            $phpFiles[] = $path;
//        }
//    }
//
//    return $phpFiles;
//}

function getAllPhpFiles(string $dir, array $excludedDirectories): array
{
    $phpFiles = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
//            function ($current, $key, $iterator) use ($excludedDirectories) {
                function ($current) use ($excludedDirectories) {
                if ($current->isDir() && in_array($current->getFilename(), $excludedDirectories)) {
                    return false; // Пропустить исключенные каталоги
                }
                return true;
            }
        ),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $path => $fileInfo) {
        if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
            $phpFiles[] = $path;
        }
    }

    return $phpFiles;
}

function combinePhpFiles(array $files, string $outputFile)
{
    $combinedCode = '';

    foreach ($files as $file) {
        $combinedCode .= file_get_contents($file) . PHP_EOL;
    }

    file_put_contents($outputFile, $combinedCode);
}

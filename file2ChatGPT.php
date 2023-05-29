<?php

function prepareFileForChatGPT(string $filename)
{
    // Чтение содержимого файла
    $content = file_get_contents($filename);

    // Сжатие содержимого
    $compressedContent = gzcompress($content);

    // Кодирование сжатого содержимого в base64
    $encodedContent = base64_encode($compressedContent);

    // Команда для помещения в буфер обмена в macOS 13
    $command = 'echo ' . escapeshellarg($encodedContent) . ' | pbcopy';

    // Выполнение команды
    shell_exec($command);

    // Вывод сообщения об успешной подготовке файла
    echo "Файл '$filename' успешно подготовлен и помещен в буфер обмена macOS 13.";
}

prepareFileForChatGPT('c2p.md');
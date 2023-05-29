<?php

function generateFunctionCallGraph($filename): string
{
    $code = file_get_contents($filename);
//    $tokens = token_get_all($code);
    $tokens = executeFunctionWithMemoryLimit('token_get_all', $code, 10);

    $functionCalls = array();

    $currentFunction = '';

    foreach ($tokens as $token) {
        if (is_array($token) && $token[0] === T_FUNCTION) {
            $currentFunction = '';
        }

        if (is_array($token) && $token[0] === T_STRING && $currentFunction === '') {
            $currentFunction = $token[1];
        }

        if (is_array($token) && $token[0] === T_STRING && $currentFunction !== '') {
            $functionCalls[$currentFunction][] = $token[1];
        }
    }

    $mermaidCode = "graph LR;\n";

    foreach ($functionCalls as $function => $calls) {
        $mermaidCode .= "  " . formatFunctionName($function) . "\n";

        foreach ($calls as $call) {
            $mermaidCode .= "  " . formatFunctionName($function) . "-->" . formatFunctionName($call) . "\n";
        }
    }

    return str_replace('graph_php LR;',
        'graph LR;', implode("\n",
            array_unique(explode("\n",
                optimizeMermaidCode($mermaidCode, '_php')))));
}

function formatFunctionName($function)
{
    return str_replace('\\', '\\\\', $function);
}

function executeFunctionWithMemoryLimit(string $functionName, $argument, int $limit)
{
    $currentLimit = ini_get('memory_limit');
    $newLimit = $currentLimit;

    do {

        register_shutdown_function($functionName, $argument);

        $newLimit = (int)$newLimit + $limit . 'M';
        ini_set('memory_limit', $newLimit);

    } while (strpos(
        error_get_last()['message'] ?? '',
        'Allowed memory size') !== false);

//    echo 'Выделил памяти' . ini_get('memory_limit');
//    print_r('Ошибка последняя:' . error_get_last()['message'] ?? '');

    $result = $functionName($argument);

    // Восстановление исходного лимита памяти
    ini_set('memory_limit', $currentLimit);

    return $result;
}

function optimizeMermaidCode(string $mermaidCode, string $suffix): string
{
    $keywords = ['call', 'class', 'click', 'end', 'graph', 'link', 'note', 'participant', 'style', 'subgraph', 'title'];

    foreach ($keywords as $keyword) {
        $pattern = "/(?<!\\w)$keyword(?!\\w)/"; // Match the keyword only when it's not part of a larger word

        $mermaidCode = preg_replace($pattern, $keyword . $suffix, $mermaidCode);
    }

    return $mermaidCode;
}

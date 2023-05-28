<?php

function generateFunctionCallGraph($filename)
{
    $code = file_get_contents($filename);
    $tokens = token_get_all($code);

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

    return $mermaidCode;
}

function formatFunctionName($function)
{
    return str_replace('\\', '\\\\', $function);
}

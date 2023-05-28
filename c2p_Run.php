<?php

require_once 'c2p.php';

$filename = 'project_Files_All.code';
//$filename = 'c2p.php';

$mermaidCode = generateFunctionCallGraph($filename);

echo $mermaidCode;

file_put_contents('c2p.md', $mermaidCode);
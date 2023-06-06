<?php
require_once 'vendor/autoload.php';

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

//if (!isset($argv[1]))
//    echo_Log_Exit("Не указан файл php");
//if (!isset($argv[2]))
//    $argv[2] = '';
//aChain($argv[1], $argv[2]);

/**
 * @param string $fileSource
 * @param string $fileDest
 * @return void
 * @throws Exception
 */
function aChain(string $fileSource, string $fileDest = 'code2Mermaid.md'): void
{
    file_put_contents(
        $fileDest,
        '```mermaid' . PHP_EOL . mermaidElementsCircle(
            mermaidSplit(
                generateMermaidCode(
                    phpCode2AST(
                        file_get_contents(
                            string2File(
                                fileName2Code($fileSource),
                                __DIR__ . '/php.code')))))));
}

/**
 * Строку кода php в AST деревос синтаксическое абстрактное
 * @param string $phpCode
 * @return Stmt[]|null
 */
function phpCode2AST(string $phpCode): ?array
{
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    return $parser->parse($phpCode);
}

/**
 * Mermaid элементы, начинающимся с $word сделать в кружках.
 *
 * @param string $inputString = 'anytext
 *                            state_1 --> func_1
 *                            state_1 --> func2
 *                            func_1 --> stateAnySymbols
 *                            func2 --> stateAnySymbols';
 * @param string $word        = 'state';
 * @param string $separator   = "-->";
 * @return string = 'anytext
 *                            state_1((state_1)) --> func_1
 *                            state_1 --> func2
 *                            func_1 --> stateAnySymbols((stateAnySymbols))
 *                            func2 --> stateAnySymbols';
 */
function mermaidElementsCircle(string $inputString, string $word = 'state', string $separator = '-->'): string
{
    $lines = explode("\n", $inputString); // Разбиваем строку на отдельные строки

    foreach ($lines as &$line) {
        $parts = explode($separator, $line); // Разбиваем каждую строку на две части по указанному разделителю

        if (count($parts) === 2) {
            $source = trim($parts[0]); // Первая часть - исходный элемент
            $target = trim($parts[1]); // Вторая часть - целевой элемент

            if (startsWith($source, $word)) {
                $source = "$source(($source))"; // Добавляем круглые скобки к исходному элементу
            }

            if (startsWith($target, $word)) {
                $target = "$target(($target))"; // Добавляем круглые скобки к целевому элементу
            }

            $line = $source . ' ' . $separator . ' ' . $target; // Формируем новую строку
        }
    }

    return implode("\n", $lines); // Соединяем строки обратно в одну строку и возвращаем результат

}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function startsWith(string $haystack, string $needle): bool
{
    return strpos($haystack, $needle) === 0;
}

/**
 * Mermaid строка - обрамляет элемент, если он начинается с $word
 *
 * @param string $string    = 'state_1 --> func_1';
 * @param string $word      = 'state'
 * @param string $separator = '-->'
 * @param string $left      = '(('
 * @param string $right     = '))'
 * @return string = 'state_1((state_1)) --> func_1';
 */
function mermaidStringElementDecor(string $string,
                                   string $word = 'state',
                                   string $separator = '-->',
                                   string $left = '((',
                                   string $right = '))'): string
{

    $elements = explode($separator, $string);
    $decoratedElements = [];

    foreach ($elements as $element) {
        $trimmedElement = trim($element);

        if (strpos($trimmedElement, $word) === 0) {
            $decoratedElement = $word . $left . $trimmedElement . $right;
        } else {
            $decoratedElement = $trimmedElement;
        }

        $decoratedElements[] = $decoratedElement;
    }

    return implode($separator, $decoratedElements);
}

/**
 * @param array  $astArray
 * @param string $code
 * @return string
 */
function generateMermaidCode(array $astArray, string $code = "graph TD;" . PHP_EOL): string
{

    foreach ($astArray as $node) {
        if ($node instanceof PhpParser\Node\Stmt\Function_) {
            $name = $node->name->name;
            $calls = findFunctionCalls($node->stmts);

            if (!empty($calls)) {
                $code .= "    $name --> " . implode(", ", $calls) . PHP_EOL;
            }
        }
    }

    return $code;
}

/**
 * @param $stmts
 * @return array
 */
function findFunctionCalls($stmts): array
{
    $calls = [];

    foreach ($stmts as $stmt) {
        if ($stmt instanceof PhpParser\Node\Stmt\Expression) {
            $expr = $stmt->expr;
            if ($expr instanceof PhpParser\Node\Expr\FuncCall) {
                $name = $expr->name->toString();
                $calls[] = $name;
            }
        }
    }

    return $calls;
}

//$mermaidAST = ast2Mermaid(phpCode2AST($phpCode));
//$mermaid = mermaidElementsCircle(
//    ast2Mermaid(
//       array2File(
//           phpCode2AST($code))));
//file_put_contents('code2mermaid.md', '```mermaid' . PHP_EOL . $mermaid);

/**
 * Расщепляет строки по признакам.
 *
 * @param string $string    = "any text\nstate_1 --> func_1, word, hz\nstate_2 --> func_3''
 * @param string $arrows    = ' --> '
 * @param string $separator = ','
 * @param string $delimiter = "\n"
 * @return string "any text\nstate_1 --> func_1\nstate_1 --> word\nstate_1 --> hz\nstate_2 --> func_3"
 */
function mermaidSplit(string $string, string $arrows = ' --> ', string $separator = ',', string $delimiter = "\n"): string
{
    $result = '';
    $lines = explode($delimiter, $string); // Разбиваем строку на отдельные строки по указанному разделителю

    foreach ($lines as $line) {
        $parts = explode($arrows, $line); // Разбиваем каждую строку на две части по указанному признаку

        if (count($parts) === 2) {
            $state = trim($parts[0]); // Первая часть - состояние (state)
            $funcs = explode($separator,
                             trim($parts[1])); // Вторая часть - функции (funcs), разбиваем их по указанному разделителю

            foreach ($funcs as $func) {
                $result .= $state . $arrows . trim($func) . $delimiter; // Составляем новую строку, добавляя состояние и каждую функцию
            }
        } else {
            $result .= $line . $delimiter; // Если строка не соответствует формату, оставляем её без изменений
        }
    }

    return rtrim($result, $delimiter); // Удаляем последний разделитель строки и возвращаем результат
}

/**
 * Собирает содержимое файлов, в том числе по require.
 * Берёт содержимое файла $filePath.
 * Если в файле есть выражения include, include_once, require, require_once,
 * то собирает содержимое этих файлов.
 * Избегает зацикливания - один и тот же файл, второй раз пропускает.
 * Делает проверку на существование файла.
 *
 * @param string $filePath
 * @return string содержимое файлов
 * @throws Exception если файл не существует
 */
function fileName2Code(string $filePath): string
{
    // Check if the file path is relative
    if (!isAbsolutePath($filePath)) {
        $currentDirectory = dirname($filePath);
        $filePath = joinPath($currentDirectory, $filePath);
    }

    if (!file_exists($filePath)) {
        return '';
    }

    static $includedFiles = []; // Track included files

    if (in_array($filePath, $includedFiles)) {
        return ''; // File has already been included, return empty string
    }

    $includedFiles[] = $filePath; // Add file to included files
    $content = file_get_contents($filePath); // Read the content of the original file

    $matches = filesIncludes($content);

    foreach ($matches as $match) {

        $pathRequire = $match['path'];

        if (!isAbsolutePath($pathRequire))
            if ($match['dirname'] === '.')
                $pathRequire = joinPath(dirname($filePath), $pathRequire);

        // Recursively call fileName2Code for each referenced file
        $content .= fileName2Code($pathRequire);
    }

    return $content;
}

/**
 * @param $content
 * @return array
 * @author michaelpopov
 * @date   2023-06-05 23:27
 */
function filesIncludes($content): array
{
    // Search for all references to require and include
    $pattern = '/(require(_once)?|include(_once)?)\s*([\'"])(.+?)\4;/';
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $includes = [];

    $commentPattern = '/\/\/.*|\/\*.*\*\//';

    foreach ($matches as $match) {
        // TODO не ловит комментарии

        $lineStartPos = strrpos(substr($content, 0, $match[0][1]), "\n") + 1;
        $lineEndPos = strpos($content, "\n", $match[0][1]);
        $line = substr($content, $lineStartPos, $lineEndPos - $lineStartPos);

        // Check if the line is a comment
        if (preg_match($commentPattern, $line)) {
            continue;
        }

        $type = $match[1][0]; // require, require_once, include, include_once
        $path = $match[5][0]; // path to the file

        // Check if the path starts with __DIR__ and replace it with the actual directory path
        if (strpos($path, '__DIR__') === 0) {
            $path = str_replace('__DIR__', dirname(__FILE__), $path);
        }

        // Extract additional information from the path, if available
        $info = pathinfo($path);
        $dirname = $info['dirname']; // directory path
        $basename = $info['basename']; // file name with extension
        $filename = $info['filename']; // file name without extension
        $extension = $info['extension'] ?? ''; // file extension (if available)

        // Create an array with the extracted information
        $include = [
            'type' => $type,
            'path' => $path,
            'dirname' => $dirname,
            'basename' => $basename,
            'filename' => $filename,
            'extension' => $extension
        ];

        $includes[] = $include;
    }

    return $includes;
}

/**
 * @param $path
 * @return bool
 * @author michaelpopov
 * @date   2023-06-05 23:02
 */
function isAbsolutePath($path): bool
{
    return $path[0] === '/' || preg_match('/^[A-Z]:\\\\/i', $path) === 1;
}

/**
 * @param $directory
 * @param $file
 * @return string
 * @author michaelpopov
 * @date   2023-06-05 23:02
 */
function joinPath($directory, $file): string
{
    return rtrim($directory, DIRECTORY_SEPARATOR) .
        DIRECTORY_SEPARATOR .
        ltrim($file, DIRECTORY_SEPARATOR);
}

/**
 * Сохранит строку в файл и передаст по цепочке
 * или завершит программу
 *
 * @param string $string
 * @param string $file
 * @return string
 * @noinspection PhpInconsistentReturnPointsInspection
 */
function string2File(string $string, string $file): string
{
    if (!file_put_contents($file, $string))
        echo_Log_Exit(__FUNCTION__ . "!file_put_contents($file, $string)");
    else
        return $string;
}

/**
 * Показать, записать, выход.
 *
 * @param string $message
 * @return void
 */
function echo_Log_Exit(string $message): void
{
    echo $message;
    error_log($message);
    exit();
}

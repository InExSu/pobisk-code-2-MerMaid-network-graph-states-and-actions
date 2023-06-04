<?php
require_once 'vendor/autoload.php';

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

/**
 * @return void
 */
function aChain():void {
    file_put_contents(
        'code2Mermaid.md',
        '```mermaid' . PHP_EOL . mermaidElementsCircle(
            mermaidSplit(
                generateMermaidCode(
                    phpCode2AST(
                        file_get_contents(
                            string2File(
                                fileName2Code('file1.php'),
                                'php.code')))))));
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
 * state_1 --> func_1
 * state_1 --> func2
 * func_1 --> stateAnySymbols
 * func2 --> stateAnySymbols';
 * @param string $word = 'state';
 * @param string $separator = "-->";
 * @return string = 'anytext
 * state_1((state_1)) --> func_1
 * state_1 --> func2
 * func_1 --> stateAnySymbols((stateAnySymbols))
 * func2 --> stateAnySymbols';
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
 * @param string $string = 'state_1 --> func_1';
 * @param string $word = 'state'
 * @param string $separator = '-->'
 * @param string $left = '(('
 * @param string $right = '))'
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
 * Расщепляет строку по признакам
 *
 * @param string $string = "any text\nstate_1 --> func_1, word, hz\nstate_2 --> func_3''
 * @param string $arrows = ' --> '
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
            $funcs = explode($separator, trim($parts[1])); // Вторая часть - функции (funcs), разбиваем их по указанному разделителю

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
 * Берёт содержимое файла $filePHP.
 * Если в файле есть выражения include, include_once, require, require_once,
 * то собирает содержимое этих файлов.
 * Избегает зацикливания - один и тот же файл, второй раз пропускает.
 *
 * @param string $filePHP имя файла, с которого начать собирать содержимое файлов
 * @return string содержимое файлов
 */
function fileName2Code(string $filePHP): string
{
    //TODO  продолжи тестирование

    // Проверяем, существует ли файл
    if (!file_exists($filePHP))
        echo_Log_Exit(__FILE__ . ". !file_exists($filePHP)");

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
 * @param string $message
 * @return void
 */
function echo_Log_Exit(string $message): void
{
    echo $message;
    error_log($message);
    exit();
}

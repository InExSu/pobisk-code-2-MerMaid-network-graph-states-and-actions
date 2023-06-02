<?php
require 'vendor/autoload.php'; // Путь к установленной библиотеке php-parser

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

/**
 * @param $phpCode
 * @return Stmt[]|null
 */
function phpCode2AST($phpCode)
{
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    return $parser->parse($phpCode);
}

/**
 * @param $ast
 * @return string
 */
function ast2Mermaid($ast): string
{
    $mermaidCode = "Flowchart TB;\n";

    foreach ($ast as $node) {
        if ($node instanceof PhpParser\Node\Stmt\Function_) {
            $functionName = $node->name->name;
            $stmts = $node->stmts;
            $mermaidCode .= "    $functionName\n";

            foreach ($stmts as $stmt) {
                if ($stmt instanceof PhpParser\Node\Stmt\Expression) {
                    $expr = $stmt->expr;
                    if ($expr instanceof PhpParser\Node\Expr\FuncCall) {
                        $funcName = $expr->name->parts[0];
                        $mermaidCode .= "    $functionName --> $funcName\n";
                    }
                }
            }
        }
    }

    return $mermaidCode;
}

/**
 * Mermaid элементы, начинающимся с $word сделать в кружках.
 *
 * @param string $inputString = 'state_1 --> func_1
 * state_1 --> func2
 * func_1 --> stateAnySymbols
 * func2 --> stateAnySymbols';
 * @param string $word = 'state';
 * @param string $separator = "-->";
 * @return string = 'state_1((state_1)) --> func_1
 * state_1 --> func2
 * func_1 --> stateAnySymbols((stateAnySymbols))
 * func2 --> stateAnySymbols';
 */
function mermaidElementsCircle(string $inputString, string $word = 'state', string $separator = '-->'): string
{
    // TODO рефакторинг!
    if (strpos($inputString, "\n") !== false) {

        $lines = explode("\n", $inputString);
        $outputLines = [];

        foreach ($lines as $line) {
            if (strpos($line, $word) !== false) {
                if (strpos($line, $separator) !== false) {

                    $outputLines[] = mermaidStringElementDecor($line);

                } else {
                    $outputLines[] = $line;
                }
            } else {
                $outputLines[] = $line;
            }
        }

        return implode("\n", $outputLines);
    }
    return $inputString;
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

// Пример использования
$phpCode = <<<PHP
<?php
function state_a() {
b(); 
//d();
}
function b() {
state_c();
}
function state_c() {
// ...
//}
}
// function d() {state_c();}
state_a();
PHP;

$code = <<<PHP
<?php
function state_1()
{
    func_1();
    func_2();
}

function state_2()
{
    func_3();
}

function state_3()
{
    func_4();
}

function state_4()
{
    func_5();
}

function state_5()
{

}

function func_1()
{
    state_2();
}

function func_2()
{
    state_3();
}

function func_3()
{
    state_4();
}

function func_4()
{
    state_4();
}

function func_5()
{
    state_5();
}
state_1();
PHP;

//$mermaidAST = ast2Mermaid(phpCode2AST($phpCode));
$mermaidAST = mermaidElementsCircle(
    ast2Mermaid(
        phpCode2AST($code)));
echo $mermaidAST;
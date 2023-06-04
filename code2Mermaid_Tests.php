<?php

require_once 'code2Mermaid.php';
function MermaidStringElementDecor_Test()
{
    $testCases = [
        ['state_1 --> func_1', 'state', '-->', '((', '))', 'state_1((state_1)) --> func_1'],
        ['state_1 --> func_1', 'func', '-->', '((', '))', 'state_1 --> func_1'],
        ['state_1 --> state_2 --> state_3', 'state', '-->', '((', '))', 'state_1((state_1)) --> state_2((state_2)) --> state_3((state_3))'],
        ['state_1', 'state', '-->', '((', '))', 'state_1((state_1))'],
        ['state_1', 'func', '-->', '((', '))', 'state_1'],
    ];

    foreach ($testCases as $testCase) {
        $string = $testCase[0];
        $word = $testCase[1];
        $separator = $testCase[2];
        $left = $testCase[3];
        $right = $testCase[4];
        $expectedResult = $testCase[5];

        $result = mermaidStringElementDecor($string, $word, $separator, $left, $right);

//        echo "Input: $string, Word: $word, Separator: $separator, Left: $left, Right: $right\n";
//        echo "Expected Result: $expectedResult\n";
//        echo "Actual Result: $result\n";

        assert($result === $expectedResult);
    }
}


/**
 * @return void
 */
function regTest()
{
    $code = <<<CODE
require_once 'O:/reqonce.php';
include 'incl.php';
require "req.php";
include_once "z:/inclonce.php";
echo \$mermaidCode;
CODE;

//    $pattern = '/^(?:require_once|include|require|include_once)\s+\['"]([^'"]+)['"];\//m';
    $pattern = '/^(?:require_once|include|require|include_once)\s+["\']([^"\']+)["\'];/m';

    preg_match_all($pattern, $code, $matches);

    $fileNames = $matches[1];
    print_r($fileNames);
}

/**
 * @return void
 */
function fileName2Code_Test()
{
    file_put_contents('from1file.code',
        fileName2Code('/Users/michaelpopov/PhpstormProjects/Bitrix24-entity-operations/aTasks_IN_Work.php'));
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
//state_1();
PHP;

//regTest();
//fileName2Code_Test();
//MermaidStringElementDecor_Test();

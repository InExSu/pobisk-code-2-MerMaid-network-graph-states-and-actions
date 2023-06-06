<?php /** @noinspection PhpUnused */

require_once 'code2Mermaid.php';
/**
 * @return void
 */
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
 * @throws Exception
 */
function fileName2Code_Test()
{
//    file_put_contents('from1file.code',
//                      fileName2Code('/Users/michaelpopov/PhpstormProjects/Bitrix24-entity-operations/aTasks_IN_Work.php'));
    file_put_contents('from1file.code',
                      fileName2Code("/Users/michaelpopov/PhpstormProjects/pobisk-code-2-MerMaid-network-graph-states-and-actions/testFiles/file1.php"));
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

/**
 * @return void
 * @throws Exception
 */
function aChain_Test()
{
    aChain(__DIR__ . '/testFiles/file1.php');
}

/**
 * @return void
 * @author michaelpopov
 * @date   2023-06-05 23:36
 */
function filesIncludes_Test()
{
    // Test case 1
    $content1 = '
        <?php
//        require "path/to/file.php";
//        include_once \'path/to/another_file.php\';
//        require_once "path/to/include.php";
//        include "path/to/file_with_extension.html";
        require_once __DIR__ . \'/file3.php\';
        ';

    $result1 = filesIncludes($content1);

    echo "Test case 1:\n";
    filesIncludes_TestCase($result1);

    // Test case 2
    $content2 = '
        <?php
        require_once "path/to/include.php";
        ';

    $result2 = filesIncludes($content2);

    echo "Test case 2:\n";
    filesIncludes_TestCase($result2);
}

/**
 * @param $result1
 * @author michaelpopov
 * @date   2023-06-05 23:36
 */
function filesIncludes_TestCase($result1)
{
    foreach ($result1 as $include) {
        echo "Type: {$include['type']}\n";
        echo "Path: {$include['path']}\n";
        echo "Directory: {$include['dirname']}\n";
        echo "Filename with extension: {$include['basename']}\n";
        echo "Filename without extension: {$include['filename']}\n";
        echo "Extension: {$include['extension']}\n";
        echo "--------\n";
    }
}

// Run the test
filesIncludes_Test();
//aChain_Test();
//regTest();
//fileName2Code_Test();
//MermaidStringElementDecor_Test();
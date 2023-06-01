<?php

declare(strict_types=1);

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
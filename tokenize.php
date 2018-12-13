<?php

const TOKENS = [
    T_VARIABLE,
    T_SWITCH,
    T_CASE,
    T_ARRAY,
    T_FUNCTION,
    T_IF,
    T_ELSE,
    T_ENDIF,
    T_FOR,
    T_ENDFOR,
    T_FOREACH,
    T_ENDFOREACH,
    T_DO,
    T_WHILE,
    T_IS_EQUAL,
    T_IS_GREATER_OR_EQUAL,
    T_ISSET,
    T_IS_NOT_EQUAL,
    T_IS_NOT_IDENTICAL,
    T_IS_SMALLER_OR_EQUAL,
    T_LOGICAL_AND,
    T_LOGICAL_OR,
    T_OBJECT_OPERATOR,
    T_TRY,
    T_THROW,
    T_CATCH,
    T_BOOLEAN_AND,
    T_BOOLEAN_OR,
    T_CONCAT_EQUAL,
    T_DOUBLE_ARROW,
    T_EMPTY,
    T_RETURN,
];


$riskyFiles = scandir('samples/risky');
foreach ($riskyFiles as $riskyFile) {
    if($riskyFile == '.' || $riskyFile == '..') {
        continue;
    }

    tokenize('samples/risky/'.$riskyFile , 'risky');
}

$unknownFiles = scandir('samples/unknown');
foreach ($unknownFiles as $unknownFile) {
    if($unknownFile == '.' || $unknownFile == '..') {
        continue;
    }

    tokenize('samples/unknown/'.$unknownFile , 'unknown');
}

$safeFiles = scandir('samples/safe');
foreach ($safeFiles as $safeFile) {
    if($safeFile == '.' || $safeFile == '..') {
        continue;
    }

    tokenize('samples/safe/'.$safeFile , 'safe');
}



function tokenize($file, $codeSafety)
{
    $codeBlocks = [];

// Init headers
    foreach (TOKENS as $token) {
        $codeBlocks[0][] = token_name($token);
    }
    $codeBlocks[0][] = 'risky';
    $codeBlocks[0][] = 'safe';
    $codeBlocks[0][] = 'unknown';

    $i = 1;
    $codeBlocks[$i] = [];

    foreach (TOKENS as $token) {
        $codeBlocks[$i][$token] = 0;
    }

    switch($codeSafety) {
        case 'risky':
            $codeBlocks[$i][] = 1;
            $codeBlocks[$i][] = 0;
            $codeBlocks[$i][] = 0;
            break;
        case 'safe':
            $codeBlocks[$i][] = 0;
            $codeBlocks[$i][] = 1;
            $codeBlocks[$i][] = 0;
            break;
        case 'unknown':
            $codeBlocks[$i][] = 0;
            $codeBlocks[$i][] = 0;
            $codeBlocks[$i][] = 1;
            break;
    }

    // Get all relevant PHP tokens
    $tokens = getTokens($file);

    // Stores tokens info in blocks
    foreach ($tokens as $token) {
        // Increment token count for concerned line
        $codeBlocks[$i][$token[0]]++;
    }

    @mkdir('results/'.$codeSafety, 0777, true);

    $resultFile = fopen('results/' . $codeSafety . '/' . basename($file) . '.csv', 'w');


    foreach ($codeBlocks as $codeBlock) {
        fputcsv($resultFile, $codeBlock);
    }

    fclose($resultFile);

}



function getTokens($file) {
    $result = [];

    $tokens = token_get_all(file_get_contents($file));

    foreach ($tokens as $token) {
        if (is_array($token) && in_array($token[0], TOKENS)) {
            $result[] = $token;
        }
    }

    return $result;
}
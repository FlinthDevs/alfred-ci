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


$previsionFiles = scandir('previsions/');
foreach ($previsionFiles as $previsionFile) {
    if($previsionFile == '.' || $previsionFile == '..') {
        continue;
    }

    prevision('previsions/'.$previsionFile);
}


function prevision($file)
{
    $codeBlocks = [];

    // Init headers
    foreach (TOKENS as $token) {
        $codeBlocks[0][] = token_name($token);
    }

    $i = 1;
    $codeBlocks[$i] = [];

    foreach (TOKENS as $token) {
        $codeBlocks[$i][$token] = 0;
    }

    // Get all relevant PHP tokens
    $tokens = getTokens($file);

    // Stores tokens info in blocks
    foreach ($tokens as $token) {
        // Increment token count for concerned line
        $codeBlocks[$i][$token[0]]++;
    }

    @mkdir('results/prevision', 0777, true);

    $resultFile = fopen('results/prevision/' . basename($file) . '.csv', 'w');

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
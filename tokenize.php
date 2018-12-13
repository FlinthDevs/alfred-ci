<?php

const TOKENS = [
    T_VARIABLE,
    T_ARRAY,
    T_IF,
    T_SWITCH,
    T_ELSE,
    T_ENDIF,
    T_FOR,
    T_ENDFOR,
    T_FOREACH,
    T_ENDFOREACH,
    T_DO,
    T_WHILE,
    T_ENDWHILE,
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
    T_COMMENT,
    T_CONCAT_EQUAL,
    T_DOUBLE_ARROW,
    T_EMPTY,
    T_RETURN,
    T_ECHO,
    T_PRINT
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
    $codeBLocks = [];
    $blockLength = 9;

// Init headers
    foreach (TOKENS as $token) {
        $codeBLocks[0][] = token_name($token);
    }
    $codeBLocks[0][] = 'risky';
    $codeBLocks[0][] = 'safe';
    $codeBLocks[0][] = 'unknown';

    // Get all relevant PHP tokens
    $tokens = getTokens($file);

// Stores tokens info in blocks
    foreach ($tokens as $token) {
        for ($i = $token[2] - $blockLength; $i < $token[2] + $blockLength; $i++) {
            // skip negatives indexes
            if ($i <= 0) {
                continue;
            }

            // Init line array if needed
            if (empty($codeBLocks[$i])) {
                $codeBLocks[$i] = [];
                foreach (TOKENS as $tokenId) {
                    $codeBLocks[$i][$tokenId] = 0;
                }
                switch($codeSafety) {
                    case 'risky':
                        $codeBLocks[$i][] = 1;
                        $codeBLocks[$i][] = 0;
                        $codeBLocks[$i][] = 0;
                        break;
                    case 'safe':
                        $codeBLocks[$i][] = 0;
                        $codeBLocks[$i][] = 1;
                        $codeBLocks[$i][] = 0;
                        break;
                    case 'unknown':
                        $codeBLocks[$i][] = 0;
                        $codeBLocks[$i][] = 0;
                        $codeBLocks[$i][] = 1;
                        break;
                }
            }

            // Increment token count for concerned line
            $codeBLocks[$i][$token[0]]++;
        }
    }

    $result = array_slice($codeBLocks, 0, count($codeBLocks) - $blockLength);

    @mkdir('results/'.$codeSafety, 0777, true);

    $resultFile = fopen('results/' . $codeSafety . '/' . basename($file) . '.csv', 'w');

    foreach ($result as $res) {
        fputcsv($resultFile, $res);
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
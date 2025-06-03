<?php

/**
 * @param $sec
 * @return string
 */
function formatTime($sec): string
{
    $mins = floor($sec / 60);
    $secs = floor($sec % 60);
    return sprintf("%d:%02d", $mins, $secs);
}

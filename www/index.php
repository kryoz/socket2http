<?php
$secondsToDelay = 5;
sleep($secondsToDelay);
?>
I'm a webpage.
I have slept for <?=$secondsToDelay?> seconds, now I can respond!
Your request was <?=print_r($_REQUEST)?>
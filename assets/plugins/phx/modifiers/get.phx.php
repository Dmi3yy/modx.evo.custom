<?php

// Returns the GET parameter which has been posted as a query string
// It takes the paramater name as an argument
// e.g. a page with URL of http://www.whatever.com/modx?paramname=rckt
// [*phx:get=`paramname`*] returns "rckt"

return $_GET[$options];

?>
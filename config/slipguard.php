<?php

return [

    /*
    |-----------------------------------------------------------------
    | Betting Slip Limits
    |-----------------------------------------------------------------
    |
    | Bounds enforced when a customer builds a betting slip manually.
    | Kept configurable rather than hard-coded per leg validation.
    |
    */

    'min_legs' => 1,

    'max_legs' => 20,

];

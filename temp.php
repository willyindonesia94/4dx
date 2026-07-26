<?php
echo json_encode(App\Models\BreakdownWig::with("unit")->whereHas("unit", function($q) { $q->where("type", "UID"); })->get());


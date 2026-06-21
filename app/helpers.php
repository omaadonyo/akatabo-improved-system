<?php

use App\Models\Business;

if (! function_exists('activeBusiness')) {
    function activeBusiness(): ?Business
    {
        $id = session('active_business_id', auth()->user()?->business_id);
        return $id ? Business::find($id) : null;
    }
}

if (! function_exists('activeBusinessId')) {
    function activeBusinessId(): int|null
    {
        return session('active_business_id', auth()->user()?->business_id);
    }
}

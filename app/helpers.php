<?php

if (!function_exists('showPrices')) {
    /**
     * Check if prices should be displayed in the UI
     * 
     * @return bool
     */
    function showPrices(): bool
    {
        return config('app.show_prices', false);
    }
}


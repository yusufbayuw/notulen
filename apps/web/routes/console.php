<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('notulen:about', function () {
    $this->info('Notulen AI — development scaffold');
});

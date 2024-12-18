<?php

namespace App\Provider;

interface ProviderAdapterInterface
{
    public function fetchTasks(): array;
}
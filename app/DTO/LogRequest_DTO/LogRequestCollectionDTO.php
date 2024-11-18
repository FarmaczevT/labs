<?php

namespace App\DTO\LogRequest_DTO;

use Illuminate\Support\Collection;

class LogRequestCollectionDTO
{
    public Collection $logs;

    public function __construct(Collection $logs)
    {
        $this->logs = $logs->map(fn ($log) => new LogRequestDTO($log->toArray()));
    }

    public function toArray(): array
    {
        return $this->logs->toArray();
    }
}
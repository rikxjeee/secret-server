<?php

namespace App\Service;

use App\Entity\Secret;
use Symfony\Component\HttpFoundation\Response;

interface ResponseBuilder
{
    public function supports(string $type): bool;

    public function buildResponse(Secret $secret): Response;

    public function buildErrorResponse(string $message, int $status): Response;
}

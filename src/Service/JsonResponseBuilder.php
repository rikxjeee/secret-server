<?php

namespace App\Service;

use App\Entity\Secret;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseBuilder implements ResponseBuilder
{
    public function supports(string $type): bool
    {
        return $type === 'application/json';
    }

    public function buildResponse(Secret $secret): Response
    {
        return new JsonResponse([
            'secret' => $secret->getSecret(),
            'hash' => $secret->getHash(),
            'createdAt' => $secret->getCreatedAt()->format('Y-m-d\TH:i:s.v\Z'),
            'expiresAt' => $secret->getCreatedAt()->modify('+' . $secret->getExpireAfter() . 'days')->format('Y-m-d\TH:i:s.v\Z'),
            'remainingViews' => $secret->getExpireAfterViews()
        ]);
    }

    public function buildErrorResponse(string $message, int $status): Response
    {
        return new JsonResponse(['message' => $message], $status);
    }
}

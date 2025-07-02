<?php

namespace App\Service;

use App\Entity\Secret;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResponseBuilderChain
{
    /**
     * @var ResponseBuilder[]
     */
    private array $builders;

    public function addBuilder(ResponseBuilder $builder): void
    {
        $this->builders[] = $builder;
    }

    public function build(?Secret $secret, string $type): Response
    {
        try {
            if ($secret === null) {
                return $this->getBuilder($type)->buildErrorResponse('Secret not found or expired', Response::HTTP_NOT_FOUND);
            }

            return $this->getBuilder($type)->buildResponse($secret);
        } catch (HttpException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_NOT_IMPLEMENTED);
        }
    }

    private function getBuilder(string $type): ResponseBuilder
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($type)) {
                return $builder;
            }
        }

        throw new HttpException(501, 'Unsupported response type: ' . $type);
    }
}

<?php

namespace App\Service;

use App\Component\HttpFoundation\XmlResponse;
use App\Entity\Secret;
use Symfony\Component\HttpFoundation\Response;

class XmlResponseBuilder implements ResponseBuilder
{
    private const string RESPONSE_XML = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <Secret>
            <hash>%s</hash>
            <secretText>%s</secretText>
            <createdAt>%s</createdAt>
            <expiresAt>%s</expiresAt>
            <remainingViews>%s</remainingViews>
        </Secret>
        XML;

    private const string ERROR_RESPONSE_XML = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <Error>
            <message>%s</message>
        </Error>
        XML;

    public function supports(string $type): bool
    {
        return $type === 'application/xml';
    }

    public function buildResponse(Secret $secret): Response
    {
        $data = sprintf(
            self::RESPONSE_XML,
            $secret->getHash(),
            $secret->getSecret(),
            $secret->getCreatedAt()->format('Y-m-d\TH:i:s.v\Z'),
            $secret->getCreatedAt()->modify('+' . $secret->getExpireAfter() . 'days')->format('Y-m-d\TH:i:s.v\Z'),
            $secret->getExpireAfterViews()
        );

        return new XmlResponse($data);
    }

    public function buildErrorResponse(string $message, int $status): Response
    {
        return new XmlResponse(sprintf(self::ERROR_RESPONSE_XML, $message), $status);
    }
}

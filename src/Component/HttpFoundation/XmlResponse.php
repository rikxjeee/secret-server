<?php

namespace App\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

class XmlResponse extends Response
{
    public function __construct(?string $content = '', int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, array_merge($headers, [
            'Content-Type' => 'application/xml',
        ]));
    }
}

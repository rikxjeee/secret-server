<?php

namespace App\Controller;

use App\Entity\Secret;
use App\Repository\SecretRepository;
use App\Service\ResponseBuilderChain;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

final class SecretController extends AbstractController
{
    private const string BASE_PATH = '/v1';

    public function __construct(
        private readonly ResponseBuilderChain $responseBuilder,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route(self::BASE_PATH . '/secret', name: 'new_secret', methods: ['POST'])]
    public function index(Request $request, ValidatorInterface $validator): Response
    {
        try {
            $secret = new Secret();
            $now = new DateTimeImmutable();
            $secret->setCreatedAt($now);
            $secret->setSecret(trim($request->request->get('secret')));
            $secret->setExpireAfter($request->request->get('expireAfter'));
            $secret->setExpireAfterViews($request->request->get('expireAfterViews'));
            $secret->setHash(hash('sha256', $request->request->get('secret') . $now->getTimestamp()));

            $errors = $validator->validate($secret);
            if (count($errors) > 0) {
                throw new BadRequestException($errors);
            }

            $this->entityManager->persist($secret);
            $this->entityManager->flush();
        } catch (Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $type = $request->getAcceptableContentTypes();

        return $this->responseBuilder->build($secret, array_shift($type));
    }

    #[Route(self::BASE_PATH . '/secret/{hash}', name: 'find_secret_by_hash', methods: ['GET'])]
    public function findByHash($hash, SecretRepository $repository, Request $request): Response
    {
        $secret = $repository->findValidSecret($hash);
        $this->updateRemainingViews($secret);
        $type = $request->getAcceptableContentTypes();

        return $this->responseBuilder->build($secret, array_shift($type));
    }

    private function updateRemainingViews(?Secret $secret): void
    {
        if ($secret === null) {
            return;
        }

        $secret->setExpireAfterViews($secret->getExpireAfterViews() - 1);
        $this->entityManager->persist($secret);
        $this->entityManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ExchangeRateDayRequest;
use App\Dto\ExchangeRateRequest;
use App\Service\ExchangeRateService;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/rates')]
final class ExchangeRatesController extends AbstractController
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/last-24h', methods: ['GET'])]
    public function getLast24Hours(Request $request): JsonResponse
    {
        $dto = new ExchangeRateRequest();
        $dto->pair = $request->query->get('pair', '');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return $this->json(['error' => (string) $violations[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = $this->exchangeRateService->getRatesForLast24Hours(
                $dto->getFromCurrency(),
                $dto->getToCurrency()
            );

            return $this->json([
                'pair'   => $dto->pair,
                'period' => 'last-24h',
                'data'   => $data
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch rates for last 24h', [
                'pair' => $dto->pair,
                'error' => $e->getMessage()
            ]);

            return $this->json(['error' => 'Unable to fetch exchange rates'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/day', methods: ['GET'])]
    public function getDay(Request $request): JsonResponse
    {
        $dto = new ExchangeRateDayRequest();
        $dto->pair = $request->query->get('pair', '');
        $dto->date = $request->query->get('date', '');

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return $this->json(['error' => (string) $violations[0]->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $targetDate = new DateTimeImmutable($dto->date);

            $data = $this->exchangeRateService->getRatesForDay(
                $dto->getFromCurrency(),
                $dto->getToCurrency(),
                $targetDate
            );

            return $this->json([
                'pair' => $dto->pair,
                'period' => $dto->date,
                'data' => $data
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch rates for specific day', [
                'pair' => $dto->pair,
                'date' => $dto->date,
                'error' => $e->getMessage()
            ]);

            return $this->json(['error' => 'Unable to fetch exchange rates'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
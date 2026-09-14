<?php

namespace App\Services\State\Processor\PageView;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\PageViewRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class PageViewLogProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PageViewRepository $pageViewRepository,
        private readonly RequestStack $requestStack,
        private readonly string $appSecret,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Response
    {
        if (!$data instanceof PageViewLogDto) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();
        $ipAddress = $request?->getClientIp() ?? '';
        $userAgent = $request?->headers->get('User-Agent') ?? '';

        $this->pageViewRepository->logView($data->path, $this->computeVisitorHash($ipAddress, $userAgent));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Hash journalier anonymisé (IP + User-Agent + jour + secret applicatif) : deux vues
     * du même visiteur le même jour partagent le même hash (=> "visiteur unique" du jour),
     * mais l'IP et le User-Agent en clair ne sont jamais persistés.
     */
    private function computeVisitorHash(string $ipAddress, string $userAgent): string
    {
        $day = (new \DateTimeImmutable())->format('Y-m-d');

        return hash('sha256', $ipAddress.'|'.$userAgent.'|'.$day.'|'.$this->appSecret);
    }
}
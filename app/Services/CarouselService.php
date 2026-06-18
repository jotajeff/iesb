<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CarouselRepository;

final class CarouselService
{
    public function __construct(
        private readonly CarouselRepository $repository = new CarouselRepository(),
    ) {
    }

    public function carousels(): array
    {
        return $this->repository->list();
    }

    public function carouselsAtivos(): array
    {
        return $this->repository->listAtivos();
    }

    public function findCarousel(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function carouselItems(int $idCarousel): array
    {
        return $this->repository->findItemsByCarouselId($idCarousel);
    }

    public function saveCarousel(array $data): int
    {
        return $this->repository->save($data);
    }

    public function saveCarouselItem(array $data): int
    {
        return $this->repository->saveItem($data);
    }

    public function deleteCarouselItem(int $id): void
    {
        $this->repository->deleteItem($id);
    }
}

<?php
/**
 * Event service.
 */

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class EventService.
 */
class EventService implements EventServiceInterface
{
    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Event repository.
     */
    private EventRepository $eventRepository;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param PaginatorInterface       $paginator       Paginator
     * @param EventRepository          $eventRepository Event repository
     */
    public function __construct(CategoryServiceInterface $categoryService, PaginatorInterface $paginator, EventRepository $eventRepository)
    {
        $this->categoryService = $categoryService;
        $this->paginator = $paginator;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->eventRepository->queryByAuthor($filters),
            $page,
            EventRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedNowList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);
        $queryBilder = $this->eventRepository->queryByAuthor($filters);
        $queryBilder->andWhere('event.date BETWEEN :sub and :add')
            ->setParameter('add', new \DateTimeImmutable('+7 days'), Types::DATETIME_IMMUTABLE)
            ->setParameter('sub', new \DateTimeImmutable('-7 days'), Types::DATETIME_IMMUTABLE);

        return $this->paginator->paginate(
            $queryBilder,
            $page,
            EventRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedFutureList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);
        $queryBilder = $this->eventRepository->queryByAuthor($filters);
        $queryBilder->andWhere('event.date > :now')
            ->setParameter('now', new \DateTimeImmutable('+7 days'), Types::DATETIME_IMMUTABLE);

        return $this->paginator->paginate(
            $queryBilder,
            $page,
            EventRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Event $event Event entity
     */
    public function save(Event $event): void
    {
        $this->eventRepository->save($event);
    }

    /**
     * Delete event.
     *
     * @param Event $event Event entity
     */
    public function delete(Event $event): void
    {
        $this->eventRepository->delete($event);
    }

    /**
     * Find by id.
     *
     * @param int $id Event id
     *
     * @return Event|null Event entity
     */
    public function findOneById(int $id): ?Event
    {
        return $this->eventRepository->findOneById($id);
    }

    /**
     * Prepare filters for the events list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        return $resultFilters;
    }
}

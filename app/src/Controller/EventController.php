<?php
/**
 * Event Controller.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\Type\EventType;
use App\Repository\EventRepository;
use App\Service\EventServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EventController.
 */
#[Route('/event')]
class EventController extends AbstractController
{
    /**
     * @var EventServiceInterface interface service
     */
    private EventServiceInterface $eventService;

    /**
     * Translator.
     *
     * @var TranslatorInterface interfece transaltror
     */
    private TranslatorInterface $translator;

    /**
     * @param EventServiceInterface $eventService costam
     * @param TranslatorInterface   $translator   costam
     */
    public function __construct(EventServiceInterface $eventService, TranslatorInterface $translator)
    {
        $this->eventService = $eventService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_event_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $paginationFuture = $this->eventService->getPaginatedFutureList(
            $request->query->getInt('page', 1),
            $filters
        );
        $paginationNow = $this->eventService->getPaginatedNowList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('event/index.html.twig', ['paginationFuture' => $paginationFuture, 'paginationNow' => $paginationNow]);
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/all',
        name: 'app_event_all',
        methods: 'GET'
    )]
    public function all(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->eventService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('event/all.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @param Request         $request         param
     * @param EventRepository $eventRepository param
     *
     * @return Response return
     */
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventService->save($event);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * @param Event $event param
     *
     * @return Response return
     */
    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Request         $request         param
     * @param Event           $event           param
     * @param EventRepository $eventRepository parma
     *
     * @return Response return
     */
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $this->addFlash('error', $this->translator->trans('message.form_error'));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Event   $event   Event entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'app_event_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Event $event): Response
    {
        $form = $this->createForm(
            FormType::class,
            $event,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('app_event_delete', ['id' => $event->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventService->delete($event);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_event_index');
        }

        return $this->render(
            'event/delete.html.twig',
            [
                'form' => $form->createView(),
                'event' => $event,
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');

        return $filters;
    }
}

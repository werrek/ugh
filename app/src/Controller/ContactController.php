<?php
/**
 * Contact controller.
 */

namespace App\Controller;

use App\Entity\Contact;
use App\Form\Type\ContactType;
use App\Repository\ContactRepository;
use App\Service\ContactServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ContactController.
 */
#[Route('/contact')]
class ContactController extends AbstractController
{
    /**
     * @var ContactServiceInterface interface contac
     */
    private ContactServiceInterface $contactService;

    /**
     * Translator.
     *
     * @var TranslatorInterface interfece transaltror
     */
    private TranslatorInterface $translator;

    /**
     * @param ContactServiceInterface $contactService param
     * @param TranslatorInterface     $translator     param
     */
    public function __construct(ContactServiceInterface $contactService, TranslatorInterface $translator)
    {
        $this->contactService = $contactService;
        $this->translator = $translator;
    }

    /**
     * @param Request $request param
     *
     * @return Response return
     */
    #[Route('/', name: 'app_contact_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $pagination = $this->contactService->getPaginatedList(
            $request->query->getInt('page', 1),
            $this->getUser()
        );

        return $this->render('contact/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @param Request           $request           param
     * @param ContactRepository $contactRepository param
     *
     * @return Response return
     */
    #[Route('/new', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContactRepository $contactRepository): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactService->save($contact);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'contact/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Contact $contact param
     *
     * @return Response return
     */
    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render(
            'contact/show.html.twig',
            ['contact' => $contact,
            ]
        );
    }

    /**
     * @param Request           $request           param
     * @param Contact           $contact           param
     * @param ContactRepository $contactRepository param
     *
     * @return Response return
     */
    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, Contact $contact, ContactRepository $contactRepository): Response
    {
        $form = $this->createForm(ContactType::class, $contact, [
            'method' => 'PUT',
            'action' => $this->generateUrl('app_contact_edit', ['id' => $contact->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactService->save($contact);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'contact/edit.html.twig',
            [
                'form' => $form->createView(),
                'contact' => $contact,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Contact $contact Contact entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'app_contact_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(
            FormType::class,
            $contact,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('app_contact_delete', ['id' => $contact->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactService->delete($contact);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_contact_index');
        }

        return $this->render(
            'contact/delete.html.twig',
            [
                'form' => $form->createView(),
                'contact' => $contact,
            ]
        );
    }
}

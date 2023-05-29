<?php
/**
 * Event Controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EventController.
 */
#[Route('/')]
class HelloController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_hello_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        return $this->redirectToRoute('app_event_all');
    }
}

<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     * @param Request $r
     * @return Response
     */
    public function homePage(Request $r): Response
    {
        if ($this->getUser()) {
            $r->getSession()->set(
                Security::LAST_USERNAME,
                $this->getUser()->getUsername()
            );
            return $this->redirectToRoute('inboxPage');
        } else {
            return $this->render('mail/homePage.html.twig');
        }
    }
}
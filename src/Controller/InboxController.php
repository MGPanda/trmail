<?php


namespace App\Controller;


use App\Entity\Mail;
use App\Entity\User;
use App\Form\ComposeFormType;
use App\Form\LoginFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InboxController extends AbstractController
{
    /**
     * @Route("/inbox", name="inboxPage")
     * @param EntityManagerInterface $emi
     * @param Request $r
     * @param SessionInterface $session
     * @return Response
     * @throws Exception
     */
    public function inboxPage(EntityManagerInterface $emi, Request $r, SessionInterface $session): Response
    {
        $userEmail = $r->getSession()->get(Security::LAST_USERNAME);
        $form = $this->createForm(ComposeFormType::class);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($emi->getRepository(User::class)->findOneBy(["email" => $data['receiver']])) {
                $composedMail = new Mail();
                $composedMail->setSubject($data['subject']);
                $composedMail->setSender($userEmail);
                $composedMail->setReceiver($data['receiver']);
                $composedMail->setIsRead(false);
                $composedMail->setDate(new DateTime('NOW', timezone_open('Europe/Madrid')));
                $composedMail->setContent($data['content']);
                $emi->persist($composedMail);
                $emi->flush();
            } else {
                $this->addFlash('error', 'User ' . $data['receiver'] . 'has not been found.');
            }
        }

        $repo = $emi->getRepository(Mail::class);
        $sent = $repo->findBy(['sender' => $userEmail]);
        $received = $repo->findBy(['receiver' => $userEmail]);
        $mails = array_merge($sent, $received);
        usort($mails, function ($a, $b) {
            return $a->getDate() > $b->getDate() ? -1 : 1;
        });
        return $this->render('mail/inboxPage.html.twig', ['mails' => $mails, 'userEmail' => $userEmail]);
    }

    /**
     * @Route("/mail/{id}", name="mailPage")
     * @param EntityManagerInterface $emi
     * @param $id
     * @return Response
     */
    public function mailPage(EntityManagerInterface $emi, $id): Response
    {
        $mails = $emi->getRepository(Mail::class);
        $mail = $mails->findOneBy(['id' => $id]);
        if (!$mail->getIsRead()) {
            $mail->setIsRead(true);
            $emi->persist($mail);
            $emi->flush();
        }
        return $this->render('mail/mailPage.html.twig', ['mail' => $mail]);
    }
}
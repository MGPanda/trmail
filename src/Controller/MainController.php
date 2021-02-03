<?php


namespace App\Controller;

use App\Entity\Mail;
use App\Entity\User;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     */
    public function homePage(): Response
    {
        return $this->render('mail/homePage.html.twig');
    }

    /**
     * @Route("/login", name="loginPage")
     * @param Request $r
     * @param EntityManagerInterface $emi
     * @return Response
     */
    public function loginPage(Request $r, EntityManagerInterface $emi, SessionInterface $session): Response
    {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'] . "@trm.com";
            $password = $data['password'];

            $users = $emi->getRepository(User::class);
            $foundUser = $users->findOneBy(['email' => $email]);

            if ($foundUser) {
                if ($foundUser->getPassword() == $password) {
                    $this->addFlash('success', 'Valid credentials.');
                    $session->set('userEmail', $email);
                    return $this->redirectToRoute('inboxPage');
//                    return $this->forward('App\Controller\InboxController::inboxPage', ['userEmail' => $email]);
                } else {
                    $this->addFlash('error', 'Invalid credentials.');
                }
            } else {
                $newUser = new User();
                $newUser->setEmail($email);
                $newUser->setPassword($password);
                $emi->persist($newUser);
                $this->addFlash('success', 'User created.');

                $emi->persist($this->createWelcomeMail($email));
                $emi->flush();

                $session->set('userEmail', $email);
                return $this->redirectToRoute('inboxPage');
//                return $this->forward('App\Controller\InboxController::processPage', ['userEmail' => $email]);
            }
        }
        return $this->render('mail/loginPage.html.twig', ['loginForm' => $form->createView()]);
    }

    private function createWelcomeMail($receiver): Mail
    {
        $mail = new Mail();
        $mail->setContent("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam quis quam est. Donec lorem ipsum, tincidunt tempor gravida in, consequat vitae leo. Duis auctor mi vitae pellentesque dapibus. Aenean nec metus in purus fermentum aliquet vel vitae augue. Donec vitae dui in orci pulvinar suscipit ut ut dolor. In rutrum turpis ac velit imperdiet porttitor. Praesent malesuada tellus magna, a posuere turpis bibendum sed. Nullam finibus facilisis dapibus. Vestibulum in ornare orci, vel tempus nisl. Integer tempus elit et velit ultrices, rutrum finibus ipsum suscipit. Maecenas non augue ut dolor laoreet efficitur ac quis mauris. Vivamus ac neque laoreet, fermentum libero sit amet, ullamcorper massa. Mauris a rhoncus lectus. Mauris velit libero, euismod quis faucibus eu, aliquet hendrerit turpis. Donec a iaculis ante, consequat tristique eros. Maecenas pellentesque ligula nec lectus hendrerit, vitae tristique odio ultricies. Nulla facilisi. Vivamus tempor consectetur ante, in sagittis tellus fermentum non. Sed lobortis pellentesque interdum. Aliquam eu ligula in mauris euismod tincidunt. Sed vitae arcu arcu. Nam ultricies sed felis interdum viverra. In id tempor dolor. Quisque eget vehicula metus. In finibus lectus in mauris rhoncus, eu rhoncus arcu dictum. Morbi vel urna sapien. Donec eget felis orci. Integer feugiat gravida tristique. Suspendisse vehicula euismod arcu, et pretium orci iaculis a. Duis commodo ex magna, ac vehicula turpis pulvinar volutpat. Phasellus sit amet dignissim ex. Nunc et eros a est maximus dictum. Sed mattis ullamcorper augue. Donec bibendum felis nulla, a sagittis mi porttitor vitae. Mauris vitae rhoncus orci. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus et lacus eget quam dignissim feugiat. Vivamus vitae cursus arcu, vel imperdiet felis. Cras bibendum felis ante, eget bibendum justo ultrices a. Proin tempor maximus ligula. Duis neque eros, vulputate in ultricies et, luctus ac tellus. Aliquam ante tortor, faucibus quis rhoncus eget, aliquam sed arcu. Nam in purus risus. Curabitur vitae auctor nibh, sit amet scelerisque risus. Fusce eget enim sed justo gravida dapibus. Praesent mattis commodo leo sit amet imperdiet. Pellentesque interdum, ex a cursus mattis, felis dolor consequat nisi, a efficitur turpis magna ac augue. Nullam blandit dui in aliquam sodales. Morbi dapibus auctor quam, ut dignissim lectus euismod vitae. Ut libero arcu, elementum in cursus non, rutrum eu ligula. In fringilla porttitor arcu ut ultricies. Aenean porta luctus sapien, in facilisis odio viverra ut. Curabitur et elementum urna. Morbi tempor nisl a nunc tristique, quis vehicula diam dignissim. Phasellus ipsum tortor, ornare vel nulla id, rutrum rutrum tortor. Ut vel ipsum at orci molestie sagittis. Donec ut sem iaculis, tempus neque sit amet, pharetra urna. Nam a felis mollis turpis pharetra consectetur sit amet eleifend arcu. Suspendisse a metus porttitor sem fringilla pellentesque. Nullam at bibendum lorem. Nam auctor magna dignissim lorem sollicitudin, sed rutrum sem semper. Donec ut sagittis leo. Quisque id ligula justo. Fusce varius arcu non vehicula gravida. Curabitur nec consectetur mauris, ut feugiat nisi. Vestibulum vel odio nisl. Duis dapibus imperdiet pharetra. Praesent imperdiet rutrum aliquet.");
        $mail->setDate(new \DateTime('NOW', timezone_open('Europe/Madrid')));
        $mail->setIsRead(false);
        $mail->setReceiver($receiver);
        $mail->setSender("admin@trm.com");
        $mail->setSubject("Welcome to Totally Reliable Mail, $receiver!");
        return $mail;
    }
}
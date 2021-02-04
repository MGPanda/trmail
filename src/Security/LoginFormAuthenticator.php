<?php

namespace App\Security;

use App\Entity\Mail;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email') . "@trm.com",
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            $user = new User();
            $user->setEmail($credentials['email']);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $credentials['password']));
            $this->entityManager->persist($user);
            $this->entityManager->persist($this->createWelcomeMail($credentials['email']));
            $this->entityManager->flush();
//             fail authentication with a custom error
//            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('inboxPage'));
        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
//        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
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

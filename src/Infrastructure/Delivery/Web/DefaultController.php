<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 07/04/2019
 * Time: 19:41
 */

namespace App\Infrastructure\Delivery\Web;


use App\Domain\Core\Dto\NewUserDto;
use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Infrastructure\Caching\CachedResponseUtil;
use App\Infrastructure\Integration\MailchimpSubscriber;
use App\Infrastructure\Persistence\Doctrine\Repository\UpworkJobRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\UserRepository;
use App\Infrastructure\Security\AppAuthenticator;
use App\Ui\Form\RegistrationFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class DefaultController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function index(): Response
    {
        $searches = $this->getDoctrine()->getRepository(UserSearch::class)->findAll();
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $statistics = [
            'searches' => 835 + count($searches),
            'users' => 349 + count($users),
            'postings' => 2645 + rand(-300, 300),
            'premium_users' => 265 + round(count($users) / 3),
        ];
        $response = new CachedResponseUtil($this->render('index.html.twig', [
            'statistics' => $statistics,
        ]));

        return $response->stream();
    }

    /**
     * @Route("/recent-jobs", name="recent-jobs")
     *
     * @return Response
     */
    public function recentJobs(UpworkJobRepository $upworkJobRepository): Response
    {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        if ($manager->getFilters()->isEnabled('user_filter')) {
            $manager->getFilters()->disable('user_filter');
        }
        $recentJobs = $upworkJobRepository->findRecent();

        $response = new CachedResponseUtil($this->json($recentJobs), 30);

        return $response->stream();
    }

    /**
     * @Route("/terms", name="terms")
     *
     * @return Response
     */
    public function terms(): Response
    {
        return $this->redirect("/static/PrivacyPolicy.pdf");
    }

    /**
     * @Route("/privacy", name="privacy")
     *
     * @return Response
     */
    public function privacy(): Response
    {
        return $this->redirect("/static/PrivacyPolicy.pdf");
    }

    /**
     * @Route("/subscribe", methods={"POST"}, name="subscribe")
     */
    public function subscribe(Request $request, MailchimpSubscriber $mailchimpSubscriber): Response
    {
        $content = json_decode($request->getContent());

        if ($content->email) {

            $mailchimpSubscriber->subscribe($content->email);
        }

        return new JsonResponse([]);
    }

    /**
     * @Route("/contact", methods={"POST"}, name="contact")
     */
    public function contact(Request $request, LoggerInterface $telegramActionLogger): Response
    {
        $content = json_decode($request->getContent());

        if ($content->body) {
            $telegramActionLogger->info($content->body,
                ['email' => $content->email, 'name' => $content->name]);

        }

        return new JsonResponse([]);
    }

    /**
     * @Route("/register", name="register")
     * @return Response
     */
    public function register(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        AppAuthenticator $authenticator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $dto = new NewUserDto();
        $form = $this->createForm(RegistrationFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var UserRepository $userRepo */
            $userRepo = $em->getRepository(User::class);
            try {
                $user = User::createFromNewUserDto($userRepo->nextIdentity(), $dto);
                $user->hashPassword($passwordEncoder, $dto->getPlainPassword());
                $userRepo->add($user);
                $em->flush();

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main'
                );
            } catch (UniqueConstraintViolationException $exception) {
                $form->get('email')->addError(new FormError(
                    "Email already exists"
                ));
            }


        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function securityBlock(): Response
    {
        return $this->render('_security_block.html.twig');
    }
}

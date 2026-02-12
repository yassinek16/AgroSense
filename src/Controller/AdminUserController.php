<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\NewUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private UserRepository $userRepository,
        private ManagerRegistry $doctrine,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * List all users with pagination and sorting
     */
    #[Route('', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $sort = $request->query->get('sort', 'dateCreation');
        $order = strtoupper($request->query->get('order', 'DESC'));
        
        // Validate sort parameter to prevent SQL injection
        $validSortFields = ['firstName', 'lastName', 'email', 'roles', 'statutCompte', 'subscriptionStatus', 'dateCreation'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'dateCreation';
        }
        
        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Build query
        $query = $this->userRepository->createQueryBuilder('u')
            ->orderBy("u.$sort", $order)
            ->getQuery();

        // Create paginator
        $paginator = new Paginator($query, true);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / self::ITEMS_PER_PAGE);

        // Set first result
        $query->setFirstResult(($page - 1) * self::ITEMS_PER_PAGE)
              ->setMaxResults(self::ITEMS_PER_PAGE);

        $users = $query->getResult();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'currentSort' => $sort,
            'currentOrder' => $order,
        ]);
    }

    /**
     * Create a new user
     */
    #[Route('/create', name: 'app_admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(NewUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Set creation date
            if (!$user->getDateCreation()) {
                $user->setDateCreation(new \DateTime());
            }

            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', sprintf('User "%s" has been created successfully.', $user->getFullName()));
            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request): Response
    {
        // Prevent privilege escalation: check if admin is trying to remove their own admin role
        $currentUser = $this->getUser();
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Prevent admin from removing their own ROLE_ADMIN
            if ($currentUser->getId() === $user->getId() && !in_array('ROLE_ADMIN', $form->getData()->getRoles())) {
                $this->addFlash('error', 'You cannot remove your own admin role.');
                return $this->redirectToRoute('app_admin_user_edit', ['id' => $user->getId()]);
            }

            $em = $this->doctrine->getManager();
            $em->flush();

            $this->addFlash('success', sprintf('User "%s" has been updated successfully.', $user->getFullName()));
            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Delete a user
     */
    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST', 'DELETE'])]
    public function delete(User $user, Request $request): Response
    {
        $currentUser = $this->getUser();

        // Prevent deleting the currently authenticated admin
        if ($currentUser->getId() === $user->getId()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_user_index');
        }

        // CSRF token validation
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_admin_user_index');
        }

        $em = $this->doctrine->getManager();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', sprintf('User "%s" has been deleted successfully.', $user->getFullName()));
        return $this->redirectToRoute('app_admin_user_index');
    }

    /**
     * Get status badge CSS class
     */
    public static function getStatusBadgeClass(string $status = null): string
    {
        return match ($status) {
            'active' => 'badge bg-success',
            'away' => 'badge bg-warning',
            'offline' => 'badge bg-secondary',
            'suspended' => 'badge bg-danger',
            default => 'badge bg-light text-dark',
        };
    }

    /**
     * Get status badge label
     */
    public static function getStatusLabel(string $status = null): string
    {
        return match ($status) {
            'active' => '● Active',
            'away' => '● Away',
            'offline' => '● Offline',
            'suspended' => '● Suspended',
            default => '● Unknown',
        };
    }
}

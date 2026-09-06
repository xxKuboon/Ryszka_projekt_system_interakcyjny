<?php

/**
 * Copyright (c) Jakub Ryszka.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\User;
use App\Form\Type\TaskType;
use App\Security\Voter\TaskVoter;
use App\Service\TaskServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Task controller.
 */
#[Route('/task')]
class TaskController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TaskServiceInterface $taskService Task service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly TaskServiceInterface $taskService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route(name: 'task_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        return $this->redirectToRoute('task_vote_ongoing');
    }

    /**
     * Vote ongoing action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route('/vote/ongoing', name: 'task_vote_ongoing', methods: ['GET'])]
    public function voteOngoing(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedListOngoing($page);

        return $this->render('task/index.html.twig', [
            'pagination' => $pagination,
            'current_tab' => 'ongoing',
        ]);
    }

    /**
     * Vote upcoming action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route('/vote/upcoming', name: 'task_vote_upcoming', methods: ['GET'])]
    public function voteUpcoming(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedListUpcoming($page);

        return $this->render('task/index.html.twig', [
            'pagination' => $pagination,
            'current_tab' => 'upcoming',
        ]);
    }

    /**
     * Vote ended action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route('/vote/ended', name: 'task_vote_ended', methods: ['GET'])]
    public function voteEnded(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedListEnded($page);

        return $this->render('task/index.html.twig', [
            'pagination' => $pagination,
            'current_tab' => 'ended',
        ]);
    }

    /**
     * Category tasks action.
     *
     * @param Category $category Category entity
     * @param int      $page     Page number
     *
     * @return Response Response
     */
    #[Route('/category/{id}', name: 'task_category', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function categoryTasks(Category $category, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedList($page, $category, null);

        return $this->render('task/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Author tasks action.
     *
     * @param User $author Author entity
     * @param int  $page   Page number
     *
     * @return Response Response
     */
    #[Route('/author/{id}', name: 'task_author', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function authorTasks(User $author, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedList($page, null, $author);

        return $this->render('task/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Pending action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route('/pending', name: 'task_pending', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pending(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->taskService->getPaginatedListByStatus($page, TaskStatus::PENDING);

        return $this->render('task/pending.html.twig', ['pagination' => $pagination]);
    }

    /**
     * My tasks action.
     *
     * @param int $page Page number
     *
     * @return Response Response
     */
    #[Route('/my', name: 'task_my', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myTasks(#[MapQueryParameter] int $page = 1): Response
    {
        /** @var User $author */
        $author = $this->getUser();
        $pagination = $this->taskService->getPaginatedListByAuthor($page, $author);

        return $this->render('task/my.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Change status action.
     *
     * @param Request    $request HTTP request
     * @param Task       $task    Task entity
     * @param TaskStatus $status  Task status
     *
     * @return Response Response
     */
    #[Route('/{id}/status/{status}', name: 'task_status', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatus(Request $request, Task $task, TaskStatus $status): Response
    {
        if ($this->isCsrfTokenValid('status'.$task->getId(), $request->request->get('_token'))) {
            if (TaskStatus::APPROVED === $status && null === $task->getEndsAt()) {
                $this->addFlash('danger', $this->translator->trans('message.ends_at_required_for_approval'));

                return $this->redirectToRoute('task_pending');
            }

            $task->setStatus($status);
            $this->taskService->save($task);

            if (TaskStatus::APPROVED === $status) {
                $this->addFlash('success', $this->translator->trans(
                    'message.approved_successfully',
                    ['%id%' => $task->getId()]
                ));
            } elseif (TaskStatus::REJECTED === $status) {
                $this->addFlash('success', $this->translator->trans(
                    'message.rejected_successfully',
                    ['%id%' => $task->getId()]
                ));
            } else {
                $this->addFlash('success', $this->translator->trans(
                    'message.edited_successfully',
                    ['%id%' => $task->getId()]
                ));
            }
        }

        return $this->redirectToRoute('task_pending');
    }

    /**
     * Vote action.
     *
     * @param Request $request HTTP request
     * @param Task    $task    Task entity
     *
     * @return Response Response
     */
    #[Route('/{id}/vote', name: 'task_vote', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    public function vote(Request $request, Task $task): Response
    {
        $fallbackUrl = $request->headers->get('referer') ?: $this->generateUrl('task_vote_ongoing');

        if (!$this->isCsrfTokenValid('vote'.$task->getId(), $request->request->get('_token'))) {
            return $this->redirect($fallbackUrl);
        }

        $now = new \DateTimeImmutable();
        if ($task->getStartsAt() && $task->getStartsAt() > $now) {
            $this->addFlash('danger', $this->translator->trans('message.the_voting_hasnt_started'));

            return $this->redirect($fallbackUrl);
        }

        if ($task->isExpired()) {
            $this->addFlash('danger', $this->translator->trans('message.voting_has_ended'));

            return $this->redirect($fallbackUrl);
        }

        if (TaskStatus::APPROVED !== $task->getStatus()) {
            $this->addFlash('warning', $this->translator->trans('message.voting_not_allowed'));

            return $this->redirect($fallbackUrl);
        }

        $session = $request->getSession();
        $user = $this->getUser();

        if (!$user) {
            if ($session->get('anonymous_voted', false)) {
                $this->addFlash('warning', $this->translator->trans('message.already_voted_in_session'));

                return $this->redirect($fallbackUrl);
            }
        } else {
            $votedTasks = $session->get('voted_tasks', []);
            if (in_array($task->getId(), $votedTasks, true)) {
                $this->addFlash('warning', $this->translator->trans('message.already_voted_in_session'));

                return $this->redirect($fallbackUrl);
            }
        }

        $task->setVotesCount($task->getVotesCount() + 1);
        $this->taskService->save($task);

        if (!$user) {
            $session->set('anonymous_voted', true);
        } else {
            $votedTasks = $session->get('voted_tasks', []);
            $votedTasks[] = $task->getId();
            $session->set('voted_tasks', $votedTasks);
        }

        $this->addFlash('success', $this->translator->trans('message.vote_added'));

        return $this->redirect($fallbackUrl);
    }

    /**
     * View action.
     *
     * @param Task $task Task entity
     *
     * @return Response Response
     */
    #[Route('/{id}', name: 'task_view', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    #[IsGranted(TaskVoter::VIEW, subject: 'task')]
    public function view(Task $task): Response
    {
        return $this->render('task/view.html.twig', ['task' => $task]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response Response
     */
    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $task = new Task();

        if ($this->getUser()) {
            /** @var User $user */
            $user = $this->getUser();
            $task->setAuthor($user);
        }

        $task->setStatus(TaskStatus::PENDING);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->save($task);

            $this->addFlash('success', $this->translator->trans('message.task_created_successfully'));

            return $this->redirectToRoute('task_vote_ongoing');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Task    $task    Task entity
     *
     * @return Response Response
     */
    #[Route('/{id}/edit', name: 'task_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted(TaskVoter::EDIT, subject: 'task')]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(
            TaskType::class,
            $task,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('task_edit', ['id' => $task->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->save($task);

            $this->addFlash('success', $this->translator->trans(
                'message.edited_successfully',
                ['%id%' => $task->getId()]
            ));

            if (TaskStatus::PENDING === $task->getStatus()) {
                return $this->redirectToRoute('task_pending');
            }

            return $this->redirectToRoute('task_vote_ongoing');
        }

        return $this->render(
            'task/edit.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Task    $task    Task entity
     *
     * @return Response Response
     */
    #[Route('/{id}/delete', name: 'task_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    #[IsGranted(TaskVoter::DELETE, subject: 'task')]
    public function delete(Request $request, Task $task): Response
    {
        $form = $this->createForm(FormType::class, $task, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('task_delete', ['id' => $task->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->delete($task);

            $this->addFlash('success', $this->translator->trans('message.task_deleted_successfully'));

            return $this->redirectToRoute('task_vote_ongoing');
        }

        return $this->render(
            'task/delete.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task,
            ]
        );
    }
}

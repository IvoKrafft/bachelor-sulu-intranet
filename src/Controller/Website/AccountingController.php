<?php

declare(strict_types=1);

namespace App\Controller\Website;

use App\Controller\ApplicationController;
use App\Entity\Accounting;
use App\Form\AccountingType;
use App\Repository\AccountingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/accounting')]
class AccountingController extends ApplicationController
{
	#[Route('/', name: 'app_accounting_index', methods: ['GET'])]
	#[IsGranted('index', subject: 'accounting')]
	public function index(Request $request, AccountingRepository $accountingRepository, Accounting $accounting = new Accounting()): Response {
		/** @var User $user */
		$user = $this->getUser();

		$accountings = $accountingRepository->findFiltered($user, $request->query->getInt('page', 1));

		return $this->render('accounting/index.html.twig', ['accountings' => $accountings]);
	}


	#[Route('/new', name: 'app_accounting_new', methods: ['GET', 'POST'])]
	#[IsGranted('new', subject: 'accounting')]
	public function new(Request $request, EntityManagerInterface $em, Accounting $accounting = new Accounting()): Response {
		$accounting = new Accounting();

		/** @var User $currentUser */
		$currentUser = $this->getUser();
		$accounting->setUser($currentUser);

		/** @var Branch $branch */
		$branch = $this->getBranches($currentUser)->first();
		$accounting->setBranch($branch);

		$form = $this->createForm(AccountingType::class, $accounting, ['currentUser' => $this->getUser()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($accounting);
			$em->flush();

			return $this->redirectToRoute('app_accounting_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('accounting/new.html.twig', ['accounting' => $accounting, 'form' => $form]);
	}


	#[Route('/{id}/edit', name: 'app_accounting_edit', methods: ['GET', 'POST'])]
	#[IsGranted('edit', subject: 'accounting')]
	public function edit(Request $request, Accounting $accounting, EntityManagerInterface $em): Response {
		$form = $this->createForm(AccountingType::class, $accounting, ['currentUser' => $this->getUser()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();

			return $this->redirectToRoute('app_accounting_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('accounting/edit.html.twig', ['accounting' => $accounting, 'form' => $form]);
	}


	#[Route('/{id}', name: 'app_accounting_delete', methods: ['POST'])]
	#[IsGranted('delete', subject: 'accounting')]
	public function delete(Request $request, Accounting $accounting, EntityManagerInterface $em): Response {
		/** @var ?string $token */
		$token = $request->request->get('_token');
		if ($this->isCsrfTokenValid('delete' . $accounting->getId(), $token)) {
			$em->remove($accounting);
			$em->flush();
		}

		return $this->redirectToRoute('app_accounting_index', [], Response::HTTP_SEE_OTHER);
	}
}

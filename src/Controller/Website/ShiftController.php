<?php

declare(strict_types=1);

namespace App\Controller\Website;

use App\Controller\ApplicationController;
use App\Entity\Shift;
use App\Entity\Vacation;
use App\Form\ShiftAssignmentType;
use App\Form\ShiftBatchType;
use App\Form\ShiftType;
use App\Repository\ShiftRepository;
use App\Repository\VacationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface as BranchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/shift')]
class ShiftController extends ApplicationController
{
	public function __construct(protected EntityManagerInterface $em, private BranchRepository $branchRepository) {
	}


	#[Route('/', name: 'app_shift_index', methods: ['GET'])]
	#[IsGranted('index', subject: 'shift')]
	public function index(Request $request, ShiftRepository $shiftRepository, VacationRepository $vacationRepository, Shift $shift = new Shift()): Response {
		/** @var User $user */
		$user = $this->getUser();

		$branchName = $request->query->get('branch');
		$branches = $user->getContact()->getTags();
		$branch = null;
		foreach ($branches as $branchEntry) if ($branchEntry->getName() == $branchName) $branch = $branchEntry;

		$shifts = $shiftRepository->findFiltered($user, $branch);
		$vacations = $vacationRepository->findFilteredByBranch($user, $branch);
		$shiftPlan = $this->getShiftPlanStructure($shifts, $vacations);
		$weeks = array_keys($shiftPlan);

		$kw = $request->query->get('kw');
		/** @phpstan-ignore-next-line */
		if (($kw != null || $kw != '') && preg_match('/^KW \d{2}-\d{4}$/', $kw, $matches) > 0 && array_key_exists($kw, $shiftPlan)) {
			$shiftPlan = $shiftPlan[$kw];
			$dates = $this->createWeekDates($kw);
		} else {
			$now = new \DateTime();
			$shiftPlan = $shiftPlan['KW '.$now->format('W-Y')];
			$dates = [];
		}

		$branchNameArray = [];
		foreach ($branches as $branch) $branchNameArray[] = $branch->getName();

		return $this->render('shift/index.html.twig', ['shifts' => $shifts, 'shiftPlan' => $shiftPlan, 'branches' => $branchNameArray, 'weeks' => $weeks, 'dates' => $dates]);
	}


	#[Route('/new', name: 'app_shift_new', methods: ['GET', 'POST'])]
	#[IsGranted('new', subject: 'shift')]
	public function new(Request $request, Shift $shift = new Shift()): Response {
		$shift = new Shift();

		/** @var User $currentUser */
		$currentUser = $this->getUser();
		$shift->setUser($currentUser);

		$form = $this->createForm(ShiftType::class, null, ['currentUser' => $currentUser, 'shift' => $shift]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$formData = $request->request->all();

			/** @var \DateTime $date */
			$branch = $this->branchRepository->findById($formData['branch']);
			$this->setShiftData($shift, $form->get('start_at')->getData(), $formData, $branch);
			$this->em->persist($shift);
			$this->em->flush();

			return $this->redirectToRoute('app_shift_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('shift/new.html.twig', ['shift' => $shift, 'form' => $form]);
	}


	#[Route('/new_batch', name: 'app_shift_new_batch', methods: ['GET', 'POST'])]
	#[IsGranted('newBatch', subject: 'shift')]
	public function newBatch(Request $request, Shift $shift = new Shift()): Response {
		$shift = new Shift();

		/** @var User $currentUser */
		$currentUser = $this->getUser();
		$shift->setUser($currentUser);

		$form = $this->createForm(ShiftBatchType::class, null, ['currentUser' => $currentUser]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->createBatchShifts($request->request->all());
			$this->em->flush();

			return $this->redirectToRoute('app_shift_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('shift/new_batch.html.twig', ['shift' => $shift, 'form' => $form]);
	}


	#[Route('/{id}/edit', name: 'app_shift_edit', methods: ['GET', 'POST'])]
	#[IsGranted('edit', subject: 'shift')]
	public function edit(Request $request, Shift $shift): Response {
		$form = $this->createForm(ShiftType::class, $shift, ['currentUser' => $this->getUser(), 'shift' => $shift]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$formData = $request->request->all();

			/** @var \DateTime $date */
			$branch = $this->branchRepository->findById($formData['branch']);
			$this->setShiftData($shift, $form->get('start_at')->getData(), $formData, $branch);
			$this->em->persist($shift);
			$this->em->flush();

			return $this->redirectToRoute('app_shift_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('shift/edit.html.twig', ['shift' => $shift, 'form' => $form]);
	}


	#[Route('/{id}/edit_assignment', name: 'app_shift_edit_assignment', methods: ['GET', 'POST'])]
	#[IsGranted('editAssignment', subject: 'shift')]
	public function editAssignment(Request $request, Shift $shift): Response {
		$form = $this->createForm(ShiftAssignmentType::class, $shift, ['currentUser' => $this->getUser()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('app_shift_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('shift/_form_assignment.html.twig', ['shift' => $shift, 'form' => $form]);
	}


	#[Route('/{id}', name: 'app_shift_delete', methods: ['POST'])]
	#[IsGranted('delete', subject: 'shift')]
	public function delete(Request $request, Shift $shift): Response {
		/** @var ?string $token */
		$token = $request->request->get('_token');
		if ($this->isCsrfTokenValid('delete' . $shift->getId(), $token)) {
			$this->em->remove($shift);
			$this->em->flush();
		}

		return $this->redirectToRoute('app_shift_index', [], Response::HTTP_SEE_OTHER);
	}


	/*
	 * Private methods
	 */

	/**
	 * @param array<Shift> $shifts
	 * @param array<Vacation> $vacations
	 * @return array<mixed>
	 */
	private function getShiftPlanStructure(array $shifts, array $vacations): array {
		$shiftPlan = [];
		$shiftPlan['KW '.(new \DateTime())->format('W-Y')]['weeknumber'] = 'KW '.(new \DateTime())->format('W-Y');
		$shiftPlan['KW '.(new \DateTime())->format('W-Y')]['days'] = [];

		foreach ($shifts as $shift) {
			/** @var \DateTime $startAt */
			$startAt = $shift->getStartAt();
			/** @var \DateTime $endAt */
			$endAt = $shift->getEndAt();

			$dateInterval = \DateInterval::createFromDateString('1 day');
			$fromForInterval = (clone $startAt)->setTime(0, 0);
			$toForInterval = (clone $endAt)->setTime(23, 59, 59, 999);
			$dateRange = new \DatePeriod($fromForInterval, $dateInterval, $toForInterval);

			foreach ($dateRange as $date) {
				$weekdays = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
				$weekNumber = 'KW '.$date->format('W-Y');
				$weekday = $weekdays[$date->format('w')];

				$shiftPlan[$weekNumber]['weeknumber'] = $weekNumber;
				$shiftPlan[$weekNumber]['days'][$weekday][] = $this->getShiftArrayForPlan($shift);
			}
		}

		foreach ($vacations as $vacation) {
			/** @var \DateTime $startAt */
			$startAt = $vacation->getStartAt();
			/** @var \DateTime $endAt */
			$endAt = $vacation->getEndAt();

			$dateInterval = \DateInterval::createFromDateString('1 day');
			$fromForInterval = (clone $startAt)->setTime(0, 0);
			$toForInterval = (clone $endAt)->setTime(23, 59, 59, 999);
			$dateRange = new \DatePeriod($fromForInterval, $dateInterval, $toForInterval);

			foreach ($dateRange as $date) {
				$weekdays = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
				$weekNumber = 'KW '.$date->format('W-Y');
				$weekday = $weekdays[$date->format('w')];

				$shiftPlan[$weekNumber]['weeknumber'] = $weekNumber;
				$shiftPlan[$weekNumber]['days'][$weekday][] = $this->getVacationArrayForPlan($vacation, $date);
			}
		}

		return $shiftPlan;
	}


	/**
	 * @return array<mixed>
	 */
	private function getShiftArrayForPlan(Shift $shift): array {
		$shiftArray = [
			'type' => 'shift',
			'id' => $shift->getId(),
			'start_at' => $shift->getStartAt(),
			'end_at' => $shift->getEndAt(),
			'break_start_at' => $shift->getBreakStartAt(),
			'break_end_at' => $shift->getBreakEndAt(),
			'employee' => null,
		];
		if ($shift->getEmployee() != null) {
			$shiftArray['employee'] = [
				'lastname' => $shift->getEmployee()->getLastname(),
				'firstname' => $shift->getEmployee()->getFirstname(),
			];
		}
		return $shiftArray;
	}


	/**
	 * @return array<mixed>
	 */
	private function getVacationArrayForPlan(Vacation $vacation, \DateTime $date): array {
		$vacationArray = [
			'type' => 'vacation',
			'id' => $vacation->getId(),
			'start_at' => (clone $date)->setTime(0, 0),
			'end_at' => (clone $date)->setTime(21, 00),
			'employee' => null,
		];
		if ($vacation->getEmployee() != null) {
			$vacationArray['employee'] = [
				'lastname' => $vacation->getEmployee()->getLastname(),
				'firstname' => $vacation->getEmployee()->getFirstname(),
			];
		}
		return $vacationArray;
	}


	/** @phpstan-ignore-next-line */
	private function createBatchShifts(array $formData): void {
		/** @var \DateTime $startDate */
		$startDate = \DateTime::createFromFormat('Y-m-d', $formData['start_date']);
		/** @var \DateTime $endDate */
		$endDate = \DateTime::createFromFormat('Y-m-d', $formData['end_date']);
		$allBranches = $this->branchRepository->findAll();
		$branches = [];
		foreach ($allBranches as $branch) {
			if (in_array($branch->getId(), $formData['branches'])) $branches[] = $branch;
		}

		$datePeriod = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate, \DatePeriod::INCLUDE_END_DATE);

		/** @var Branch $branch */
		foreach ($branches as $branch) {
			foreach ($datePeriod as $date) {
				$shift = new Shift();
				$shift = $this->setShiftData($shift, $date, $formData, $branch);
				$this->em->persist($shift);
			}
		}
	}


	private function createWeekDates(string $week): array {
		if (preg_match('/KW (\d+)-(\d+)/', $week, $matches)) {
			$weeknumber = $matches[1];
			$year = $matches[2];
		}

		$date = new \DateTime();
		$date->setISODate((int)$year, (int)$weeknumber);

		$dates = [];
		for ($i = 0; $i < 7; $i++) {
			$dates[] = $date->format('Y-m-d');
			$date->modify('+1 day');
		}

		return $dates;
	}


	private function setShiftData(Shift $shift, \DateTime $date, array $times, Branch $branch): Shift {
		/** @var \DateTime $start */
		$start = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d').' '.$times['start_time']);
		$shift->setStartAt($start);

		/** @var \DateTime $end */
		$end = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d').' '.$times['end_time']);
		$shift->setEndAt($end);

		/** @var \DateTime $startBreak */
		$startBreak = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d').' '.$times['start_break']);
		$shift->setBreakStartAt($startBreak);

		/** @var \DateTime $endBreak */
		$endBreak = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d').' '.$times['end_break']);
		$shift->setBreakEndAt($endBreak);

		$shift->setBranch($branch);

		return $shift;
	}
}

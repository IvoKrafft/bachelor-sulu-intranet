<?php

namespace App\Repository;

use App\Entity\Shift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;


/**
 * @method Shift|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shift|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shift[]    findAll()
 * @method Shift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Shift::class);
	}


	/** @phpstan-ignore-next-line */
	public function findFiltered(User $currentUser, Branch $branch = null): array {
		$qb = $this->_em->createQueryBuilder();

		$qb->select('x')->from(Shift::class, 'x');
		$qb->where('x.branch = :branch')->setParameter('branch', $branch);
		$this->filterForRoles($qb, $currentUser);
		$qb->orderBy('x.startAt', 'DESC');
		$result = $qb->getQuery()->getResult();

		return $result;
	}


	/** @phpstan-ignore-next-line */
	public function findByDate(Branch $branch, \DateTime $date): array {
		$qb = $this->_em->createQueryBuilder();

		$qb->select('x')->from(Shift::class, 'x');
		$qb->where('x.branch = :branch')->setParameter('branch', $branch);
		$startComparisonDateTime = (clone $date)->setTime(23, 59, 59);
		$endComparisonDateTime = (clone $date)->setTime(0, 0);
		$qb->andWhere('x.startAt <= :date1')->setParameter('date1', $startComparisonDateTime);
		$qb->andWhere('x.endAt >= :date2')->setParameter('date2', $endComparisonDateTime);

		$result = $qb->getQuery()->getResult();

		return $result;
	}


	/*
	 * Private methods
	 */

	private function filterForRoles(QueryBuilder $qb, User $currentUser): void {
		if (in_array('ROLE_SULU_BEZIRKSLEITER', $currentUser->getRoles())) {
			$qb->andWhere('x.branch IN (:branches)')->setParameter('branches', $currentUser->getContact()->getTags());

		} elseif (in_array('ROLE_SULU_FILIALE', $currentUser->getRoles())) {
			$qb->andWhere('x.branch = :branch')->setParameter('branch', $currentUser->getContact()->getTags()->first());

		} elseif (in_array('ROLE_SULU_PERSONAL', $currentUser->getRoles())) {
			$qb->andWhere('x.branch IN (:branches)')->setParameter('branches', $currentUser->getContact()->getTags());

		} else {
			$qb->andWhere('TRUE = FALSE');
		}
	}
}

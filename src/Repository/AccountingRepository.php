<?php

namespace App\Repository;

use App\Entity\Accounting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;


/**
 * @method Accounting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accounting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accounting[]    findAll()
 * @method Accounting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator) {
		parent::__construct($registry, Accounting::class);
	}


	/** @phpstan-ignore-next-line */
	public function findFiltered(User $currentUser, int $page = 1): PaginationInterface {
		$qb = $this->_em->createQueryBuilder();

		$qb->select('x')->from(Accounting::class, 'x');
		$this->filterForRoles($qb, $currentUser);
		$qb->orderBy('x.date', 'DESC');
		$query = $qb->getQuery();

		$result = $this->paginator->paginate($query, $page, 25);

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

		} else {
			$qb->andWhere('TRUE = FALSE');
		}
	}
}

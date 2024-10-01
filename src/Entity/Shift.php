<?php

namespace App\Entity;

use App\Entity\Employee;
use App\Repository\ShiftRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ShiftRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Shift
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid', unique: true)]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	private UuidInterface|string $id;

	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private \DateTime $createdAt;

	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private \DateTime $updatedAt;


	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private ?\DateTime $startAt;

	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private ?\DateTime $endAt;

	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private ?\DateTime $breakStartAt;

	#[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
	#[Assert\NotNull]
	private ?\DateTime $breakEndAt;


	#[ORM\ManyToOne(targetEntity: Branch::class, cascade: ['persist'])]
	private ?Branch $branch;

	#[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'shifts', cascade: ['persist'])]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	private ?Employee $employee;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'shifts', cascade: ['persist'])]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	private ?User $user;



	public function __construct() {
		$this->setCreatedAt();
		$this->setUpdatedAt();

		$this->startAt = null;
		$this->endAt = null;
		$this->breakStartAt = null;
		$this->breakEndAt = null;

		$this->branch = null;
		$this->employee = null;
		$this->user = null;
	}


	#[ORM\PreUpdate]
	public function onPreUpdate(): void {
		$this->setUpdatedAt();
	}



	/*
	 * Getter and setter methods
	 */

	public function getId(): string {
		return $this->id;
	}


	public function getCreatedAt(): \DateTime {
		return $this->createdAt;
	}

	public function setCreatedAt(): void {
		$this->createdAt = new \DateTime();
	}


	public function getUpdatedAt(): \DateTime {
		return $this->updatedAt;
	}

	public function setUpdatedAt(): void {
		$this->updatedAt = new \DateTime();
	}


	public function getStartAt(): ?\DateTime {
		return $this->startAt;
	}

	public function setStartAt(\DateTime $startAt): void {
		$this->startAt = $startAt;
	}


	public function getEndAt(): ?\DateTime {
		return $this->endAt;
	}

	public function setEndAt(\DateTime $endAt): void {
		$this->endAt = $endAt;
	}


	public function getBreakStartAt(): ?\DateTime {
		return $this->breakStartAt;
	}

	public function setBreakStartAt(\DateTime $breakStartAt): void {
		$this->breakStartAt = $breakStartAt;
	}


	public function getBreakEndAt(): ?\DateTime {
		return $this->breakEndAt;
	}

	public function setBreakEndAt(\DateTime $breakEndAt): void {
		$this->breakEndAt = $breakEndAt;
	}



	/*
	 * Relation methods
	 */

	public function getBranch(): ?Branch {
		return $this->branch;
	}

	public function setBranch(?Branch $branch): void {
		$this->branch = $branch;
	}


	public function getEmployee(): ?Employee {
		return $this->employee;
	}

	public function setEmployee(?Employee $employee): void {
		$this->employee = $employee;
	}


	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): void {
		$this->user = $user;
	}


	/*
	 * Other methods
	 */

	public function getDuration(): float {
		$duration = $this->getEndAt()?->getTimestamp() - $this->getStartAt()?->getTimestamp();
		$breaks = $this->getBreakEndAt()?->getTimestamp() - $this->getBreakStartAt()?->getTimestamp();

		$duration = (float)($duration - $breaks) / 60 / 60;

		return $duration;
	}
}

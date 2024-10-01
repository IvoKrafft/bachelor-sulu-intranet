<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class AccountingReference
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


	#[ORM\Column(type: TYPES::DATE_MUTABLE)]
	#[Assert\NotNull]
	private ?\DateTime $date;

	#[ORM\Column(type: TYPES::FLOAT)]
	#[Assert\NotNull]
	private ?float $salesOneYearAgo;

	#[ORM\Column(type: TYPES::FLOAT)]
	#[Assert\NotNull]
	private ?float $desired;


	#[ORM\ManyToOne(targetEntity: Branch::class, cascade: ['persist'])]
	private ?Branch $branch;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'shifts', cascade: ['persist'])]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	private ?User $user;



	public function __construct() {
		$this->setCreatedAt();
		$this->setUpdatedAt();

		$this->date = null;
		$this->salesOneYearAgo = null;
		$this->desired = null;

		$this->branch = null;
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


	public function getDate(): ?\DateTime {
		return $this->date;
	}

	public function setDate(\DateTime $date): void {
		$this->date = $date;
	}


	public function getSalesOneYearAgo(): ?float {
		return $this->salesOneYearAgo;
	}

	public function setSalesOneYearAgo(float $salesOneYearAgo): void {
		$this->salesOneYearAgo = $salesOneYearAgo;
	}


	public function getDesired(): ?float {
		return $this->desired;
	}

	public function setDesired(float $desired): void {
		$this->desired = $desired;
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


	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): void {
		$this->user = $user;
	}
}

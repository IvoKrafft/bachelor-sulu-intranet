<?php

namespace App\Entity;

use App\Repository\AccountingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\TagBundle\Entity\Tag as Branch;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: AccountingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Accounting
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
	private ?float $sales;

	#[ORM\Column(type: TYPES::FLOAT)]
	#[Assert\NotNull]
	private ?float $returns;

	#[ORM\Column(type: TYPES::INTEGER)]
	#[Assert\NotNull]
	private ?int $customers;

	// Not persisted variable for App\EventListener\AccountingLoadListener
	private ?float $hourlyOutput;


	#[ORM\ManyToOne(targetEntity: Branch::class, cascade: ['persist'])]
	private ?Branch $branch;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'shifts', cascade: ['persist'])]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	private ?User $user;



	public function __construct() {
		$this->setCreatedAt();
		$this->setUpdatedAt();

		$this->date = null;
		$this->sales = null;
		$this->returns = null;
		$this->customers = null;
		$this->hourlyOutput = null;

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


	public function getSales(): ?float {
		return $this->sales;
	}

	public function setSales(float $sales): void {
		$this->sales = $sales;
	}


	public function getReturns(): ?float {
		return $this->returns;
	}

	public function setReturns(float $returns): void {
		$this->returns = $returns;
	}


	public function getCustomers(): ?int {
		return $this->customers;
	}

	public function setCustomers(int $customers): void {
		$this->customers = $customers;
	}


	public function getHourlyOutput(): ?float {
		return $this->hourlyOutput;
	}

	public function setHourlyOutput(?float $hourlyOutput): void {
		$this->hourlyOutput = $hourlyOutput;
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

<?php

namespace App\Controller\Website;

use Sulu\Bundle\FormBundle\Repository\DynamicRepository;
use Sulu\Bundle\WebsiteBundle\Controller\WebsiteController;
use Sulu\Component\Content\Compat\StructureInterface;
use Symfony\Component\HttpFoundation\Response;


class TicketProcessingController extends WebsiteController
{
    public function __construct(private DynamicRepository $formRepository) {
    }


    public function index(StructureInterface $structure, bool $preview = false, bool $partial = false): Response {
        $user = $this->getUser();
        $forms = $this->formRepository->findAll();

        if ($user != null && $user->getRoles()[1] == 'ROLE_SULU_HANDWERKER') {
            $myForm = $this->formRepository->findBy(['typeName' => 'Reparaturen']);
        } elseif ($user != null && $user->getRoles()[1] == 'ROLE_SULU_VERWALTUNG') {
            $myForm = $this->formRepository->findBy(['typeName' => 'Verwaltung']);
        } else {
            $myForm = null;
        }

        return $this->renderStructure(
            $structure,
            [
                'this' => $structure,
                'user' => $user,
                'forms' => $forms,
                'myForm' => $myForm,
            ],
            $preview,
            $partial
        );
    }
}
<?php

namespace App\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Sulu\Bundle\FormBundle\Repository\DynamicRepository;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\WebsiteBundle\Controller\WebsiteController;
use Sulu\Component\Content\Compat\StructureInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TicketController extends WebsiteController
{
    public function __construct(private DynamicRepository $formRepository) {
    }


    public function index(StructureInterface $structure, bool $preview = false, bool $partial = false): Response {
        /** @var User $user */
        $user = $this->getUser();

        $forms = $this->formRepository->findAll();
        $params = ['Reparaturen', 'Verwaltung'];
        $myForm = $this->formRepository->findBy(['creator' => $user, 'typeName' => $params]);

        return $this->renderStructure(
            $structure,
            [
                'this' => $this,
                'user' => $user,
                'forms' => $forms,
                'myForm' => $myForm,
            ],
            $preview,
            $partial
        );
    }

    #[Route('/delete/{id}?={url}', name: 'app_ticket_delete')]
    public function delete(int $id, string $url, EntityManagerInterface $em): RedirectResponse {
        /** @var Dynamic $form */
        $form = $this->formRepository->find($id);
        $em->remove($form);
        $em->flush();

        $this->addFlash('success', 'Ticket gelöscht!');

        return $this->redirect('/'.$url);
    }
}

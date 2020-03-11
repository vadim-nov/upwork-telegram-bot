<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 07/04/2019
 * Time: 19:41
 */

namespace App\Infrastructure\EasyAdmin\Controller;


use App\Domain\Core\Entity\User;
use App\Infrastructure\Form\Dto\UserPlanDto;
use App\Infrastructure\Form\Type\UserPlanFormType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

class UserController extends EasyAdminController
{

    public function resetPlaceAction(){
        $id = $this->request->query->get('id');
        /** @var User $entity */
        $entity = $this->em->getRepository(User::class)->find($id);
        $entity->resetPlace();
        $this->em->flush();
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }
    public function editPlanAction()
    {
        $id = $this->request->query->get('id');
        /** @var User $entity */
        $entity = $this->em->getRepository(User::class)->find($id);
        $dto = new UserPlanDto();
        $dto->plan = $entity->getCurrentPlan();
        $dto->from = $entity->getCurrentPlanFrom();
        $dto->to = $entity->getCurrentPlanTo();
        $form = $this->createForm(UserPlanFormType::class, $dto);
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity->updatePlanFromDto($dto);
            $this->em->flush();
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => $this->request->query->get('entity'),
            ));
        } else {
            return $this->render('admin/edit_plan.html.twig', ['form' => $form->createView(), 'entity' => $entity]);
        }

    }
}

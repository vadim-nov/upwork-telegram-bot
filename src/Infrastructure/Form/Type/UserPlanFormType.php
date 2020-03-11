<?php


namespace App\Infrastructure\Form\Type;


use App\Domain\Core\Entity\Plan;
use App\Infrastructure\Form\Dto\UserPlanDto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPlanFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plan', EntityType::class, ['class' => Plan::class]);
        $builder->add('from', DateType::class, ['format'=>DateType::DEFAULT_FORMAT]);
        $builder->add('to', DateType::class, ['format'=>DateType::DEFAULT_FORMAT]);
        $builder->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', UserPlanDto::class);
        parent::configureOptions($resolver); // TODO: Change the autogenerated stub
    }


}

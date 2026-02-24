<?php

namespace App\Form;

use App\Entity\Priority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriorityCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('save',  SubmitType::class, ['label' => $options['is_edit'] ? 'Edit' : 'Add'])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Priority::class,
            'is_edit' => false,
        ]);
    }
}

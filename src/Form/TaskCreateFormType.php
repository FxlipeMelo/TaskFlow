<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Priority;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add('priority', EntityType::class, ['class' => Priority::class, 'choice_label' => 'name'])
            ->add('save', SubmitType::class, ['label' => $options['is_edit'] ? 'Edit' : 'Add'])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'is_edit' => false
        ]);
    }
}

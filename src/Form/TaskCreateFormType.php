<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Priority;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Task'])
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add('priority', EntityType::class, ['class' => Priority::class, 'choice_label' => 'name'])
            ->add('description', TextareaType::class, [
                'label' => 'Task Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxLength' => 1500,
                    'placeholder' => 'Add details, notes, or steps for this task...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}

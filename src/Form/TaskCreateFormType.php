<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Priority;
use App\Entity\Task;
use App\Entity\Workspace;
use App\Repository\WorkspaceRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        if (!$user) {
            throw new \LogicException('The TaskCreateFormType requires a User object to be passed in the options.');
        }

        $builder
            ->add('name', TextType::class, ['label' => 'Task'])
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add('priority', EntityType::class, ['class' => Priority::class, 'choice_label' => 'name'])
            ->add('workspace', EntityType::class, ['class' => Workspace::class, 'choice_label' => 'name',
                'query_builder' => function (WorkspaceRepository $er) use ($user) {
                    return $er->findWorkspacesByUser($user);
                }]
            )
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
            'user' => null
        ]);
    }
}

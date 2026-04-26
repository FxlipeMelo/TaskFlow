<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

class UserSecurityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Corporate Email'])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => ['label' => 'New Password'],
                'second_options' => ['label' => 'Confirm New Password'],
                'constraints' => [
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Your password should be at least {{ limit }} characters'
                    )
                ]
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => 'Your current password is incorrect'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

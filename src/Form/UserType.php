<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Form\DataTransformer\RolesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'] ?? null;

        $builder->add('username')
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'First Name',
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Last Name',
            ])
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Admin' => 'ROLE_ADMIN',
                    'Staff' => 'ROLE_STAFF',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Verified',
            ]);

        $builder->get('roles')->addModelTransformer(new RolesTransformer());

        // Only add the status field when the target user is not an admin.
        // This prevents editing the status of admin accounts via the form.
        if (!($user instanceof User && in_array('ROLE_ADMIN', $user->getRoles(), true))) {
            $builder->add('status', EnumType::class, [
                'class' => UserStatus::class,
                'choice_label' => fn(UserStatus $status) => $status->getLabel(),
                'required' => true,
            ]);
        }

        $builder->add('password', PasswordType::class, [
            'mapped' => false, // ✅ Important: we hash manually
            'required' => false, // Make it optional for edits
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

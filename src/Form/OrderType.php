<?php

namespace App\Form;

use App\Entity\Order;
use App\Enum\PaymentStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderNumber')
            ->add('customerName')
            ->add('customerContact')
            ->add('paymentMethod')
            // ->add('paymentMethod', ChoiceType::class, [
            //     'choices' => [
            //         'GCash' => PaymentStatus::GCASH,
            //         'Maya' => PaymentStatus::MAYA,
            //         'Cash' => PaymentStatus::CASH,
            //     ],
            //     'placeholder' => 'Select Payment Method',
            // ])
            ->add('notes')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Processing' => 'processing',
                    'Completed' => 'completed',
                    'Cancelled' => 'cancelled',
                ],
                'data' => 'pending', // default value
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}

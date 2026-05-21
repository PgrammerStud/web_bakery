<?php

namespace App\Form;

use App\Entity\Order;
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
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => [
                'Cash on Delivery' => 'cod',
                'Stripe (Card)' => 'stripe',
                        ],
                'placeholder' => 'Select Payment Method',
                'required' => true,
                  ])
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
                  'Pending'    => 'PENDING',
                  'Paid'       => 'PAID',
                  'Processing' => 'PROCESSING',
                  'Cancelled'  => 'CANCELLED',
                           ],
                   'data' => 'PENDING',
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

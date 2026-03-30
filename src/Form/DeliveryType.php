<?php

namespace App\Form;

use App\Entity\Delivery;
use App\Enum\DeliveryStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Order;

class DeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orders', EntityType::class, [
                'class' => Order::class,
                'choice_label' => 'orderNumber',
                'placeholder' => 'Select Order',
            ])
            ->add('delivery_address')
            ->add('delivery_contact')
            ->add('delivery_date', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => DeliveryStatus::PENDING,
                    'In Transit' => DeliveryStatus::IN_TRANSIT,
                    'Delivered' => DeliveryStatus::DELIVERED,
                    'Returned' => DeliveryStatus::RETURNED,
                ],
            ])
            ->add('delivery_fee', NumberType::class, [
                'scale' => 2,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Delivery::class,
        ]);
    }
}
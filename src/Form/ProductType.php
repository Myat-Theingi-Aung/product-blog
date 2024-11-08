<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// https://www.youtube.com/watch?v=rE79_Wnkmhs
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', null, [
            'attr' => ['class' => 'form-control mb-3', 'placeholder' => 'Product Name'],
            'required' => false,
            'empty_data' => '',
        ])
        ->add('price', null, [
            'attr' => ['class' => 'form-control mb-3', 'placeholder' => 'Product Price'],
            'required' => false,
            'empty_data' => '0.00',
        ])
        ->add('stockQuantity', null, [
            'attr' => ['class' => 'form-control mb-3', 'placeholder' => 'Product Stock Quantity'],
            'required' => false,
            'empty_data' => '0',
        ])
        ->add('description', null, [
            'attr' => ['class' => 'form-control mb-3', 'placeholder' => 'Product Description', 'rows' => 3],
            'required' => false,
            'empty_data' => '',
        ]) 
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

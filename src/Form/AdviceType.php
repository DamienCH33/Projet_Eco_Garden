<?php

namespace App\Form;

use App\Entity\Advice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le contenu du conseil est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'Le contenu ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('month', CollectionType::class, [
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Veuillez indiquer au moins un mois.',
                    ]),
                    new Assert\All([
                        new Assert\Range([
                            'min' => 1,
                            'max' => 12,
                            'notInRangeMessage' => 'Chaque mois doit être compris entre {{ min }} et {{ max }}.',
                        ]),
                    ]),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Advice::class,
            'csrf_protection' => false,
            'allow_extra_fields' => false,
        ]);
    }
}

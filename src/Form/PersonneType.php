<?php

namespace App\Form;

use App\Entity\Hobby;
use App\Entity\Profile;
use App\Entity\Personne;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname')
            ->add('name')
            ->add('age')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('profile', EntityType::class, [
                'expanded' => true,
                'class' => Profile::class,
                'multiple' => false
            ])
            ->add('hobbies', EntityType::class, [
                'expanded' => false,
                'class' => Hobby::class,
                'multiple' => true,
                //requête pour classer les hobbies par ordre alphabétique
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('h')
                        ->orderBy('h.designation', 'ASC');
                },
                //équivalent de la fonction '_toString'
                'choice_label' => 'designation'
            ])
            ->add('job')
            ->add('Soumettre', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personne::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Job;
use App\Entity\Hobby;
use App\Entity\Profile;
use App\Entity\Personne;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname')
            ->add('name')
            ->add('age')
            // ->add('createdAt')
            // ->add('updatedAt')
            ->add('profile', EntityType::class, [
                'expanded' => false,
                'required' => false,
                'class' => Profile::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('hobbies', EntityType::class, [
                'expanded' => false,
                'class' => Hobby::class,
                'required' => false,
                'multiple' => true,
                //requête pour classer les hobbies par ordre alphabétique
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('h')
                        ->orderBy('h.designation', 'ASC');
                },
                //équivalent de la fonction '_toString'
                'choice_label' => 'designation',
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('job', EntityType::class, [
                'required' => false,
                'class' => Job::class,
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Votre image de profil (Des fichiers images uniquement)',
                //'unmapped' = signifie que ce champ n'est associé à aucune propriété d'entité
                'mapped' => false,
                // chaque fois que vous modifiez le détail
                'required' => false,
                // les champs non mappés ne peuvent pas définir leur validation à l'aide d'annotations
                // dans l'entité associée, vous pouvez donc utiliser les contraintes PHP
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => "Format de l'image incorrect !",
                    ])
                ],
            ])
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

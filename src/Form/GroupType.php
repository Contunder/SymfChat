<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Asserts;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Group $group */
        $group = $builder->getData();

        $id = $options['id'];

        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'name',
                    'constraints' => array(
                        new Asserts\NotBlank()
                    )
                ])

            ->add('user_to_groups', EntityType::class,
                [
                    'label' => 'Utilisateurs',
                    'multiple' => true,
                    'attr'=>['class' => 'form-control chosen-select',
                             'data-placeholder' => '---------------- Sélection des Utilisateurs ----------------'],
                    'placeholder' => '-- Sélection --',
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) use($id) {
                        return $er->createQueryBuilder('d')
                            ->where('d.id != :id')
                            ->setParameter('id', $id)
                            ->addOrderBy('d.name', 'ASC');
                    },
                    'choice_label' => function ($user) {
                        /** @var User $user */
                        return $user->getName().' '.$user->getLastname();
                    }
                ]
            );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);

        $resolver->setRequired(['id']);
    }

    public function getBlockPrefix(): string
    {
        return 'form_new_group_';
    }

}
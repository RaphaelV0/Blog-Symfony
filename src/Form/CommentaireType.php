<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajouter les champs du formulaire
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Votre commentaire', 
                'attr' => ['placeholder' => 'Entrez votre commentaire ici...', 'rows' => 5]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Spécifiez que ce formulaire est lié à l'entité Commentaire
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}

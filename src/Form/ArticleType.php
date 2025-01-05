<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Titre de l\'article'],
            ])
            ->add('corps', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['placeholder' => 'Rédigez le contenu de votre article'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (facultatif)',
                'mapped' => false, // L'image n'est pas liée directement à l'entité Article
                'required' => false,
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('datePublication', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de publication',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer l\'article',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

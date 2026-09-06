<?php

/**
 * Task type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class TaskType.
 */
class TaskType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authChecker Authorization checker
     */
    public function __construct(private readonly AuthorizationCheckerInterface $authChecker)
    {
    }

    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.title',
                'required' => true,
                'attr' => ['max_length' => 255],
            ]
        );
        $builder->add(
            'comment',
            TextareaType::class,
            [
                'label' => 'label.comment',
                'required' => false,
            ]
        );
        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'choice_label' => fn (Category $category): ?string => $category->getTitle(),
                'label' => 'label.category',
                'placeholder' => 'label.none',
                'required' => true,
            ]
        );

        if ($this->authChecker->isGranted('ROLE_USER')) {
            $builder->add(
                'startsAt',
                DateTimeType::class,
                [
                    'label' => 'label.starts_at',
                    'required' => false,
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                ]
            );
            $builder->add(
                'endsAt',
                DateTimeType::class,
                [
                    'label' => 'label.ends_at',
                    'required' => true,
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                ]
            );
            $builder->add(
                'minVotesRequired',
                IntegerType::class,
                [
                    'label' => 'label.min_votes_required',
                    'required' => false,
                    'empty_data' => '0',
                ]
            );
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Task::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'task';
    }
}

<?php

/**
 * Contact type.
 */

namespace App\Form\Type;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactType.
 */
class ContactType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder bulider
     * @param array<string, mixed> $options options
     *
     * @see FormTypeExtensionInterface::buildForm() form bulid
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                'label' => 'label.name',
                'required' => true,
                'attr' => ['max_length' => 64],
                ]
            )
            ->add(
                'surname',
                TextType::class,
                [
                    'label' => 'label.surname',
                    'required' => true,
                    'attr' => ['max_length' => 64],
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'label' => 'label.address',
                    'required' => true,
                    'attr' => ['max_length' => 64],
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'label.phone',
                    'required' => true,
                    'attr' => ['max_length' => 64],
                ]
            )
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Contact::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string string return
     */
    public function getBlockPrefix(): string
    {
        return 'contact';
    }
}

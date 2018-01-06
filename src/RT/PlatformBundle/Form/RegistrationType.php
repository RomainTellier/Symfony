<?php
/**
 * Created by PhpStorm.
 * User: Romain
 * Date: 30/12/2017
 * Time: 22:09
 */

namespace RT\PlatformBundle\Form;

    use FOS\UserBundle\Util\LegacyFormHelper;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Validator\Constraints\Choice;
    use Symfony\Component\Validator\Constraints\Length;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
        ->add('prenom')
        ->add('age', IntegerType::class)
        ->add('tel', TextType::class, array(
            'constraints' => new Length(array('min' => 10, 'max' => 10))))
        ->add('ville', ChoiceType::class, array(
            'required' =>false,
            'placeholder' => 'Choix de votre ville',
            'empty_data' => 'Non renseigné',
            'choices' => array(
                'Saint-Quentin' => 'Saint-Quentin',
                'Paris' => 'Paris',
                'Autre ville' => 'Non renseigné',
            ),
            'preferred_choices' => array('Non renseigné'),
        ))
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    // For Symfony 2.x
    public function getNom()
    {
        return $this->getBlockPrefix();
    }
    public function getPrenom()
    {
        return $this->getBlockPrefix();
    }
    public function getAge()
    {
        return $this->getBlockPrefix();
    }
    public function getTel()
    {
        return $this->getBlockPrefix();
    }
    public function getVille()
    {
        return $this->getBlockPrefix();
    }
}

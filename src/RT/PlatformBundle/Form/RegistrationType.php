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
            'constraints' => new Length(array('min' => 10, 'max' => 10)),
        ));
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
}

<?php

namespace Gitonomy\Bundle\FrontendBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class UserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('fullname', 'text')
            ->add('timezone', 'timezone')
            ->add('globalRoles', 'entity', array(
                'class'   => 'Gitonomy\Bundle\CoreBundle\Entity\Role',
                'query_builder' => function(EntityRepository $er) {
                    $query = $er
                        ->createQueryBuilder('R')
                        ->where('R.isGlobal = true')
                        ->orderBy('R.name', 'ASC');

                    return $query;
                },
                'multiple' => true,
                'expanded' => true,
            ))
        ;
    }

    public function getDefaultOptions()
    {
        return array(
            'data_class' => 'Gitonomy\Bundle\CoreBundle\Entity\User',
            'user'       => null,
        );
    }

    public function getParent(array $options)
    {
        return 'baseadmin';
    }

    public function getName()
    {
        return 'adminuser';
    }
}

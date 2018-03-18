<?php

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Form\Field\IconTextType;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortOptionType extends AbstractType {
	
	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		
		$builder
		->add ( 'name', IconTextType::class, [
				'icon' => 'fa fa-tag',
				'label' => 'Sort Option\'s name',
		] )
		->add ( 'field', TextType::class, [
		] )
		->add ( 'save', SubmitEmsType::class, [ 
				'attr' => [ 
						'class' => 'btn-primary btn-sm ' 
				],
				'icon' => 'fa fa-save' 
		] );
		
		if(! $options['createform']){
			$builder->add('remove', SubmitEmsType::class, [
					'attr' => [
							'class' => 'btn-primary btn-sm '
					],
					'icon' => 'fa fa-trash'
			] );
		}
	}
	
	public function configureOptions(OptionsResolver $resolver){
		$resolver->setDefaults ( array (
				'createform' => false,
		) );
	}
}

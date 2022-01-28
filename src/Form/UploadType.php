<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'required' => false,
                'label' => false,
                'attr'=>[
                    'class' => 'form form_upload_file',
                    'required' => false
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'image/*',
                            'image/gif',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.ms-excel',
                            'application/vnd.oasis.opendocument.text',
                            'application/x-abiword',
                            'application/octet-stream',
                            'application/x-bzip',
                            'application/x-bzip2',
                            'application/vnd.oasis.opendocument.presentation',
                            'application/vnd.oasis.opendocument.spreadsheet',
                            'application/vnd.oasis.opendocument.text',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/x-rar-compressed',
                            'application/x-tar',
                            'application/zip',
                            'application/x-7z-compressed',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/csv',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Fichier Image, Document ou Archive autorisée',
                    ])
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_upload_';
    }

}
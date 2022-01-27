<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;
use DateTime;

class SecurityController extends AbstractController
{
    private $doctrine;
    private $entityManager;
    private $formFactory;
    private $hasher;

    /**
     * @param ManagerRegistry $doctrine
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, UserPasswordHasherInterface $hasher)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->hasher = $hasher;
    }

    /**
     * @Route("/", name="security_login")
     */
    public function Login(): Response
    {
        return $this->render('security/login.html.twig', []);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function Logout(){

    }

    /**
     * @Route("/signup", name="security_signup")
     */
    public function Signup(Request $request, ValidatorInterface $validator){

        # FORMULAIRE DE CREATION D'UTILISATEUR
        $newUser = new User();
        $userType = new RegistrationType();
        $form = $this->formFactory->createNamedBuilder($userType->getBlockPrefix().'add', RegistrationType::class, $newUser)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if (!empty($form['pp'])){
                $uploadDirectory = 'upload/pp/';
                $newPP = $form['username']->getViewData();
                $file = $form['pp']->getData();
                $filename = $form['pp']->getViewData()->getClientOriginalName();
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $newPP = $newPP.'.'.$ext;
                $file->move($uploadDirectory, $newPP);
            }else{
                $newPP = 'user-default.png';
            }
            $hash = $this->hasher->hashPassword($newUser, $newUser->getPassword());
            $newUser->setPassword($hash);
            $newUser->setPP('upload/pp/'.$newPP);
            $newUser->setCreatedAt(new DateTimeImmutable());
            $newUser->setUpdatedAt(new DateTime());
            $this->entityManager->persist($newUser);
            $this->entityManager->flush();
            return $this->redirectToRoute('security_login');
        }
        $errors = $validator->validate($newUser);
        if (count($errors) > 0) {
            return $this->render('security/signup.html.twig', [
                'errors' => $errors,
                'FormUser' => $form->createView(),
            ]);
        }
        return $this->render('security/signup.html.twig', [
            'FormUser' => $form->createView(),
            'errors' => '',
        ]);
    }
}

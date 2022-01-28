<?php


namespace App\Controller;

use App\Entity\Message;
use App\Entity\Group;
use App\Entity\User;
use App\Form\GroupType;
use App\Form\UploadType;
use App\Service\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;
use DateTime;

class ChatController extends AbstractController
{
    private $doctrine;
    private $entityManager;
    private $formFactory;

    /**
     * @param ManagerRegistry $doctrine
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/new/conversation", name="new_conversation")
     */
    public function createConversation(Request $request)
    {

        # FORMULAIRE DE CREATION DE CONVERSATION
        $newGroup = new Group();
        $groupType = new GroupType();
        $form = $this->formFactory->createNamedBuilder($groupType->getBlockPrefix() . 'add', GroupType::class, $newGroup, ['id' => $this->getUser()])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newGroup->addUserToGroups($this->getUser());
            $newGroup->setCreatedAt(new DateTimeImmutable());
            $newGroup->setUpdatedAt(new DateTime());
            $this->entityManager->persist($newGroup);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('chat/group.html.twig', [
            'formGroup' => $form->createView(),
        ]);
    }

    /**
     * @Route("/home", name="home")
     */
    public function index(Activity $activity): Response
    {
        # RECUPERE LES INFOS DES CONVERSATIONS DE L'UTILISATEUR
        $groupRepo = $this->doctrine->getRepository(Group::class);
        $groups = $groupRepo->groupsOfUser($this->getUser()->getUserIdentifier());
        $lastSeen = $activity->lastSeen($this->getUser()->getUpdatedAt());


        return $this->render('chat/home.html.twig', [
            'controller_name' => 'ChatController',
            'user' => $this->getUser(),
            'groups' => $groups,
            'lastSeen' => $lastSeen,
        ]);
    }


    /**
     * @Route("/chat/{id}", name="chat")
     */
    public function chat($id, Activity $activity): Response
    {
        $userRepo = $this->doctrine->getRepository(User::class);
        $groupRepo = $this->doctrine->getRepository(Group::class);
        $messageRepo = $this->doctrine->getRepository(Message::class);


        # RÉCUPERE LES UTILISATEURS DE LA CONVERSATION + LA CONV + MET A JOUR LA DATE D'ACTIVITE + LES MESSAGES DE LA CONV
        $user = $userRepo->user($this->getUser()->getUserIdentifier());
        $userInConv = $userRepo->userInGroup($user[0]->getId());
        $group = $groupRepo->oneGroup($id);
        $messages = $messageRepo->messagesOfGroup($group);

        # SAVOIR SI LA CONVERSATIONS EST ACTIVE OU NON
        $lastSeen = $activity->lastSeen($group[0]->getUpdatedAt());

        // Création pour uploader un fichier
        $uploadType = new UploadType();
        $uploadForm = $this->formFactory->createNamedBuilder($uploadType->getBlockPrefix() . 'add', UploadType::class)->getForm();


        return $this->render('chat/chat.html.twig', [
            'controller_name' => 'ChatController',
            'user' => $this->getUser(),
            'userInConv' => $userInConv,
            'group' => $group,
            'messages' => $messages,
            'lastSeen' => $lastSeen,
            'uploadForm' => $uploadForm->createView()
        ]);


    }

    /**
     * @Route("/upload/", name="upload")
     */
    public
    function upload(Request $request): Response
    {

        $uploadType = new UploadType();
        $uploadForm = $this->formFactory->createNamedBuilder($uploadType->getBlockPrefix() . 'add', UploadType::class)->getForm();
        $file = $request->files->get('file');
        $filename = $file->getClientOriginalName();
        $size = $file->getSize();
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploadDirectory = 'upload/files/';
            $newFilename = substr($filename,0,-4).'_'.$this->getUser()->getUserIdentifier().'.'.$file->guessExtension();
            $file->move($uploadDirectory,$newFilename);
            $newFileDirectory = 'file:'.$uploadDirectory.$newFilename;
            return new JsonResponse(
                [
                    'fileSuccess' => true,
                    'message' => $newFileDirectory,
                    'size' => $size,
                    'filename' => $filename,
                ]
            );

        }
        else{
            return new JsonResponse(
                [
                    'fileSuccess' => false,
                    'error' => true,
                    'message' => 'Erreur:' . $uploadForm->getErrors(true),
                ]);
        }

    }

}


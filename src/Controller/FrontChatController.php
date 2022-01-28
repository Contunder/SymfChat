<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Message;
use App\Form\GroupType;
use App\Form\UploadType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTimeImmutable;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FrontChatController extends AbstractController
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
     * @Route("/chat/front/index", name="chat_front_index")
     */
    public function chatApp(): Response
    {
        # RECUPERE LES INFOS DES CONVERSATIONS DE L'UTILISATEUR
        $userRepo = $this->doctrine->getRepository(User::class);
        $groupRepo = $this->doctrine->getRepository(Group::class);
        $username = $this->getUser()->getUserIdentifier();
        $user = $userRepo->user($username);
        $groups = $groupRepo->groupsOfUser($username);
        return $this->render('chat/front/index.html.twig', [
            'user' => $user,
            'groups' => $groups,
        ]);
    }

    /**
     * @Route("/chat/front/render", name="chat_front_render")
     */
    public function jsRender(Request $request): Response
    {
        if (!empty($request->get("groupId"))) {
            $id = $request->get("groupId");
            $userRepo = $this->doctrine->getRepository(User::class);
            $groupRepo = $this->doctrine->getRepository(Group::class);
            $messageRepo = $this->doctrine->getRepository(Message::class);
            $username = $this->getUser()->getUserIdentifier();
            $user = $userRepo->user($username);


            # RÉCUPERE LES UTILISATEURS DE LA CONVERSATION + LA CONV + MET A JOUR LA DATE D'ACTIVITE + LES MESSAGES DE LA CONV
            $userInConv = $userRepo->userInGroup($user[0]->getId());
            $group = $groupRepo->oneGroup($id);
            $messages = $messageRepo->messagesOfGroup($group);

            # SAVOIR SI LA CONVERSATIONS EST ACTIVE OU NON
            $lastSeen = $groupRepo->lastSeen($id);

            // Création pour uploader un fichier
            $uploadType = new UploadType();
            $uploadForm = $this->formFactory->createNamedBuilder($uploadType->getBlockPrefix() . 'add_'.$id, UploadType::class)->getForm();
            $view = $this->renderView('chat/front/chat.html.twig', [
                'user' => $this->getUser(),
                'userInConv' => $userInConv,
                'group' => $group,
                'messages' => $messages,
                'lastSeen' => $lastSeen,
                'uploadForm' => $uploadForm->createView()
            ]);
            return new JsonResponse(
                [
                    'success' => true,
                    'view' => $view,
                ]
            );
        } else {
            return new JsonResponse(
                [
                    'success' => false,
                    'view' => 'erreur',
                ]
            );
        }

    }

    /**
     * @Route("/chat/front/upload/{id}/", name="chat_front_upload")
     */
    public function upload(Request $request, $id): Response
    {
        $uploadType = new UploadType();
        $uploadForm = $this->formFactory->createNamedBuilder($uploadType->getBlockPrefix() . 'add_'.$id, UploadType::class)->getForm();
        $file = $request->files->get('form_upload_add_'.$id);
        $filename = $file['file']->getClientOriginalName();
        $size = $file['file']->getSize();
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploadDirectory = 'upload/files/';
            $newFilename = substr($filename,0,-4).'_'.$this->getUser()->getUserIdentifier().'.'.$file['file']->guessExtension();
            $file['file']->move($uploadDirectory,$newFilename);
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
                    'error' => $uploadForm->isSubmitted(),
                    'view' => $this->render('chat/front/form-errors.html.twig', ['form' => $uploadForm->createView()])->getContent(),
                    'message' => 'Erreur:' . $uploadForm->getErrors(true),
                ]);
        }

    }

    /**
     * @Route("/chat/front/group", name="chat_front_group")
     */
    public function newGroup(Request $request)
    {
        $directoryRepo = $this->doctrine->getRepository(User::class);
        $username = $this->getUser()->getUserIdentifier();
        $user = $directoryRepo->user($username);

        # FORMULAIRE DE CREATION DE CONVERSATION
        $newGroup = new Group();
        $groupType = new GroupType();
        $form = $this->formFactory->createNamedBuilder($groupType->getBlockPrefix() . 'add', GroupType::class, $newGroup, ['id' => $user[0]->getId()])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupeName = $request->get('form_new_group_add');
            $groupeName = $groupeName['name'];
            $newGroup->addUserToGroups($user[0]);
            $newGroup->setCreatedAt(new DateTimeImmutable());
            $newGroup->setUpdateAt(new DateTime());
            $this->entityManager->persist($newGroup);
            $this->entityManager->flush();
            return new JsonResponse(
                [
                    'createGroupSuccess' => true,
                    'groupMessage' => 'Groupe :' . $groupeName . 'crée avec sucées',

                ]
            );
        }
        $view = $this->renderView('chat/front/group.html.twig', [
            'formGroup' => $form->createView(),
        ]);
        return new JsonResponse(
            [
                'groupSuccess' => true,
                'createGroupSuccess' => false,
                'view' => $view,

            ]
        );
    }

    /**
     * @Route("/chat/front/delete/group/{id}", name="chat_front_delete_group")
     */
    public function deleteGroup($id)
    {

        $groupRepo = $this->doctrine->getRepository(Group::class);
        $group = $groupRepo->find($id);
        $groupName = $group->getName();
        $this->entityManager->remove($group);
        $this->entityManager->flush();
            return new JsonResponse(
                [
                    'deleteGroupSuccess' => true,
                    'groupMessage' => 'Groupe : ' . $groupName . ' supprimé avec sucées',

                ]
            );

    }

    /**
     * @Route("/chat/front/update/group/{id}", name="chat_front_update_group")
     */
    public function updateGroup(Request $request, $id)
    {
        $directoryRepo = $this->doctrine->getRepository(User::class);
        $groupRepo = $this->doctrine->getRepository(Group::class);
        $group = $groupRepo->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $user = $directoryRepo->user($username);

        # FORMULAIRE DE CREATION DE CONVERSATION
        $newGroup = new Group();
        $groupType = new GroupType();
        $form = $this->formFactory->createNamedBuilder($groupType->getBlockPrefix() . 'update', GroupType::class, $newGroup, ['id' => $user[0]->getId()])->getForm();
        //$form ->
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupeName = $request->get('form_new_group_add');
            $groupeName = $groupeName['Name'];
            $newGroup->setCreatedAt(new DateTimeImmutable());
            $newGroup->setUpdateAt(new DateTime());
            $this->entityManager->persist($newGroup);
            $this->entityManager->flush();
            return new JsonResponse(
                [
                    'createGroupSuccess' => true,
                    'groupMessage' => 'Groupe :' . $groupeName . 'mis à jour avec sucées',

                ]
            );
        }
        $view = $this->renderView('chat/front/group.html.twig', [
            'formGroup' => $form->createView(),
        ]);
        return new JsonResponse(
            [
                'groupSuccess' => true,
                'createGroupSuccess' => false,
                'view' => $view,

            ]
        );
    }
}

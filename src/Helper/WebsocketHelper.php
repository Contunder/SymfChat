<?php

namespace App\Helper;

use App\Entity\Message;
use App\Entity\Group;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class WebsocketHelper implements MessageComponentInterface
{

    protected $connections;
    private $doctrine;
    private $entityManager;

    /**
     * @param ManagerRegistry $doctrine
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager)
    {

        $this->connections = new SplObjectStorage;
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
    }

    public function onOpen(ConnectionInterface $conn){
        $this->connections->attach($conn);

        echo "Nouvelle Connection! (".$conn->resourceId.")\n";
    }

    public function onMessage(ConnectionInterface $from, $msg){
        foreach($this->connections as $connection)
        {
            if($connection === $from)
            {
                $newMSG = json_decode($msg);
                $groupRepo = $this->doctrine->getRepository(Group::class);

                # D'ENVOIE DANS LE CHAT
                if($newMSG->message = "CONNECTED"){
                    $user = $this->entityManager->getRepository(User::class)->find($newMSG->messUserId);
                    $user->setUpdatedAt(new DateTime());
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }
                else if ($newMSG->message != "..." && $newMSG->message != "delete"){
                    $group = $this->entityManager->getRepository(Group::class)->find($newMSG->messGroupId);
                    $user = $this->entityManager->getRepository(User::class)->find($newMSG->messUserId);
                    $newMessage = new Message();
                    $newMessage->setAppUser($user);
                    # SI IL S'AGIT D'UN FICHIER + COMPARAISON IMAGE OU FICHIER
                    if (substr($newMSG->message, 0,5)=="file:"){
                        $fileName = substr($newMSG->message,5);
                        $extFile = pathinfo($fileName, PATHINFO_EXTENSION);
                        $extAllowedImg = array("jpg", "jpeg", "png", "gif");
                        if (in_array($extFile, $extAllowedImg)) {
                            $newMessage->setMessage("img:".$fileName);
                        }else{
                            $newMessage->setMessage("file:".$fileName);}
                    }else{
                        $newMessage->setMessage($newMSG->message);
                    }
                    $newMessage->setCreatedAt(new DateTimeImmutable());
                    $newMessage->setUpdatedAt(new DateTime());
                    $group->setUpdatedAt(new DateTime());
                    $newMessage->addMessageToGroups($groupRepo->find($newMSG->messGroupId));
                    //$this->entityManager->persist($newMessage);
                    //$this->entityManager->flush();
                    echo "Transfert Terminer\n";
                }
                continue;
            }
            $connection->send($msg);
            echo $msg;
        }

    }

    public function onClose(ConnectionInterface $conn){
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e){
        $this->connections->detach($conn);
        $conn->close();
    }

}
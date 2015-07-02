<?php

//src//Mara//OauthBundle//PHPservices/YoutubeRequestNewVids.php

namespace Mara\Ymanager\PHPservices;

/**
 * Created by PhpStorm.
 * User: humito
 * Date: 06/04/2015
 * Time: 18:20
 */

use Doctrine\ORM\EntityManager;
use Exception;
use Google_Service_YouTube_Playlist;
use Google_Service_YouTube_PlaylistItem;
use Google_Service_YouTube_PlaylistItemSnippet;
use Google_Service_YouTube_PlaylistSnippet;
use Google_Service_YouTube_PlaylistStatus;
use Google_Service_YouTube_ResourceId;
use Symfony\Component\HttpFoundation\Session\Session;


class YoutubeRequestNewVids
{
    protected $doctrine;
    protected $session;

    /**
     * @InjectParams
     */
    public function __construct(EntityManager $em, Session $session)
    {
        $this->em =$em;
        $this->session=$session;
    }

    // recupère les vidéos des chaines de $chanList, publiées entre $dateAfter et $dateBefore (mettre -1 si pas de $datebefore), renvoie un tableau d'id de vidéos
   public  function getVideosFromChans($bot, $dateAfter)
    {
        $chansList= array();
        $videoList = array();

        // retrieve channels subscribed
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:BotChan', 'u')
            ->where('u.botId = :bid')
            ->setParameter('bid',$bot);

        $result = $qb->getQuery()->getResult();

        foreach($result as $r)
        {
            array_push($chansList,$r->getChannelId());
        }

        foreach ($chansList as $chan) {
            //$htmlBody.='-----------------------<br><div>channel : '.$chan.'</div>';
            // récupération des vidéos pour la chaine $chan
            $videoListToMerge = $this->searchVidsFromOneChan($chan, $dateAfter, time());
            //$videoListToMerge=searchVidsFromTo($chan['id'],$dateAfter,time());

            $videoList = array_merge($videoList, $videoListToMerge);
        }
        return $videoList;
    }

    // remplace la valeur du champ "lastHarvest" (timestamp) du bot ($botId) par $newDate (timestamp), renvoi 1 si success , 0 si fail
    public function changeLastHarvest($botId,$newDate)
    {
        // retrieve channels subscribed
        $qb = $this->em->createQueryBuilder();
        $qb->update('MaraYmanagerBundle:Bot','u')
            ->set('u.lastHarvest', $newDate)
            ->where('u.id = :bid')
            ->setParameter('bid',$botId);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // remplace la valeur du champ "lastHarvest" (timestamp) du watchBot ($botId) par $newDate (timestamp), renvoi 1 si success , 0 si fail
    public function changeLastHarvestW($botId,$newDate)
    {
        // retrieve channels subscribed
        $qb = $this->em->createQueryBuilder();
        $qb->update('MaraYmanagerBundle:watchBot','u')
            ->set('u.lastHarvest', $newDate)
            ->where('u.id = :bid')
            ->setParameter('bid',$botId);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // retourne le résultat de la requête qui récupère l'utilisateur avec le compte youtube associé $myChannelId (id youtube), => pour lire le résultat : result[0]
    public function getUserByYoutubeId($myChannelId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:OauthUser', 'u')
            ->where('u.googleId = :gid')
            ->setParameter('gid', $myChannelId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // retourne le résultat de la requête qui récupère le bot avec le compte youtube associé $myChannelId (id youtube) et créé à la date de $createDate(timestamp), => pour lire le résultat : result[0]
    // est utilisé pour récupérer l'id d'un bot nouvellement créé.
    public function getBotByUserAndCreateDate($myChannelId, $createDate )
    {
        // retrieve the new bot (to have the auto-increment id)
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:Bot', 'u')
            ->where('u.userId = :uid AND u.createDate= :ucd' )
            ->setParameter('uid', $myChannelId)
            ->setParameter('ucd', $createDate)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result[0];
    }

    // retourne le résultat de la requête qui récupère le watchBot avec le compte youtube associé $myChannelId (id youtube) et créé à la date de $createDate(timestamp), => pour lire le résultat : result[0]
    // est utilisé pour récupérer l'id d'un bot nouvellement créé.
    public function getWatchBotByUserAndCreateDate($myChannelId, $createDate )
    {
        // retrieve the new bot (to have the auto-increment id)
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:watchBot', 'u')
            ->where('u.userId = :uid AND u.createDate= :ucd' )
            ->setParameter('uid', $myChannelId)
            ->setParameter('ucd', $createDate)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result[0];
    }

    // efface le bot ($botId) et les subs associées,1 si success, 0 si fail
    // TODO  optimiser la requete SQL, soit pas join soit par cascade dans BDD
    public function deleteBot($botId)
    {
        $ret=1;
        // retrieve the new bot (to have the auto-increment id)
        $qb = $this->em->createQueryBuilder();
        $qb->Select('u')
            ->from('MaraYmanagerBundle:Bot', 'u')
            ->where('u.id= :bid')
            ->setParameter('bid', $botId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        if (count($result))
        {
            $this->em->remove($result[0]);
            $this->em->flush();
        }else
        {
            $ret=0;
        }

        $qb= $this->em->createQueryBuilder();
        $qb->Select('u')
        ->from('MaraYmanagerBundle:BotChan', 'u')
        ->where('u.botId= :bid')
        ->setParameter('bid', $botId);

        $result = $qb->getQuery()->getResult();
         if (count($result))
         {
             foreach($result as $botChan)
             {
                 $this->em->remove($botChan);
                 $this->em->flush();
              }
             $this->em->remove($result[0]);
             $this->em->flush();

         }else
         {
             $ret=0;
         }

        return $ret;
    }

    // récupère un bot par son Id
    public function getBotById($botId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:Bot', 'u')
            ->where('u.id = :bid' )
            ->setParameter('bid', $botId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // récupère un watchBot par son Id
    public function getWBotById($botId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:watchBot', 'u')
            ->where('u.id = :bid' )
            ->setParameter('bid', $botId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    // efface la chaine souscrite ($chanId) pour le bot $botId, retourne, 1 si success, 0 si fail
    public function deleteBotChan($chanId, $botId)
    {
        // retrieve the new bot (to have the auto-increment id)
        $qb = $this->em->createQueryBuilder();
        $qb->Select('u')
            ->from('MaraYmanagerBundle:BotChan', 'u')
            ->where('u.channelId = :cid AND u.botId= :bid' )
            ->setParameter('cid', $chanId)
            ->setParameter('bid', $botId)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        if (count($result))
        {
            $this->em->remove($result[0]);
            $this->em->flush();
            $ret=1;
        }else
        {
            $ret=0;
        }
        return $ret;
    }

    // retourne un tableau des id des videos d'une chaine , publiées après $dateAfter
    function searchVidsFromOneChan($chan, $dateAfter, $dateBefore)
    {
        $youtube = $this->session->get('youtube');

        $chan=trim($chan);

        $videoList = array();

        $dateAfterRFC = date('c', $dateAfter);
        try{

            if ($dateBefore == '-1') {
                $videosResponse = $youtube->search->listSearch('snippet',
                    array(
                        'channelId' => $chan,
                        'publishedAfter' => $dateAfterRFC,
                        'order' => 'date',
                        'maxResults' => '50',
                        'type' => 'video'
                    )
                );

            } else {

                $dateBeforeRFC = date('c', $dateBefore);
                $videosResponse = $youtube->search->listSearch('snippet',
                    array(
                        'channelId' => $chan,
                        'publishedAfter' => $dateAfterRFC,
                        'publishedBefore' => $dateBeforeRFC,
                        'order' => 'date',
                        'maxResults' => '50',
                        'type' => 'video'
                    )
                );
            }

        } catch (Google_ServiceException $e) {
            throw $e;

        } catch (Google_Exception $e) {
          throw $e;
        }



        //var_dump($videosResponse['items']);
        // ajout des vidéos au tableau des vidéos
        foreach ($videosResponse['items'] as $vid) {
            if ($vid['id']['kind'] == 'youtube#video') {
                //$videoList[]=array("name"=>$vid[],"id"=>$vid['id']['videoId']);
                $videoList[] = $vid;
            }
        }


        return $videoList;
    }


    // return list of $botId channels
    function affBotChannels ($botId)  {

        $resp=array();

        // retrieve channels subscribed
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:BotChan', 'u')
            ->where('u.botId = :bid')
            ->setParameter('bid',$botId);

        $result = $qb->getQuery()->getResult();


        foreach($result as $r)
        {
            array_push($resp,$r );
        }

        return $resp;
    }

    // crée une plyalist privée, retourne l'Id youtube de la playlist
    public function createPrivPlaylist($title, $description){

        $youtube = $this->session->get('youtube');
        $playlistResponse='';

        try{

            //create snippet
            $playlistSnippet=new Google_Service_YouTube_PlaylistSnippet();
            $playlistSnippet->setTitle($title);
            $playlistSnippet->setDescription($description);

            // set status
            $playlistStatus=new Google_Service_YouTube_PlaylistStatus();
            $playlistStatus->setPrivacyStatus('public');

            // create playlist and associate resources
            $youTubePlaylist = new Google_Service_YouTube_Playlist();
            $youTubePlaylist->setSnippet($playlistSnippet);
            $youTubePlaylist->setStatus($playlistStatus);

            // call of the create method
            $playlistResponse = $youtube->playlists->insert('snippet,status',  $youTubePlaylist, array());
        } catch (Google_ServiceException $e) {
            throw $e;

        } catch (Google_Exception $e) {
            throw $e;
        }
        return $playlistResponse;
    }

    // ajoute la video youtube ($vidId) à la playlist ($playlistId)
    public function addVid($vidId, $playlistId){

        $youtube = $this->session->get('youtube');
        // defining the resource

        try{
            $resourceId = new Google_Service_YouTube_ResourceId();
            $resourceId->setVideoId($vidId);
            $resourceId->setKind('youtube#video');

            //  snippet for the playlist item.
            $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
            $playlistItemSnippet->setTitle('First video in the test playlist');
            $playlistItemSnippet->setPlaylistId($playlistId);
            $playlistItemSnippet->setResourceId($resourceId);

            //create playlistItem and add it
            $playlistItem = new Google_Service_YouTube_PlaylistItem();

            $playlistItem->setSnippet($playlistItemSnippet);

            $playlistItemResponse = $youtube->playlistItems->insert(
                'snippet,contentDetails', $playlistItem, array());

        } catch (Google_ServiceException $e) {
            throw $e;

        } catch (Google_Exception $e) {
            throw $e;
        }


        return $playlistItemResponse;
    }

    // retourne une liste des vidéos publiées depuis la date lastHarvest pour le watchBot  $watchBotId
    public function getVideosFromWatcherBot($watchBotId)
    {
        // retrieve channels subscribed
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('MaraYmanagerBundle:watchBot', 'u')
            ->where('u.id = :bid')
            ->setParameter('bid',$watchBotId)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();




        $youtube = $this->session->get('youtube');

        $videoList = array();

        $dateAfterRFC = date('c', $result[0]->getLastHarvest());

        try{

                $videosResponse = $youtube->search->listSearch('snippet',
                    array(
                        'q'=>$result[0]->getSearchWord(),
                        'publishedAfter' => $dateAfterRFC,
                        'order' => 'date',
                        'maxResults' => '50',
                        'type' => 'video'
                    )
                );


        } catch (Google_ServiceException $e) {
            throw $e;

        } catch (Google_Exception $e) {
            throw $e;
        }

        //var_dump($videosResponse['items']);
        // ajout des vidéos au tableau des vidéos
        foreach ($videosResponse['items'] as $vid) {
            if ($vid['id']['kind'] == 'youtube#video') {
                //$videoList[]=array("name"=>$vid[],"id"=>$vid['id']['videoId']);
                $videoList[] = $vid;
            }
        }
        return $videoList;
    }
}

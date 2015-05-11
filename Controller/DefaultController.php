<?php




namespace Mara\Ymanager\Controller;


use Mara\Ymanager\Entity\Bot;
use Mara\Ymanager\Entity\BotChan;
use SplHeap;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Mara\Ymanager\Token\OauthToken;
use Mara\Ymanager\Entity\OauthUser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        //create google Client
        $client = new \Google_Client();

        $client->setClientId($this->container->getParameter('client_id'));
        $client->setClientSecret($this->container->getParameter('client_secret'));
        $client->setScopes('https://www.googleapis.com/auth/youtube');


        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
            FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);


        if ($this->get('security.context')->isGranted('ROLE_OAUTH_USER'))
        {

            $searchVidsService=$this->container->get('search.Vids');
            $myId=$request->getSession()->get('user')->getGoogleID();
            $request->getSession()->get('user')->setBots($searchVidsService->getMyBots($myId));

            //$newVids=$ret->getsVideosFromChans(bot.chan time(),-1);

            return $this->render('MaraYmanagerBundle:Default:index.html.twig', array('test' => 'test'));
          //  return $this->render('MaraYmanager:Default:index.html.twig', array( 'test' => $this->get('security.token_storage')->getToken()->getGoogleId()));
        }
        else  // if not authenticate as Oauth
        {
            if ($request->query->get('code'))  // if code is set (callback from google login form)
            {
                // exchange code vs access and refresh token
                $client->authenticate($request->query->get('code'));

                // create youtube service object
                $youtube = new \Google_Service_YouTube($client);
                $request->getSession()->set('youtube', $youtube);

                //retrieve channelId
                $myChannelId=$this->getMyChannelId($youtube);

                // retrieve my subscriptions
                $subs=$this->mySubscriptions($youtube);

                $searchVidsService=$this->container->get('search.Vids');
               // $ret=$searchVidsService->deleteBotChan($chanId, $botId);

                // check if user exists in Database
                $result=$searchVidsService->getUSerByYoutubeId($myChannelId);


                // if not exists, create it and persist
                if (count($result)) {
                    $user=$result[0];
                    $user->setBots($searchVidsService->getMyBots($myChannelId));
                    $user->setSubs($subs);
                } else {
                    $em=$this->getDoctrine()->getManager();
                    $user=new OauthUser($myChannelId);
                    $user->setSubs($subs);
                    $em->persist($user);
                    $em->flush();
                }

                // manually authenticate
                $roles=array('ROLE_USER', 'ROLE_OAUTH_USER');
                $token = new OauthToken($myChannelId, null, "ym", $roles, $client->getAccessToken(), $myChannelId, $subs);
                $this->get('security.token_storage')->setToken($token);
                $request->getSession()->set('_security_hello', serialize($token));


                // keep user in session
                $request->getSession()->set('user', $user);

                return $this->render('MaraYmanagerBundle:Default:index.html.twig', array('test'=> $myChannelId, 'user'=>$user));
            }else{

             // if code is not set (not yet passed by google login form)
                $state = mt_rand();
                $client->setState($state);
                $_SESSION['state'] = $state;

                $stateStr=strval($state);
                $client->setApprovalPrompt('force');
                $client->setAccessType('offline');

                $authUrl = $client->createAuthUrl();

                return $this->redirect($authUrl);
            }
        }
    }


    public function harvestAction(Request $request)
    {
        $newDateHarvest=time();
        $playlistId='0';
        $botName='';
        if($request->isXmlHttpRequest()) {
            $botId = $request->request->get('botId');
            $searchVidsService = $this->container->get('search.vids');

            $result=$searchVidsService->getBotById($botId);

            if (count($result))
                {
                    $dateAfter = $result[0]->getLastHarvest();
                    $botName= $result[0]->getName();
                }



            $vids = $searchVidsService->getVideosFromChans($botId, $dateAfter);
            $vidsOrdered=$this->orderVideoList($vids);

            $title = 'videos from ' . date('r', $dateAfter) . ' to ' . date('r');
            $description = 'videos from ' . date('r', $dateAfter) . ' to ' . date('r');

            // voir pour try catch
                $playlistId = $searchVidsService->createPrivPlaylist($title, $description);
                foreach($vidsOrdered as $vid)
                {
                    $searchVidsService->addVid($vid['id']['videoId'], $playlistId['id']);
                }

                $searchVidsService->changeLastHarvest($botId, $newDateHarvest);
        }
            return new Response ($playlistId['id'].";".$newDateHarvest.";".$botName);
    }

    public function deleteBotChanAction(Request $request)
    {
        $ret =1;
        if($request->isXmlHttpRequest())
        {
            $chanId=$request->request->get('chanId');
            $botId=$request->request->get('botId');

            $searchVidsService=$this->container->get('search.Vids');
            $ret=$searchVidsService->deleteBotChan($chanId, $botId);
        }
        return new Response($ret);
    }

    public function deleteBotAction(Request $request)
    {
        $ret =1;
        if($request->isXmlHttpRequest())
        {

            $botId=$request->request->get('botId');

            $searchVidsService=$this->container->get('search.Vids');
            $ret=$searchVidsService->deleteBot($botId);

        }
        return new Response($ret);
    }

    public function createBotAction(Request $request)
    {
        $return='fail';
        $chans=array();

        if($request->isXmlHttpRequest()) {

            // retrieve list of channel
            $chansTab=$request->request->get('chansTab');
            $chansTemp=explode(';',$chansTab);
            $name=$request->request->get('name');

            for ($i=0;$i<count($chansTemp);$i=$i+2)
            {
                $chans[$i/2]=array('channelId'=>$chansTemp[$i],'title'=>$chansTemp[$i+1]);
            }

            // retrieve doctrine manager and mychannelId
            $em=$this->getDoctrine()->getManager();
            $myChannelId=$this->get('security.token_storage')->getToken()->getGoogleId();

            // create newBot and persist
            $newBot= new Bot($myChannelId, $name);
            $em->persist($newBot);
            $em->flush();

            $searchVidsService=$this->container->get('search.Vids');
            $newBot=$searchVidsService->getBotByUserAndCreateDate($myChannelId, $newBot->getCreateDate());


            if ($newBot!=null)
            {
                $user=$request->getSession()->get('user');
                $oldBots=$user->getBots();
                array_push($oldBots, $newBot);
                $user->setBots($oldBots);
                $request->getSession()->set('user', $user);
                foreach($chans as $chan)
                {
                    $return=$newBot->getId();
                    $botChan=new BotChan($return,trim($chan["channelId"]),trim($chan["title"]));
                    $em->persist($botChan);
                }
            }else // if fail
            {
                //TODO gérer erreur
            }
            $em->flush();
        }
        return new Response($return);
    }

        /**
         * @param $youtube
         * @return array of subscribed channels
         */
        function mySubscriptions($youtube){

        try{
            $channelsResponse=$youtube->subscriptions->listSubscriptions('snippet', array('mine' => true, 'maxResults'=>'50'));

            $subscriptionsList=array();

            // ajout des chaine aux tableaux des chaines
            foreach($channelsResponse['items'] as $channel)
            {
                $subscriptionsList[]=$channel;
            }


        } catch (Google_ServiceException $e) {
            throw $e;

        } catch (Google_Exception $e) {
            throw $e;
        }


        return $subscriptionsList;
      }

    /**
     * @param $youtube
     * @return string my youtubeChannelId
     */
      function getMyChannelId($youtube){

          try{
              $myChanResponse=$youtube->channels->listChannels('id', array('mine'=>true));
              $myChan=$myChanResponse['items']['0']['id'];

          } catch (Google_ServiceException $e) {
              throw $e;

          } catch (Google_Exception $e) {
              throw $e;
          }



        return $myChan;
     }


    // order a videos List from older to earlier release, return the new videoList
    function orderVideoList($videoList )
    {
        $newVideoList=new SimpleHeap();

        foreach ($videoList as $vid)
        {
            $newVideoList->insert($vid);
        }
        return $newVideoList;
    }


}

// max Heap List
class SimpleHeap extends SplHeap
{

    public function  compare( $value1, $value2 ) {
        $stamp1=strtotime($value1['snippet']['publishedAt']);
        $stamp2=strtotime($value2['snippet']['publishedAt']);

        if ( $stamp1 > $stamp2)
        {
            $resp=-1;
        }
        else if ($stamp1==$stamp2)
        {
            $resp=0;
        }
        else{
            $resp=1;
        }

        return $resp;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: humito
 * Date: 15/04/2015
 * Time: 19:27
 */

namespace Mara\Ymanager\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class OauthToken extends UsernamePasswordToken
{
     private $googleId;
     private $accessToken;
     private $subs;
     private $bots;


    /**
     * @param string|array $accessToken The OAuth access token
     * @param array        $roles       Roles for the token
     */

    public function __construct($user, $credentials, $providerKey, array $roles = array(),$accessToken, $googleId, $subs)
    {
        parent::__construct($user, $credentials, $providerKey, $roles);
        $this->setAccessToken($accessToken);
        $this->setGoogleId($googleId);
        $this->subs=$subs;
        $this->bots='';
    }


    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @param mixed $googleId
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return mixed
     */
    public function getSubs()
    {
        return $this->subs;
    }

    /**
     * @param mixed $subs
     */
    public function setSubs($subs)
    {
        $this->subs = $subs;
    }

    /**
     * @return mixed
     */
    public function getBots()
    {
        return $this->bots;
    }

    /**
     * @param mixed $bots
     */
    public function setBots($bots)
    {
        $this->bots = $bots;
    }


    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccesstoken($accessToken)
    {
        $this->accessToken=$accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->accessToken, $this->subs,$this->bots, $this->googleId, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->accessToken, $this->subs, $this->bots, $this->googleId, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
?>
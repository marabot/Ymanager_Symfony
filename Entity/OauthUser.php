<?php
// src//Mara//OauthBundle//Entity//OauthUser.php


namespace Mara\Ymanager\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Created by PhpStorm.
 * User: humito
 * Date: 06/04/2015
 * Time: 16:32
 */

/**
 * OauthUser
 *
 * @ORM\Table(name="OauthUser")
 * @ORM\Entity(repositoryClass="Mara\Ymanager\Entity\OauthUserRepository")

 */

class OauthUser implements UserInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var array
     */
    protected $subs;

    /**
     * @var array
     */
    protected $bots;

    /**
     * @var array
     */
    protected $watchBots;

    /**
     * @var string
     *
     * @ORM\Column(name="google_id", type="string", length=255, unique=true, nullable=true)
     */
    protected $googleId;

    /**
     * @param string $username
     */
    public function __construct($googleId)
    {
        $this->googleId = $googleId;
    }


    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
    }


    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }


    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     * @return OauthUser
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @return array
     */
    public function getWatchBots()
    {
        return $this->watchBots;
    }

    /**
     * @param array $bots
     */
    public function setWatchBots($watchBots)
    {
        $this->watchBots = $watchBots;
    }

    /**
     * @return array
     */
    public function getBots()
    {
        return $this->bots;
    }

    /**
     * @param array $bots
     */
    public function setBots($bots)
    {
        $this->bots = $bots;
    }

    /**
     * @return array
     */
    public function getSubs()
    {
        return $this->subs;
    }

    /**
     * @param array $subs
     */
    public function setSubs($subs)
    {
        $this->subs = $subs;
    }
}

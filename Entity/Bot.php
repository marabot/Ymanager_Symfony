<?php
// src//Mara//OauthBundle//Entity//Bot.php

/**
 * Created by PhpStorm.
 * User: humito
 * Date: 16/04/2015
 * Time: 17:36
 */

namespace Mara\Ymanager\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Bot
 * @package Mara\Ymanager\Entity
 *
 * @ORM\Table (name="bot")
 * @ORM\Entity(repositoryClass="Mara\Ymanager\Entity\BotRepository")
 */
class Bot
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
     * @var string
     *
     * @ORM\Column(name="userId", type="string")
     *
     */
    protected $userId;


    /**
     * @var integer
     *
     * @ORM\Column (name="lastHarvest", type="integer")
     */
    protected $lastHarvest;

    /**
     * @var string
     *
     * @ORM\Column (name="name", type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column (name="createDate", type="integer")
     */
    protected $createDate;

    /**
     * @param $myChan
     * @param $name
     */
    public function __construct($myChan, $name)
    {
        $this->userId=$myChan;
        $this->createDate=time();
        $this->lastHarvest=time();
        $this->name=$name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @return int
     */
    public function getLastHarvest()
    {
        return $this->lastHarvest;
    }

    /**
     * @param int $lastharvest
     */
    public function setLastHarvest($lastharvest)
    {
        $this->lastharvest = $lastharvest;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

}

?>
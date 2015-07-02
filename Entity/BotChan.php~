<?php
// src//Mara//OauthBundle//Entity//BotChan.php

/**
* Created by PhpStorm.
* User: humito
* Date: 16/04/2015
* Time: 17:36
*/

namespace Mara\Ymanager\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * BotChan
 * @package Mara\Ymanager\Entity
 *
 * @ORM\Table (name="botChan")
 * @ORM\Entity(repositoryClass="Mara\Ymanager\Entity\BotChanRepository")
 */
class BotChan
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="botId",type="string")
     */
    protected $botId;

    /**
     * @var string
     *
     *
     * @ORM\Id
     * @ORM\Column(name="channelId",type="string")
     */
    protected $channelId;

    /**
     * @var string
     * @ORM\Column(name="title", type="string");
     */
    protected $title;

    public function __construct($botId,$channelId,$title){
        $this->botId=$botId;
        $this->channelId=$channelId;
        $this->title=$title;
    }


    /**
     * @return string
     */
    public function getBotId()
    {
        return $this->botId;
    }


    /**
     * @return string
     */
    public function getChannelId()
    {
        return $this->channelId;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

}

?>
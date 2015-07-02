<?php

namespace Mara\Ymanager\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * watchBot
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mara\Ymanager\Entity\watchBotRepository")
 */
class watchBot
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="userId", type="string", length=255)
     */
    private $userId;



    
    /**
     * @var integer
     *
     * @ORM\Column(name="lastHarvest", type="integer")
     */
    private $lastHarvest;

    /**
     * @var string
     *
     * @ORM\Column(name="$searchWord", type="string", length=255)
     */
    private $searchWord;

    /**
     * @var integer
     *
     * @ORM\Column(name="$createDate", type="integer")
     */
    private $createDate;




    /**
     * @param $myChan
     * @param $name
     */
    public function __construct($myChan, $textToWatch)
    {
        $this->userId=$myChan;
        $this->createDate=time();
        $this->lastHarvest=time();
        $this->searchWord=$textToWatch;

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
     * Set userId
     *
     * @param string $userId
     * @return watchBot
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set lastHarvest
     *
     * @param integer $lastHarvest
     * @return watchBot
     */
    public function setLastHarvest($lastHarvest)
    {
        $this->lastHarvest = $lastHarvest;

        return $this;
    }

    /**
     * Get lastHarvest
     *
     * @return integer 
     */
    public function getLastHarvest()
    {
        return $this->lastHarvest;
    }

    /**
     * Set $searchWord
     *
     * @param string $$searchWord
     * @return watchBot
     */
    public function setSearchWord($searchWord)
    {
        $this->searchWord = $searchWord;

        return $this;
    }

    /**
     * Get $searchWord
     *
     * @return string 
     */
    public function getSearchWord()
    {
        return $this->searchWord;
    }

    /**
     * Set $createDate
     *
     * @param integer $$createDate
     * @return watchBot
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get $createDate
     *
     * @return integer 
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return watchBot
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
}

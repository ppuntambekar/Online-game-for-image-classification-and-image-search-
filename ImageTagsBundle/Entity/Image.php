<?php
namespace ULL\Bundle\ImageTagsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="images")
 * @ORM\Entity()
 */

class Image
{
	/**
	 * @ORM\Column(type="integer" , columnDefinition="integer unsigned")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Id
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", name="location", length=500,  unique=true)
	 */
	private $location;
	
	/**
	 * @ORM\OneToMany(targetEntity="ImageTag", mappedBy="image")
	 */
	private $tags;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set tag
	 *
	 * @param Zen\LoginBundle\Entity\State $state
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}
	
	/**
	 * Get tag
	 *
	 * @return Zen\LoginBundle\Entity\State
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Set tag
	 *
	 * @param Zen\LoginBundle\Entity\State $state
	 */
	public function setLocation($location) {
		$this->location = $location;
	}
	
	/**
	 * Get tag
	 *
	 * @return Zen\LoginBundle\Entity\State
	 */
	public function getLocation() {
		return $this->location;
	}
	
}
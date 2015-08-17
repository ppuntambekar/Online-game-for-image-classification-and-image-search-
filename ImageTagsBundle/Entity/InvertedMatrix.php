<?php
namespace ULL\Bundle\ImageTagsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="invertedmatrix")
 * @ORM\Entity()
 */

class InvertedMatrix
{
	/**
	 * @ORM\Column(type="string", name="tag", length=100,  unique=true)
	 * @ORM\Id
	 */
	private $tag;
	
	/**
	 * @ORM\Column(type="string", name="imageids", length=1000)
	 */
	private $imageIds;
	
	
	/**
	 * Set tag
	 *
	 * @param Zen\LoginBundle\Entity\State $state
	 */
	public function setTag($tag) {
		$this->tag = $tag;
	}
	
	/**
	 * Get tag
	 *
	 * @return Zen\LoginBundle\Entity\State
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * Set tag
	 *
	 * @param Zen\LoginBundle\Entity\State $state
	 */
	public function setImageIds($imageIds) {
		$this->imageIds = $imageIds;
	}
	
	/**
	 * Get tag
	 *
	 * @return Zen\LoginBundle\Entity\State
	 */
	public function getImageIds() {
		return $this->imageIds;
	}
	
}
<?php
namespace ULL\Bundle\ImageTagsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zen\LoginBundle\Entity\ImageTag
 *
 * @ORM\Table(name="imagetagweights")
 * @ORM\Entity()
 */

class ImageTagWeight
{
	/**
	 * @ORM\Column(type="integer" , columnDefinition="integer unsigned")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Id
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", name="tag", length=700)
	 */
	private $tag;

	/**
	 * @ORM\ManyToOne(targetEntity="Image", inversedBy="tags")
	 * @ORM\JoinColumn(name="imageid", referencedColumnName="id")
	 */
	private $image;

	/**
	* @ORM\Column(type="integer" , columnDefinition="integer unsigned" , name="weight")
	* 	 
	* */
	private $weight;
	
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
	 * Set image
	 *
	 * @param \Zen\IncentiveBundle\Entity\Merchant $merchant
	 * @return MerchantIncentiveType
	 */
	public function setImage(\ULL\Bundle\ImageTagsBundle\Entity\Image $image)
	{
		$this->image = $image;
	
		return $this;
	}
	
	/**
	 * Get image
	 *
	 * @return \Zen\IncentiveBundle\Entity\Merchant
	 */
	public function getImage()
	{
		return $this->image;
	}
	
	/**
	 * Set user
	 *
	 * @param \Zen\IncentiveBundle\Entity\Merchant $merchant
	 * @return MerchantIncentiveType
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
		return $this;
	}
	
	/**
	 * Get user
	 *
	 * @return \Zen\IncentiveBundle\Entity\Merchant
	 */
	public function getWeight()
	{
		return $this->weight;
	}
	
	
	
	
}
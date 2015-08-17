<?php
namespace ULL\Bundle\ImageTagsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="users")
 */

class User
{
	/**
	 * @ORM\Column(type="integer" , columnDefinition="integer unsigned")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Id
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", name="email", length=200,  unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="integer" , columnDefinition="integer unsigned", name="points")
	 */
	private $points = 0;
	
	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set email
	 *
	 * @param string $email
	 * @return Merchant
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	
	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}
	
	
	/**
	 * Set points
	 *
	 * @param boolean $points
	 * @return Merchant
	 */
	public function setPoints($points) {
		$this->points = $points;
		return $this;
	}
	
	/**
	 * Get points
	 *
	 * @return boolean
	 */
	public function getPoints() {
		return $this->points;
	}
	

}
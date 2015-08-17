<?php

namespace ULL\Bundle\ImageTagsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ULL\Bundle\ImageTagsBundle\Entity\User;
use ULL\Bundle\ImageTagsBundle\Entity\Image;
use ULL\Bundle\ImageTagsBundle\Entity\ImageTag;
use ULL\Bundle\ImageTagsBundle\Entity\ImageTagWeight;
use ULL\Bundle\ImageTagsBundle\Entity\InvertedMatrix;
class DefaultController extends Controller
{
    
    public function userLoginAction(Request $request)
    {
    	
    	$defaultData = array('message' => '');
    	$form = $this->createFormBuilder($defaultData)->add('email', 'email')->getForm();
    	
    	$form->handleRequest($request);
    	
    	if ($form->isValid()) {
    		// data is an array with "name", "email", and "message" keys
    		$data = $form->getData();
    		$email = $data['email'];
    		$user = $this->getDoctrine()->getRepository('ULLImageTagsBundle:User')
    		->findByEmail($email);
    		 
    		if (!$user) {
	    		$user = new User();
	    		$user->setEmail($email);
	    		$em = $this->getDoctrine()->getManager();
	    		$em->persist($user);
	    		$em->flush();
    		}
    		$session = $request->getSession();
    		$session->set('userEmail' , $email);
    		if($email == 'piyupuntambekar@gmail.com'){
    			return $this->redirect($this->generateUrl('ull_image_tags_uploadpage'));
    		}
    		return $this->redirect($this->generateUrl('ull_image_tags_taggingpage'));
    	}
    	
    	return $this->render('ULLImageTagsBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    
    public function tagImageAction(Request $request)
    {
    	$defaultData = array('message' => '');
    	$email = $request->getSession()->get('userEmail');
    	$user =  $this->getDoctrine()->getRepository('ULLImageTagsBundle:User')->findOneByEmail($email);
    	$results = $this->getImageLocation($user);
    	foreach($results as $result){
    		$tagCount = $result['tagCount'];
    		$location = $result['loc'];
    		$imageId = $result['imageId'];
    		// we only need to select one location, hence break from the loop
    		break;
    	}

    	$form = $this->createFormBuilder($defaultData)
    	->add('tags', 'text')
    	->add('imageId' , 'hidden', array(
    			'data' => $imageId,
    	))
    	->getForm();
    	
    	$form->handleRequest($request);
    	if ($request->getMethod() === 'POST') {
    		if($form->isValid()){
    			// data is an array with "name", "email", and "message" keys
    			$data = $form->getData();
    			$tags = $data['tags'];
    			$imageId = $data['imageId'];
    			//get Image by Id
    			$image = $this->getDoctrine()->getRepository('ULLImageTagsBundle:Image')->find($imageId);
    			// split the tags by ,
    			$tagArray = explode ( ',' , $tags );
    			// add tags to database
    			foreach($tagArray as $tag){
    				$this->addNewImageTag($tag, $user, $image);	
    				$this->addOrUpdateImageTagWeight($tag, $image);
    				$this->updateInvertedMatrix($tag, $image);
    			}
    		}
    		return $this->redirect($this->generateUrl('ull_image_tags_taggingpage'));
    	}
    	 
    	$count = $this->getCountOfTaggedImages($user->getEmail());
    	$cnt = 0;
    	if($count > 0){
    		$cnt = $this->getMyScore($user->getEmail());
    	}
    	if(!isset($imageId)){
    		$session->getFlashBag()->add('error', 'No images are available for tagging.');
    		return $this->render('ULLImageTagsBundle:Default:home.html.twig', array('form' => $form->createView(), 
    				'count' => $count , 'score' => $cnt , 'user' => $user));
    	}else{
	    	return $this->render('ULLImageTagsBundle:Default:home.html.twig', array('form' => $form->createView(), 'location' => $location ,
	    			'imageId' =>$imageId, 'count' => $count , 'score' => $cnt , 'user' => $user));
    	}
    }
    
    private function getMyScore($email){
//     	select * from imagetagweights where imageid in (select imageid from imagetags where userid = 1)
//     	and tag in (select tag from imagetags where userid = 1)
//     	and weight > 1;
    	 
    	try{
    		$result = $this->getDoctrine()->getEntityManager()
    		->createQuery('SELECT count(itw.id) as cnt FROM ULLImageTagsBundle:ImageTagWeight itw where
    					itw.image in (select i.id from ULLImageTagsBundle:ImageTag it join it.user u join it.image i where u.email = :email) and
    					itw.tag in (select it1.tag from ULLImageTagsBundle:ImageTag it1 join it1.user u1 join it1.image i1 where u1.email = :email) and 
	    				itw.weight > 1')
    		->setParameter('email' , $email)
    		->getResult();
    	}catch(\Exception $e){
    		throw $e;
    	}
    	return $result[0]['cnt'];
    }
    
    private function getCountOfTaggedImages($email){
    	try{
    		$result = $this->getDoctrine()->getEntityManager()
    		->createQuery('SELECT count(distinct it.image) as cnt FROM ULLImageTagsBundle:ImageTag it
	    				join it.user u where u.email = :email')
    							->setParameter('email' , $email)
    							->getResult();
    	}catch(\Exception $e){
    		throw $e;
    	}
    	return $result[0]['cnt']; 
    }
    
    public function addNewImageTag($tag, $user, $image){
    	$em = $this->getDoctrine()->getManager();
    	// check if the same tag exists for the same user for same image
    	$imageTagExisting =  $this->getDoctrine()->getRepository('ULLImageTagsBundle:ImageTag')->findOneBy(
    			array('tag' => $tag, 'image' => $image->getId() , 'user' => $user->getId())
    			);
    	if($imageTagExisting == null){
	    	$imageTag = new ImageTag();
	    	$imageTag->setTag($tag);
	    	$imageTag->setUser($user);
	    	$imageTag->setImage($image);
	    	$em->persist($imageTag);
	    	$em->flush();
    	}
    }
    
    public function addOrUpdateImageTagWeight($tag, $image){
    	$em = $this->getDoctrine()->getManager();
    	$imageTagExisting =  $this->getDoctrine()->getRepository('ULLImageTagsBundle:ImageTagWeight')->findOneBy(
    			array('tag' => $tag, 'image' => $image->getId())
    	);
    	if($imageTagExisting == null){
    		$imageTagWeight = new ImageTagWeight();
    		$imageTagWeight->setTag($tag);
    		$imageTagWeight->setImage($image);
    		$imageTagWeight->setWeight(1);
    		$em->persist($imageTagWeight);
    	}else{
    		$currWeight = $imageTagExisting->getWeight();
    		$imageTagExisting->setWeight($currWeight + 1);
    	}
    	$em->flush();
    }
    
    public function updateInvertedMatrix($tag, $image){
    	$em = $this->getDoctrine()->getManager();
    	try{
    		$results = $this->getDoctrine()->getEntityManager()
    		->createQuery('SELECT i.id as imageId FROM ULLImageTagsBundle:ImageTagWeight itw join itw.image as i
	    				   where itw.tag = :tag ORDER BY itw.weight DESC')
    							->setParameter('tag' , $tag)
    							->getResult();
    		$imageIds = "";
    		
    		if(count($results) > 0){
    			$imageIdArr = $results;
    			if(count($imageIdArr) > 0){
    				foreach($imageIdArr as $imageId){
    					$imageIds = $imageIds.$imageId['imageId'].",";
    				}
    			}
    		}
    	}catch(\Exception $e){
    		throw $e;
    	}
    	
    	$invertedMatrix =  $this->getDoctrine()->getRepository('ULLImageTagsBundle:InvertedMatrix')->findOneBy(
    			array('tag' => $tag)
    	);
    	if($invertedMatrix == null){
    		$invertedMatrix = new InvertedMatrix();
    		$invertedMatrix->setTag($tag);
    		$invertedMatrix->setImageIds($imageIds);
    		$em->persist($invertedMatrix);
    	}else{
    		$invertedMatrix->setImageIds($imageIds);
    	}
    	$em->flush();
    }
    
    public function getImageLocation(\ULL\Bundle\ImageTagsBundle\Entity\User $user){
    	// select all the imageids tagged by me.
    	$imageTags =  $this->getDoctrine()->getRepository('ULLImageTagsBundle:ImageTag')->findBy(
    			array('user' => $user->getId())
    	);
    	$imageIds = array();
    	$i = 0; 
    	foreach($imageTags as $imageTag){
    		$imageIds[$i++] = $imageTag->getImage()->getId();
    	}
    	
    	if(count($imageIds) > 0){
	    	try{
	    		$results = $this->getDoctrine()->getEntityManager()
	    		->createQuery('SELECT i.id as imageId , i.location as loc, count(distinct it.user) as tagCount FROM ULLImageTagsBundle:Image i 
	    				left join i.tags it where i.id not in (:imageIds)  
						group by i.id having count(distinct it.user) <= 1 ORDER BY tagCount DESC')
								->setParameter('imageIds' , $imageIds)
	    						->getResult();
	    	}catch(\Exception $e){
	    		throw $e;
	    	}
	    	
	    	if(count($results) == 0){
	    		// do another query to find any image with lowest number of tags.
	    		try{
	    			$results = $this->getDoctrine()->getEntityManager()
	    			->createQuery('SELECT i.id as imageId , i.location as loc, count(distinct it.user) as tagCount FROM ULLImageTagsBundle:Image i 
	    					left join i.tags it where i.id not in (:imageIds) 
							group by i.id having count(distinct it.user) > 1 ORDER BY tagCount ASC')
								->setParameter('imageIds' , $imageIds)
	    						->getResult();
	    		}catch(\Exception $e){
	    			throw $e;
	    		}
	    	}
    	}else{
    		try{
    			$results = $this->getDoctrine()->getEntityManager()
    			->createQuery('SELECT i.id as imageId , i.location as loc, count(distinct it.user) as tagCount FROM ULLImageTagsBundle:Image i
	    				left join i.tags it
						group by i.id having count(distinct it.user) <= 1 ORDER BY tagCount DESC')
    								->getResult();
    		}catch(\Exception $e){
    			throw $e;
    		}
    		
    		if(count($results) == 0){
    			// do another query to find any image with lowest number of tags.
    			try{
    				$results = $this->getDoctrine()->getEntityManager()
    				->createQuery('SELECT i.id as imageId , i.location as loc, count(distinct it.user) as tagCount FROM ULLImageTagsBundle:Image i
	    					left join i.tags it
							group by i.id having count(distinct it.user) > 1 ORDER BY tagCount ASC')
    									->getResult();
    			}catch(\Exception $e){
    				throw $e;
    			}
    		}
    		
    	}
    	return $results;
    	 
    }
    
    public function uploadImageAction(Request $request)
    {
    	$session = $request->getSession();
    	$defaultData = array('message' => '');
    	$form = $this->createFormBuilder($defaultData)->add('folderLocation', 'text')->getForm();
    	$root = $this->container->getParameter('webappRoot');
    	$form->handleRequest($request);
    	
    	if ($form->isValid()) {
    		$data = $form->getData();
    		$location = $data['folderLocation'];
    		// read the file names.
    		if ($handle = opendir($location)) {
    			/* This is the correct way to loop over the directory. */
    			$em = $this->getDoctrine()->getManager();
    			while (false !== ($entry = readdir($handle))) {
    					$image = new Image();
    					if($entry == '.' || $entry == '..'){
    						continue;
    					}
    					if(strpos($location, '/') !== false){
    						$path = $location.'/'.$entry;
    					}else{
    						$path = $location.$entry;
    					}		
    					 
    					$path = str_replace($root, '', $path);
	    				$image->setLocation($path);
	    				$em->persist($image);
    			}
    			$em->flush();
    			closedir($handle);
    			$session->getFlashBag()->add('success', 'The images have been uploaded.');
    			return $this->redirect($this->generateUrl('ull_image_tags_uploadpage'));
    		}
    	}
    	
    	return $this->render('ULLImageTagsBundle:Default:upload.html.twig', array('form' => $form->createView()));
    }
    
    public function searchAction(Request $request){
    	$session = $request->getSession();
    	$defaultData = array('message' => '');
    	$form = $this->createFormBuilder($defaultData)->add('query', 'text', array(
    			'label' => 'Search term',
    	))->getForm();
    	$form->handleRequest($request);
    	$locations = array();
    	 
    	if ($request->getMethod() === 'POST') {
	    	if ($form->isValid()) {
	    		$data = $form->getData();
	    		$i = 0;
	    		$queryResult = $data['query'];
	    		$queries = explode(',', $queryResult);
	    		foreach($queries as $query){
// 		    		$imageTagWeights = $this->getDoctrine()->getEntityManager()  
// 		    		->createQuery('SELECT i.location FROM ULLImageTagsBundle:ImageTagWeight itw left join itw.image i
// 		    				where itw.tag = :query ORDER BY itw.weight DESC')
// 		    				->setParameter('query' , $query)
// 		    				->getResult();
		    		
	    			$result = $this->getDoctrine()->getEntityManager()
	    			->createQuery('SELECT im.imageIds FROM ULLImageTagsBundle:InvertedMatrix im
	    						   where im.tag = :query')
	    			->setParameter('query' , $query)
	    			->getSingleResult();
	    			if(count($result) > 0){
		    			$result = $result['imageIds'].'0';
		    			$ids = explode(',', $result);
		    			$imageTagWeights = $this->getDoctrine()->getEntityManager()
		    			->createQuery('SELECT i.location FROM ULLImageTagsBundle:Image i
		    						   where i.id in (:ids)')
		    			->setParameter('ids' , $ids)
		    			->getResult();
			    		foreach($imageTagWeights as $imageTagWeight){
			    			$locations[$i++] = $imageTagWeight['location'];
			    		}
	    			}
	    		}
	    	}	
    	}
    	return $this->render('ULLImageTagsBundle:Default:search.html.twig', array('form' => $form->createView() , 'locations' => $locations ));
    }
    
    public function logoutAction(Request $request){
    	$session = $request->getSession();
    	$session->set('userEmail' , null);
    	return $this->redirect($this->generateUrl('ull_image_tags_loginpage'));
    }
    
}

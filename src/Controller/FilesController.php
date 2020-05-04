<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Files;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\Length;

class FilesController extends AbstractController
{
    /**
     * @var FilesRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    public function __construct(FilesRepository $repository,EntityManagerInterface $em){
        $this->repository = $repository;
        $this->em = $em;

    }

    function diverse_array($vector) {
        $result = array();
        foreach($vector as $key1 => $value1)
            foreach($value1 as $key2 => $value2)
                $result[$key2][$key1] = $value2;
        return $result;
    }
    
    
    /**
     * @Route("/add-files",name="addFiles",methods="GET|POST")
     */
    public function addFiles(Request $request){
    
        $data_name= $_FILES["files"]["name"];
        //$data= $this->diverse_array($_FILES["files"]);
        //$date = new \DateTime();
        $data2 = $request->files->get("files");
        
        for($i=0;$i<count($data2);$i++){

            $name=uniqid(md5($data_name[$i])).'.'.$data2[$i]->guessExtension();
            $data2[$i]->move($this->getParameter("uploads_directory"),$name);
            $files[$i] = new Files();
            $files[$i]->setName($data_name[$i]);
            $files[$i]->setAjout(new \DateTime("now"));
            $files[$i]->setUploadName($name);
            $this->em->persist($files[$i]);
           
            
        }
        $this->em->flush();

        return new JSONResponse("success!!");
    }

    /**
     * @Route("/load-files",name="loadFiles",methods="GET")
     */
    public function loadFiles(){
        $em =$this->em;

        $query = $em->createQuery(
            'SELECT c 
            FROM App:Files c
            ORDER BY c.Ajout'
        ); 
        
        $files = $query->getArrayResult();
        return new JsonResponse($files);
    }

    /**
     * @Route("/download-file/{filename}-{extension}",name="downloadFile",methods="GET|POST")
     */

     public function downloadFile($filename,$extension){
        //  $response = new BinaryFileResponse($this->get('kernel')->getRootDir().'../public/uploads/'.$filename);
        //  $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$filename);
        //  return $response;
        $path = $this->getParameter("uploads_directory").'/'.$filename.'.'.$extension;
        //echo $filename;
        // return new BinaryFileResponse($path);
        return $this->file($path);
        
     }

     /**
      * @Route("/delete-file/{id}-{filename}-{extension}",name="deleteFile",methods="GET|DELETE")
      */
     public function deleteFile($id,$filename,$extension){
        
        
         $file = $this->repository->findOneBy(array('id'=>$id));
        
         try{
            $filesystem=new Filesystem() ;

            $path = $this->getParameter("uploads_directory").'/'.$filename.'.'.$extension;
             $filesystem->remove($path);
            $this->em->remove($file);
            $this->em->flush();
            return new Response("file deleted");
           }catch(\Doctrine\ORM\ORMException $e){
               return new response ( $e->getMessage());
           }
         

         
     }
    
}










?>
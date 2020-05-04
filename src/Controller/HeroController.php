<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Flex\Options as FlexOptions;

class HeroController extends AbstractController
{

    /**
     * @var HeroRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(HeroRepository $repository,EntityManagerInterface $em){
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/hero", name="hero")
     */
    public function index()
    {
        return $this->render('hero/index.html.twig', [
            'controller_name' => 'HeroController',
        ]);
    }

    /**
     * @Route("/show",name="show")
     */
    public function show(){
        
        // $repository = $this->getDoctrine()->getRepository(Hero::class);  
        // return new JsonResponse($repository->findAll());
        $em = $this->em;
        // $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT c
            FROM App:Hero c'
        );

        $hero = $query->getArrayResult();

        return new JsonResponse($hero);

    }

    /**
     * @Route("/pdf",name="pdf",methods="GET|POST")
     */
    public function generatePDF(){
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/mypdf_test.html.twig', [
            'title' => "Welcome to our PDF Test"
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        return new BinaryFileResponse( $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]));
    }


    /**
     * @Route("/add-user",name="addUser",methods="GET|POST")
     */
    public function addUser(){
        $hero = new Hero();
        if(isset($_POST["first_name"])&&isset($_POST["last_name"])&&isset($_POST["hero_name"])){
            $firstname = $_POST["first_name"];
            $lastname = $_POST["last_name"];
            $heroName = $_POST["hero_name"];
           
        
            $hero->setFirstName($firstname);
            $hero->setLastName($lastname);
            $hero->setHeroName($heroName);

            try{
            $this->em->persist($hero);
            $this->em->flush();

            return new response("les elements ont ete ajouter avec succes".$firstname."+".$lastname."+".$heroName);
            }catch(\Doctrine\ORM\ORMException $e){
                return new response ( $e->getMessage());
            }
        
            
        }
        return new response ( "un element du formulaire n'a pas été rempli");
    }

    /**
     * @Route("/update-hero/{id}",name="updateHero",methods="PUT|POST")
     */
    public function updateHero($id){

        $hero = $this->repository->findOneBy(array('id'=>$id));
        if(!$hero){
            throw $this->createNotFoundException(
                'No Hero found for id '.$id
            );
        }else{
            if(isset($_POST["first_name"])&&isset($_POST["last_name"])&&isset($_POST["hero_name"])){
                $firstname = $_POST["first_name"];
                $lastname = $_POST["last_name"];
                $heroName = $_POST["hero_name"];

                $hero->setFirstName($firstname);
                $hero->setLastName($lastname);
                $hero->setHeroName($heroName);
                $this->em->flush();
                return new Response("update reussi");
            }else{
        return new Response($hero->getFirstName());
            }
        }
        
    }


    /**
     * @Route("/delete-hero/{id}",name="deleteHero",methods="DELETE|GET")
     */
    public function deleteHero($id){
        
        $hero = $this->repository->findOneBy(array('id' => $id));
        try{
         $this->em->remove($hero);
         $this->em->flush();
         return new Response("ca marche");
        }catch(\Doctrine\ORM\ORMException $e){
            return new response ( $e->getMessage());
        }
    }

    /**
     * @Route("/add-csv",name="addCsv",methods="POST")
     */
    public function addFromCsv(Request $request){
        $file = $request->files->get("file");
        $csvfile=fopen($file,"r");
        $arr=[];
        while($data = fgetcsv($csvfile,1000,";")){
            $arr[]= $data;
    }
        fclose($csvfile);
        for($i=0;$i<count($arr);$i++){
            
            
            $hero[$i] = new Hero();
            $hero[$i]->setFirstName($arr[$i][0]);
            $hero[$i]->setLastName($arr[$i][1]);
            $hero[$i]->setHeroName($arr[$i][2]);

            $this->em->persist($hero[$i]);
            
        }
        
        $this->em->flush();
    return new JsonResponse("success !!");
}
}
?>
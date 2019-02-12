<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Map;
use App\Entity\User;
use App\Repository\MapRepository;


class HomeController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', []);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class)
            ->add('password', PasswordType::class)
            ->add('verify', PasswordType::class, ['mapped' => false])
            ->add('register', SubmitType::class)
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            if($user->getPassword() == $form->get('verify')->getData()){
                $em->persist($user);
                $em->flush();
            }
        }
        
        return $this->render('main/register.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/upload-file", name="upload_file")
     */
    public function fileUploader(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createFormBuilder()
        ->add('name', TextType::class)
        ->add('file', FileType::class)
        ->add('upload', SubmitType::class)
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $map = new Map();
            $file = $data['file'];
            $fileName = $data['name']; 
            $map->setName($fileName);
            $map->setFileLocation($fileName. '.' .$file->guessExtension());
            $file->move(
                $this->getParameter('maps_directory'),
                $map->getFileLocation(),
            );

            $em->persist($map);
            $em->flush();

        }


        return $this->render('main/upload_file.html.twig',[
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/maps", name="maps")
     */
    public function maps(MapRepository $mapRepo)
    {
        $maps = $mapRepo->findAll();
        return $this->render('main/maps.html.twig',[
            'maps' => $maps,
        ]);

    }



}
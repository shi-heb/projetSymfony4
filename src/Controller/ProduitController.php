<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/produit")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produit_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', ['produits' => $produitRepository->findAll()]);
    }

    /**
     * @Route("/new", name="produit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $produit = new Produit();
        //$user = $produit->getUser();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


           // $file = $produit->getImage();
            $file = $form->get('image')->getData();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $produit->setImage($fileName);





            $entityManager = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $produit->setUser($user);


            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('user_default_admin');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', ['produit' => $produit]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Produit $produit): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('produit_index', ['id' => $produit->getId()]);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        //if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
           // $entityManager = $this->getDoctrine()->getManager();
          //  $entityManager->remove($produit);
          //  $entityManager->flush();

        $id = $request->get('id');
        $em=$this->getDoctrine()->getManager();

        $t=$em->getRepository('App:Produit')->find($id);
        $em->remove($t);
        $em->flush();


        return $this->redirectToRoute('user_default_admin');

        }





    public function index2Action()
    {
        return $this->render('homePage.html.twig');
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();
        $produits = $em->getRepository("App:Produit")->findAll();
        return $this->render('adminLayout.html.twig', array('produits' => $produits));
    }





}
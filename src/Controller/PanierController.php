<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 31/07/2018
 * Time: 15:55
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Panier;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PanierController extends Controller
{


    public function reserverAction(Request $request)
    {

        $id = $request->get('id');
        $em=$this->getDoctrine()->getManager();
        $a=$em->getRepository('App:Produit')->find($id);

        if ($request->isMethod('post')) {

            if($a->getQuantite() >= $request->get('nbre')) {
                $nbre = $request->get('nbre');
               $a=$em->getRepository('App:Produit')->find($id);
               $y = $a;
               $nb = $a->getQuantite() - $nbre;
                /* @var $entity \App\Entity\Produit */
               $a=$em->getRepository('App:Produit')
                   ->decrementer($id,$nb);


                $r = new Panier();

                $em=$this->getDoctrine()->getManager();
                $user=$this->getUser();
                $r->setUser($user);
                $a=$em->getRepository('App:Produit')->find($id);
                $r->setProduit($a);
                $r->setQunantite($nbre);

                $r->setLibelle($a->getLibelle());
                $r->setPrix($a->getPrix());

                $em->persist($r);
                $em->flush();





               return $this->redirectToRoute('list_Panier');

            }


        }
        return $this->render('panShow.html.twig',
            array('produit'=>$a));


    }



    public function indexAction()
    {

        //$em=$this->getDoctrine()->getManager();
       // $pan = $em->getRepository("PruductBundle:Panier")
            //->findPaniers($this->getProduit()->getId());

        //return $this->render('listPaniers.html.twig',array('Paniers'=>$pan));
        $em = $this->getDoctrine()->getManager();

        $pan = $em->getRepository('App:Panier')->findAll();

        return $this->render('listPaniers.html.twig', array(
            'paniers' => $pan,
        ));





    }



    public function annulerAction(Request $request)
    {
        $id = $request->get('id');

        $em=$this->getDoctrine()->getManager();
        $res = $em->getRepository("App:Panier")->find($id);
        $a=$em->getRepository('App:Produit')->find($res->getProduit()->getId());

        $a=$em->getRepository('App:Produit')
            ->incrementer($res->getProduit()->getId(),$res->getQunantite()+$a->getId());

        $t=$em->getRepository('App:Panier')->find($id);
        $em->remove($t);
        $em->flush();


        return $this->redirectToRoute('list_Panier');
    }

    public function viderAction()
    {
        $em=$this->getDoctrine()->getManager();

        $classMetaData = $em->getClassMetadata(Panier::class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
        }
        $em = $this->getDoctrine()->getManager();

        $produits = $em->getRepository('App:Produit')->findAll();
        return $this->render('produit/index.html.twig', array(
            'produits' => $produits,
        ));



    }





}
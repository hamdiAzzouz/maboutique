<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\User;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    /*
    *1ère étape du tunel d'achat
    *choix de l'adresse de livraison et du transporteur 
    */
    #[Route('/commande/livraison', name: 'app_order')]
    public function index(): Response
    {
        $addresses = $this->getUser()->getAdresses();
        if (count($addresses) == 0) {
            return $this->redirectToRoute('app_account_address_form');
        }

        $form = $this->createForm(OrderType::class, null, [
            'adresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary')
        ]);
        return $this->render('order/index.html.twig', [
            'deliveryForm' => $form->createView(),
        ]);
    }
    /*
    *2ère étape du tunel d'achat
    *Récap de la commande de l'utilisateur
    * Insertion dans la base de données
    *Préparation de payement vers strip
    */
    #[Route('/commande/recapitulatif', name: 'app_order_summary')]
    public function add(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }


        $form = $this->createForm(OrderType::class, null, [
            'adresses' => $this->getUser()->getAdresses(),
        ]);

        $products = $cart->getCart();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adressOb = $form->get('adresses')->getData();
            //création de la chaine adresse
            $address = $adressOb->getFirstName() . " " . $adressOb->getLastName() . "<br>";
            $address .= $adressOb->getAdress() . "<br>";
            $address .= $adressOb->getPostal() . " " . $adressOb->getCit() . "<br>";
            $address .= $adressOb->getCountry() . "<br>";
            $address .= $adressOb->getPhone();

            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTime());
            $order->setState(1);
            $order->setCarrierName($form->get('carriers')->getData()->getName());
            $order->setCarrierPrice($form->get('carriers')->getData()->getPrice());
            $order->setDelivery($address);
            foreach ($products as $product) {
                $orderDetail = new OrderDetail();
                $orderDetail->setProductName($product['object']->getName());
                $orderDetail->setProductIllustration($product['object']->getIllustration());
                $orderDetail->setProductPrice($product['object']->getPrice());
                $orderDetail->setProductTva($product['object']->getTva());
                $orderDetail->setProductQuantity($product['qty']);
                $order->addOrderDetail($orderDetail);
            }
            $entityManager->persist($order);
            $entityManager->flush();
        }

        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $products,
            'totalwt' => $cart->getTotalWt()
        ]);
    }
}

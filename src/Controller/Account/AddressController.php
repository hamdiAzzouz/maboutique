<?php

namespace App\Controller\Account;

use App\Classe\Cart;
use App\Entity\Adress;
use App\Form\AddressUserType;
use App\Repository\AdressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AddressController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/compte/adresses', name: 'app_account_addresses')]
    public function index(): Response
    {
        return $this->render('account/address/index.html.twig');
    }

    #[Route('/compte/adresses/delete/{id}', name: 'app_account_address_delete')]
    public function delete($id, AdressRepository $adressRepository): Response
    {
        $address = $adressRepository->findOneById($id);
        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_account_addresses');
        }
        $this->addFlash(
            'success',
            "Votre adresse est correctement supprimée"
        );
        $this->entityManager->remove($address);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_account_addresses');
    }


    #[Route('/compte/adresse/ajouter/{id}', name: 'app_account_address_form', defaults: ['id' => null])]
    public function form(Request $request, $id, AdressRepository $adressRepository, Cart $cart): Response
    {
        if ($id) {
            $address = $adressRepository->findOneById($id);
            if (!$address || $address->getUser() != $this->getUser()) {
                return $this->redirectToRoute('app_account_addresses');
            }
        } else {
            $address = new Adress();
            $address->setUser($this->getUser());
        }


        $form = $this->createForm(AddressUserType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                "Votre adresse est correctement ajoutée"
            );

            if ($cart->fullQuantity() > 0) {
                return $this->redirectToRoute('app_order');
            }
            return $this->redirectToRoute('app_account_addresses');
        }


        return $this->render('account/address/form.html.twig', [
            'addressForm' => $form
        ]);
    }
}

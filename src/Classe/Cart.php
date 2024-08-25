<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{

    public function __construct(private RequestStack $requestStack) {}
    public function add($product)
    {
        // Appeler la session de Symfony
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $productId = $product->getId();

        // Vérifier si le produit est déjà dans le panier
        if (isset($cart[$productId])) {
            // Le produit est déjà dans le panier, incrémenter la quantité
            $cart[$productId]['qty'] += 1;
        } else {
            // Le produit n'est pas encore dans le panier, l'ajouter
            $cart[$productId] = [
                'object' => $product,
                'qty' => 1
            ];
        }

        // Sauvegarder le panier mis à jour dans la session
        $session->set('cart', $cart);
    }
    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }
    public function decrease($id)
    {
        $cart = $this->requestStack->getSession()->get('cart');
        if ($cart[$id]['qty'] > 1) {
            $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
        } else {
            unset($cart[$id]);
        }
        $session = $this->requestStack->getSession();
        $session->set('cart', $cart);
    }
    public function getTotalWt()
    {
        $cart = $this->requestStack->getSession()->get('cart');
        $price = 0;

        if (!isset($cart)) {
            return 0;
        }
        foreach ($cart as $product) {
            $price = $price + $product['object']->getPriceWt() * $product['qty'];
        }
        return $price;
    }

    public function fullQuantity()
    {
        $cart = $this->requestStack->getSession()->get('cart');
        $quantity = 0;
        if (!isset($cart)) {
            return 0;
        }

        foreach ($cart as $product) {
            $quantity = $quantity + $product['qty'];
        }
        return $quantity;
    }


    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart');
    }
}

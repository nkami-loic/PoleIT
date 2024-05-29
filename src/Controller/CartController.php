<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    private $manager;
    private $gateway;
    private $security;
    private $mailer;

    public function __construct(EntityManagerInterface $manager, Security $security, MailerInterface $mailer)
    {
        $this->manager = $manager;
        $this->gateway = new StripeClient($_ENV['STRIPE_SECRETKEY']);
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository)
    {
        $panier = $session->get('panier', []);
        $data = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);
            if ($product) {
                $data[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    #[Route('/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session)
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session)
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session)
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session)
    {
        $session->remove('panier');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(SessionInterface $session, ProductsRepository $productsRepository): RedirectResponse
    {
        $panier = $session->get('panier', []);
        $lineItems = [];

        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);

            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $_ENV['STRIPE_CURRENCY'],
                        'product_data' => [
                            'name' => $product->getName(),
                        ],
                        'unit_amount' => $product->getPrice() * 100, // Convertir le prix en centimes
                    ],
                    'quantity' => $quantity,
                ];
            }
        }

        $checkoutSession = $this->gateway->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('cart_cart_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?id_sessions={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('cart_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?id_sessions={CHECKOUT_SESSION_ID}',
        ]);

        return new RedirectResponse($checkoutSession->url);
    }

    #[Route('/success', name: 'cart_success')]
    public function success(Request $request, SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em,): Response
    {
        $id_sessions = $request->query->get('id_sessions');
        $customer = $this->gateway->checkout->sessions->retrieve($id_sessions, []);
        $name = $customer["customer_details"]["name"];
        $email = $customer["customer_details"]["email"];
        $payment_status = $customer["payment_status"];
        $amount = $customer['amount_total'];

        // Mise à jour de la quantité des produits dans la base de données
        foreach ($session->get('panier', []) as $itemId => $quantity) {
            $product = $productsRepository->find($itemId);
            if ($product) {
                $product->setStock($product->getStock() - $quantity);
                $em->persist($product);
            }
        }

        $em->flush();

        // Envoi de l'email de confirmation au client
        $email = (new Email())
            ->from('nkamiloic237@gmail.com')
            ->to($email)
            ->subject('Confirmation de commande')
            ->html('<p>Votre commande a été validée avec succès. Merci pour votre achat!</p>');

        $this->mailer->send($email);

        // Rediriger vers la méthode add du contrôleur OrdersController pour sauvegarder la commande
        return $this->redirectToRoute('app_orders_add');
    }

    #[Route('/cancel', name: 'cancel')]
    public function cancel(Request $request): Response
    {
        dd("cancel");
    }
}

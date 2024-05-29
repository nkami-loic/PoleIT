<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/contact/submit', name: 'app_contact_submit', methods: ['POST'])]
    public function submit(Request $request, MailerInterface $mailer): Response
    {
        $userName = $request->request->get('userName');
        $userSurname = $request->request->get('userSurname');
        $userEmail = $request->request->get('userEmail');
        $messageContent = $request->request->get('message');

        // Envoi d'un email à l'administrateur
        $email = (new Email())
            ->from('nkamiloic237@gmail.com')
            ->to('nkamiloic237@gmail.com')
            ->subject('Nouveau message de contact')
            ->html("<p>Nom: $userName $userSurname</p><p>Email: $userEmail</p><p>Message: $messageContent</p>");

        $mailer->send($email);

        $this->addFlash('success', 'Votre message a été envoyé avec succès.');

        return $this->redirectToRoute('app_contact');
    }
}

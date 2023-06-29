<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();

            $name = $data['name'];
            $firstname = $data['firstname'];
            $address = $data['email'];
            $subject = $data['subject'];
            $content = $data['message'];
            $category = $data['category'];


            $email = (new Email())
                ->from($address)
                ->to('admin@admin.fr')
                ->name($name)
                ->firstname($firstname)
                ->subject($subject)
                ->category($category)
                ->text($content);

            $mailer->send($email);
        }

        return $this->renderForm('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'formulaire' => $form
        ]);
    }
}

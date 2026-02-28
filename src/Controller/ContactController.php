<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'app_api_contact', methods: ['POST'])]
    public function send(Request $request, TransportInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? 'Не указано';
        $company = $data['company'] ?? 'Не указано';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? 'Не указано';
        $service = $data['service'] ?? 'Не указано';
        $message = $data['message'] ?? 'Нет деталей';

        if (empty($email)) {
            return new JsonResponse(['success' => false, 'error' => 'Email is required'], 400);
        }

        $emailObj = (new TemplatedEmail())
            // Sender MUST be info@lamba.by for Yandex SMTP to work
            ->from(new Address('info@lamba.by', 'RDN.BY Site'))
            ->to('info@lamba.by')
            ->replyTo($email)
            ->subject('Новая заявка с сайта RDN.BY: ' . $service)
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'name' => $name,
                'company' => $company,
                'client_email' => $email,
                'phone' => $phone,
                'service' => $service,
                'message_text' => $message,
            ]);

        try {
            $mailer->send($emailObj);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }

        return new JsonResponse(['success' => true]);
    }
}

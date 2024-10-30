<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Settings;
use App\Service\OpenAI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $messages = $entityManager->getRepository(Message::class)->findAll();
        $settings = $entityManager->getRepository(Settings::class)->findOneBy(["name" => Settings::TRAINING]);

        return $this->render('home/index.html.twig', [
            'messages' => $messages,
            'settings' => $settings ? $settings->getValue() : ""
        ]);
    }

    /**
     * @Route("/send", name="app_send", methods={"POST"})
     */
    public function send(Request $request, EntityManagerInterface $entityManager, OpenAI $openAI): RedirectResponse
    {
        $text = $request->request->get("message");
        $response = $text ? $openAI->ask($text) : "";

        if ($text) {
            $message = new Message();
            $message->setText($text);
            $message->setRole(Message::ROLE_USER);
            $entityManager->persist($message);
        }

        if ($response) {
            $message = new Message();
            $message->setText($response);
            $message->setRole(Message::ROLE_CHAT);
            $entityManager->persist($message);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/save", name="app_save", methods={"POST"})
     */
    public function save(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $text = $request->request->get("settings");

        if ($text) {
            $settings = $entityManager->getRepository(Settings::class)->findOneBy(["name" => Settings::TRAINING]);

            if (!$settings) {
                $settings = new Settings();
                $settings->setName(Settings::TRAINING);
            }
            $settings->setValue($text);
            $entityManager->persist($settings);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }
}

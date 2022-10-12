<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\User;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pins/index.html.twig', compact('pins'));
    }

    /**
     * @Route("/pins/create", name="app_pins_create", methods="GET|POST")
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'You need to log in first!');

            return $this->redirectToRoute('app_login');
        }

        if (!$this->getUser()->isVerified()) {
            $this->addFlash('error', 'You need to verify your email first!');

            return $this->redirectToRoute('app_home');
        }

        $pin = new Pin;

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show", methods="GET")
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit", methods="GET|POST")
     */
    public function edit(Request $request, EntityManagerInterface $em, Pin $pin): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'You need to log in first!');

            return $this->redirectToRoute('app_login');
        }

        if (!$this->getUser()->isVerified()) {
            $this->addFlash('error', 'You need to verify your email first!');

            return $this->redirectToRoute('app_home');
        }

        if ($pin->getUser() != $this->getUser() && !$this->IsGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Access forbidden.');

            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render(
            'pins/edit.html.twig',
            [
                'pin' => $pin,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/delete", name="app_pins_delete", methods="POST")
     */
    public function delete(Request $request, EntityManagerInterface $em, Pin $pin): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'You need to log in first!');

            return $this->redirectToRoute('app_login');
        }

        if (!$this->getUser()->isVerified()) {
            $this->addFlash('error', 'You need to verify your email first!');

            return $this->redirectToRoute('app_home');
        }

        if ($pin->getUser() != $this->getUser() && !$this->IsGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Access forbidden.');

            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('_csrf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted!');
        } else {
            $this->addFlash('error', 'Invalid Csrf token!');
        }

        return $this->redirectToRoute('app_home');
    }
}

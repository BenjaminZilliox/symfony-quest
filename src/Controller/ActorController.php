<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/actor", name: "actor_", methods: ['GET'])]
class ActorController extends AbstractController
{
    #[Route("/", name: "index", methods: ['GET'])]
    public function index(
        ActorRepository $actorRepository
    ): Response {
        $actors = $actorRepository->findAll();
        return $this->render('actor/index.html.twig', [
            'actors' => $actors,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, ActorRepository $actorRepository, SluggerInterface $slugger): Response
    {
        $actor = new actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($actor->getName());
            $actor->setSlug($slug);
            $actorRepository->save($actor, true);
            $this->addFlash('success', 'Actor created successfully!');
            return $this->redirectToRoute('actor_index');
        }

        return $this->render('actor/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Actor $actor): Response
    {
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }
}
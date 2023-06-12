<?php

namespace App\Controller;

use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Form\SearchProgramType;
use Symfony\Component\Mime\Email;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route("/program", name: "program_")]
class ProgramController extends AbstractController
{
    #[Route("/", name: "index", methods: ['GET', 'POST'])]
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            $programs = $programRepository->findAll();
        }

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, MailerInterface $mailer, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $programRepository->save($program, true);
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);
            $this->addFlash('success', 'Program created successfully!');
            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{slug}/edit", name: "edit", methods: ['GET', 'POST'])]
    #[Route("/", name: 'app_home')]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        if ($this->getUser() !== $program->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $programRepository->save($program, true);
            $this->addFlash('success', 'Program updated successfully!');
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program, true);
            $this->addFlash('danger', 'Program deleted successfully!');
        }
        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route("/{slug}", requirements: ['page' => '\d+'], name: "show", methods: ['GET'])]
    public function show(Program $program): Response
    {
        $seasons = $program->getSeasons();
        if (!$program) {
            throw $this->createNotFoundException(
                'Aucune série avec le numéro : ' . $program->getId() . ' n\'a été trouvée dans la liste des séries.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    #[Route("/{slug}/seasons/{seasonId}", name: "season_show", methods: ['GET'])]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    #[ParamConverter('season', options: ['mapping' => ['seasonId' => 'slug']])]
    public function showSeason(
        Program $program,
        Season $season,
        EpisodeRepository $episodeRepository
    ): Response {

        $episodes = $episodeRepository->findBy(['season' => $season]);
        if (!$program) {
            throw $this->createNotFoundException(
                'Aucune série avec le numéro : ' . $program->getId() . ' n\'a été trouvée dans la liste des séries.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'Aucune saison avec le numéro : ' . $season->getId() . ' n\'a été trouvée dans la liste des saisons.'
            );
        }
        return $this->render('program/season_show.html.twig', [
            'programs' => $program,
            'seasons' => $season,
            'episodes' => $episodes,
        ]);
    }

    #[Route("/{programSlug}/seasons/{seasonSlug}/episodes/{episodeSlug}", name: "episode_show", methods: ['GET'])]
    #[ParamConverter('program', options: ['mapping' => ['programSlug' => 'slug']])]
    #[ParamConverter('season', options: ['mapping' => ['seasonSlug' => 'slug']])]
    #[ParamConverter('episode', options: ['mapping' => ['episodeSlug' => 'slug']])]
    public function showEpisode(
        Program $program,
        Season $season,
        Episode $episode
    ): Response {
        if (!$program) {
            throw $this->createNotFoundException(
                'Aucune série avec le numéro : ' . $program->getId() . ' n\'a été trouvée dans la liste des séries.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'Aucune saison avec le numéro : ' . $season->getId() . ' n\'a été trouvée dans la liste des saisons.'
            );
        }
        if (!$episode) {
            throw $this->createNotFoundException(
                'Aucun épisode avec le numéro : ' . $episode->getId() . ' n\'a été trouvée dans la liste des épisodes.'
            );
        }
        return $this->render('program/episode_show.html.twig', [
            'programs' => $program,
            'seasons' => $season,
            'episodes' => $episode,
        ]);
    }
}
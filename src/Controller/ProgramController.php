<?php

namespace App\Controller;

use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/program", name: "program_")]
class ProgramController extends AbstractController
{
    #[Route("/", name: "index", methods: ['GET'])]
    public function index(
        ProgramRepository $programRepository
    ): Response {
        $programs = $programRepository->findAll();
        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }
    #[Route("/{id}", requirements: ['page' => '\d+'], name: "show", methods: ['GET'])]
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

    #[Route("/{programId}/seasons/{seasonId}", requirements: ['programId' => '\d+', 'seasonId' => '\d+'], name: "season_show", methods: ['GET'])]
    #[Entity('program', expr: 'repository.find(programId)')]
    #[Entity('season', expr: 'repository.find(seasonId)')]
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

    #[Route("/{programId}/seasons/{seasonId}/episodes/{episodeId}", requirements: ['programId' => '\d+', 'seasonId' => '\d+', 'episodeId' => '\d+'], name: "episode_show", methods: ['GET'])]
    #[Entity('program', expr: 'repository.find(programId)')]
    #[Entity('season', expr: 'repository.find(seasonId)')]
    #[Entity('episode', expr: 'repository.find(episodeId)')]
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
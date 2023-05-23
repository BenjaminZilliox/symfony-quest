<?php

namespace App\Controller;

use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function show(int $id, ProgramRepository $programRepository): Response
    {
        $programs = $programRepository->find($id);
        if (!$programs) {
            throw $this->createNotFoundException(
                'Aucune série avec le numéro : ' . $id . ' n\'a été trouvée dans la liste des séries.'
            );
        }
        return $this->render('program/show.html.twig', [
            'programs' => $programs,
        ]);
    }

    #[Route("/{programId}/seasons/{seasonId}", requirements: ['programId' => '\d+', 'seasonId' => '\d+'], name: "season_show", methods: ['GET'])]
    public function showSeason(
        int $programId,
        int $seasonId,
        ProgramRepository $programRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository
    ): Response {
        $programs = $programRepository->find($programId);
        $seasons = $seasonRepository->find($seasonId);
        $episodes = $episodeRepository->findBy(['season' => $seasonId]);
        if (!$programs) {
            throw $this->createNotFoundException(
                'Aucune série avec le numéro : ' . $programId . ' n\'a été trouvée dans la liste des séries.'
            );
        }
        if (!$seasons) {
            throw $this->createNotFoundException(
                'Aucune saison avec le numéro : ' . $seasonId . ' n\'a été trouvée dans la liste des saisons.'
            );
        }
        return $this->render('program/season_show.html.twig', [
            'programs' => $programs,
            'seasons' => $seasons,
            'episodes' => $episodes,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\RaceResult;
use App\Entity\Races;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Csv\Reader;
use App\Form\CsvImportType;
use DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class CsvImportController extends AbstractController
{


    #[Route('/', name: 'app_csv_import')]
    public function index(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $form = $this->createForm(CsvImportType::class);

        $lastRace = $doctrine->getManager()->getRepository(Races::class)->findOneBy([], ['id' => 'DESC']);

        if ($lastRace) {
            $raceResults = $doctrine->getManager()
                ->getRepository(RaceResult::class)
                ->findBy(['distance' => 'long', 'race' => $lastRace->getId()], ['time' => 'ASC']);

            $mediumRaceResults = $doctrine->getManager()
                ->getRepository(RaceResult::class)
                ->findBy(['distance' => 'medium', 'race' => $lastRace->getId()], ['time' => 'ASC']);

        }
        $allRaceResults = $doctrine->getManager()
            ->getRepository(RaceResult::class)
            ->findAll();

        $allRaceResultsPagination = $paginator->paginate(
            $allRaceResults,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('csv_import/index.html.twig', [
            'form' => $form->createView(),
            'raceResults' => $lastRace ? $raceResults : null,
            'averageFinishTime' => $lastRace ? $lastRace->getAverageLongTime() : null,
            'allRaceResults' => $allRaceResults,
            'mediumAverageFinishTime' => $lastRace ? $lastRace->getAverageMediumTime() : null,
            'raceTitle' => $lastRace ? $lastRace->getTitle() : null,
            'mediumRaceResults' => $lastRace ? $mediumRaceResults : null,
            'allRaceResultsPagination' => $allRaceResultsPagination

        ]);
    }

    public function importAction(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, SessionInterface $session)
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $race = new Races();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($race);
            $entityManager->flush();


            $csvFile = $form->get('csvFile')->getData();
            $csvReader = Reader::createFromPath($csvFile->getPathname());
            $csvReader->setHeaderOffset(0);

            $records = $csvReader->getRecords();
            foreach ($records as $record) {
                $fullName = $record['fullName'];
                $distance = $record['distance'];
                $timeString = $record['time'];
                $ageCategory = $record['ageCategory'];
                $title = $record['title'];
                $date = $record['date'];

                $currentTime = DateTimeImmutable::createFromFormat('U', time());

                $time = DateTime::createFromFormat('H:i:s', $timeString);

                $errors = [];

                switch (true) {
                    case empty($fullName):
                        $errors[] = "Full Name is required in csv.";
                        break;
                    case !in_array($distance, ['long', 'medium']):
                        $errors[] = "Invalid distance value, it can be long or medium.";
                        break;
                    case empty($timeString):
                        $errors[] = "Time is required.";
                        break;
                    case empty($ageCategory):
                        $errors[] = "Age category is required.";
                        break;
                    case empty($title):
                        $errors[] = "Race title is required";
                        break;
                    case empty($date):
                        $errors[] = "Race date is required";
                        break;
                }

                if (empty($errors)) {

                    $result = new RaceResult();
                    $result->setFullName($fullName);
                    $result->setDistance($distance);
                    $result->setTime($time);
                    $result->setRace($race);
                    $result->setAgeCategory($ageCategory);
                    $result->setCreatedAt($currentTime);
                    $entityManager->persist($result);
                    $entityManager->flush();

                    $raceDate = DateTimeImmutable::createFromFormat('d.m.Y.', $date);
                    if ($raceDate) {
                        $race->setTitle($title);
                        if ($raceDate) {
                            $race->setDate($raceDate);
                        } else {
                            $race->setDate(DateTimeImmutable::createFromFormat('U', time()));
                        }
                        $entityManager->persist($race);
                        $entityManager->flush();
                    }


                }

            }

            $raceResults = $doctrine->getManager()
                ->getRepository(RaceResult::class)
                ->findBy(['distance' => 'long', 'race' => $race->getId()], ['time' => 'ASC']);
            $ageCategoryPlacements = [];

            foreach ($raceResults as $key => $raceResult) {
                $raceResult->setOverallPlacement($key + 1);

                $ageCategory = $raceResult->getAgeCategory();
                if (!isset($ageCategoryPlacements[$ageCategory])) {
                    $ageCategoryPlacements[$ageCategory] = 1;
                } else {
                    $ageCategoryPlacements[$ageCategory]++;
                }

                $raceResult->setAgeCategoryPlacement($ageCategoryPlacements[$ageCategory]);

                $entityManager->persist($raceResult);
            }
            $entityManager->flush();


            $longRaceResults = $doctrine->getManager()
                ->getRepository(RaceResult::class)
                ->findBy(['distance' => 'long', 'race' => $race->getId()], ['time' => 'ASC']);
            if (!empty($longRaceResults)) {
                $longRaceTimes = array_map(function ($longRaceResult) {
                    return $longRaceResult->getTime();
                }, $longRaceResults);
                $averageLongFinishTime = $this->calculateAverageTime($longRaceTimes);
                $race->setAverageLongTime(DateTime::createFromFormat('H:i:s', $averageLongFinishTime));
                $entityManager->persist($race);
                $entityManager->flush();

            }

            $mediumRaceResults = $doctrine->getManager()
                ->getRepository(RaceResult::class)
                ->findBy(['distance' => 'medium', 'race' => $race->getId()]);
            if (!empty($mediumRaceResults)) {
                $mediumRaceTimes = array_map(function ($mediumRaceResult) {
                    return $mediumRaceResult->getTime();
                }, $mediumRaceResults);
                $mediumAverageFinishTime = $this->calculateAverageTime($mediumRaceTimes);
                $race->setAverageMediumTime(DateTime::createFromFormat('H:i:s', $mediumAverageFinishTime));
                $entityManager->persist($race);
                $entityManager->flush();
            }



            $session->getFlashBag()->add('success', 'CSV file was successfully imported.');


            return new RedirectResponse($this->generateUrl('app_csv_import'));

        }


    }

    function calculateAverageTime(array $raceTimes): string
    {
        $numResults = count($raceTimes);

        $totalSeconds = array_reduce($raceTimes, function ($carry, $raceTime) {
            $timeParts = explode(':', $raceTime);
            $hours = (int) $timeParts[0];
            $minutes = (int) $timeParts[1];
            $seconds = (int) $timeParts[2];

            return $carry + ($hours * 3600) + ($minutes * 60) + $seconds;
        }, 0);

        $averageSeconds = $totalSeconds / $numResults;
        $averageTime = gmdate('H:i:s', $averageSeconds);

        return $averageTime;
    }
}
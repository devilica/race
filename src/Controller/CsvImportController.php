<?php

namespace App\Controller;

use App\Entity\RaseResult;
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
use Knp\Component\Pager\PaginatorInterface;
use DateTimeImmutable;


class CsvImportController extends AbstractController
{


    #[Route('/', name: 'app_csv_import')]
    public function index(Request $request, ManagerRegistry $doctrine)
    {
        $form = $this->createForm(CsvImportType::class);

        $raceResults = $doctrine->getManager()
            ->getRepository(RaseResult::class)
            ->findBy(['distance' => 'long'], ['time' => 'ASC']);

        $totalFinishTime = 0;
        $numberOfResults = count($raceResults);

        foreach ($raceResults as $raceResult) {
            $finishTime = $raceResult->getTime();
            $totalFinishTime += $finishTime->getTimestamp();
        }

        $averageFinishTime = $numberOfResults > 0 ? round($totalFinishTime / $numberOfResults) : 0;

        $allRaceResults = $doctrine->getManager()
            ->getRepository(RaseResult::class)
            ->findAll();

        /*$pagination = $paginator->paginate(
            $allRaceResults,
            $request->query->getInt('page', 1),
            10
        );*/

        $mediumRaceResults = $doctrine->getManager()
            ->getRepository(RaseResult::class)
            ->findBy(['distance' => 'medium']);

        $mediumTotalFinishTime = 0;
        $mediumNumberOfResults = count($mediumRaceResults);

        foreach ($mediumRaceResults as $raceResult) {
            $finishTime = $raceResult->getTime();
            $mediumTotalFinishTime += $finishTime->getTimestamp();
        }

        $mediumAverageFinishTime = $mediumNumberOfResults > 0 ? round($mediumTotalFinishTime / $mediumNumberOfResults) : 0;



        return $this->render('csv_import/index.html.twig', [
            'form' => $form->createView(),
            'raceResults' => $raceResults,
            'averageFinishTime' => $averageFinishTime,
            'allRaceResults' => $allRaceResults,
            //'pagination' => $pagination,
            'mediumAverageFinishTime' => $mediumAverageFinishTime,

        ]);
    }

    public function importAction(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, SessionInterface $session)
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();
            $csvReader = Reader::createFromPath($csvFile->getPathname());
            $csvReader->setHeaderOffset(0);

            $records = $csvReader->getRecords();
            foreach ($records as $record) {
                $fullName = $record['fullName'];
                $distance = $record['distance'];
                $timeString = $record['time'];
                $ageCategory = $record['ageCategory'];

                $currentTime = DateTimeImmutable::createFromFormat('U', time());

                $time = DateTime::createFromFormat('H:i:s', $timeString);

                $errors = [];
                if (empty($fullName)) {
                    $errors[] = "Full Name is required in csv.";
                }

                if (!in_array($distance, ['long', 'medium'])) {
                    $errors[] = "Invalid distance value, it can be long or medium.";
                }

                if (empty($timeString)) {
                    $errors[] = "Time is required.";
                }

                if (empty($ageCategory)) {
                    $errors[] = "Age category is required.";
                }

                if (empty($errors)) {

                    $result = new RaseResult();
                    $result->setFullName($fullName);
                    $result->setDistance($distance);
                    $result->setTime($time);
                    $result->setAgeCategory($ageCategory);
                    $result->setCreatedAt($currentTime);
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($result);
                    $entityManager->flush();
                }

            }

            $raceResults = $doctrine->getManager()
                ->getRepository(RaseResult::class)
                ->findBy(['distance' => 'long'], ['time' => 'ASC']);
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


            $session->getFlashBag()->add('success', 'CSV file was successfully imported.');


            return new RedirectResponse($this->generateUrl('app_csv_import'));

        }

    }
}
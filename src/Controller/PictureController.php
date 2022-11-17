<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PictureController extends AbstractController
{
    #[Route('/picture', name: 'app_picture')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);
    }

    #[Route('api/pictures/{idPicture}', name: "pictures.get", methods: ['GET'])]
    public function getPicture(
        int $idPicture,
        Request $request,
        SerializerInterface $serializer,
        PictureRepository $repository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $picture = $repository->find($idPicture);
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace("/assets", "assets", $relativePath);

        if ($picture) {
            return new JsonResponse($serializer->serialize($picture, 'json', ["groups" => "getPicture"]),
                Response::HTTP_OK, ["Location" => $location], true);
        } else {
            return new JsonResponse(null);
        }
    }

    #[Route('api/pictures', name: 'picture_create', methods: ['POST'])]
    public function createPicture(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $picture = new Picture();
        $files = $request->files->get('file');
        $picture->setFile($files);
        $picture->setMimeType($files->getClientMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $picture->setPublicPath("/assets/pictures");
        $picture->setStatus("1");

        $entityManager->persist($picture);
        $entityManager->flush();

        $location = $urlGenerator->generate("pictures.get", ['idPicture' => $picture->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonShop = $serializer->serialize($picture, 'json', ['groups' => 'getShop']);
        return new JsonResponse($jsonShop, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}

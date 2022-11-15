<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Context\SerializerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface as SerializerSerializerInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CategoryController.php',
        ]);
    }

    #[Route('/api/categorys', name: 'categorys.getAll', methods: ['GET'])]
    public function getAllcategorys(
        CategoryRepository $repository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;
        // return $this->json($repository->findCategories($page, $limit), 200, [], ['groups' => 'getAllcategorys']);

        $idCache = 'getAllCategorys';
        $context = SerializationContext::create()->setGroups(["getAllCategorys"]);

        $jsonCategory = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('CategoryCache');


            $category = $repository->findAll();
        return $serializer->serialize($category, 'json', $context /*['groups' => 'getAllCategorys']*/);

        } );
        return new JsonResponse($jsonCategory, 200, [], true);
    }

    #[Route('/api/category/{idCategory}', name: 'categorys.getCategory', methods: ['GET'])]
    #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\Category')]
    public function getCategory(
        Category $category,
        CategoryRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getCategory';
        $jsonCategory = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $category) {
            $item->tag("getCategory");
            $context = SerializationContext::create()->setGroups('getCategory');

            $categories = $repository->find($category);
            return $serializer->serialize($categories, 'json', $context);
        });

        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
    }

    #[Route('/api/category/{idCategory}', name: 'categorys.deleteCategory', methods: ['DELETE'])]
    #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\category')]
    public function deleteCategory(
        Category $category,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        // $cache->invalidateTags{["categoryCache"]};

        $entityManager->remove($category);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/category', name: '$category.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $updateCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json');
        $category->setName($updateCategory->getName() ? $updateCategory->getName() : $category->getName());
        $category->setType($updateCategory->getType() ? $updateCategory->getType() : $category->getType());
        $category->setStatus("1");

        $errors = $validator->validate($category);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate("categorys.getCategory", ['idCategory' => $category->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonCategory = $serializer->serialize($category, 'json', ['groups' => 'getCategory']);
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // update route
    #[Route('/api/category/{id}', name: 'Category.update', methods: ['PUT'])]
    // #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\Category')]
    public function updateCategory(
        Category $Category,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        CategoryRepository $categoryRepository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        // $Category = $serializer->deserialize(
        //     $request->getContent(),
        //     Category::class,
        //     'json',
        //     [AbstractNormalizer::OBJECT_TO_POPULATE => $Category]
        // );

        $updateCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json'
        );

        $Category->setName($updateCategory->getName() ? $updateCategory->getName() : $Category->getName());
        $Category->setType($updateCategory->getType() ? $updateCategory->getType() : $Category->getType());

        $Category->setStatus("1");

        $content = $request->toArray();
        $id = $content['idCategory'];

        $entityManager->persist($Category);
        $entityManager->flush();

        $location = $urlGenerator->generate("categorys.getCategory", ['idCategory' => $Category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(["getAllCategorys"]);

        $jsonBoutique = $serializer->serialize($Category, 'json', $context /*['groups' => 'getAllCategorys']*/);
        return new JsonResponse($jsonBoutique, JsonResponse::HTTP_CREATED, ['$location' => ''], true);
    }
}
